<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use DB;

class UnlistedLocationsController extends Controller
{
    public function index(Request $request)
    {
        // Get all employees with their department names
        $allEmployees = DB::connection('intra_payroll')
            ->table('tbl_employee as e')
            ->leftJoin('tbl_department as d', 'e.department', '=', 'd.id')
            ->select('e.bio_id', 'e.first_name', 'e.last_name', 'e.department', 'd.department as department_name')
            ->get();

        // Extract unique department names
        $departments = $allEmployees
            ->pluck('department_name')
            ->filter(function($dept) { return !empty($dept); })
            ->unique()
            ->sort()
            ->values();

        // Get all employees for filtering with names
        $employees = $allEmployees
            ->map(function($emp) {
                return (object) [
                    'bio_id' => $emp->bio_id,
                    'first_name' => $emp->first_name,
                    'last_name' => $emp->last_name,
                    'department' => $emp->department_name ?? $emp->department
                ];
            })
            ->sortBy('first_name')
            ->values();

        return view('unlisted_locations.index', compact('departments', 'employees'));
    }
    
    public function debugApiCall(Request $request)
    {
        try {
            \Log::info('=== DEBUG API CALL ===');
            \Log::info('Attempting to get all locations...');
            
            $allLocations = $this->getAllLocations();
            \Log::info('Success! Got ' . count($allLocations) . ' locations');
            
            if (count($allLocations) > 0) {
                \Log::info('First location sample: ' . json_encode($allLocations[0]));
            }
            
            return response()->json([
                'status' => 'success',
                'message' => 'API is working',
                'location_count' => count($allLocations),
                'sample' => count($allLocations) > 0 ? $allLocations[0] : null
            ]);
        } catch (\Throwable $e) {
            \Log::error('DEBUG ERROR: ' . $e->getMessage());
            \Log::error('Stack: ' . $e->getTraceAsString());
            
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }
    
    public function getUnlistedLocationsData(Request $request)
    {
        $draw = (int)$request->get('draw', 1);
        
        try {
            $start = (int)$request->get('start', 0);
            $length = (int)$request->get('length', 10);
            $search = $request->get('search', []);
            $searchValue = isset($search['value']) ? (string)$search['value'] : '';
            
            // Get all locations
            $allLocations = $this->getAllLocations();
            \Log::info('Total locations fetched: ' . count($allLocations));
            
            // Get listed location names to filter them out
            $listedLocationNames = DB::table('tbl_listed_locations')
                ->distinct('location')
                ->pluck('location')
                ->toArray();
            
            \Log::info('Total listed locations: ' . count($listedLocationNames));
            
            // Filter out listed locations
            $unlistedLocations = collect($allLocations)->filter(function($location) use ($listedLocationNames) {
                return !in_array($location['location'], $listedLocationNames);
            })->values();
            
            // Get total records before search
            $totalRecords = $unlistedLocations->count();
            
            // Apply search
            if (!empty($searchValue)) {
                $unlistedLocations = $unlistedLocations->filter(function($location) use ($searchValue) {
                    $searchLower = strtolower($searchValue);
                    $bioId = isset($location['bio_id']) ? strtolower((string)$location['bio_id']) : '';
                    $empName = isset($location['employee_name']) ? strtolower((string)$location['employee_name']) : '';
                    $loc = isset($location['location']) ? strtolower((string)$location['location']) : '';
                    
                    return 
                        strpos($bioId, $searchLower) !== false ||
                        strpos($empName, $searchLower) !== false ||
                        strpos($loc, $searchLower) !== false;
                })->values();
            }
            
            // Get filtered records after search
            $filteredRecords = $unlistedLocations->count();
            
            // Paginate
            $data = [];
            foreach ($unlistedLocations->slice($start, $length) as $location) {
                try {
                    $fullLocation = isset($location['location']) ? (string)$location['location'] : '';
                    $truncatedLocation = substr($fullLocation, 0, 50) . (strlen($fullLocation) > 50 ? '...' : '');
                    
                    // Handle Unix timestamp or datetime string from phone_timestamp
                    $timestamp = 'N/A';
                    if (isset($location['phone_timestamp']) && !empty($location['phone_timestamp'])) {
                        try {
                            $ts = $location['phone_timestamp'];
                            // Check if it's a Unix timestamp (numeric and reasonable length)
                            if (is_numeric($ts) && strlen((string)$ts) == 10) {
                                $timestamp = \Carbon\Carbon::createFromTimestamp((int)$ts)->format('M d, Y H:i');
                            } else {
                                $timestamp = \Carbon\Carbon::parse((string)$ts)->format('M d, Y H:i');
                            }
                        } catch (\Throwable $dateErr) {
                            \Log::warning('Date parse error: ' . $dateErr->getMessage());
                            $timestamp = 'N/A';
                        }
                    }
                    
                    $data[] = [
                        'bio_id' => isset($location['bio_id']) ? (string)$location['bio_id'] : 'UNKNOWN',
                        'employee_name' => isset($location['employee_name']) ? (string)$location['employee_name'] : 'Unknown Employee',
                        'location' => $truncatedLocation,
                        'date_time' => $timestamp,
                        'action' => $this->getUnlistedAction($location),
                    ];
                } catch (\Throwable $rowError) {
                    \Log::warning('Error processing location row: ' . $rowError->getMessage());
                    continue;
                }
            }
            
            $responseData = [
                'draw' => $draw,
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $data,
            ];
            
            // Safely encode to JSON, handling invalid UTF-8 characters
            $jsonOptions = JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE;
            $jsonString = json_encode($responseData, $jsonOptions);
            
            if ($jsonString === false) {
                \Log::error('JSON encode failed: ' . json_last_error_msg());
                $jsonString = json_encode([
                    'draw' => $draw,
                    'recordsTotal' => $totalRecords,
                    'recordsFiltered' => $filteredRecords,
                    'data' => []
                ], $jsonOptions);
            }
            
            \Log::info('Returning ' . count($data) . ' records');
            
            return response($jsonString, 200, ['Content-Type' => 'application/json; charset=utf-8']);
            
        } catch (\Throwable $e) {
            \Log::error('CRITICAL Error in getUnlistedLocationsData: ' . $e->getMessage());
            \Log::error('File: ' . $e->getFile() . ':' . $e->getLine());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            $errorResponse = [
                'draw' => $draw,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => []
            ];
            
            $jsonString = json_encode($errorResponse, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
            return response($jsonString, 200, ['Content-Type' => 'application/json; charset=utf-8']);
        }
    }
    
    private function getUnlistedAction($location)
    {
        try {
            $bioId = isset($location['bio_id']) ? (string)$location['bio_id'] : '';
            $empName = isset($location['employee_name']) ? (string)$location['employee_name'] : '';
            $locStr = isset($location['location']) ? (string)$location['location'] : '';
            
            // Sanitize all values to ensure UTF-8
            $bioId = $this->sanitizeUtf8($bioId);
            $empName = $this->sanitizeUtf8($empName);
            $locStr = $this->sanitizeUtf8($locStr);
            
            $viewBtn = htmlspecialchars('<i class="fas fa-eye"></i>', ENT_QUOTES, 'UTF-8');
            $checkBtn = htmlspecialchars('<i class="fas fa-check"></i>', ENT_QUOTES, 'UTF-8');
            $routeUrl = route('mark_as_listed');
            $csrfField = csrf_field();
            
            return '<button class="btn btn-sm btn-info view-location-btn" data-bio-id="' . htmlspecialchars($bioId, ENT_QUOTES, 'UTF-8') . '" data-employee-name="' . htmlspecialchars($empName, ENT_QUOTES, 'UTF-8') . '" data-location="' . htmlspecialchars($locStr, ENT_QUOTES, 'UTF-8') . '" title="View Details"><i class="fas fa-eye"></i></button> <form action="' . htmlspecialchars($routeUrl, ENT_QUOTES, 'UTF-8') . '" method="POST" style="display:inline;">' . $csrfField . '<input type="hidden" name="bio_id" value="' . htmlspecialchars($bioId, ENT_QUOTES, 'UTF-8') . '"><input type="hidden" name="employee_name" value="' . htmlspecialchars($empName, ENT_QUOTES, 'UTF-8') . '"><input type="hidden" name="location" value="' . htmlspecialchars($locStr, ENT_QUOTES, 'UTF-8') . '"><button type="submit" class="btn btn-sm btn-success" title="Mark as Listed Location"><i class="fas fa-check"></i></button></form>';
        } catch (\Throwable $e) {
            \Log::warning('Error generating action HTML: ' . $e->getMessage());
            return '<span class="text-muted">N/A</span>';
        }
    }
    
    public function getListedLocationsData(Request $request)
    {
        $draw = (int)$request->get('draw', 1);
        
        try {
            $start = (int)$request->get('start', 0);
            $length = (int)$request->get('length', 10);
            $search = $request->get('search', []);
            $searchValue = $search['value'] ?? '';
            
            $listedLocations = DB::table('tbl_listed_locations')
                ->orderByDesc('date_listed')
                ->get();
            
            // Convert to collection for filtering
            $listedLocations = collect($listedLocations);
            
            // Get total records before search
            $totalRecords = $listedLocations->count();
            
            // Apply search
            if (!empty($searchValue)) {
                $listedLocations = $listedLocations->filter(function($location) use ($searchValue) {
                    $searchLower = strtolower($searchValue);
                    return 
                        strpos(strtolower($this->sanitizeUtf8($location->bio_id ?? '')), $searchLower) !== false ||
                        strpos(strtolower($this->sanitizeUtf8($location->employee_name ?? '')), $searchLower) !== false ||
                        strpos(strtolower($this->sanitizeUtf8($location->location ?? '')), $searchLower) !== false;
                })->values();
            }
            
            // Get filtered records after search
            $filteredRecords = $listedLocations->count();
            
            $data = [];
            foreach ($listedLocations->slice($start, $length) as $location) {
                try {
                    $fullLocation = $this->sanitizeUtf8($location->location ?? '');
                    $truncatedLocation = substr($fullLocation, 0, 50) . (strlen($fullLocation) > 50 ? '...' : '');
                    
                    $data[] = [
                        'bio_id' => $this->sanitizeUtf8($location->bio_id ?? 'UNKNOWN'),
                        'employee_name' => $this->sanitizeUtf8($location->employee_name ?? 'Unknown'),
                        'location' => $truncatedLocation,
                        'date_listed' => \Carbon\Carbon::parse($location->date_listed)->format('M d, Y H:i'),
                        'action' => $this->getListedAction($location),
                    ];
                } catch (\Exception $rowError) {
                    \Log::warning('Error processing listed location row: ' . $rowError->getMessage());
                    continue;
                }
            }
            
            $responseData = [
                'draw' => $draw,
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $data,
            ];

            // Safely encode to JSON, handling invalid UTF-8 characters
            $jsonOptions = JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE;
            $jsonString = json_encode($responseData, $jsonOptions);
            
            if ($jsonString === false) {
                $jsonString = json_encode([
                    'draw' => $draw,
                    'recordsTotal' => $totalRecords,
                    'recordsFiltered' => $filteredRecords,
                    'data' => []
                ], $jsonOptions);
            }

            return response($jsonString, 200, ['Content-Type' => 'application/json; charset=utf-8']);
        } catch (\Throwable $e) {
            \Log::error('Error in getListedLocationsData: ' . $e->getMessage() . ' ' . $e->getTraceAsString());
            $errorResponse = [
                'draw' => $draw,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => []
            ];
            $jsonString = json_encode($errorResponse, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
            return response($jsonString, 200, ['Content-Type' => 'application/json; charset=utf-8']);
        }
    }
    
    private function getListedAction($location)
    {
        try {
            $bioId = isset($location->bio_id) ? (string)$location->bio_id : '';
            $empName = isset($location->employee_name) ? (string)$location->employee_name : '';
            $locStr = isset($location->location) ? (string)$location->location : '';
            $id = isset($location->id) ? (int)$location->id : 0;
            
            // Sanitize all values to ensure UTF-8
            $bioId = $this->sanitizeUtf8($bioId);
            $empName = $this->sanitizeUtf8($empName);
            $locStr = $this->sanitizeUtf8($locStr);
            
            $routeUrl = route('remove_listed', $id);
            $csrfField = csrf_field();
            $methodField = method_field('DELETE');
            
            return '<button class="btn btn-sm btn-info view-location-btn" data-bio-id="' . htmlspecialchars($bioId, ENT_QUOTES, 'UTF-8') . '" data-employee-name="' . htmlspecialchars($empName, ENT_QUOTES, 'UTF-8') . '" data-location="' . htmlspecialchars($locStr, ENT_QUOTES, 'UTF-8') . '" title="View Details"><i class="fas fa-eye"></i></button> <form action="' . htmlspecialchars($routeUrl, ENT_QUOTES, 'UTF-8') . '" method="POST" style="display:inline;" onsubmit="return confirm(\'Are you sure?\');">' . $csrfField . $methodField . '<button type="submit" class="btn btn-sm btn-danger" title="Remove from Listed"><i class="fas fa-trash"></i> Remove</button></form>';
        } catch (\Throwable $e) {
            \Log::warning('Error generating listed action HTML: ' . $e->getMessage());
            return '<span class="text-muted">N/A</span>';
        }
    }
    
    private function getAllLocations()
    {
        $result = [];
        
        try {
            // Get ALL locations from tbl_entries (face_db) - NO deduplication
            try {
                $faceLocations = DB::connection('face_db')
                    ->table('tbl_entries')
                    ->select('biometric_id', 'location', 'phone_timestamp')
                    ->whereNotNull('location')
                    ->where('location', '!=', '')
                    ->where('location', '!=', 'Error fetching location')
                    ->orderByDesc('phone_timestamp')
                    ->get();
                
                foreach ($faceLocations as $location) {
                    $result[] = [
                        'bio_id' => $this->sanitizeUtf8($location->biometric_id ?? ''),
                        'location' => $this->sanitizeUtf8($location->location ?? ''),
                        'phone_timestamp' => $location->phone_timestamp,
                        'source' => 'face_db'
                    ];
                }
                \Log::info('Face DB locations loaded: ' . count($faceLocations));
            } catch (\Throwable $e) {
                \Log::error('Error fetching from tbl_entries (face_db): ' . $e->getMessage());
            }
            
            // Get ALL locations from tbl_raw_logs (intra_payroll) - NO deduplication
            try {
                $prlLocations = DB::connection('intra_payroll')
                    ->table('tbl_raw_logs')
                    ->select('biometric_id', 'location', 'logs')
                    ->whereNotNull('location')
                    ->where('location', '!=', '')
                    ->where('location', '!=', 'Error fetching location')
                    ->orderByDesc('logs')
                    ->get();
                
                foreach ($prlLocations as $location) {
                    $result[] = [
                        'bio_id' => $this->sanitizeUtf8($location->biometric_id ?? ''),
                        'location' => $this->sanitizeUtf8($location->location ?? ''),
                        'phone_timestamp' => $location->logs,
                        'source' => 'intra_payroll'
                    ];
                }
                \Log::info('Intra payroll locations loaded: ' . count($prlLocations));
            } catch (\Throwable $e) {
                \Log::error('Error fetching from tbl_raw_logs (intra_payroll): ' . $e->getMessage());
            }
            
            // Cache employee names to avoid repeated DB queries
            $employeeCache = [];
            
            // Now add employee names to all locations (keep ALL records, no deduplication)
            $finalResult = [];
            foreach ($result as $location) {
                try {
                    $bioId = $location['bio_id'] ?? 'UNKNOWN';
                    
                    // Get employee name from cache or fetch from DB
                    if (!isset($employeeCache[$bioId])) {
                        try {
                            $employee = DB::connection('intra_payroll')
                                ->table('tbl_employee')
                                ->where('bio_id', $bioId)
                                ->first(['first_name', 'last_name']);
                            
                            if ($employee) {
                                $firstName = $this->sanitizeUtf8($employee->first_name ?? '');
                                $lastName = $this->sanitizeUtf8($employee->last_name ?? '');
                                $employeeCache[$bioId] = trim($firstName . ' ' . $lastName) ?: ('Bio ID: ' . $bioId);
                            } else {
                                $employeeCache[$bioId] = 'Bio ID: ' . $bioId;
                            }
                        } catch (\Throwable $e) {
                            \Log::warning('Error fetching employee with bio_id ' . $bioId . ': ' . $e->getMessage());
                            $employeeCache[$bioId] = 'Bio ID: ' . $bioId . ' (deleted)';
                        }
                    }
                    
                    $finalResult[] = [
                        'bio_id' => $bioId,
                        'employee_name' => $employeeCache[$bioId] ?? 'Unknown',
                        'location' => $location['location'] ?? '',
                        'phone_timestamp' => $location['phone_timestamp'] ?? null
                    ];
                } catch (\Throwable $e) {
                    \Log::warning('Error processing location entry: ' . $e->getMessage());
                    continue;
                }
            }
            
            \Log::info('Final locations result count: ' . count($finalResult));
            return $finalResult;
            
        } catch (\Throwable $e) {
            \Log::error('Critical error in getAllLocations: ' . $e->getMessage());
            return [];
        }
    }
    
    private function sanitizeUtf8($string)
    {
        if (is_null($string)) {
            return null;
        }

        if (!is_string($string)) {
            return (string)$string;
        }

        // Remove invalid UTF-8 sequences
        $string = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $string);
        
        // If the string is valid UTF-8, return it
        if (mb_check_encoding($string, 'UTF-8')) {
            return $string;
        }

        // Try to detect and convert from common encodings
        $detectedEncoding = mb_detect_encoding($string, ['UTF-8', 'ISO-8859-1', 'Windows-1252', 'UTF-16'], true);
        
        if ($detectedEncoding && $detectedEncoding !== 'UTF-8') {
            return mb_convert_encoding($string, 'UTF-8', $detectedEncoding);
        }

        // Last resort: force conversion and remove invalid characters
        return iconv('UTF-8', 'UTF-8//IGNORE', $string);
    }
    
    public function markAsListed(Request $request)
    {
        $request->validate([
            'bio_id' => 'required|string',
            'employee_name' => 'required|string',
            'location' => 'required|string',
        ]);
        
        try {
            DB::table('tbl_listed_locations')->insert([
                'bio_id' => $request->bio_id,
                'employee_name' => $request->employee_name,
                'location' => $request->location,
                'date_listed' => now(),
                'listed_by' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            return redirect()->route('unlisted_locations')->with('success', 'Location marked as listed successfully.');
        } catch (\Exception $e) {
            return redirect()->route('unlisted_locations')->with('error', 'Error marking location as listed: ' . $e->getMessage());
        }
    }
    
    public function removeListed($id)
    {
        try {
            DB::table('tbl_listed_locations')->where('id', $id)->delete();
            return redirect()->route('unlisted_locations')->with('success', 'Location removed from listed.');
        } catch (\Exception $e) {
            return redirect()->route('unlisted_locations')->with('error', 'Error removing location: ' . $e->getMessage());
        }
    }

    public function report()
    {
        // Get all employees with their department names
        $allEmployees = DB::connection('intra_payroll')
            ->table('tbl_employee as e')
            ->leftJoin('tbl_department as d', 'e.department', '=', 'd.id')
            ->select('e.bio_id', 'e.first_name', 'e.last_name', 'e.department', 'd.department as department_name')
            ->get();

        // Extract unique department names
        $departments = $allEmployees
            ->pluck('department_name')
            ->filter(function($dept) { return !empty($dept); })
            ->unique()
            ->sort()
            ->values();

        // Get all employees for filtering with names
        $employees = $allEmployees
            ->map(function($emp) {
                return (object) [
                    'bio_id' => $emp->bio_id,
                    'first_name' => $emp->first_name,
                    'last_name' => $emp->last_name,
                    'department' => $emp->department_name ?? $emp->department
                ];
            })
            ->sortBy('first_name')
            ->values();

        return view('unlisted_locations.report', compact('departments', 'employees'));
    }

    public function getLowAttendanceData(Request $request)
    {
        try {
            $draw = $request->get('draw', 1);
            $start = $request->get('start', 0);
            $length = $request->get('length', 10);
            $department = $request->get('department', '');
            $bioId = $request->get('bio_id', '');
            
            // Get all locations
            $allLocations = $this->getAllLocations();
            
            // Group by employee and date
            $groupedByEmployeeDate = [];
            foreach ($allLocations as $location) {
                // Parse the timestamp
                $date = 'Unknown';
                if (!empty($location['phone_timestamp'])) {
                    try {
                        if (is_numeric($location['phone_timestamp']) && strlen($location['phone_timestamp']) == 10) {
                            $date = \Carbon\Carbon::createFromTimestamp($location['phone_timestamp'])->format('Y-m-d');
                        } else {
                            $date = \Carbon\Carbon::parse($location['phone_timestamp'])->format('Y-m-d');
                        }
                    } catch (\Exception $e) {
                        // Keep as Unknown
                    }
                }
                
                $key = $location['bio_id'] . '_' . $date;
                
                if (!isset($groupedByEmployeeDate[$key])) {
                    $groupedByEmployeeDate[$key] = [
                        'bio_id' => $location['bio_id'],
                        'employee_name' => $location['employee_name'],
                        'date' => $date,
                        'department' => $this->getEmployeeDepartment($location['bio_id']),
                        'count' => 0,
                        'records' => []
                    ];
                }
                
                $groupedByEmployeeDate[$key]['count']++;
                $groupedByEmployeeDate[$key]['records'][] = $location;
            }
            
            // Filter for records with less than 5 per day
            $lowAttendance = array_filter($groupedByEmployeeDate, function($item) {
                return $item['count'] < 5;
            });
            
            // Apply filters
            if (!empty($department)) {
                $lowAttendance = array_filter($lowAttendance, function($item) use ($department) {
                    return $item['department'] === $department;
                });
            }
            
            if (!empty($bioId)) {
                $lowAttendance = array_filter($lowAttendance, function($item) use ($bioId) {
                    return $item['bio_id'] === $bioId;
                });
            }
            
            // Sort by date descending
            usort($lowAttendance, function($a, $b) {
                return strcmp($b['date'], $a['date']);
            });
            
            $totalRecords = count($lowAttendance);
            
            // Paginate
            $data = [];
            foreach (array_slice($lowAttendance, $start, $length) as $item) {
                $data[] = [
                    'bio_id' => $item['bio_id'],
                    'employee_name' => $item['employee_name'],
                    'department' => $item['department'] ?? 'N/A',
                    'date' => $item['date'],
                    'count' => $item['count'],
                    'records' => count($item['records']),
                    'action' => $this->getLowAttendanceAction($item),
                ];
            }
            
            $responseData = [
                'draw' => $draw,
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $totalRecords,
                'data' => $data,
            ];
            
            // Safely encode to JSON
            $jsonOptions = JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE;
            $jsonString = json_encode($responseData, $jsonOptions);
            
            if ($jsonString === false) {
                $jsonString = json_encode([
                    'draw' => $draw,
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => []
                ], $jsonOptions);
            }
            
            return response($jsonString, 200, ['Content-Type' => 'application/json; charset=utf-8']);
        } catch (\Exception $e) {
            \Log::error('Error in getLowAttendanceData: ' . $e->getMessage() . ' ' . $e->getTraceAsString());
            $errorResponse = [
                'draw' => $request->get('draw', 1),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => []
            ];
            $jsonString = json_encode($errorResponse, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
            return response($jsonString, 200, ['Content-Type' => 'application/json; charset=utf-8']);
        }
    }

    private function getLowAttendanceAction($item)
    {
        return '
            <button class="btn btn-sm btn-success export-record-btn" 
                data-bio-id="' . htmlspecialchars($item['bio_id']) . '" 
                data-employee-name="' . htmlspecialchars($item['employee_name']) . '" 
                data-department="' . htmlspecialchars($item['department']) . '"
                data-date="' . htmlspecialchars($item['date']) . '"
                title="Export This Record">
                <i class="fas fa-download"></i>
            </button>
        ';
    }

    private function getEmployeeDepartment($bioId)
    {
        static $cache = [];
        
        if (!isset($cache[$bioId])) {
            try {
                $employee = DB::connection('intra_payroll')
                    ->table('tbl_employee as e')
                    ->leftJoin('tbl_department as d', 'e.department', '=', 'd.id')
                    ->where('e.bio_id', $bioId)
                    ->first(['d.department as department_name', 'e.department']);
                
                if ($employee) {
                    $cache[$bioId] = $this->sanitizeUtf8($employee->department_name ?? $employee->department ?? 'N/A');
                } else {
                    $cache[$bioId] = 'N/A';
                }
            } catch (\Exception $e) {
                \Log::warning('Error fetching department for bio_id ' . $bioId . ': ' . $e->getMessage());
                $cache[$bioId] = 'N/A';
            }
        }
        
        return $cache[$bioId];
    }

    public function unlistedLocationsReport()
    {
        // Get all employees with their department names
        $allEmployees = DB::connection('intra_payroll')
            ->table('tbl_employee as e')
            ->leftJoin('tbl_department as d', 'e.department', '=', 'd.id')
            ->select('e.bio_id', 'e.first_name', 'e.last_name', 'e.department', 'd.department as department_name')
            ->get();

        // Extract unique department names
        $departments = $allEmployees
            ->pluck('department_name')
            ->filter(function($dept) { return !empty($dept); })
            ->unique()
            ->sort()
            ->values();

        // Get all employees for filtering with names
        $employees = $allEmployees
            ->map(function($emp) {
                return (object) [
                    'bio_id' => $emp->bio_id,
                    'first_name' => $emp->first_name,
                    'last_name' => $emp->last_name,
                    'department' => $emp->department_name ?? $emp->department
                ];
            })
            ->sortBy('first_name')
            ->values();

        return view('unlisted_locations.unlisted_report', compact('departments', 'employees'));
    }

    public function getUnlistedLocationsReportData(Request $request)
    {
        try {
            $draw = $request->get('draw', 1);
            $start = $request->get('start', 0);
            $length = $request->get('length', 10);
            $department = $request->get('department', '');
            $bioId = $request->get('bio_id', '');
            
            // Get all locations
            $allLocations = $this->getAllLocations();
            
            // Get listed location names to filter them out
            $listedLocationNames = DB::table('tbl_listed_locations')
                ->distinct('location')
                ->pluck('location')
                ->toArray();
            
            // Filter out listed locations (keep only unlisted)
            $unlistedLocations = collect($allLocations)->filter(function($location) use ($listedLocationNames) {
                return !in_array($location['location'], $listedLocationNames);
            })->values();
            
            // Apply department filter
            if (!empty($department)) {
                $unlistedLocations = $unlistedLocations->filter(function($location) use ($department) {
                    $empDept = $this->getEmployeeDepartment($location['bio_id']);
                    return $empDept === $department;
                })->values();
            }
            
            // Apply employee filter
            if (!empty($bioId)) {
                $unlistedLocations = $unlistedLocations->filter(function($location) use ($bioId) {
                    return $location['bio_id'] === $bioId;
                })->values();
            }
            
            // Get total and filtered records
            $totalRecords = $unlistedLocations->count();
            $filteredRecords = $unlistedLocations->count();
            
            $data = [];
            foreach ($unlistedLocations->slice($start, $length) as $location) {
                $fullLocation = $location['location'];
                $truncatedLocation = substr($fullLocation, 0, 50) . (strlen($fullLocation) > 50 ? '...' : '');
                
                // Handle Unix timestamp or datetime string
                $timestamp = 'N/A';
                if (isset($location['phone_timestamp']) && !empty($location['phone_timestamp'])) {
                    try {
                        $ts = $location['phone_timestamp'];
                        if (is_numeric($ts) && strlen($ts) == 10) {
                            $timestamp = \Carbon\Carbon::createFromTimestamp($ts)->format('M d, Y H:i');
                        } else {
                            $timestamp = \Carbon\Carbon::parse($ts)->format('M d, Y H:i');
                        }
                    } catch (\Exception $e) {
                        $timestamp = 'N/A';
                    }
                }
                
                $data[] = [
                    'bio_id' => $location['bio_id'],
                    'employee_name' => $location['employee_name'],
                    'location' => $truncatedLocation,
                    'full_location' => $fullLocation,
                    'date_time' => $timestamp,
                    'department' => $this->getEmployeeDepartment($location['bio_id']),
                ];
            }
            
            $responseData = [
                'draw' => $draw,
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $data,
            ];
            
            // Safely encode to JSON
            $jsonOptions = JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE;
            $jsonString = json_encode($responseData, $jsonOptions);
            
            if ($jsonString === false) {
                $jsonString = json_encode([
                    'draw' => $draw,
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => []
                ], $jsonOptions);
            }
            
            return response($jsonString, 200, ['Content-Type' => 'application/json; charset=utf-8']);
        } catch (\Exception $e) {
            \Log::error('Error in getUnlistedLocationsReportData: ' . $e->getMessage() . ' ' . $e->getTraceAsString());
            $errorResponse = [
                'draw' => $request->get('draw', 1),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => []
            ];
            $jsonString = json_encode($errorResponse, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
            return response($jsonString, 200, ['Content-Type' => 'application/json; charset=utf-8']);
        }
    }

    public function exportUnlistedLocations(Request $request)
    {
        $department = $request->get('department', '');
        $bioId = $request->get('bio_id', '');
        
        // Get all locations
        $allLocations = $this->getAllLocations();
        
        // Get listed location names to filter them out
        $listedLocationNames = DB::table('tbl_listed_locations')
            ->distinct('location')
            ->pluck('location')
            ->toArray();
        
        // Filter out listed locations (keep only unlisted)
        $unlistedLocations = collect($allLocations)->filter(function($location) use ($listedLocationNames) {
            return !in_array($location['location'], $listedLocationNames);
        })->values();
        
        // Apply department filter
        if (!empty($department)) {
            $unlistedLocations = $unlistedLocations->filter(function($location) use ($department) {
                $empDept = $this->getEmployeeDepartment($location['bio_id']);
                return $empDept === $department;
            })->values();
        }
        
        // Apply employee filter
        if (!empty($bioId)) {
            $unlistedLocations = $unlistedLocations->filter(function($location) use ($bioId) {
                return $location['bio_id'] === $bioId;
            })->values();
        }
        
        // Prepare data for Excel
        $exportData = [];
        $exportData[] = ['Bio ID', 'Employee Name', 'Department', 'Location', 'Date & Time'];
        
        foreach ($unlistedLocations as $location) {
            $timestamp = 'N/A';
            if (!empty($location['phone_timestamp'])) {
                try {
                    if (is_numeric($location['phone_timestamp']) && strlen($location['phone_timestamp']) == 10) {
                        $timestamp = \Carbon\Carbon::createFromTimestamp($location['phone_timestamp'])->format('M d, Y H:i');
                    } else {
                        $timestamp = \Carbon\Carbon::parse($location['phone_timestamp'])->format('M d, Y H:i');
                    }
                } catch (\Exception $e) {
                    $timestamp = 'N/A';
                }
            }
            
            $exportData[] = [
                $location['bio_id'],
                $location['employee_name'],
                $this->getEmployeeDepartment($location['bio_id']),
                $location['location'],
                $timestamp,
            ];
        }
        
        // Create and return Excel file
        $filename = 'unlisted_locations_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
        
        // Use Laravel Excel export
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\UnlistedLocationsExport($exportData),
            $filename
        );
    }

    public function exportLowAttendance(Request $request)
    {
        $department = $request->get('department', '');
        $bioId = $request->get('bio_id', '');
        
        // Get all locations
        $allLocations = $this->getAllLocations();
        
        // Group by employee and date
        $groupedByEmployeeDate = [];
        foreach ($allLocations as $location) {
            // Parse the timestamp
            $date = 'Unknown';
            if (!empty($location['phone_timestamp'])) {
                try {
                    if (is_numeric($location['phone_timestamp']) && strlen($location['phone_timestamp']) == 10) {
                        $date = \Carbon\Carbon::createFromTimestamp($location['phone_timestamp'])->format('Y-m-d');
                    } else {
                        $date = \Carbon\Carbon::parse($location['phone_timestamp'])->format('Y-m-d');
                    }
                } catch (\Exception $e) {
                    // Keep as Unknown
                }
            }
            
            $key = $location['bio_id'] . '_' . $date;
            
            if (!isset($groupedByEmployeeDate[$key])) {
                $groupedByEmployeeDate[$key] = [
                    'bio_id' => $location['bio_id'],
                    'employee_name' => $location['employee_name'],
                    'date' => $date,
                    'department' => $this->getEmployeeDepartment($location['bio_id']),
                    'count' => 0,
                    'records' => []
                ];
            }
            
            $groupedByEmployeeDate[$key]['count']++;
            $groupedByEmployeeDate[$key]['records'][] = $location;
        }
        
        // Filter for records with less than 5 per day
        $lowAttendance = array_filter($groupedByEmployeeDate, function($item) {
            return $item['count'] < 5;
        });
        
        // Apply filters
        if (!empty($department)) {
            $lowAttendance = array_filter($lowAttendance, function($item) use ($department) {
                return $item['department'] === $department;
            });
        }
        
        if (!empty($bioId)) {
            $lowAttendance = array_filter($lowAttendance, function($item) use ($bioId) {
                return $item['bio_id'] === $bioId;
            });
        }
        
        // Sort by date descending
        usort($lowAttendance, function($a, $b) {
            return strcmp($b['date'], $a['date']);
        });
        
        // Prepare data for Excel
        $exportData = [];
        $exportData[] = ['Bio ID', 'Employee Name', 'Department', 'Date', 'Record Count', 'Timestamp', 'Location'];
        
        foreach ($lowAttendance as $item) {
            foreach ($item['records'] as $record) {
                $timestamp = 'N/A';
                if (!empty($record['phone_timestamp'])) {
                    try {
                        if (is_numeric($record['phone_timestamp']) && strlen($record['phone_timestamp']) == 10) {
                            $timestamp = \Carbon\Carbon::createFromTimestamp($record['phone_timestamp'])->format('M d, Y H:i');
                        } else {
                            $timestamp = \Carbon\Carbon::parse($record['phone_timestamp'])->format('M d, Y H:i');
                        }
                    } catch (\Exception $e) {
                        $timestamp = 'N/A';
                    }
                }
                
                $exportData[] = [
                    $item['bio_id'],
                    $item['employee_name'],
                    $item['department'],
                    $item['date'],
                    $item['count'],
                    $timestamp,
                    $record['location']
                ];
            }
        }
        
        // Create CSV
        $filename = 'low_attendance_report_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
        $handle = fopen('php://memory', 'r+');
        foreach ($exportData as $line) {
            fputcsv($handle, $line);
        }
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);
        
        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
