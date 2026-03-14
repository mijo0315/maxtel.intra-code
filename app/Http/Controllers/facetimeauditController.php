<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use DB;
use Storage;
use DateTime;
use DateTimeZone;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Hash;
use App\Exports\FaceTimeAuditExport;
use Maatwebsite\Excel\Facades\Excel;

class facetimeauditController extends Controller
{
    public function face_and_time_audit(){
        $role_id = Auth::user()->role_id;
        $tbl_employee = DB::connection("intra_payroll")->table("tbl_employee")->where("is_active",1);

        if(Auth::user()->access["face_and_time_audit"]["user_type"] != "employee"){

            if ($role_id === 4) { // HR Group D
                // $tbl_employee = $tbl_employee->where("hr_group", "group_a");
                $tbl_employee = $tbl_employee->where(function ($q) {
                    $q->where("hr_group", "group_d")
                      ->orWhere("user_id", Auth::user()->id); 
                });
            } elseif ($role_id === 5) { // HR Group B,C,E
                // $tbl_employee = $tbl_employee->where("hr_group", "group_b");
                $tbl_employee = $tbl_employee->where(function ($q) {
                    $q->whereIn("hr_group", ["group_b","group_c","group_e"])
                      ->orWhere("user_id", Auth::user()->id);
                });
            } elseif ($role_id === 14) { // HR Group B,C
                $tbl_employee = $tbl_employee->where(function ($q) {
                    $q->whereIn("hr_group", ["group_b","group_c"])
                      ->orWhere("user_id", Auth::user()->id);
                });
            } elseif ($role_id === 15) { // HR Group C,E
                $tbl_employee = $tbl_employee->where(function ($q) {
                    $q->whereIn("hr_group", ["group_c","group_e"])
                      ->orWhere("user_id", Auth::user()->id);
                });
            } 
            // elseif ($role_id === 22) { // HR Group E
            //     $tbl_employee = $tbl_employee->where(function ($q) {
            //         $q->where("hr_group", "group_e")
            //           ->orWhere("user_id", Auth::user()->id);
            //     });
            // }

       
        }else{
             $tbl_employee = $tbl_employee->where("id",Auth::user()->company["linked_employee"]["id"]);

        }
        
        $tbl_employee = $tbl_employee->orderBy("last_name","asc")->get();

        return view("face_time_audit.index")
        ->with("tbl_employee", $tbl_employee)
        ;

    }
    
    public function load_face_time_audit_tbl(Request $request)
    {
        $logs = DB::connection("intra_payroll")
            ->table("tbl_face_time_audit AS a")
            ->leftJoin("tbl_raw_logs AS r", function($join) {
                $join->on("r.biometric_id", "=", "a.emp_id")
                     ->on("r.state", "=", "a.state")
                     ->on("r.logs", "=", "a.created_at");
            })
            ->where("a.emp_id", $request->emp_id)
            ->select(
                "a.*",
                "r.location AS r_location"
            );
    
        // Date range filter
        if ($request->filled('date_range')) {
            [$start_date, $end_date] = explode(' - ', $request->date_range);
            $logs->whereBetween('a.created_at', [
                $start_date . ' 00:00:00',
                $end_date . ' 23:59:59'
            ]);
        }
    
        $total = $logs->count();
    
        $logs = $logs->orderBy("a.created_at", "DESC")
            ->skip(($request->page - 1) * 20)
            ->take(20)
            ->get();
    
        return response()->json([
            'total_images' => $total,
            'images' => collect($logs)->map(function($row) {
    
                // Fallback: face_time_audit.location → raw_logs.location → No Data
                $location = $row->location ?: $row->r_location ?: "No Data";
    
                return [
                    'image' => $row->image,
                    'state' => $this->getStateButton($row->state),
                    'loc' => $location,
                    'created_at' => (new DateTime($row->created_at))
                        ->format('Y-m-d H:i:s')
                ];
            })
        ]);
    }



    public function load_face_time_audit_tbl_old(Request $request)
    {
        // Start by querying the tbl_face_time_audit table for the given emp_id
        $logs = DB::connection("intra_payroll")->table("tbl_face_time_audit")
            ->where("emp_id", $request->emp_id);

        // Apply a date range filter if provided
        if ($request->filled('date_range')) {
            [$start_date, $end_date] = explode(' - ', $request->date_range);
            $logs->whereBetween('created_at', [$start_date . ' 00:00:00', $end_date . ' 23:59:59']);
        }

        // Get the total count of records for pagination
        $total = $logs->count();

        // Apply pagination: Default 50 items per page, based on the current page
        $logs = $logs->orderBy("created_at", "DESC")
            ->skip(($request->page - 1) * 20)  // Skip records based on the current page
            ->take(20)  // Limit results to 50 per page
            ->get();

        $array = collect($logs);

        // Return data with pagination information
        return response()->json([
            'total_images' => $total,
            'images' => $array->map(function($row) {
                return [
                    'image' => $row->image,  // You might want to modify this to use `asset_with_env`
                    'state' => $this->getStateButton($row->state),  // Get formatted state
                    'loc' => $row->location,
                    'created_at' => (new DateTime($row->created_at, new DateTimeZone('UTC')))->format('Y-m-d H:i:s')
                ];
            })
        ]);
    }

    // Helper function to format the state as a button
    private function getStateButton($state)
    {
        switch ($state) {
            case "AM_IN":
                return "<div class='btn btn-success btn-sm'>AM IN</div>";
            case "AM_OUT":
                return "<div class='btn btn-warning btn-sm'>AM OUT</div>";
            case "PM_IN":
                return "<div class='btn btn-success btn-sm'>PM IN</div>";
            case "PM_OUT":
                return "<div class='btn btn-warning btn-sm'>PM OUT</div>";
            case "OT_IN":
                return "<div class='btn btn-success btn-sm'>OT IN</div>";
            case "OT_OUT":
                return "<div class='btn btn-warning btn-sm'>OT OUT</div>";
            case "FLEX_IN":
                return "<div class='btn btn-warning btn-sm'>START TIME</div>";
            case "FLEX_OUT":
                return "<div class='btn btn-warning btn-sm'>END TIME</div>";
            default:
                return "<div class='btn btn-dark btn-sm'>UNKNOWN</div>";
        }
    }

    public function export_face_time_audit(Request $request)
    {
        try {
            $empId = $request->get('emp_id', null);
            $dateRange = $request->get('date_range', null);

            $fileName = 'Face_Time_Audit_' . date('Y-m-d_His') . '.xlsx';

            return Excel::download(
                new FaceTimeAuditExport($empId, $dateRange),
                $fileName
            );
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to export: ' . $e->getMessage()], 500);
        }
    }

}
