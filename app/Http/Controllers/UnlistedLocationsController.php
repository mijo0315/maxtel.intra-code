<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use DB;

class UnlistedLocationsController extends Controller
{
    public function index(Request $request)
    {
        return view('unlisted_locations.index');
    }
    
    public function getUnlistedLocationsData(Request $request)
    {
        $draw = $request->get('draw', 1);
        $start = $request->get('start', 0);
        $length = $request->get('length', 10);
        $search = $request->get('search', []);
        $searchValue = $search['value'] ?? '';
        
        // Get all locations
        $allLocations = $this->getAllLocations();
        
        // Get listed location names to filter them out
        $listedLocationNames = DB::table('tbl_listed_locations')
            ->distinct('location')
            ->pluck('location')
            ->toArray();
        
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
                return 
                    strpos(strtolower($location['bio_id']), $searchLower) !== false ||
                    strpos(strtolower($location['employee_name']), $searchLower) !== false ||
                    strpos(strtolower($location['location']), $searchLower) !== false;
            })->values();
        }
        
        // Get filtered records after search
        $filteredRecords = $unlistedLocations->count();
        
        // Paginate
        $data = [];
        foreach ($unlistedLocations->slice($start, $length) as $location) {
            $fullLocation = $location['location'];
            $truncatedLocation = substr($fullLocation, 0, 50) . (strlen($fullLocation) > 50 ? '...' : '');
            
            // Handle Unix timestamp or datetime string from phone_timestamp
            $timestamp = 'N/A';
            if (isset($location['phone_timestamp']) && !empty($location['phone_timestamp'])) {
                try {
                    $ts = $location['phone_timestamp'];
                    // Check if it's a Unix timestamp (numeric and reasonable length)
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
                'date_time' => $timestamp,
                'action' => $this->getUnlistedAction($location),
            ];
        }
        
        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data,
        ]);
    }
    
    private function getUnlistedAction($location)
    {
        return '
            <button class="btn btn-sm btn-info view-location-btn" data-bio-id="' . htmlspecialchars($location['bio_id']) . '" data-employee-name="' . htmlspecialchars($location['employee_name']) . '" data-location="' . htmlspecialchars($location['location']) . '" title="View Details">
                <i class="fas fa-eye"></i>
            </button>
            <form action="' . route('mark_as_listed') . '" method="POST" style="display:inline;">
                ' . csrf_field() . '
                <input type="hidden" name="bio_id" value="' . htmlspecialchars($location['bio_id']) . '">
                <input type="hidden" name="employee_name" value="' . htmlspecialchars($location['employee_name']) . '">
                <input type="hidden" name="location" value="' . htmlspecialchars($location['location']) . '">
                <button type="submit" class="btn btn-sm btn-success" title="Mark as Listed Location">
                    <i class="fas fa-check"></i>
                </button>
            </form>
        ';
    }
    
    public function getListedLocationsData(Request $request)
    {
        $draw = $request->get('draw', 1);
        $start = $request->get('start', 0);
        $length = $request->get('length', 10);
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
                    strpos(strtolower($location->bio_id), $searchLower) !== false ||
                    strpos(strtolower($location->employee_name), $searchLower) !== false ||
                    strpos(strtolower($location->location), $searchLower) !== false;
            })->values();
        }
        
        // Get filtered records after search
        $filteredRecords = $listedLocations->count();
        
        $data = [];
        foreach ($listedLocations->slice($start, $length) as $location) {
            $fullLocation = $location->location;
            $truncatedLocation = substr($fullLocation, 0, 50) . (strlen($fullLocation) > 50 ? '...' : '');
            
            $data[] = [
                'bio_id' => $location->bio_id,
                'employee_name' => $location->employee_name,
                'location' => $truncatedLocation,
                'date_listed' => \Carbon\Carbon::parse($location->date_listed)->format('M d, Y H:i'),
                'action' => $this->getListedAction($location),
            ];
        }
        
        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data,
        ]);
    }
    
    private function getListedAction($location)
    {
        return '
            <button class="btn btn-sm btn-info view-location-btn" data-bio-id="' . htmlspecialchars($location->bio_id) . '" data-employee-name="' . htmlspecialchars($location->employee_name) . '" data-location="' . htmlspecialchars($location->location) . '" title="View Details">
                <i class="fas fa-eye"></i>
            </button>
            <form action="' . route('remove_listed', $location->id) . '" method="POST" style="display:inline;" onsubmit="return confirm(\'Are you sure you want to remove this from listed locations?\');">
                ' . csrf_field() . '
                ' . method_field('DELETE') . '
                <button type="submit" class="btn btn-sm btn-danger" title="Remove from Listed">
                    <i class="fas fa-trash"></i> Remove
                </button>
            </form>
        ';
    }
    
    private function getAllLocations()
    {
        $result = [];
        
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
                    'bio_id' => $location->biometric_id,
                    'location' => $location->location,
                    'phone_timestamp' => $location->phone_timestamp,
                    'source' => 'face_db'
                ];
            }
        } catch (\Exception $e) {
            \Log::error('Error fetching from tbl_entries: ' . $e->getMessage());
        }
        
        // Get ALL locations from tbl_raw_logs (intra_payroll) - NO deduplication
        try {
            $prlLocations = DB::connection('intra_payroll')
                ->table('tbl_raw_logs')
                ->select('biometric_id', 'location')
                ->whereNotNull('location')
                ->where('location', '!=', '')
                ->where('location', '!=', 'Error fetching location')
                ->get();
            
            foreach ($prlLocations as $location) {
                $result[] = [
                    'bio_id' => $location->biometric_id,
                    'location' => $location->location,
                    'phone_timestamp' => null,
                    'source' => 'intra_payroll'
                ];
            }
        } catch (\Exception $e) {
            \Log::error('Error fetching from tbl_raw_logs: ' . $e->getMessage());
        }
        
        // Deduplicate by bio_id + location combination (keep only unique pairs)
        $unique = [];
        $seenPairs = [];
        
        foreach ($result as $location) {
            $pairKey = $location['bio_id'] . '|' . $location['location'];
            
            // Only add if this bio_id + location combination hasn't been seen
            if (!in_array($pairKey, $seenPairs)) {
                $seenPairs[] = $pairKey;
                $unique[] = $location;
            }
        }
        
        // Now add employee names to all locations
        $finalResult = [];
        foreach ($unique as $location) {
            $employee = DB::connection('intra_payroll')
                ->table('tbl_employee')
                ->where('bio_id', $location['bio_id'])
                ->first(['first_name', 'last_name']);
            
            // Use employee name if found, otherwise use bio_id
            $employeeName = ($employee) 
                ? trim($employee->first_name . ' ' . $employee->last_name)
                : 'Bio ID: ' . $location['bio_id'];
            
            $finalResult[] = [
                'bio_id' => $location['bio_id'],
                'employee_name' => $employeeName,
                'location' => $location['location'],
                'phone_timestamp' => $location['phone_timestamp']
            ];
        }
        
        return $finalResult;
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
}
