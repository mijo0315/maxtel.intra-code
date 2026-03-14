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
use App\Exports\PictureAppAuditExport;
use App\Exports\EntriesExport;
use Maatwebsite\Excel\Facades\Excel;

class pictureAppAuditController extends Controller
{
    public function picture_app_audit(){
        $role_id = Auth::user()->role_id;
        $tbl_employee = DB::connection("intra_payroll")->table("tbl_employee")->where("is_active",1);

        if(Auth::user()->access["face_and_time_audit"]["user_type"] != "employee"){

            if ($role_id === 4) { // HR Group D
                //$tbl_employee = $tbl_employee->where("hr_group", "group_a");
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
            } elseif ($role_id === 15) { // HR Group C
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

        return view("picture_app_audit.index")
        ->with("tbl_employee", $tbl_employee)
        ;

    }

    public function load_picture_app_audit_tbl(Request $request)
    {
        // Start by querying the tbl_entries table for the given emp_id
        $logs = DB::connection("face_db")->table("tbl_entries")
            ->where("biometric_id", $request->bio_id);

        // Apply a date range filter if provided
        // if ($request->filled('date_range')) {
        //     [$start_date, $end_date] = explode(' - ', $request->date_range);
        //     $logs->whereBetween('created_at', [$start_date . ' 00:00:00', $end_date . ' 23:59:59']);
        // }
        if ($request->filled('date_range')) {

            [$start_date, $end_date] = explode(' - ', $request->date_range);

            // Convert to UNIX timestamps
            $start_ts = strtotime($start_date . ' 00:00:00');
            $end_ts   = strtotime($end_date . ' 23:59:59');

            $logs->whereBetween('phone_timestamp', [$start_ts, $end_ts]);
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
            'images' => collect($logs)->map(function($row) {
        
                // Validate timestamp
                if (!empty($row->phone_timestamp) && is_numeric($row->phone_timestamp)) {
        
                    $dt = (new DateTime('@' . $row->phone_timestamp))
                        ->setTimezone(new DateTimeZone('Asia/Manila'))
                        ->format('Y-m-d H:i:s');
                } else {
                    $dt = null; // fallback
                }
        
                return [
                    'image' => $row->image_url,
                    'remarks' => $row->remarks,
                    'server_timestamp' => $dt,
                    'app_location' => $row->location ?? 'No Data',
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

    public function export_picture_app_audit(Request $request)
    {
        try {
            $bioId = $request->get('bio_id', null);
            $dateRange = $request->get('date_range', null);

            $fileName = 'Picture_App_Audit_' . date('Y-m-d_His') . '.xlsx';

            return Excel::download(
                new PictureAppAuditExport($bioId, $dateRange),
                $fileName
            );
        } catch (\Exception $e) {
            \Log::error('Picture App Audit Export Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to export. The picture app database may not be configured. Error: ' . $e->getMessage()], 500);
        }
    }

    public function export_all_entries(Request $request)
    {
        try {
            $bioId = $request->get('bio_id', null);
            $dateRange = $request->get('date_range', null);

            $fileName = 'Entries_' . date('Y-m-d_His') . '.xlsx';

            return Excel::download(
                new EntriesExport($bioId, $dateRange),
                $fileName
            );
        } catch (\Exception $e) {
            \Log::error('Entries Export Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to export. Error: ' . $e->getMessage()], 500);
        }
    }
}
