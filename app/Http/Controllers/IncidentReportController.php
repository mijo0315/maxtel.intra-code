<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\IncidentReport;
use App\Models\NteNote;
use Illuminate\Support\Facades\Auth;

class IncidentReportController extends Controller
{
    /**
     * Display a listing of the incident reports.
     */
    public function index(Request $request)
    {
        // Get user permission for this page
        $routeName = 'incident_report';
        $userAccess = Auth::user()->access[$routeName] ?? null;
        $userPermission = $userAccess['access'] ?? null;
        
        // Check if user has read-only permission (value 3 = "R" only, without C or U)
        $isReadOnly = $userPermission === '3' || (preg_match("/R/i", $userPermission ?? '') && !preg_match("/C|U/i", $userPermission ?? ''));
        
        // Get search query from request
        $search = $request->input('search', '');
        
        $role_id = Auth::user()->role_id;
        $employee = null;
        
        // Filter employees based on role-based group management
        $query = Employee::where('tbl_employee.is_active', 1);
        $role_id_all = [1,4,5,27]; //all access
        
        if (in_array($role_id,$role_id_all)) {
            // Admin and role_id 27 see all employees
            $employees = $query->get();
        // } elseif ($role_id === 4) { // HR Group D
        //     $employees = $query->where(function ($q) {
        //         $q->where("tbl_employee.hr_group", "group_d")
        //         ->orWhere("tbl_employee.user_id", Auth::user()->id);
        //     })->get();
        // } elseif ($role_id === 5) { // HR Group B,C,E
        //     $employees = $query->where(function ($q) {
        //         $q->whereIn("tbl_employee.hr_group", ["group_b","group_c","group_e"])
        //         ->orWhere("tbl_employee.user_id", Auth::user()->id);
        //     })->get();
        } elseif ($role_id === 14) { // HR Group B,C
            $employees = $query->where(function ($q) {
                $q->whereIn("tbl_employee.hr_group", ["group_b","group_c"])
                ->orWhere("tbl_employee.user_id", Auth::user()->id);
            })->get();
        } elseif ($role_id === 15) { // HR Group C,E
            $employees = $query->where(function ($q) {
                $q->whereIn("tbl_employee.hr_group", ["group_c","group_e"])
                ->orWhere("tbl_employee.user_id", Auth::user()->id);
            })->get();
        } else {
            // For other roles, only show their own record
            $employees = $query->where("tbl_employee.user_id", Auth::user()->id)->get();
        }
        
        // Get position names for all employees
        foreach ($employees as $emp) {
            if ($emp->position_id) {
                try {
                    $position = \DB::connection('intra_payroll')->table('lib_position')
                        ->where('id', (int)$emp->position_id)
                        ->value('name');
                    if ($position) {
                        $emp->position_name = $position;
                    }
                } catch (\Exception $e) {
                    // Silently fail
                }
            }
        }
        
        // Get current user's employee record
        $currentEmployeeId = null;
        if (Auth::check()) {
            $currentEmployee = Employee::where('user_id', Auth::id())->first();
            $currentEmployeeId = $currentEmployee ? $currentEmployee->id : null;
        }
        
        // Fetch incident reports
        if ($isReadOnly && $currentEmployeeId) {
            // Staff sees only reports where they are the involved employee
            // Use JSON_CONTAINS to check if currentEmployeeId is in the name_involved JSON array
            $reportQuery = IncidentReport::whereRaw("JSON_CONTAINS(name_involved, '\"" . $currentEmployeeId . "\"')")
                ->with(['reportedByEmployee', 'disciplinaryNote']);
            
            // Apply search filter
            if ($search) {
                $reportQuery->where(function ($q) use ($search) {
                    $q->where('document_number', 'LIKE', "%$search%")
                      ->orWhere('incident_type', 'LIKE', "%$search%")
                      ->orWhere('location', 'LIKE', "%$search%");
                });
            }
            
            $myIncidentReports = $reportQuery->orderBy('date_time_report', 'desc')->paginate(15);
            $incidentReports = collect();
        } else {
            // Admin/Manager sees reports for employees in their managed groups
            $myIncidentReports = collect();
            
            $reportQuery = IncidentReport::with(['reportedByEmployee', 'disciplinaryNote']);
            
            // Filter by role-based groups
            if (in_array($role_id,$role_id_all)) {
                // Admin and role_id 27 see all reports
                $reportQuery = $reportQuery;
            // } elseif ($role_id === 4) { // HR Group D
            //     $reportQuery = $reportQuery->whereHas('reportedByEmployee', function($q) {
            //         $q->where(function($subQ) {
            //             $subQ->where("hr_group", "group_d")
            //             ->orWhere("user_id", Auth::user()->id);
            //         });
            //     });
            // } elseif ($role_id === 5) { // HR Group B,C,E
            //     $reportQuery = $reportQuery->whereHas('reportedByEmployee', function($q) {
            //         $q->where(function($subQ) {
            //             $subQ->whereIn("hr_group", ["group_b","group_c","group_e"])
            //             ->orWhere("user_id", Auth::user()->id);
            //         });
            //     });
            } elseif ($role_id === 14) { // HR Group B,C
                $reportQuery = $reportQuery->whereHas('reportedByEmployee', function($q) {
                    $q->where(function($subQ) {
                        $subQ->whereIn("hr_group", ["group_b","group_c"])
                        ->orWhere("user_id", Auth::user()->id);
                    });
                });
            } elseif ($role_id === 15) { // HR Group C,E
                $reportQuery = $reportQuery->whereHas('reportedByEmployee', function($q) {
                    $q->where(function($subQ) {
                        $subQ->whereIn("hr_group", ["group_c","group_e"])
                        ->orWhere("user_id", Auth::user()->id);
                    });
                });
            } else {
                // For other roles, only show reports they created
                $reportQuery = $reportQuery->where('reported_by', $currentEmployeeId);
            }
            
            // Apply search filter
            if ($search) {
                $reportQuery->where(function ($q) use ($search) {
                    $q->where('document_number', 'LIKE', "%$search%")
                      ->orWhere('incident_type', 'LIKE', "%$search%")
                      ->orWhere('location', 'LIKE', "%$search%")
                      ->orWhereHas('reportedByEmployee', function ($eq) use ($search) {
                          $eq->where('first_name', 'LIKE', "%$search%")
                            ->orWhere('last_name', 'LIKE', "%$search%");
                      });
                });
            }
            
            $incidentReports = $reportQuery->orderBy('date_time_report', 'desc')->paginate(15);
        }
        
        return view('incident_report.index', compact('employees', 'isReadOnly', 'incidentReports', 'myIncidentReports', 'employee', 'search'));
    }

    /**
     * Show the form for creating a new incident report.
     */
    public function create(Request $request)
    {
        $employee = null;
        $employees = [];
        
        // Get employee list for dropdown
        $role_id = Auth::user()->role_id;
        $query = Employee::where('tbl_employee.is_active', 1);
        
        if ($role_id === 1 || $role_id === 27) {
            $employees = $query->get();
        } elseif ($role_id === 4) {
            $employees = $query->where(function ($q) {
                $q->where("tbl_employee.hr_group", "group_d")
                ->orWhere("tbl_employee.user_id", Auth::user()->id);
            })->get();
        } elseif ($role_id === 5) {
            $employees = $query->where(function ($q) {
                $q->whereIn("tbl_employee.hr_group", ["group_b","group_c","group_e"])
                ->orWhere("tbl_employee.user_id", Auth::user()->id);
            })->get();
        } elseif ($role_id === 14) {
            $employees = $query->where(function ($q) {
                $q->whereIn("tbl_employee.hr_group", ["group_b","group_c"])
                ->orWhere("tbl_employee.user_id", Auth::user()->id);
            })->get();
        } elseif ($role_id === 15) {
            $employees = $query->where(function ($q) {
                $q->whereIn("tbl_employee.hr_group", ["group_c","group_e"])
                ->orWhere("tbl_employee.user_id", Auth::user()->id);
            })->get();
        } else {
            $employees = $query->where("tbl_employee.user_id", Auth::user()->id)->get();
        }
        
        // If employee_id is provided via query parameter, fetch and set it
        if ($request->has('employee_id') && $request->employee_id) {
            $employee = Employee::find($request->employee_id);
            // Get position name if position_id exists
            if ($employee && $employee->position_id) {
                try {
                    $position = \DB::connection('intra_payroll')->table('lib_position')
                        ->where('id', (int)$employee->position_id)
                        ->value('name');
                    if ($position) {
                        $employee->position_name = $position;
                    }
                } catch (\Exception $e) {
                    // Silently fail if position lookup fails
                }
            }
        }
        
        // Get position names for all employees
        foreach ($employees as $emp) {
            if ($emp->position_id) {
                try {
                    $position = \DB::connection('intra_payroll')->table('lib_position')
                        ->where('id', (int)$emp->position_id)
                        ->value('name');
                    if ($position) {
                        $emp->position_name = $position;
                    }
                } catch (\Exception $e) {
                    // Silently fail
                }
            }
        }
        
        return view('incident_report.create', compact('employees', 'employee'));
    }

    /**
     * Store a newly created incident report in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'reported_by' => 'required|integer|exists:tbl_employee,id',
            'position' => 'required|string|max:255',
            'date_time_report' => 'required|date_format:Y-m-d\TH:i',
            'incident_no' => 'nullable|string|max:255',
            'incident_type' => 'required|string|max:255',
            'date_incident' => 'required|date_format:Y-m-d\TH:i',
            'location' => 'required|string|max:255',
            'incident_description' => 'required|string',
            'name_involved' => 'required|array',
            'name_involved.*' => 'integer|exists:tbl_employee,id',
            'name_witness' => 'nullable|array',
            'name_witness.*' => 'integer|exists:tbl_employee,id',
            'recommended_action' => 'required|string',
            'disciplinary_note_id' => 'nullable|integer|exists:disciplinary_notes,id',
        ]);

        try {
            // Generate document number
            $validated['document_number'] = IncidentReport::generateDocumentNumber();

            // Convert involved array to JSON for storage
            if (!empty($validated['name_involved'])) {
                $validated['name_involved'] = json_encode($validated['name_involved']);
            } else {
                $validated['name_involved'] = null;
            }

            // Convert witness array to JSON for storage
            if (!empty($validated['name_witness'])) {
                $validated['name_witness'] = json_encode($validated['name_witness']);
            } else {
                $validated['name_witness'] = null;
            }

            // Create the incident report
            $incidentReport = IncidentReport::create($validated);

            return redirect()->route('incident-report.index')
                ->with('success', 'Incident report submitted successfully. Document No: ' . $incidentReport->document_number);
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error creating incident report: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified incident report.
     */
    public function show($id)
    {
        $report = IncidentReport::with(['reportedByEmployee', 'disciplinaryNote'])
            ->findOrFail($id);
        
        // Decode involved employee IDs and get their names
        $involvedNames = [];
        if ($report->name_involved) {
            $involvedIds = json_decode($report->name_involved, true);
            if (is_array($involvedIds)) {
                $involved = Employee::whereIn('id', $involvedIds)->get();
                $involvedNames = $involved->map(function($e) {
                    return $e->first_name . ' ' . $e->last_name;
                })->toArray();
            }
        }
        
        // Decode witness IDs and get their names
        $witnessNames = [];
        if ($report->name_witness) {
            $witnessIds = json_decode($report->name_witness, true);
            if (is_array($witnessIds)) {
                $witnesses = Employee::whereIn('id', $witnessIds)->get();
                $witnessNames = $witnesses->map(function($w) {
                    return $w->first_name . ' ' . $w->last_name;
                })->toArray();
            }
        }
        
        return response()->json([
            'id' => $report->id,
            'document_number' => $report->document_number,
            'incident_no' => $report->incident_no,
            'reported_by_name' => $report->reportedByEmployee ? $report->reportedByEmployee->first_name . ' ' . $report->reportedByEmployee->last_name : 'N/A',
            'position' => $report->position,
            'date_time_report' => $report->date_time_report,
            'incident_type' => $report->incident_type,
            'date_incident' => $report->date_incident,
            'location' => $report->location,
            'incident_description' => $report->incident_description,
            'name_involved_name' => !empty($involvedNames) ? implode(', ', $involvedNames) : 'N/A',
            'name_witness_name' => !empty($witnessNames) ? implode(', ', $witnessNames) : 'N/A',
            'recommended_action' => $report->recommended_action,
        ]);
    }

    /**
     * Show the form for editing the specified incident report.
     */
    public function edit($id)
    {
        // $report = IncidentReport::findOrFail($id);
        // return view('incident_report.edit', compact('report'));
    }

    /**
     * Update the specified incident report in storage.
     */
    public function update(Request $request, $id)
    {
        // Validation and update logic here
    }

    /**
     * Remove the specified incident report from storage.
     */
    public function destroy(Request $request, $id = null)
    {
        $reportId = null;
        try {
            // Handle both URL parameter and POST data
            $reportId = $id ?? $request->input('id');
            
            // Write debug info to a file
            $debugInfo = "=== Delete Incident Report " . date('Y-m-d H:i:s') . " ===\n";
            $debugInfo .= "Report ID: " . print_r($reportId, true) . "\n";
            $debugInfo .= "Request Method: " . $request->method() . "\n";
            $debugInfo .= "Request Data: " . json_encode($request->all()) . "\n";
            file_put_contents(storage_path('logs/delete_debug.log'), $debugInfo, FILE_APPEND);
            
            \Log::info("Attempting to delete incident report with ID: " . $reportId);
            
            if (!$reportId) {
                return response()->json([
                    'success' => false,
                    'message' => 'No report ID provided.'
                ], 400);
            }
            
            $report = IncidentReport::findOrFail($reportId);
            \Log::info("Found incident report: " . $report->id);
            
            $report->delete();
            \Log::info("Successfully deleted incident report: " . $reportId);
            
            file_put_contents(storage_path('logs/delete_debug.log'), "SUCCESS: Report $reportId deleted\n", FILE_APPEND);
            
            return response()->json([
                'success' => true,
                'message' => 'Incident report deleted successfully.'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::warning("Incident report not found: " . ($reportId ?? 'unknown'));
            file_put_contents(storage_path('logs/delete_debug.log'), "NOT FOUND: " . $e->getMessage() . "\n", FILE_APPEND);
            return response()->json([
                'success' => false,
                'message' => 'Incident report not found.'
            ], 404);
        } catch (\Throwable $e) {
            $errorMsg = "ERROR: " . get_class($e) . " - " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine() . "\n";
            file_put_contents(storage_path('logs/delete_debug.log'), $errorMsg, FILE_APPEND);
            
            \Log::error("Error deleting incident report: " . $e->getMessage());
            \Log::error("Exception Type: " . get_class($e));
            \Log::error("Stack trace: " . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Server Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
