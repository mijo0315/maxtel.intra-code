<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Auth;
use DB;
use Storage;
use Yajra\DataTables\DataTables;
use DateTime;
use DateTimeZone; //update raw logs
use DateInterval;
use DatePeriod;
use Maatwebsite\Excel\Facades\Excel; //add export btn
use App\Exports\LogsExport; //add export btn
use App\Exports\OTApplyExport;
use App\Exports\RawLogsExport;

class timekeepingController extends Controller
{
      public function process_logs_crontab(Request $request){
        DB::beginTransaction();
        $now = date("Y-m-d");
        $yesterday = date("Y-m-d", strtotime($now . " -1 day"));
        try {
            $logs = DB::connection("intra_payroll")->table("tbl_raw_logs")
            ->join("tbl_employee", "bio_id", "=", "biometric_id")
            ->whereBetween("logs",[$yesterday, $now])
            ->where("biometric_id", "!=", "")
            ->orderBy("tbl_raw_logs.logs", "ASC")
            ->orderBy("state")
            ->get();
            foreach($logs  as $log_data){
                
                $target_date = date("Y-m-d", strtotime($log_data->logs));
                $log_info = strtotime($log_data->logs);
                //CHECK TC
                $tc = DB::connection("intra_payroll")->table("tbl_timecard")
                    ->where("emp_id", $log_data->id)
                    ->where("target_date", $target_date)
                    ->first();
                if($tc != null){
                    $tc =json_decode(json_encode($tc), true);
                    if($log_data->state == "FLEX_IN" || $log_data->state == "FLEX_OUT" ){
                        
                    }else{
                        $inserted_time = $tc[$log_data->state];
                    }
                    
                    if($log_data->state == "AM_IN" || $log_data->state == "PM_IN" || $log_data->state == "OT_IN")
                    {
                        if($inserted_time != "" || $inserted_time != null){
                            $current = strtotime($inserted_time);
                                if($log_info < $current){
                                    DB::connection("intra_payroll")->table("tbl_timecard")
                                        ->where("emp_id", $tc["emp_id"])
                                        ->where("target_date", $target_date)
                                        ->where("is_manual", 0)
                                        ->update([
                                            $log_data->state => $log_data->logs,
                                            "user_id" => Auth::user()->id
                                        ]);
                                }
                        }else{
                            DB::connection("intra_payroll")->table("tbl_timecard")
                                ->where("emp_id", $tc["emp_id"])
                                ->where("target_date", $target_date)
                                ->where("is_manual", 0)
                                ->update([
                                    $log_data->state => $log_data->logs,
                                    "user_id" => Auth::user()->id
                                ]);
                        }
                    }
                    elseif($log_data->state == "AM_OUT" || $log_data->state == "PM_OUT" || $log_data->state == "OT_OUT"){
                        if($inserted_time != "" || $inserted_time != null){
                            $current = strtotime($inserted_time);
                                if($log_info > $current){
                                    DB::connection("intra_payroll")->table("tbl_timecard")
                                        ->where("emp_id", $tc["emp_id"])
                                        ->where("target_date", $target_date)
                                        ->where("is_manual", 0)
                                        ->update([
                                            $log_data->state => $log_data->logs,
                                            "user_id" => Auth::user()->id
                                        ]);
                                }
                        }else{
                            DB::connection("intra_payroll")->table("tbl_timecard")
                                ->where("emp_id", $tc["emp_id"])
                                ->where("target_date", $target_date)
                                ->where("is_manual", 0)
                                ->update([
                                    $log_data->state => $log_data->logs,
                                    "user_id" => Auth::user()->id
                                ]);
                        }
                    }else{
                        if($log_data->state == "FLEX_OUT"){
                            $tc_flex = DB::connection("intra_payroll")->table("tbl_timecard")
                                ->where("emp_id", $tc["emp_id"])
                                ->where("target_date", $target_date)
                                ->where("is_manual", 0)
                                ->first();
                            
                            if($tc_flex != NULL){
                                    // dd($tc_flex);"2023-08-23 23:16:36
                                $flex_in_log =strtotime($tc_flex->AM_IN);
                                $flex_out_log = strtotime($log_data->logs);
                                // dd($log_data->logs);"2023-08-23 23:20:25"
                                $time_consume = $flex_in_log - $flex_out_log;
                                
                                $total_consume = round(abs($time_consume) / 60,2);
                                // dd($total_consume);
                                $hours = $total_consume / 60;
                                
                                
                                $total_hours = $tc_flex->flexi_hours + $hours;
                            
                          
                                DB::connection("intra_payroll")->table("tbl_timecard")
                                ->where("emp_id", $tc["emp_id"])
                                ->where("target_date", $target_date)
                                ->where("is_manual", 0)
                                ->update([
                                    "AM_IN" => NULL,
                                    "user_id" => Auth::user()->id,
                                    "flexi_hours" => $total_hours
                                ]);
                            
                            }
                        }elseif($log_data->state == "FLEX_IN"){
                            DB::connection("intra_payroll")->table("tbl_timecard")
                            ->where("emp_id", $tc["emp_id"])
                            ->where("target_date", $target_date)
                            ->where("is_manual", 0)
                            ->update([
                                "AM_IN" => $log_data->logs,
                                "user_id" => Auth::user()->id,
                            ]);
                        }
                    }
                    
                }else{
                    if($log_data->state == "OT_IN" || $log_data->state == "OT_OUT"){
                        //CHECK IF HAS IN AND OUT
                        $tc2 = DB::connection("intra_payroll")->table("tbl_timecard")
                            ->where("emp_id", $log_data->id)
                            ->where("target_date", $target_date)
                            ->where("AM_IN", "!=", NULL)
                            ->orWhere("emp_id", $log_data->id)
                            ->where("target_date", $target_date)
                            ->where("AM_IN", "!=", "")
                            
                            ->first();
                        if($tc2 != null){
                            $log2 = strtotime($tc2->logs);
                            $current_log = strtotime($log_data->logs);
                                if($log2 > $current_log){
                                    $target_date = date("Y-m-d", strtotime($target_date ." -1 day"));
                                }
                                $tc3 = DB::connection("intra_payroll")->table("tbl_timecard")
                                ->where("emp_id", $log_data->id)
                                ->where("target_date", $target_date)
                                ->first();
                                if($tc3 != null){
                                    DB::connection("intra_payroll")->table("tbl_timecard")
                                        ->where("emp_id", $tc["emp_id"])
                                        ->where("target_date", $target_date)
                                        ->where("is_manual", 0)
                                        ->update([
                                            $log_data->state => $log_data->logs,
                                            "user_id" => Auth::user()->id
                                        ]);
                                }else{
                                    DB::connection("intra_payroll")->table("tbl_timecard")
                                        ->insert([
                                            $log_data->state => $log_data->logs,
                                            "user_id" => Auth::user()->id,
                                            "target_date" => $target_date,
                                            "emp_id" => $log_data->id
                                        ]);
                                }
                        }else{
                            DB::connection("intra_payroll")->table("tbl_timecard")
                                        ->insert([
                                            $log_data->state => $log_data->logs,
                                            "user_id" => Auth::user()->id,
                                            "target_date" => $target_date,
                                            "emp_id" => $log_data->id
                                        ]);
                        }
                    }elseif($log_data->state == "AM_IN" || $log_data->state == "AM_OUT" || $log_data->state == "PM_IN" || $log_data->state == "PM_OUT"){
                        DB::connection("intra_payroll")->table("tbl_timecard")
                        ->insert([
                            $log_data->state => $log_data->logs,
                            "user_id" => Auth::user()->id,
                            "target_date" => $target_date,
                            "emp_id" => $log_data->id
                        ]);
                    }else{
                        if($log_data->state == "FLEX_IN"){
                            DB::connection("intra_payroll")->table("tbl_timecard")
                            ->insert([
                                "AM_IN" => $log_data->logs,
                                "user_id" => Auth::user()->id,
                                "target_date" => $target_date,
                                "emp_id" => $log_data->id
                            ]);
                        }
                    }
                  
                }
            }
            DB::commit();
            //$this->gettimecard($yesterday, $now);
            return json_encode("Success");
        } catch (\Throwable $th) {
            DB::rollback();
            return json_encode($th->getMessage());
        }
    }
    public function process_raw_logs(Request $request){
        $role_id = Auth::user()->role_id;
        DB::beginTransaction();
        try {
             $logs = DB::connection("intra_payroll")->table("tbl_raw_logs")
            ->join("tbl_employee", "bio_id", "=", "biometric_id")
            ->whereBetween("logs",[$request->tc_from, $request->tc_to]);

            if ($role_id === 4) { // HR Group D
                $logs = $logs->where("tbl_employee.hr_group", "group_d");
            } elseif ($role_id === 5) { // HR Group B,C,E
                $logs = $logs->whereIn("tbl_employee.hr_group", ["group_b","group_c","group_e"]);
            }

            $logs = $logs->where("biometric_id", "!=", "")
            ->orderBy("tbl_raw_logs.logs", "ASC")
            ->orderBy("state")
            ->get();
            foreach($logs  as $log_data){
                
                $target_date = date("Y-m-d", strtotime($log_data->logs));
                $log_info = strtotime($log_data->logs);
                //CHECK TC
                $tc = DB::connection("intra_payroll")->table("tbl_timecard")
                    ->where("emp_id", $log_data->id)
                    ->where("target_date", $target_date)
                    ->first();
                if($tc != null){
                    $tc =json_decode(json_encode($tc), true);
                    if($log_data->state == "FLEX_IN" || $log_data->state == "FLEX_OUT" ){
                        
                    }else{
                        $inserted_time = $tc[$log_data->state];
                    }
                    
                    if($log_data->state == "AM_IN" || $log_data->state == "PM_IN" || $log_data->state == "OT_IN")
                    {
                        if($inserted_time != "" || $inserted_time != null){
                            $current = strtotime($inserted_time);
                                if($log_info < $current){
                                    DB::connection("intra_payroll")->table("tbl_timecard")
                                        ->where("emp_id", $tc["emp_id"])
                                        ->where("target_date", $target_date)
                                        ->where("is_manual", 0)
                                        ->update([
                                            $log_data->state => $log_data->logs,
                                            "user_id" => Auth::user()->id
                                        ]);
                                }
                        }else{
                            DB::connection("intra_payroll")->table("tbl_timecard")
                                ->where("emp_id", $tc["emp_id"])
                                ->where("target_date", $target_date)
                                ->where("is_manual", 0)
                                ->update([
                                    $log_data->state => $log_data->logs,
                                    "user_id" => Auth::user()->id
                                ]);
                        }
                    }
                    elseif($log_data->state == "AM_OUT" || $log_data->state == "PM_OUT" || $log_data->state == "OT_OUT"){
                        if($inserted_time != "" || $inserted_time != null){
                            $current = strtotime($inserted_time);
                                if($log_info > $current){
                                    DB::connection("intra_payroll")->table("tbl_timecard")
                                        ->where("emp_id", $tc["emp_id"])
                                        ->where("target_date", $target_date)
                                        ->where("is_manual", 0)
                                        ->update([
                                            $log_data->state => $log_data->logs,
                                            "user_id" => Auth::user()->id
                                        ]);
                                }
                        }else{
                            DB::connection("intra_payroll")->table("tbl_timecard")
                                ->where("emp_id", $tc["emp_id"])
                                ->where("target_date", $target_date)
                                ->where("is_manual", 0)
                                ->update([
                                    $log_data->state => $log_data->logs,
                                    "user_id" => Auth::user()->id
                                ]);
                        }
                    }else{
                        if($log_data->state == "FLEX_OUT"){
                            $tc_flex = DB::connection("intra_payroll")->table("tbl_timecard")
                                ->where("emp_id", $tc["emp_id"])
                                ->where("target_date", $target_date)
                                ->first();
                            
                            if($tc_flex != NULL){
                                    // dd($tc_flex);"2023-08-23 23:16:36
                                $flex_in_log =strtotime($tc_flex->AM_IN);
                                $flex_out_log = strtotime($log_data->logs);
                                // dd($log_data->logs);"2023-08-23 23:20:25"
                                $time_consume = $flex_in_log - $flex_out_log;
                                
                                $total_consume = round(abs($time_consume) / 60,2);
                                // dd($total_consume);
                                $hours = $total_consume / 60;
                                
                                
                                $total_hours = $tc_flex->flexi_hours + $hours;
                            
                          
                                DB::connection("intra_payroll")->table("tbl_timecard")
                                ->where("emp_id", $tc["emp_id"])
                                ->where("target_date", $target_date)
                                ->where("is_manual", 0)
                                ->update([
                                    "AM_IN" => NULL,
                                    "user_id" => Auth::user()->id,
                                    "flexi_hours" => $total_hours
                                ]);
                            
                            }
                        }elseif($log_data->state == "FLEX_IN"){
                            DB::connection("intra_payroll")->table("tbl_timecard")
                            ->where("emp_id", $tc["emp_id"])
                            ->where("target_date", $target_date)
                            ->where("is_manual", 0)
                            ->update([
                                "AM_IN" => $log_data->logs,
                                "user_id" => Auth::user()->id,
                            ]);
                        }
                    }
                    
                }else{
                    if($log_data->state == "OT_IN" || $log_data->state == "OT_OUT"){
                        //CHECK IF HAS IN AND OUT
                        $tc2 = DB::connection("intra_payroll")->table("tbl_timecard")
                            ->where("emp_id", $log_data->id)
                            ->where("target_date", $target_date)
                            ->where("AM_IN", "!=", NULL)
                            ->orWhere("emp_id", $log_data->id)
                            ->where("target_date", $target_date)
                            ->where("AM_IN", "!=", "")
                            
                            ->first();
                        if($tc2 != null){
                            $log2 = strtotime($tc2->logs);
                            $current_log = strtotime($log_data->logs);
                                if($log2 > $current_log){
                                    $target_date = date("Y-m-d", strtotime($target_date ." -1 day"));
                                }
                                $tc3 = DB::connection("intra_payroll")->table("tbl_timecard")
                                ->where("emp_id", $log_data->id)
                                ->where("target_date", $target_date)
                                ->first();
                                if($tc3 != null){
                                    DB::connection("intra_payroll")->table("tbl_timecard")
                                        ->where("emp_id", $tc["emp_id"])
                                        ->where("target_date", $target_date)
                                        ->where("is_manual", 0)
                                        ->update([
                                            $log_data->state => $log_data->logs,
                                            "user_id" => Auth::user()->id
                                        ]);
                                }else{
                                    DB::connection("intra_payroll")->table("tbl_timecard")
                                        ->insert([
                                            $log_data->state => $log_data->logs,
                                            "user_id" => Auth::user()->id,
                                            "target_date" => $target_date,
                                            "emp_id" => $log_data->id
                                        ]);
                                }
                        }else{
                            DB::connection("intra_payroll")->table("tbl_timecard")
                                        ->insert([
                                            $log_data->state => $log_data->logs,
                                            "user_id" => Auth::user()->id,
                                            "target_date" => $target_date,
                                            "emp_id" => $log_data->id
                                        ]);
                        }
                    }elseif($log_data->state == "AM_IN" || $log_data->state == "AM_OUT" || $log_data->state == "PM_IN" || $log_data->state == "PM_OUT"){
                        DB::connection("intra_payroll")->table("tbl_timecard")
                        ->insert([
                            $log_data->state => $log_data->logs,
                            "user_id" => Auth::user()->id,
                            "target_date" => $target_date,
                            "emp_id" => $log_data->id
                        ]);
                    }else{
                        if($log_data->state == "FLEX_IN"){
                            DB::connection("intra_payroll")->table("tbl_timecard")
                            ->insert([
                                "AM_IN" => $log_data->logs,
                                "user_id" => Auth::user()->id,
                                "target_date" => $target_date,
                                "emp_id" => $log_data->id
                            ]);
                        }
                    }
                  
                }
            }
            DB::commit();
            //$this->gettimecard($request->tc_from, $request->tc_to);
            return json_encode("Success");
        } catch (\Throwable $th) {
            DB::rollback();
            return json_encode($th->getMessage().'--'. $th->getLine());
        }
    }
    public function timecard_logs_tbl(Request $request)
    {
        $user = Auth::user();
        $role_id = $user->role_id;
        $branch_id = $request->branch_id;
        $emp_id = $request->emp_id;
        $type = $request->type;
        // Default range
        $start_date = date("Y-m-01");
        $end_date = date("Y-m-d");
        if ($request->filled('date_range')) {
            [$start_date, $end_date] = explode(' - ', $request->date_range);
        }
        $query = DB::connection("intra_payroll")->table("tbl_timecard")
            ->select(
                'tbl_timecard.*',
                'tbl_employee.first_name',
                'tbl_employee.middle_name',
                'tbl_employee.last_name',
                'tbl_employee.ext_name',
                'tbl_employee.position_id',
                'tbl_employee.schedule_id',
                'tbl_employee.id as emp_id'
            )
            ->join('tbl_employee', 'tbl_timecard.emp_id', 'tbl_employee.id');
       
        if ($role_id === 4) { // HR Group D
            // $query->where('tbl_employee.hr_group', 'group_a');
            $query->where(function ($q) {
                $q->where("tbl_employee.hr_group", "group_d")
                ->orWhere("tbl_employee.user_id", Auth::user()->id);
            });
        } elseif ($role_id === 5) { // HR Group B,C,E
            // $query->where('tbl_employee.hr_group', 'group_b');
            $query->where(function ($q) {
                $q->whereIn("tbl_employee.hr_group", ["group_b","group_c","group_e"])
                ->orWhere("tbl_employee.user_id", Auth::user()->id);
            });
        } elseif ($role_id === 14) { // HR Group B,C
            $query->where(function ($q) {
                $q->whereIn("tbl_employee.hr_group", ["group_b","group_c"])
                ->orWhere("tbl_employee.user_id", Auth::user()->id);
            });
        } elseif ($role_id === 15) { // HR Group C
            $query->where(function ($q) {
                $q->whereIn("tbl_employee.hr_group", ["group_c","group_e"])
                ->orWhere("tbl_employee.user_id", Auth::user()->id);
            });
        } 
        // elseif ($role_id === 22) { // HR Group E
        //     $query->where(function ($q) {
        //         $q->where("tbl_employee.hr_group", "group_e")
        //         ->orWhere("tbl_employee.user_id", Auth::user()->id);
        //     });
        // }
        //Filter by employee or branch
        if (!empty($type)) {
            if ($type == "by_employee") {
                $query->where("tbl_employee.id", $emp_id);
            } else {
                $query->where("tbl_employee.branch_id", $branch_id);
            }
        }
        // If selected_dates is passed, override range
        if ($request->filled('selected_dates') && is_array($request->selected_dates)) {
            $query->whereIn("target_date", $request->selected_dates);
        } else {
            $query->whereBetween("target_date", [$start_date, $end_date]);
        }
        $data = $query->orderBy("target_date", "DESC")->get();
        $data_tbl = collect($data);
        return Datatables::of($data_tbl)
            ->addColumn('name', function ($row) {
                return $row->last_name . ", " . $row->first_name . " " . $row->middle_name . " " . $row->ext_name;
            })
            ->addColumn('position', function ($row) {
                $lib_position = DB::connection("intra_payroll")
                    ->table("lib_position")
                    ->where("id", $row->position_id)
                    ->first();
                return !empty($lib_position) ? $lib_position->name : "N/A";
            })
            ->addColumn('target_date', function ($row) {
                return date("m-d-Y", strtotime($row->target_date));
            })
            ->addColumn('AM_IN', function ($row) {
                $value = $row->AM_IN ? date("H:i", strtotime($row->AM_IN)) : '';
                return "<input type='time' class='form-control form-control-sm inline-am-in' data-id='{$row->id}' value='{$value}' />";
            })
            ->addColumn('PM_OUT', function ($row) {
                $value = $row->PM_OUT ? date("H:i", strtotime($row->PM_OUT)) : '';
                return "<input type='time' class='form-control form-control-sm inline-pm-out' data-id='{$row->id}' value='{$value}' />";
            })
            ->addColumn('schedule', function ($row) {
                // $lib_schedule = DB::connection("intra_payroll")
                //     ->table("lib_schedule")
                //     ->where("is_active", 1)
                //     ->get();
                // $tbl_daily_schedule = DB::connection("intra_payroll")
                //     ->table("tbl_daily_schedule")
                //     ->where("emp_id", $row->emp_id)
                //     ->where("schedule_date", $row->target_date)
                //     ->first();
                // if ($tbl_daily_schedule) {
                //     $selected_schedule_id = $tbl_daily_schedule->schedule_id;
                // } else {
                //     $day_name = strtolower(date('l', strtotime($row->target_date)));
                //     $week_sched = DB::connection("intra_payroll")
                //         ->table("lib_week_schedule")
                //         ->where("id", $row->schedule_id)
                //         ->where("is_active", 1)
                //         ->first();
                //     $selected_schedule_id = $week_sched ? ($week_sched->$day_name ?? 0) : 0;
                // }
                // $select = "<select class='form-control form-select inline-schedule' data-emp-id='{$row->emp_id}' data-target-date='{$row->target_date}' style='width:180px;'>";
                // $select .= "<option value='0'" . ($selected_schedule_id == 0 ? " selected" : "") . ">Rest Day</option>";
                // foreach ($lib_schedule as $sched_daily) {
                //     $is_selected = ($sched_daily->id == $selected_schedule_id) ? "selected" : "";
                //     $select .= "<option value='{$sched_daily->id}' {$is_selected}>{$sched_daily->name}</option>";
                // }
                // $select .= "</select>";
                // return $select;
                 $lib_schedule = DB::connection("intra_payroll")
                    ->table("lib_schedule")
                    ->where("is_active", 1)
                    ->get();

                $selected_schedule_id = 0;
                $day_name = strtolower(date('l', strtotime($row->target_date)));
                $tbl_daily_schedule = DB::connection("intra_payroll")
                    ->table("tbl_daily_schedule")
                    ->where("emp_id", $row->emp_id)
                    ->where("schedule_date", $row->target_date)
                    ->first();

                if ($tbl_daily_schedule) {

                    $selected_schedule_id = $tbl_daily_schedule->schedule_id;

                } else {
                    if (!empty($row->schedule_id) && $row->schedule_id != 0) {

                        $week_sched = DB::connection("intra_payroll")
                            ->table("lib_week_schedule")
                            ->where("id", $row->schedule_id)
                            ->where("is_active", 1)
                            ->first();

                        $selected_schedule_id = $week_sched ? ($week_sched->$day_name ?? 0) : 0;

                    } else {
                        $tbl_employee = DB::connection("intra_payroll")
                            ->table("tbl_employee")
                            ->where("id", $row->emp_id)
                            ->first();

                        $fallback_week_schedule_id = 0;

                        if ($tbl_employee) {

                            $lib_position = DB::connection("intra_payroll")
                                ->table("lib_position")
                                ->where("id", $tbl_employee->position_id)
                                ->first();

                            $lib_designation = DB::connection("intra_payroll")
                                ->table("lib_designation")
                                ->where("id", $tbl_employee->designation)
                                ->first();

                            $tbl_department = DB::connection("intra_payroll")
                                ->table("tbl_department")
                                ->where("id", $tbl_employee->department)
                                ->first();

                            $tbl_branch = DB::connection("intra_payroll")
                                ->table("tbl_branch")
                                ->where("id", $tbl_employee->branch_id)
                                ->first();

                            // Priority order
                            $fallback_week_schedule_id = 0;
                            if (!empty($lib_position) && !empty($lib_position->schedule_id)) {
                                $fallback_week_schedule_id = $lib_position->schedule_id;
                            } elseif (!empty($lib_designation) && !empty($lib_designation->schedule_id)) {
                                $fallback_week_schedule_id = $lib_designation->schedule_id;
                            } elseif (!empty($tbl_department) && !empty($tbl_department->schedule_id)) {
                                $fallback_week_schedule_id = $tbl_department->schedule_id;
                            } elseif (!empty($tbl_branch) && !empty($tbl_branch->schedule_id)) {
                                $fallback_week_schedule_id = $tbl_branch->schedule_id;
                            }

                        }

                        if ($fallback_week_schedule_id) {
                            $day_name = strtolower(date('l', strtotime($row->target_date)));
                            $week_sched = DB::connection("intra_payroll")
                                ->table("lib_week_schedule")
                                ->where("id", $fallback_week_schedule_id)
                                ->where("is_active", 1)
                                ->first();

                            $selected_schedule_id = $week_sched ? ($week_sched->$day_name ?? 0) : 0;
                        }
                    }
                }
                $select = "<select class='form-control form-select inline-schedule'
                            data-emp-id='{$row->emp_id}'
                            data-target-date='{$row->target_date}'
                            style='width:180px;'>";

                $select .= "<option value='0'" . ($selected_schedule_id == 0 ? " selected" : "") . ">Rest Day</option>";

                foreach ($lib_schedule as $sched_daily) {
                    $is_selected = ($sched_daily->id == $selected_schedule_id) ? "selected" : "";
                    $select .= "<option value='{$sched_daily->id}' {$is_selected}>{$sched_daily->name}</option>";
                }

                $select .= "</select>";

                return $select;
            })
            ->addColumn('action', function ($row) {
                $btn = "";
                if (Auth::user()->access["timekeeping_management"]["user_type"] != "employee") {
                    $btn .= "<button class='btn btn-danger btn-sm' onclick='delete_manual_log(" . $row->id . ");'>Delete Log</button>";
                }
                return $btn;
            })
            ->rawColumns(['AM_IN', 'PM_OUT', 'schedule', 'action'])
            ->make(true);
    }
 public function timecard_logs_tbl_old(Request $request){
        
        $branch_id = $request->branch_id;
        $emp_id = $request->emp_id;
        $type = $request->type;
        // Default range
        $start_date = date("Y-m-01");
        $end_date = date("Y-m-d");
        if ($request->filled('date_range')) {
            $arr = explode(' - ', $request->date_range);
            $start_date = $arr[0];
            $end_date = $arr[1];
        }
        $query = DB::connection("intra_payroll")->table("tbl_timecard")
            ->select('tbl_timecard.*', 'tbl_employee.first_name','tbl_employee.middle_name','tbl_employee.last_name','tbl_employee.ext_name','tbl_employee.position_id','tbl_employee.schedule_id','tbl_employee.id as emp_id')
            ->join('tbl_employee', 'tbl_timecard.emp_id', 'tbl_employee.id');
        if(!empty($type)){
            if($type == "by_employee"){
                $query = $query->where("tbl_employee.id", $emp_id);
            }else{
                $query = $query->where("tbl_employee.branch_id", $branch_id);
            }
        }
        // If selected_dates is passed, override the range filter
        if ($request->filled('selected_dates') && is_array($request->selected_dates)) {
            $query->whereIn("target_date", $request->selected_dates);
        } else {
            $query->whereBetween("target_date", [$start_date, $end_date]);
        }
        $data = $query->orderBy("target_date", "DESC")->get();
        $data_tbl = collect($data);
        return Datatables::of($data_tbl)
        ->addColumn('name', function ($row) {
            return $row->last_name . ", " . $row->first_name . " " . $row->middle_name . " " . $row->ext_name;
        })
        ->addColumn('position', function ($row) {
            $lib_position = DB::connection("intra_payroll")->table("lib_position")->where("id", $row->position_id)->first();
            return !empty($lib_position) ? $lib_position->name : "N/A"; 
        })
        ->addColumn('target_date', function ($row) {
            return date("m-d-Y", strtotime($row->target_date));
        })
        ->addColumn('AM_IN', function ($row) {
            $value = $row->AM_IN ? date("H:i", strtotime($row->AM_IN)) : '';
            return "<input type='time' class='form-control form-control-sm inline-am-in' data-id='{$row->id}' value='{$value}' />";
        })
        ->addColumn('PM_OUT', function ($row) {
            $value = $row->PM_OUT ? date("H:i", strtotime($row->PM_OUT)) : '';
            return "<input type='time' class='form-control form-control-sm inline-pm-out' data-id='{$row->id}' value='{$value}' />";
        })
        ->addColumn('schedule', function ($row) {
            $lib_schedule = DB::connection("intra_payroll")
                ->table("lib_schedule")
                ->where("is_active", 1)
                ->get();
            $tbl_daily_schedule = DB::connection("intra_payroll")
                ->table("tbl_daily_schedule")
                ->where("emp_id", $row->emp_id)
                ->where("schedule_date", $row->target_date)
                ->first();
            // Determine selected schedule
            if ($tbl_daily_schedule) {
                $selected_schedule_id = $tbl_daily_schedule->schedule_id;
            } else {
                // Get day of week (e.g., 'monday')
                $day_name = strtolower(date('l', strtotime($row->target_date)));
                // Get lib_week_schedule based on employee's schedule_id
                $week_sched = DB::connection("intra_payroll")
                    ->table("lib_week_schedule")
                    ->where("id", $row->schedule_id)
                    ->where("is_active", 1)
                    ->first();
                $selected_schedule_id = $week_sched ? ($week_sched->$day_name ?? 0) : 0;
            }
            
            $select = "<select class='form-control form-select inline-schedule' data-emp-id='{$row->emp_id}' data-target-date='{$row->target_date}' style='width:180px;'>";
            $select .= "<option value='0'" . ($selected_schedule_id == 0 ? " selected" : "") . ">Rest Day</option>";
            foreach ($lib_schedule as $sched_daily) {
                $is_selected = ($sched_daily->id == $selected_schedule_id) ? "selected" : "";
                $select .= "<option value='{$sched_daily->id}' {$is_selected}>{$sched_daily->name}</option>";
            }
            $select .= "</select>";
            return $select;
        })
        
        ->addColumn('action', function($row){
            $btn = "";
            if(Auth::user()->access["timekeeping_management"]["user_type"] != "employee"){
                // $tbl_timecard_request = DB::connection("intra_payroll")->table("tbl_timecard_request")->where("timecard_id", $row->id)->where('status','pending')->first();
                // if($tbl_timecard_request){
                //     $btn .= "<a 
                //     class='btn btn-sm btn-info mr-1'
                //         data-toggle='modal' 
                //         data-target='#requestLogModal'
                //         data-timecard_id = '$tbl_timecard_request->timecard_id'
                //         data-am_in = '$tbl_timecard_request->AM_IN'
                //         data-am_out = '$tbl_timecard_request->AM_OUT'
                //         data-pm_in = '$tbl_timecard_request->PM_IN'
                //         data-pm_out = '$tbl_timecard_request->PM_OUT'
                //         data-date_target = '$tbl_timecard_request->target_date'
                //     >
                //     Pending for Approval
                //     </a>";
                // }
                // $btn .= "<a 
                //     class='btn btn-sm btn-success mr-1'
                //         data-toggle='modal' 
                //         data-target='#timeModal'
                //         data-am_in = '$row->AM_IN'
                //         data-am_out = '$row->AM_OUT'
                //         data-pm_in = '$row->PM_IN'
                //         data-pm_out = '$row->PM_OUT'
                //         data-date_target = '$row->target_date'
                //     >
                //     Edit Log
                //     </a>";
                    $btn .= "<button class='btn btn-danger btn-sm' onclick='delete_manual_log(".$row->id.");'>
                    
                    Delete Log
                    </button>";
            }
            // else{
                // $tbl_timecard_request = DB::connection("intra_payroll")->table("tbl_timecard_request")->where("timecard_id", $row->id)->where('status','approved')->first();
                // $class_disable = '';
                // $btn_name = 'Request Edit Log';
                // if($tbl_timecard_request){
                //     $class_disable = 'disabled';
                //     $btn_name = 'Approved';
                // }
                // $btn .= "<a 
                // class='btn btn-sm btn-success mr-1 $class_disable'
                //     data-toggle='modal' 
                //     data-target='#requestLogModal'
                //     data-timecard_id = '$row->id'
                //     data-am_in = '$row->AM_IN'
                //     data-am_out = '$row->AM_OUT'
                //     data-pm_in = '$row->PM_IN'
                //     data-pm_out = '$row->PM_OUT'
                //     data-date_target = '$row->target_date'
                    
                // >
                // $btn_name
                // </a>";
            // }
                    
            return $btn;
        })
        ->rawColumns(['AM_IN', 'PM_OUT', 'schedule',  'action'])
        ->make(true);
    }
    public function manual_in_out(Request $request) {
        $check = DB::connection("intra_payroll")->table("tbl_timecard")
            ->where("emp_id", $request->emp_id)
            ->where("target_date", $request->date_target)
            ->first();
    
        // Convert times only if they are not null
        $amTimeIn  = !empty($request->amTimeIn)  ? date("Y-m-d H:i:s", strtotime($request->amTimeIn))  : null;
        $amTimeOut = !empty($request->amTimeOut) ? date("Y-m-d H:i:s", strtotime($request->amTimeOut)) : null;
        $pmTimeIn  = !empty($request->pmTimeIn)  ? date("Y-m-d H:i:s", strtotime($request->pmTimeIn))  : null;
        $pmTimeOut = !empty($request->pmTimeOut) ? date("Y-m-d H:i:s", strtotime($request->pmTimeOut)) : null;
    
        if ($check != null) {
            DB::connection("intra_payroll")->table("tbl_timecard")
                ->where("emp_id", $request->emp_id)
                ->where("target_date", $request->date_target)
                ->update([
                    "AM_IN" => $amTimeIn,
                    "AM_OUT" => $amTimeOut,
                    "PM_IN" => $pmTimeIn,
                    "PM_OUT" => $pmTimeOut,
                    "is_manual" => "1"
                ]);
        } else {
            DB::connection("intra_payroll")->table("tbl_timecard")
                ->insert([
                    "AM_IN" => $amTimeIn,
                    "AM_OUT" => $amTimeOut,
                    "PM_IN" => $pmTimeIn,
                    "PM_OUT" => $pmTimeOut,
                    "is_manual" => "1",
                    "emp_id" => $request->emp_id,
                    "target_date" => $request->date_target,
                    "user_id" => Auth::user()->id
                ]);
        }
    
        return json_encode("Success");
    }
    public function manual_in_out_request(Request $request) {
        $check = DB::connection("intra_payroll")->table("tbl_timecard_request")
            ->where("timecard_id", $request->timecard_id)
            ->first();
    
        // Convert times only if they are not null
        $amTimeIn  = !empty($request->amTimeIn)  ? date("Y-m-d H:i:s", strtotime($request->amTimeIn))  : null;
        $amTimeOut = !empty($request->amTimeOut) ? date("Y-m-d H:i:s", strtotime($request->amTimeOut)) : null;
        $pmTimeIn  = !empty($request->pmTimeIn)  ? date("Y-m-d H:i:s", strtotime($request->pmTimeIn))  : null;
        $pmTimeOut = !empty($request->pmTimeOut) ? date("Y-m-d H:i:s", strtotime($request->pmTimeOut)) : null;
    
        if ($check != null) {
            $status = 'pending';
            if($request->submitBtnVal == 'Approved'){
                $status = 'approved';
                DB::connection("intra_payroll")->table("tbl_timecard")
                ->where("id", $request->timecard_id)
                ->where("target_date", $request->date_target)
                ->update([
                    "AM_IN" => $amTimeIn,
                    "AM_OUT" => $amTimeOut,
                    "PM_IN" => $pmTimeIn,
                    "PM_OUT" => $pmTimeOut,
                    "is_manual" => "1"
                ]);
            }
            DB::connection("intra_payroll")->table("tbl_timecard_request")
                ->where("timecard_id", $request->timecard_id)
                ->update([
                    "AM_IN" => $amTimeIn,
                    "AM_OUT" => $amTimeOut,
                    "PM_IN" => $pmTimeIn,
                    "PM_OUT" => $pmTimeOut,
                    "status" => $status
                ]);
           
        } else {
            DB::connection("intra_payroll")->table("tbl_timecard_request")
                ->insert([
                    "AM_IN" => $amTimeIn,
                    "AM_OUT" => $amTimeOut,
                    "PM_IN" => $pmTimeIn,
                    "PM_OUT" => $pmTimeOut,
                    "timecard_id" => $request->timecard_id,
                    "target_date" => $request->date_target,
                    "status" => 'pending',
                    "user_id" => Auth::user()->id
                ]);
        }
    
        return json_encode("Success");
    }
    public function raw_logs_tbl(Request $request){
        $emp = DB::connection("intra_payroll")->table("tbl_employee")
            ->where("id", $request->emp_id)
            ->first();
        if($emp != null){
            $bio_id = $emp->bio_id;
        }else{
            $bio_id = "0";
        }
        $logs = DB::connection("intra_payroll")->table("tbl_raw_logs")
            ->where("biometric_id", $bio_id);
        // Filter by date range
        if ($request->filled('date_range')) {
            [$start_date, $end_date] = explode(' - ', $request->date_range);
            $logs->whereBetween('logs', [$start_date . ' 00:00:00', $end_date . ' 23:59:59']);
        }
        $logs = $logs->orderBy("logs", "DESC")->get();
        $array = collect($logs);
        return Datatables::of($array)
                ->addColumn('state', function($row){
                    
                    if($row->state == "AM_IN"){
                        return "<div class='btn btn-success btn-sm'>AM IN</div>";
                    }elseif($row->state == "AM_OUT"){
                        return "<div class='btn btn-warning btn-sm'>AM OUT</div>";
                    }elseif($row->state == "PM_IN"){
                        return "<div class='btn btn-success btn-sm'>PM IN</div>";
                    }elseif($row->state == "PM_OUT"){
                        return "<div class='btn btn-warning btn-sm'>PM OUT</div>";
                    }elseif($row->state == "OT_IN"){
                        return "<div class='btn btn-success btn-sm'>OT IN</div>";
                    }elseif($row->state == "OT_OUT"){
                        return "<div class='btn btn-warning btn-sm'>OT OUT</div>";
                    }elseif($row->state == "FLEX_IN"){
                        return "<div class='btn btn-warning btn-sm'>START TIME</div>";
                    }elseif($row->state == "FLEX_OUT"){
                        return "<div class='btn btn-warning btn-sm'>END TIME</div>";
                    }
                    
                    else{
                        return "<div class='btn btn-dark btn-sm'>UNKNOWN</div>";
                    }
                    
             
                })
                // update raw logs
                ->addColumn('logs', function($row){
                     return date('Y-m-d H:i:s', strtotime($row->logs));
                    //$currentDate = $row->logs;
                    //$date = new DateTime($currentDate, new DateTimeZone('UTC'));
                    // Set the timezone to Asia/Manila
                    //$date->setTimezone(new DateTimeZone('Asia/Manila'));
                    //return $date->format('Y-m-d H:i:s');
                })
                ->rawColumns(['state','logs'])
            ->make(true);
    }
    public function get_employee_info(Request $request){
        $emp = DB::connection("intra_payroll")->table("tbl_employee")
            ->where("id", $request->id)
            ->first();
            $return_array = array();
            if($emp != null){
                return json_encode(true);
            }else{
                return json_encode(false);
            }
        
    }
    public function set_holiday(Request $request){
        DB::beginTransaction();
        try {
            $target_day = date("Y-m-d", strtotime($request->holiday_target_day));
            if($request->holiday_type == "0"){
                $tbl_holiday = DB::connection("intra_payroll")->table("tbl_holiday")
                ->where("holiday_date", $target_day)
                ->delete();
            }else{
                $tbl_holiday = DB::connection("intra_payroll")->table("tbl_holiday")
                ->where("holiday_date", $target_day)
                ->first();
                if($tbl_holiday != null){
                    DB::connection("intra_payroll")->table("tbl_holiday")
                        ->where("id", $tbl_holiday->id)
                        ->update([
                            "holiday_name" => $request->holiday_name,
                            "holiday_type" => $request->holiday_type,
                            "user_id" => Auth::user()->id
                        ]);
                }else{
                    DB::connection("intra_payroll")->table("tbl_holiday")
                        ->insert([
                            "holiday_date" => $target_day,
                            "holiday_name" => $request->holiday_name,
                            "holiday_type" => $request->holiday_type,
                            "date_created" => date("Y-m-d H:i:s"),
                            "user_id" => Auth::user()->id
                        ]);
                }
            }
            
            DB::commit();
            return json_encode($target_day);
        } catch (\Throwable $th) {
            DB::rollback();
            return json_encode("failed");
        }
       
    }
   public function get_holiday($month_view, $page){
        
        $date_from = date("Y-m-d", strtotime($month_view));
		
      	
      
        $date_from_year = date("Y", strtotime($date_from));
        $date_from_month = date("m", strtotime($date_from));
        $date_from_day = date("d", strtotime($date_from));
        if($date_from_day > 1){
            $set_day_date_from = date("Y-m-01", strtotime($date_from));
          	$new_str_time = strtotime($set_day_date_from . ' +1 month');
         
            $date_from = date("Y-m-d", $new_str_time);
           
        }
         
        $date_to = date("Y-m-t", strtotime($date_from));
        $data_days = array();
        $cur_day = $date_from;
        $tbl_holiday = DB::connection("intra_payroll")->table("tbl_holiday")->whereBetween("holiday_date",[$date_from, $date_to])->get();
            $tbl_holiday = json_decode(json_encode($tbl_holiday), true);
        if(Auth::user()->access[$page]["user_type"] != "employee"){
            $is_edit = "1";
        }else{
            $is_edit = "0";
        }
        do{
            $data = $this->search_multi_array($tbl_holiday, "holiday_date", $cur_day);
            $color = "";
            if(isset($data["holiday_type"])){
                if($data["holiday_type"] == "RH"){
                    $color = "#48db66";
                }elseif($data["holiday_type"] == "SH"){
                    $color = "#555ce6";
                }
                if($color != ""){
                    array_push($data_days,array(
                        'title' => $data["holiday_name"] . " (".$data["holiday_type"].")",
                        'start' => $cur_day,
                        'color'  => $color,
                        'extendedProps' => array(
                                    "is_edit" => $is_edit, 
                                    "type" => $data["holiday_type"],
                                    "name" => $data["holiday_name"],
                                    "holiday_id" => $data["id"]
                                )
                    ));
                }
            
               
            }
           
         
            $cur_day = date('Y-m-d', strtotime($cur_day . ' +1 day'));
        }while(strtotime($cur_day) <= strtotime($date_to));
      
        return response()->json($data_days);
    }
    function search_multi_array($array, $key, $value) {
        foreach ($array as $subarray) {
            if (isset($subarray[$key]) && $subarray[$key] == $value) {
                return $subarray;
            }
        }
        return null;
    }
    public function timekeeping_management(){
       $role_id = Auth::user()->role_id;
        if(Auth::user()->access["timekeeping_management"]["user_type"] != "employee"){
            $tbl_employee = DB::connection("intra_payroll")->table("tbl_employee");
            if ($role_id === 4) { // HR Group D
                // $tbl_employee = $tbl_employee->where("hr_group", "group_a");
                $tbl_employee = $tbl_employee->where(function ($q) {
                    $q->where("hr_group", "group_d")
                    ->orWhere("user_id", Auth::user()->id); // always include self
                });
            } elseif ($role_id === 5) { // HR Group B,C,E
                // $tbl_employee = $tbl_employee->where("hr_group", "group_b");
                $tbl_employee = $tbl_employee->where(function ($q) {
                    $q->whereIn("hr_group", ["group_b","group_c","group_e"])
                    ->orWhere("user_id", Auth::user()->id); // always include self
                });
            } elseif ($role_id === 14) { // HR Group B,C
                $tbl_employee = $tbl_employee->where(function ($q) {
                    $q->whereIn("hr_group", ["group_b","group_c"])
                    ->orWhere("user_id", Auth::user()->id); // always include self
                });
            } elseif ($role_id === 15) { // HR Group C
                $tbl_employee = $tbl_employee->where(function ($q) {
                    $q->whereIn("hr_group", ["group_c","group_e"])
                    ->orWhere("user_id", Auth::user()->id); // always include self
                });
            } 
            // elseif ($role_id === 22) { // HR Group E
            //     $tbl_employee = $tbl_employee->where(function ($q) {
            //         $q->where("hr_group", "group_e")
            //         ->orWhere("user_id", Auth::user()->id); // always include self
            //     });
            // }
            $tbl_employee = $tbl_employee->where('is_active', 1)
            ->orderBy("last_name")
            ->orderBy("first_name")
            ->orderBy("middle_name")
            ->get();
       
        }else{
            $tbl_employee = DB::connection("intra_payroll")->table("tbl_employee")
            ->where('is_active', 1);

            if($role_id === 23){
                // Rizalyn Salvador
                // Laguna (Team Henry)
                // Bicol (Team Henry)
                // Batangas
                // Laguna (Team Dante)
                // Bicol (Team Dante)
                $tbl_employee = $tbl_employee->where(function ($q) {
                    $q->whereIn("branch_id",[71,70,57,90,59])
                    ->orWhere("user_id", Auth::user()->id); // always include self
                });
            } elseif($role_id === 24){
                // Maica Nueva
                // NCR - SM EAST
                // NCR - STA LUCIA
                // CEBU - CAD
                //add feb 25 2026
                // VIS - HM TOWER
                // VIS - JMALL
                // VIS - ILOILO
                // VIS - BACOLOD
                $tbl_employee = $tbl_employee->where(function ($q) {
                    $q->whereIn("branch_id",[50,91,52,49,55,74,93])
                    ->orWhere("user_id", Auth::user()->id); // always include self
                });
            } elseif($role_id === 25){
                // Asia Marie Dumat-ol
                // VIS - HM TOWER
                // VIS - JMALL
                // VIS - ILOILO
                // VIS - BACOLOD
                $tbl_employee = $tbl_employee->where(function ($q) {
                    $q->whereIn("branch_id",[49,55,74,93])
                    ->orWhere("user_id", Auth::user()->id); // always include self
                });
            } elseif($role_id === 26){
                // Alvin Lut Fregillana
                // Aeon
                // Davao Airport
                // SM City Davao
                // KCC Zamboanga
                $tbl_employee = $tbl_employee->where(function ($q) {
                    $q->whereIn("branch_id",[56,92,95,96,])
                    ->orWhere("user_id", Auth::user()->id); // always include self
                });
            } else{
                $tbl_employee = $tbl_employee->where("id",Auth::user()->company["linked_employee"]["id"]);
            }
            
            $tbl_employee = $tbl_employee->orderBy("last_name")
            ->orderBy("first_name")
            ->orderBy("middle_name")
            ->get();
        }
        $lib_week_schedule = DB::connection("intra_payroll")->table("lib_week_schedule")
        ->where('is_active', 1)
        ->orderBy("name")
        ->get();
        $lib_schedule = DB::connection("intra_payroll")->table("lib_schedule")
        ->where("is_active", 1)
        ->get();
        $tbl_branch = DB::connection("intra_payroll")->table("tbl_branch")
        ->where("is_active", 1)
        ->orderBy("branch","ASC")
        ->get();
        $lib_ot_table = DB::connection("intra_payroll")->table("lib_ot_table")
        ->whereNotIn("code", ['ND','RH','SH','RDRH','RDSH'])
        ->get();
        
      
        return view("timekeeping.index")
            ->with("tbl_employee", $tbl_employee)
            ->with("lib_week_schedule", $lib_week_schedule)
            ->with("lib_schedule", $lib_schedule)
            ->with("tbl_branch", $tbl_branch)
            ->with("lib_ot_table",$lib_ot_table)
            ;
    }
    public function ot_table_tbl(Request $request){
        $page_permission = Auth::user()->access[$request->page]["access"];
   
        $data = DB::connection("intra_payroll")->table("lib_ot_table")
            ->orderBy("name")
            ->get();
        $data = collect($data);
        return Datatables::of($data)
        ->addColumn('action', function($row) use ($page_permission, $request){
            $btn = "";
            if(preg_match("/U/i", $page_permission)){
                if(Auth::user()->access[$request->page]["user_type"] != "employee"){
                    $type = "ot_table";
                    $btn .= "<a 
                    class='btn btn-sm btn-success mr-1'
                    data-id = '".$row->id."'
                    data-code = '".$row->code."'
                    data-name = '".$row->name."'
                    data-rate = '".$row->rate."'    
                    data-toggle='modal' 
                    data-target='#ot_table_modal'
                    >
                    Edit
                    </a>";
                    // add delete in tk
                    $btn .= " <button 
                    class='btn btn-sm btn-danger'
                    onclick='delete_ot(" . $row->id . ", \"" . $type . "\")'
                    >
                    Delete
                    </button>";
                }
                
              
                
            }
          
            return $btn;
        })
        ->rawColumns(['action', 'dates'])
        ->make(true);
    }
    public function update_ot_rate(Request $request){
        DB::beginTransaction();
        try {
                DB::connection("intra_payroll")->table("lib_ot_table")
                ->where('id', $request->id)
                ->update([
                    "rate" => $request->rate
                ]);
            
                DB::commit();
            return json_encode("Success");
        } catch (\Throwable $th) {
            DB::rollback();
            return json_encode($th->getMessage());
        }
    }
    public function get_daily_sched_info(Request $request){
        $lib_schedule = DB::connection("intra_payroll")->table("lib_schedule")
            ->where("id", $request->id)
            ->first();
            if($lib_schedule != null){
                $return = array(
                    "is_flexi" => $lib_schedule->is_flexi,
                    "required_hours" => $lib_schedule->required_hours,
                    
                    "am_in" => date("h:i A", strtotime($lib_schedule->am_in)),
                    "am_out" => date("h:i A", strtotime($lib_schedule->am_out)),
                    "pm_in" => date("h:i A", strtotime($lib_schedule->pm_in)),
                    "pm_out" => date("h:i A", strtotime($lib_schedule->pm_out)),
                    "ot_in" => date("h:i A", strtotime($lib_schedule->ot_in)),
                    "ot_out" => date("h:i A", strtotime($lib_schedule->ot_out)),
                    "grace_period" => $lib_schedule->grace_period
                );
            }else{
                $return = array(
                    "is_flexi" => "0",
                    "required_hours" => "-",
                    "am_in" => "-",
                    "am_out" => "-",
                    "pm_in" => "-",
                    "pm_out" => "-",
                    "ot_in" => "-",
                    "ot_out" => "-",
                    "grace_period" => "-",
                );
            }
            
            return json_encode($return);
    }
    public function delete_daily_sched(Request $request){
        $target_day = date("Y-m-d", strtotime($request->target_day));
    
        DB::beginTransaction();
        try {
            DB::connection("intra_payroll")->table("tbl_daily_schedule")
                ->where("emp_id", $request->emp_id)
                ->where("schedule_date", $target_day)
                ->delete();
            DB::commit();
            return json_encode($target_day);
        } catch (\Throwable $th) {
            DB::rollback();
            return json_encode($th->getMessage());
        }
    }
    public function set_daily_sched(Request $request){
            $target_day = date("Y-m-d", strtotime($request->target_day));
            $branch_id = $request->branch_id ?? '';
    
        DB::beginTransaction();
        try {
            DB::connection("intra_payroll")->table("tbl_daily_schedule")
                ->where("emp_id", $request->emp_id)
                ->where("schedule_date", $target_day)
                ->delete();
                DB::connection("intra_payroll")->table("tbl_daily_schedule")
                    ->insert([
                        "emp_id" => $request->emp_id,
                        "schedule_date" => $target_day,
                        "schedule_id" => $request->by_emp_lib_schedule,
                        "branch_id" => $branch_id,
                        "date_created" => date("Y-m-d H:i:s"),
                        "user_id" => Auth::user()->id
                    ]);
            DB::commit();
            return json_encode($target_day);
        } catch (\Throwable $th) {
            DB::rollback();
            return json_encode($th->getMessage());
        }
    }
    public function get_emp_default_schedule(Request $request){
        $tbl_employee = DB::connection("intra_payroll")->table("tbl_employee")
            ->where("id", $request->emp_id)
            ->value("schedule_id");
            if($tbl_employee != null){
                return json_encode($tbl_employee);
            }else{
                return json_encode("0");
            }
    }
    
    public function get_temp_log(Request $request){
        date_default_timezone_set('Asia/Manila');
        // dd($request->all());
        $biometric_id = "";
      
        $bio_id = DB::connection("intra_payroll")->table("tbl_employee")
            ->where("id", $request->punch_emp) 
            ->first();
            if($bio_id != null){
                $biometric_id = $bio_id->bio_id;
            }
        if($biometric_id == "" || $biometric_id == null){
            return json_encode("No Biometric ID");
        }
     
        
        // $get_current_schedule = DB::connection("intra_payroll")->table("tbl_employee")
        //GET SCHEDULE OF EMPLOYEE BASE ON GETTING DATA
        $schedule = $this->get_schedule_by_day($bio_id->id, date("Y-m-d"));
       
        if($schedule == "false"){
            return json_encode("No Biometric ID");
        }
        if($schedule["is_flexi"] == 1){
            $required_hours = $schedule["required_hours"];
            $log_data = array();
            $log_data["required_hours"] = $required_hours;
            $logs = DB::connection("intra_payroll")->table("tbl_raw_logs")
                ->where("biometric_id", $biometric_id)
                ->where("logs", "LIKE", date("Y-m-d")."%")
                ->where("state", "LIKE", "FLEX_%")
                ->orderBy("logs", "ASC")
                ->get();
            $last_state = "";
            $cur_log = "";
            $total_consume = 0;
            foreach($logs as $log){
                if($cur_log == ""){
                  
                    if($log->state == "FLEX_IN"){
                        $cur_log = $log->logs;
                        $last_state = $log->state;
                    }
                }else{
                    if($log->state == "FLEX_OUT"){
                        if(strtotime($log->logs)>strtotime($cur_log) ){
                            
                            $time_consume = strtotime($cur_log)- strtotime($log->logs);
                            //CHECK COMPUTATION
                            $total_consume += round(abs($time_consume) / 60,2);
                            $cur_log = $log->logs;
                            $last_state = $log->state;
                        }
                    }else{
                        $cur_log = $log->logs;
                        $last_state = $log->state;
                    }
                   
                }
                
            }
        
            $log_data["flex_state"] = $last_state;
            $log_data["consumed"] = number_format($total_consume / 60, 2);
        }else{
            $logs = DB::connection("intra_payroll")->table("tbl_raw_logs")
            ->where("biometric_id", $biometric_id)
            ->where("logs", "LIKE", date("Y-m-d")."%")
            ->where("state", "NOT LIKE", "FLEX_%")
            ->groupBy("state")
            ->get();
            $log_data = array();
            $log_data["AM_IN"] = "";
            $log_data["AM_OUT"] = "";
            $log_data["PM_IN"] = "";
            $log_data["PM_OUT"] = "";
            $log_data["OT_IN"] = "";
            $log_data["OT_OUT"] = "";
            
            foreach($logs as $log){
                if($log->state == "AM_IN"){  $log_data["AM_IN"] = $log->logs;}
                if($log->state == "AM_OUT"){ $log_data["AM_OUT"] = $log->logs;}
                if($log->state == "PM_IN"){  $log_data["PM_IN"] = $log->logs;}
                if($log->state == "PM_OUT"){ $log_data["PM_OUT"] = $log->logs;}
                if($log->state == "OT_IN"){  $log_data["OT_IN"] = $log->logs;}
                if($log->state == "OT_OUT"){ $log_data["OT_OUT"] = $log->logs;}
                
            }
        }
      
         
        $log_data["flexi"] = $schedule["is_flexi"];
        
        return json_encode($log_data);
    }
    public function punch_in_out_ins(Request $request){
        date_default_timezone_set('Asia/Manila');
        // dd($request->all());
        $biometric_id = "";
        $bio_id = DB::connection("intra_payroll")->table("tbl_employee")
            ->where("id", $request->emp_id) 
            ->first();
            if($bio_id != null){
                $biometric_id = $bio_id->bio_id;
            }
            if($biometric_id == "" || $biometric_id == null){
                return json_encode("No Biometric ID");
            }
            
            DB::beginTransaction();
            try {
                
                DB::connection("intra_payroll")->table("tbl_raw_logs")
                    ->insert([
                        "biometric_id" => $biometric_id,
                        "state" => $request->state,
                        "logs" => date("Y-m-d H:i:s")
                    ]);
                DB::commit();
                return json_encode("Success " . $request->state);
            } catch (\Throwable $th) {
                //throw $th;
                DB::rollback();
                return json_encode($th->getMessage());
            }
        
    }
    public function get_punch_in_out_emp(Request $request){
        
        if(Auth::user()->access[$request->page]["user_type"] != "Admin"){
            $emp_data = DB::connection("intra_payroll")->table("tbl_employee")
                ->where("id", Auth::user()->company["linked_employee"]["id"])
                ->get();
        }else{
            $emp_data = DB::connection("intra_payroll")->table("tbl_employee")
            ->where('is_active',1)
            ->get();
        }
        return json_encode($emp_data);
    }
     public function applied_ot_tbl(Request $request)
    {
        $role_id = Auth::user()->role_id;
        $page_permission = Auth::user()->access[$request->page]["access"];
        $ot_type_array = [
            "ROT" => "Regular OT",
            "NDOT" => "Night Diff. OT",
            "SOT" => "Special OT",
            "RDOT" => "Rest Day OT",
            "RD_RH_OT" => "Rest Day RH OT",
            "RD_SH_OT" => "Rest Day SH OT",
            "SH_OT" => "Special Holiday OT",
            "RH_OT" => "Regular Holiday OT",
        ];
        $tbl_branch = json_decode(json_encode(
            DB::connection("intra_payroll")
                ->table("tbl_branch")
                ->get()
        ), true);
        $query = DB::connection("intra_payroll")
            ->table("tbl_ot_applied as ot")
            ->join("tbl_employee as emp", "emp.id", "=", "ot.emp_id")
            ->select(
                "ot.*",
                "emp.first_name",
                "emp.middle_name",
                "emp.last_name",
                "emp.ext_name",
                "emp.department",
                "emp.hr_group",
                "emp.user_id",
                "emp.branch_id as emp_branch_id"
            );
        // Filter by user type and date range
        if (Auth::user()->access[$request->page]["user_type"] != "employee") {
            if ($request->filled('date_range')) {
                [$start_date, $end_date] = explode(' - ', $request->date_range);
                $query->whereBetween('ot.date_target', [$start_date . ' 00:00:00', $end_date . ' 23:59:59']);
            }
            if ($role_id === 4) {
                // $query->where("emp.hr_group", "group_a");
                $query->where(function ($q) {
                    $q->where("emp.hr_group", "group_d")
                    ->orWhere("emp.user_id", Auth::user()->id);
                });
            } elseif ($role_id === 5) {
                // $query->where("emp.hr_group", "group_b");
                $query->where(function ($q) {
                    $q->whereIn("emp.hr_group", ["group_b","group_c","group_e"])
                    ->orWhere("emp.user_id", Auth::user()->id);
                });
            } elseif ($role_id === 14) { //aimee
                // $query->where(function ($q) {
                //     $q->where("emp.hr_group", "group_c")
                //     ->orWhere("emp.user_id", Auth::user()->id);
                // });
                $query->where(function($q) {
                    $q->where(function($q1) {
                        $q1->where("emp.branch_id", 82)
                        ->whereIn("ot.status", ["1st_Approved","APPROVED"]);
                    });
                })
                ->orWhere("emp.user_id", Auth::user()->id);
            } elseif ($role_id === 15) { //james yu
                // $query->where(function ($q) {
                //     $q->where("emp.hr_group", "group_d")
                //     ->orWhere("emp.user_id", Auth::user()->id);
                // });
                $query->where(function($q) {
                    $q->where(function($q1) {
                        $q1->whereNotIn("emp.branch_id", [75,76])
                        ->whereIn("ot.status", ["1st_Approved","APPROVED"]);
                    })
                    ->orWhere(function($q2) {
                        $q2->whereIn("emp.emp_code", ["3004","3018","3020","3010","3003","3034","3028","2015","2125"]) // managers
                        ->whereIn("ot.status", ["1st_Approved","APPROVED"]);
                    });
                })
                ->orWhere("emp.user_id", Auth::user()->id);
            } 
            // elseif ($role_id === 22) {
            //     $query->where(function ($q) {
            //         $q->where("emp.hr_group", "group_e")
            //         ->orWhere("emp.user_id", Auth::user()->id);
            //     });
            // }
        } else {
            if ($request->filled('date_range')) {
                [$start_date, $end_date] = explode(' - ', $request->date_range);
                $query->whereBetween('ot.date_target', [$start_date . ' 00:00:00', $end_date . ' 23:59:59']);
            }
            if($role_id === 6){ //1st Approver - Joefran Aeon Tower/Mindanao, Cebu/CAD, Gensan, HM Tower, Howard Hubbard Hospital, Iloilo, JMALL
                $query->where(function($q){
                    $q->whereIn("emp.branch_id", [56,78,52,51,55,72,49,74])
                    ->whereIn("ot.status", ["FILED","APPROVED"]);
                })
                ->orWhere("emp.user_id", Auth::user()->id);
            }elseif($role_id === 7){ //1st Approver - Leo Banaran, Sta. Clara, Tawi-Tawi, Zamboanga
                $query->where(function($q){
                    $q->whereIn("emp.branch_id", [61,60,63,64])
                     ->whereIn("ot.status", ["FILED","APPROVED"]);
                })
                ->orWhere("emp.user_id", Auth::user()->id);
            }elseif($role_id === 8){ // 1st Approver - RA Jabson Batangas, Bicol 1, Bicol 2, Candido - Operations, Laguna, NCR, Palawan
                $query->where(function($q){
                    // $q->whereIn("emp.branch_id", [57,59,70,46,71,50,62])
                    $q->where("emp.hr_group", "group_e")
                     ->whereIn("ot.status", ["FILED","APPROVED"]);
                })
                ->orWhere("emp.user_id", Auth::user()->id);
            }elseif($role_id === 9){ //1st Approver - Anafe (Warehouse)
                $query->where(function($q){
                    $q->where("emp.branch_id", 77)
                     ->whereIn("ot.status", ["FILED","APPROVED"]);
                })
                ->orWhere("emp.user_id", Auth::user()->id);
            }elseif($role_id === 10){ //Final App - James Brian
                $query->where(function($q) {
                    $q->where(function($q1) {
                        $q1->whereNotIn("emp.branch_id", [75,76])
                        ->whereIn("ot.status", ["1st_Approved","APPROVED"]);
                    })
                    ->orWhere(function($q2) {
                        $q2->whereIn("emp.emp_code", ["3004","3018","3020","3010","3003","3034","3028","2015","2125"]) // managers
                        ->whereIn("ot.status", ["1st_Approved","APPROVED"]);
                    });
                })
                ->orWhere("emp.user_id", Auth::user()->id);
            }elseif($role_id === 11){ //Final App - Dorcas (FA)
                $query->where(function($q){
                    $q->where("emp.branch_id", 75)
                    ->whereIn("ot.status", ["1st_Approved","APPROVED"]);
                })
                ->orWhere("emp.user_id", Auth::user()->id);
            }elseif($role_id === 12){ // Final App - Ajes (HRAD)
                $query->where(function($q) {
                    $q->where(function($q1) {
                        $q1->where("emp.branch_id", 76)
                        ->whereIn("ot.status", ["1st_Approved","APPROVED"]);
                    });
                })
                ->orWhere("emp.user_id", Auth::user()->id);
            }elseif($role_id === 13){ // Final App - Aimee (Dorotea)
                $query->where(function($q) {
                    $q->where(function($q1) {
                        $q1->where("emp.branch_id", 82)
                        ->whereIn("ot.status", ["1st_Approved","APPROVED"]);
                    });
                })
                ->orWhere("emp.user_id", Auth::user()->id);
            }else {
                // normal employee can only see their own leaves
                $query->where("ot.emp_id", Auth::user()->company["linked_employee"]["id"]);
            }
        }
        $list = $query->orderBy("ot.date_target", "DESC")->get();
        return Datatables::of($list)
            ->addColumn('name', function($row) {
                return "{$row->last_name}, {$row->first_name} {$row->middle_name} {$row->ext_name}";
            })
            ->addColumn('ot_site', function($row) use ($tbl_branch){
                $branch_id = $row->branch_id;
                // if OT branch is null use employee branch
                if(empty($branch_id)){
                    $branch_id = $row->emp_branch_id;
                }
                $branch = $this->search_multi_array($tbl_branch, "id", $branch_id);
                return $branch ? $branch["branch"] : "";
            })
            ->addColumn('ot_type', function($row) use ($ot_type_array) {
                return $ot_type_array[$row->ot_type] ?? $row->ot_type;
            })
            ->addColumn('ot_date', function($row) {
                return $row->date_target;
            })
            ->addColumn('ot_time', function($row) {
                $ot_time = "";
                $ot_time .= "<label class='btn btn-info btn-sm w-100'>FROM: " . date("H:i A", strtotime($row->time_from)) . "</label><br>";
                $ot_time .= "<label class='btn btn-info btn-sm w-100'>TO: " . date("H:i A", strtotime($row->time_to)) . "</label>";
                return $ot_time;
            })
            ->addColumn('ot_reason', function($row) {
                $short = substr($row->reason, 0, 10);
                return '<span title="' . e($row->reason) . '">' . e($short) . '...</span>';
            })
            ->addColumn('ot_status', function($row) {
                $status = $row->status;
                $role_id = Auth::user()->role_id;
                if ($status === "FILED") {
                    $status = "<span class='badge badge-warning'>WAITING FOR 1ST APPROVAL</span>";
                } elseif ($status === "1st_Approved") {
                    $status = "<span class='badge badge-info'>FOR FINAL APPROVAL</span>";
                } elseif ($status === "APPROVED") {
                    $status = "<span class='badge badge-success'>APPROVED</span>";
                }
                return $status;
            })
            ->addColumn('action', function($row) use ($page_permission, $request) {
                $btn = "";
                if (preg_match("/U/i", $page_permission)) {
                    $type = "ot_request";
                    if (Auth::user()->access[$request->page]["user_type"] != "employee") {
                        $btn .= "<a 
                            class='btn btn-sm btn-success mr-1'
                            data-toggle='modal' 
                            data-target='#ot_apply_modal'
                            data-id = '{$row->id}'
                            data-emp_id = '{$row->emp_id}'
                            data-ot_type = '{$row->ot_type}'
                            data-ot_date = '{$row->date_target}'                        
                            data-ot_from = '" . date("H:i", strtotime($row->time_from)) . "'                        
                            data-ot_to = '" . date("H:i", strtotime($row->time_to)) . "'              
                            data-reason ='{$row->reason}'
                            data-ot_site = '{$row->branch_id}'
                            data-ot_status = '{$row->status}'
                            >
                            Edit
                            </a>";
                        $btn .= " <button 
                            class='btn btn-sm btn-danger'
                            onclick='delete_ot({$row->id}, \"{$type}\")'
                            >
                            Delete
                            </button>";
                    }else{
                        $class_disable = '';
                        if($row->status === 'APPROVED'){
                            $class_disable = 'disabled';
                        }
                        $btn .= "<a 
                            class='btn btn-sm btn-success mr-1 $class_disable'
                            data-toggle='modal' 
                            data-target='#ot_apply_modal'
                            data-id = '{$row->id}'
                            data-emp_id = '{$row->emp_id}'
                            data-ot_type = '{$row->ot_type}'
                            data-ot_date = '{$row->date_target}'                        
                            data-ot_from = '" . date("H:i", strtotime($row->time_from)) . "'                        
                            data-ot_to = '" . date("H:i", strtotime($row->time_to)) . "'              
                            data-reason ='{$row->reason}'
                            data-ot_site = '{$row->branch_id}'
                            data-ot_status = '{$row->status}'
                            >
                            Edit
                            </a>";
                        $btn .= " <button 
                            class='btn btn-sm btn-danger'
                            onclick='delete_ot({$row->id}, \"{$type}\")'
                            disabled
                            >
                            Delete
                            </button>";
                    }
                }
                return $btn;
            })
            ->rawColumns(['action', 'ot_time', 'ot_reason','ot_status', 'attachment'])
            ->make(true);
    }
    public function applied_ot_tbl_newest(Request $request)
    {
        $user = Auth::user();
        $role_id = $user->role_id;
        // Base employee query
        $employeeQuery = DB::connection("intra_payroll")->table("tbl_employee");
        // Add role-based group filter
        if ($role_id === 4) { // HR Group A
            $employeeQuery->where("hr_group", "group_a");
        } elseif ($role_id === 5) { // HR Group B
            $employeeQuery->where("hr_group", "group_b");
        }
        $employee = json_decode(json_encode($employeeQuery->get()), true);
        $page_permission = $user->access[$request->page]["access"];
        $ot_type_array = array(
            "ROT" => "Regular OT",
            "NDOT" => "Night Diff. OT",
            "SOT" => "Special OT",
            "RDOT" => "Rest Day OT",
            "RD_RH_OT" => "Rest Day RH OT",
            "RD_SH_OT" => "Rest Day SH OT",
            "SH_OT" => "Special Holiday OT",
            "RH_OT" => "Regular Holiday OT",
        );
        // Handle OT list
        if ($user->access[$request->page]["user_type"] != "employee") {
            $list = DB::connection("intra_payroll")->table("tbl_ot_applied");
            // Role-based filtering for HR users
            if ($role_id === 4) {
                $list->whereIn('emp_id', function ($query) {
                    $query->select('id')
                        ->from('tbl_employee')
                        ->where('hr_group', 'group_a');
                });
            } elseif ($role_id === 5) {
                $list->whereIn('emp_id', function ($query) {
                    $query->select('id')
                        ->from('tbl_employee')
                        ->where('hr_group', 'group_b');
                });
            }
            // Date filter
            if ($request->filled('date_range')) {
                [$start_date, $end_date] = explode(' - ', $request->date_range);
                $list->whereBetween('date_target', [$start_date . ' 00:00:00', $end_date . ' 23:59:59']);
            }
            $list = $list->orderBy("date_target", "DESC")->get();
        } else {
            // Employee view
            $list = DB::connection("intra_payroll")->table("tbl_ot_applied")
                ->where("emp_id", $user->company["linked_employee"]["id"])
                ->orderBy("date_target", "DESC")
                ->get();
        }
        $data = collect($list);
        return Datatables::of($data)
            ->addColumn('name', function ($row) use ($employee) {
                $data_emp = $this->search_multi_array($employee, "id", $row->emp_id);
                if (!empty($data_emp) || $data_emp != null) {
                    return $data_emp["last_name"] . ", " . $data_emp["first_name"] . " " . $data_emp["middle_name"] . " " . $data_emp["ext_name"];
                } else {
                    return "";
                }
            })
            ->addColumn('ot_type', function ($row) use ($ot_type_array) {
                return $ot_type_array[$row->ot_type];
            })
            ->addColumn('ot_date', function ($row) {
                return $row->date_target;
            })
            ->addColumn('ot_time', function ($row) {
                $ot_time = "";
                $ot_time .= "<label class='btn btn-info btn-sm w-100'>FROM: " . date("H:i A", strtotime($row->time_from));
                $ot_time .= "</label><br>";
                $ot_time .= "<label class='btn btn-info btn-sm w-100'>TO: " . date("H:i A", strtotime($row->time_to));
                $ot_time .= "</label>";
                return $ot_time;
            })
            ->addColumn('ot_reason', function ($row) {
                $short = substr($row->reason, 0, 10);
                return '<span title="' . e($row->reason) . '">' . e($short) . '...</span>';
            })
            ->addColumn('ot_status', function ($row) {
                return $row->status;
            })
            ->addColumn('action', function ($row) use ($page_permission, $request) {
                $btn = "";
                if (preg_match("/U/i", $page_permission)) {
                    if (Auth::user()->access[$request->page]["user_type"] != "employee") {
                        $type = "ot_request";
                        $btn .= "<a 
                            class='btn btn-sm btn-success mr-1'
                            data-toggle='modal' 
                            data-target='#ot_apply_modal'
                            data-id='" . $row->id . "'
                            data-emp_id='" . $row->emp_id . "'
                            data-ot_type='" . $row->ot_type . "'
                            data-ot_date='" . $row->date_target . "'                        
                            data-ot_from='" . date("H:i", strtotime($row->time_from)) . "'                        
                            data-ot_to='" . date("H:i", strtotime($row->time_to)) . "'              
                            data-reason ='" . $row->reason . "'
                            data-ot_status='" . $row->status . "'
                            >
                            Edit
                            </a>";
                        $btn .= " <button 
                            class='btn btn-sm btn-danger'
                            onclick='delete_ot(" . $row->id . ", \"" . $type . "\")'
                            >
                            Delete
                            </button>";
                    }
                }
                return $btn;
            })
            ->rawColumns(['action', 'ot_time', 'ot_reason'])
            ->make(true);
    }
    public function applied_ot_tbl_old(Request $request){
        $employee = json_decode(json_encode(
            DB::connection("intra_payroll")->table("tbl_employee")
                ->get()
        ), true);
      
     
        $page_permission = Auth::user()->access[$request->page]["access"];
        $ot_type_array = array(
            "ROT" => "Regular OT",
            "NDOT" => "Night Diff. OT",
            "SOT" => "Special OT",
            "RDOT" => "Rest Day OT",
            "RD_RH_OT" => "Rest Day RH OT",
            "RD_SH_OT" => "Rest Day SH OT",
            "SH_OT" => "Special Holiday OT",
            "RH_OT" => "Regular Holiday OT",
        );
    
            if(Auth::user()->access[$request->page]["user_type"] != "employee"){
                $list = DB::connection("intra_payroll")->table("tbl_ot_applied");
                if ($request->filled('date_range')) {
                    [$start_date, $end_date] = explode(' - ', $request->date_range);
                    $list->whereBetween('date_target', [$start_date . ' 00:00:00', $end_date . ' 23:59:59']);
                }
                $list = $list->orderBy("date_target","DESC")->get();
    
            }else{
            
                $list = DB::connection("intra_payroll")->table("tbl_ot_applied")
                ->where("emp_id", Auth::user()->company["linked_employee"]["id"])
                ->orderBy("date_target","DESC")
                ->get();
    
            }
    
            $data = collect($list);
            return Datatables::of($data)
            ->addColumn('name', function($row) use ($employee){
                $data_emp = $this->search_multi_array($employee, "id", $row->emp_id);
                if(!empty($data_emp) || $data_emp != null){
                    return $data_emp["last_name"].", ".$data_emp["first_name"]." ".$data_emp["middle_name"]." ".$data_emp["ext_name"];
                }else{
                    return "";
                }
                
            })
            ->addColumn('ot_type', function($row) use ($ot_type_array) {
                return $ot_type_array[$row->ot_type];
            })
            ->addColumn('ot_date', function($row){
                return $row->date_target;
            })
            ->addColumn('ot_time', function($row){
                $ot_time = "";
                $ot_time .= "<label class='btn btn-info btn-sm w-100'>FROM: ".date("H:i A", strtotime($row->time_from));
                $ot_time .= "</label><br>";
                $ot_time .= "<label class='btn btn-info btn-sm w-100'>TO: ".date("H:i A", strtotime($row->time_to));
                $ot_time .= "</label>";
                return $ot_time;
            })
            ->addColumn('ot_reason', function($row){
                $short = substr($row->reason, 0, 10);
                return '<span title="'.e($row->reason).'">'.e($short).'...</span>';
            })
            ->addColumn('ot_status', function($row){
                return $row->status;
            })
            ->addColumn('action', function($row) use ($page_permission, $request){
                $btn = "";
                if(preg_match("/U/i", $page_permission)){
                    if(Auth::user()->access[$request->page]["user_type"] != "employee"){
                        $type = "ot_request";
                        $btn .= "<a 
                        class='btn btn-sm btn-success mr-1'
                        data-toggle='modal' 
                        data-target='#ot_apply_modal'
                        data-id = '".$row->id."'
                        data-emp_id = '".$row->emp_id."'
                        data-ot_type = '".$row->ot_type."'
                        data-ot_date = '".$row->date_target."'                        
                        data-ot_from = '".date("H:i", strtotime($row->time_from))."'                        
                        data-ot_to = '".date("H:i", strtotime($row->time_to))."'              
                        data-reason ='".$row->reason."'
                        data-ot_status = '".$row->status."'
                        >
                        Edit
                        </a>";
                        // add delete in tk
                        $btn .= " <button 
                        class='btn btn-sm btn-danger'
                        onclick='delete_ot(" . $row->id . ", \"" . $type . "\")'
                        >
                        Delete
                        </button>";
                    }
                    
                  
    
                    
                }
              
                return $btn;
            })
            ->rawColumns(['action', 'ot_time','ot_reason'])
            ->make(true);
    }
    public function apply_ot(Request $request){
        DB::beginTransaction();
        try {
            $from = strtotime($request->ot_from);
            $to = strtotime($request->ot_to);
            $ot_from = date("H:i:s", $from);
            $ot_to = date("H:i:s", $to);
            if($to < $from){
                $ot_from = date("Y-m-d H:i:s", strtotime($request->ot_date." ".$ot_from));
                $ot_to = date("Y-m-d H:i:s", strtotime($request->ot_date." ".$ot_to." +1 day"));
            }else{
                $ot_from = date("Y-m-d H:i:s", strtotime($request->ot_date." ".$ot_from));
                $ot_to = date("Y-m-d H:i:s", strtotime($request->ot_date." ".$ot_to));
            }
            if($request->ot_status == "APPROVED" || $request->ot_status == "REJECTED"){
                $user_approved = Auth::user()->id;
            }else{
                $user_approved = "0";
            }
            $check_ot = DB::connection("intra_payroll")->table("tbl_ot_applied")
            ->where("id", $request->ot_id)
            ->first();
            if($check_ot != null){
                //UPDATE
                DB::connection("intra_payroll")->table("tbl_ot_applied")
                    ->where("id", $check_ot->id)
                    ->update([
                        "emp_id" => $request->ot_emp_name,
                        "ot_type" => $request->ot_type,
                        "date_target" => $request->ot_date,
                        "time_from" => $ot_from,
                        "time_to" => $ot_to,
                        "status" => $request->ot_status,
                        "reason" => $request->ot_reason,
                        "user_approved" => $user_approved,
                        "user_id" => Auth::user()->id,
                        "branch_id" => $request->ot_site
                    ]);
            }else{
                DB::connection("intra_payroll")->table("tbl_ot_applied")
                ->insert([
                    "emp_id" => $request->ot_emp_name,
                    "ot_type" => $request->ot_type,
                    "date_target" => $request->ot_date,
                    "time_from" => $ot_from,
                    "time_to" => $ot_to,
                    "status" => $request->ot_status,
                    "reason" => $request->ot_reason,
                    "user_approved" => $user_approved,
                    "date_created" => date("Y-m-d H:i:s"),
                    "user_id" => Auth::user()->id,
                    "branch_id" => $request->ot_site
                ]);
            }
            DB::commit();
            return json_encode("Success");
        } catch (\Throwable $th) {
            DB::rollback();
            return json_encode($th->getMessage());
        }
   
    }
    public function update_emp_def_schedule(Request $request){
        
        DB::beginTransaction();
                
        try {
            $tbl_employee = DB::connection("intra_payroll")->table("tbl_employee")
                ->where("id", $request->emp_id)
                ->update([
                    "schedule_id" => $request->emp_def_sched
                ]);
            DB::commit();
            return json_encode("Set Schedule Success");
        } catch (\Throwable $th) {
            DB::rollback();
            return json_encode($th->getMessage());
        }
    }
    private function gettimecard($from,$now) {
        $tbl_time_card = DB::connection("intra_payroll")->table("tbl_timecard")
            ->whereBetween("target_date", [$from, $now])
            ->get();
        foreach($tbl_time_card as $tmecard){
            $this->autofileOT( $tmecard->emp_id, $tmecard->target_date);
        }
    }
    private function get_schedule_data($sched_id, $day_name){
        $lib_schedule = DB::connection("intra_payroll")->table("lib_schedule")->where('is_active', 1)->get();
        $lib_schedule = json_decode(json_encode($lib_schedule),true);
        $lib_week_schedule = DB::connection("intra_payroll")->table("lib_week_schedule")->where('is_active', 1)->get();
        $lib_week_schedule = json_decode(json_encode($lib_week_schedule),true);
            $lib_week_sched = $this->search_multi_array($lib_week_schedule, "id", $sched_id);
                if(isset($lib_week_sched)){
                    $lib_sched = $this->search_multi_array($lib_schedule, "id", $lib_week_sched[$day_name]);
                        if(isset($lib_sched["id"])){
                          return $lib_sched;
                        }else{
                           return "RD";
                        }
                }else{
                    return "RD";
             }
    }
    public function sched_req_tbl(Request $request){
            
        $emp_id = $request->sched_req_employee;
        
        if(Auth::user()->access[$request->page]["user_type"] != "employee"){
            if($emp_id == "0"){
                $emp_id = "%";
            }
        }else{
            if($emp_id == "0"){
                $get_emp_linked = DB::connection("intra_payroll")->table("tbl_employee")->where("user_id", Auth::user()->id)->value("id");
                if($get_emp_linked != null){
                    $emp_id = $get_emp_linked;
                }
                
            }
        }
        $lib_schedule = DB::connection("intra_payroll")->table("lib_schedule")->where('is_active', 1)->get();
        $lib_schedule = json_decode(json_encode($lib_schedule),true);
        $lib_employee = DB::connection("intra_payroll")->table("tbl_employee")->where('is_active', 1)->get();
        $lib_employee = json_decode(json_encode($lib_employee),true);
        $start_date = date("Y-m-01");
        $end_date = date("Y-m-d");
        if ($request->filled('sched_req_date_range')) {
            $arr = explode(' - ', $request->sched_req_date_range);
             $start_date = $arr[0];
             $end_date = $arr[1];
         }
 
         
        $data =DB::connection("intra_payroll")->table("tbl_schedule_request")
                ->where("emp_id", "LIKE", $emp_id)
                ->whereBetween("target_date", [$start_date, $end_date])
                ->orderBy("target_date", "DESC")
                ->get();
        
        
        $data_tbl = collect($data);
        return Datatables::of($data_tbl)
        ->addColumn('status', function($row){
            if($row->status == "0"){
                return "Filed";
            }elseif($row->status == "1"){
                return "Approved";
            }elseif($row->status == "2"){
                return "Declined";
            }elseif($row->status == "3"){
                return "Cancelled";
            }
        })
        ->addColumn('name', function($row) use ($lib_employee){
            
            $lib_emp = $this->search_multi_array($lib_employee, "id", $row->emp_id);
                if(count($lib_emp)>0){
                    return $lib_emp['last_name'].", ".$lib_emp['first_name'];   
                }else{
                    return "-";
                }
        })
        ->addColumn('schedule', function($row) use ($lib_schedule){
            
            $lib_sched = $this->search_multi_array($lib_schedule, "id", $row->schedule_id);
            if($lib_sched != null){
                if(count($lib_sched)>0){
                    return $lib_sched['name'];   
                }else{
                    return "Rest Day";
                }
            }else{
                return "Rest Day";
            }
                
        })
        ->addColumn('action', function($row) use ($request){
            $btn = "";
            if(Auth::user()->access[$request->page]["user_type"] != "employee"){
                if($row->status == "0"){
                    $btn .= "<button onclick='req_sched_action(".$row->id.", 1)' class='btn btn-success btn-sm'>Approved</button>";
                    $btn .= "<button onclick='req_sched_action(".$row->id.", 2)' class='btn btn-danger btn-sm'>Declined</button>";
                    
                }elseif($row->status == "1"){
                    $btn .= "<button onclick='req_sched_action(".$row->id.", 9)' class='btn btn-success btn-sm'>Repost</button>";
                }
            }else{
                if($row->status == "0"){
                    $btn .= "<button onclick='req_sched_action(".$row->id.", 3)' class='btn btn-warning btn-sm'>Cancel</button>";
                }
                
            }
            return $btn;
        })
        ->rawColumns(['action'])
        ->make(true);
    }
    public function req_sched_add(Request $request){
        $sched_target_date = $request->sched_target_date;
        $sched_req_select = $request->sched_req_select;
        $get_emp_linked = DB::connection("intra_payroll")->table("tbl_employee")->where("user_id", Auth::user()->id)->first();
        if($get_emp_linked != null){
            $emp_id = $get_emp_linked->id;
            DB::connection("intra_payroll")->table("tbl_schedule_request")
            ->insert([
                "status" => "0",
                "emp_id" => $emp_id,
                "target_date" => $sched_target_date,
                "schedule_id" => $sched_req_select,
                "user_id" => Auth::user()->id
            ]);
            return json_encode("Success");
        }else{
            return json_encode("Employee not found, Please Re-login");
        }
    }
    public function req_sched_action(Request $request){
        $status = $request->status;
        $id = $request->id;
        $sched_id = DB::connection("intra_payroll")->table("tbl_schedule_request")
                ->where("id", $id)
                ->first();
        if($sched_id != null){
            $emp_id = $sched_id->emp_id;
            $target_date = $sched_id->target_date;
            $schedule_id = $sched_id->schedule_id;
            if($status == 1){
               DB::connection("intra_payroll")->table("tbl_daily_schedule")
                    ->where("emp_id", $emp_id)
                    ->where("schedule_date", $target_date)
                    ->delete();
                DB::connection("intra_payroll")->table("tbl_daily_schedule")
                    ->insert([
                        "emp_id" => $emp_id,
                        "schedule_date" => $target_date,
                        "schedule_id" => $schedule_id,
                        "date_created" => date("Y-m-d H:i:s"),
                        "user_id" => Auth::user()->id
                    ]);
                DB::connection("intra_payroll")->table("tbl_schedule_request")
                    ->where("id", $id)
                    ->update([
                        "status" => $status
                    ]);
            }elseif($status == 2){
                DB::connection("intra_payroll")->table("tbl_schedule_request")
                ->where("id", $id)
                ->update([
                    "status" => $status
                ]);
            }elseif($status == 3){
                DB::connection("intra_payroll")->table("tbl_schedule_request")
                ->where("id", $id)
                ->update([
                    "status" => $status
                ]);
            }elseif($status == 9){
                        DB::connection("intra_payroll")->table("tbl_daily_schedule")
                        ->where("emp_id", $emp_id)
                        ->where("schedule_date", $target_date)
                        ->delete();
                        DB::connection("intra_payroll")->table("tbl_daily_schedule")
                            ->insert([
                                "emp_id" => $emp_id,
                                "schedule_date" => $target_date,
                                "schedule_id" => $schedule_id,
                                "date_created" => date("Y-m-d H:i:s"),
                                "user_id" => Auth::user()->id
                            ]);
            }
            return json_encode("Success");
        }else{
            return json_encode("Request Not found");
        }
    }
    private function autofileOT($log_id,$target_date){
        $lib_schedule = DB::connection("intra_payroll")->table("lib_schedule")->where('is_active', 1)->get();
        $lib_schedule = json_decode(json_encode($lib_schedule),true);
        $tbl_employee = DB::connection("intra_payroll")->table("tbl_employee")->where("is_active", 1)->get();
        $tbl_employee = json_decode(json_encode($tbl_employee), true);
        $lib_position = json_decode(json_encode(DB::connection("intra_payroll")->table("lib_position")->get()),true);
        $lib_designation = json_decode(json_encode(DB::connection("intra_payroll")->table("lib_designation")->get()),true);
        $tbl_department = json_decode(json_encode(DB::connection("intra_payroll")->table("tbl_department")->get()),true);
        $tbl_branch = json_decode(json_encode(DB::connection("intra_payroll")->table("tbl_branch")->get()),true);
    // automatic OT FILING
            $check_tc = DB::connection("intra_payroll")->table("tbl_timecard")
            ->where("emp_id", $log_id)
            ->where("target_date", $target_date)
            ->first();
            if($check_tc != null){  
                $am_in = $check_tc->AM_IN;
                $pm_out = $check_tc->PM_OUT;
                $emp_data = $this->search_multi_array($tbl_employee, "id", $log_id);
                if(isset($emp_data["id"])){
                    
                }else{
                    return "No employee";
                } 
                if($am_in != null && $pm_out != null){
                    $cur_date = $target_date;
                    $daily_sched = DB::connection("intra_payroll")->table("tbl_daily_schedule")->where("emp_id", $log_id)->where("schedule_date", $target_date)->get();
                    $daily_sched = json_decode(json_encode($daily_sched), true);
                    $sched_default = 0;
                    //DAILY SCHED
                    $day_sched = $this->search_multi_array($daily_sched, "schedule_date", $cur_date);
                    if(isset($day_sched["schedule_id"])){
                        $lib_sched = $this->search_multi_array($lib_schedule, "id", $day_sched["schedule_id"]);
                        if(isset($lib_sched["id"])){
                            $req_am_in = $lib_sched["am_in"];
                            $req_am_out = $lib_sched["am_out"];
                            $req_pm_in = $lib_sched["pm_in"];
                            $req_pm_out = $lib_sched["pm_out"];
                            $req_ot_in = $lib_sched["ot_in"];
                            $req_ot_out = $lib_sched["ot_out"];
                            $grace_period = $lib_sched["grace_period"];
                            $is_flexi = $lib_sched["is_flexi"];
                            $required_hours = $lib_sched["required_hours"];
                        }else{
                            $sched_default = 1;
                        }
                    }else{
                        $sched_default = 1;
                    }
                    if($sched_default == 1){
                        $day_name = date('l', strtotime($cur_date));
                        $day_name = strtolower($day_name);
                        if($emp_data["schedule_id"] !=0){
                          
                            $sched_id = $emp_data["schedule_id"];
                            $lib_sched = $this->get_schedule_data($sched_id, $day_name);
                            if($lib_sched != "RD"){
                                $req_am_in = $lib_sched["am_in"];
                                $req_am_out = $lib_sched["am_out"];
                                $req_pm_in = $lib_sched["pm_in"];
                                $req_pm_out = $lib_sched["pm_out"];
                                $req_ot_in = $lib_sched["ot_in"];
                                $req_ot_out = $lib_sched["ot_out"];
                                $grace_period = $lib_sched["grace_period"];
                                $is_flexi = $lib_sched["is_flexi"];
                                $required_hours = $lib_sched["required_hours"];
                            }else{
                                $req_am_in = $lib_sched;
                                $req_am_out = "";
                                $req_pm_in = "";
                                $req_pm_out = "";
                                $req_ot_in = "";
                                $req_ot_out = "";
                                $grace_period = 0;
                                $is_flexi = "0";
                                $required_hours = "0";
                            }
                            
        
                        }else{
                            $position_sched_id = $this->search_multi_array($lib_position, "id", $emp_data["position_id"]);
                            $designation_sched_id = $this->search_multi_array($lib_designation, "id", $emp_data["designation"]);
                            $department_sched_id = $this->search_multi_array($tbl_department, "id", $emp_data["department"]);
                            $branch_sched_id = $this->search_multi_array($tbl_branch, "id", $emp_data["branch_id"]);
                            
                              
                            if(isset($position_sched_id["schedule_id"])) {
                                $position_sched_id['schedule_id'] = 0;
                            }
                                if($position_sched_id["schedule_id"] != "0"){
                                    $sched_id = $position_sched_id["schedule_id"];
                                    $lib_sched = $this->get_schedule_data($sched_id, $day_name);
                                    if($lib_sched != "RD"){
                                        $req_am_in = $lib_sched["am_in"];
                                        $req_am_out = $lib_sched["am_out"];
                                        $req_pm_in = $lib_sched["pm_in"];
                                        $req_pm_out = $lib_sched["pm_out"];
                                        $req_ot_in = $lib_sched["ot_in"];
                                        $req_ot_out = $lib_sched["ot_out"];
                                        $grace_period = $lib_sched["grace_period"];
                                        $is_flexi = $lib_sched["is_flexi"];
                                        $required_hours = $lib_sched["required_hours"];
                                    }else{
                                        $req_am_in = $lib_sched;
                                        $req_am_out = "";
                                        $req_pm_in = "";
                                        $req_pm_out = "";
                                        $req_ot_in = "";
                                        $req_ot_out = "";
                                        $grace_period = 0;
                                        $is_flexi = "0";
                                        $required_hours = "0";
                                    }
                                }else{
                                    if($designation_sched_id["schedule_id"] != "0"){
                                        $sched_id = $designation_sched_id["schedule_id"];
                                        $lib_sched = $this->get_schedule_data($sched_id, $day_name);
                                        if($lib_sched != "RD"){
                                            $req_am_in = $lib_sched["am_in"];
                                            $req_am_out = $lib_sched["am_out"];
                                            $req_pm_in = $lib_sched["pm_in"];
                                            $req_pm_out = $lib_sched["pm_out"];
                                            $req_ot_in = $lib_sched["ot_in"];
                                            $req_ot_out = $lib_sched["ot_out"];
                                            $grace_period = $lib_sched["grace_period"];
                                            $is_flexi = $lib_sched["is_flexi"];
                                            $required_hours = $lib_sched["required_hours"];
                                        }else{
                                            $req_am_in = $lib_sched;
                                            $req_am_out = "";
                                            $req_pm_in = "";
                                            $req_pm_out = "";
                                            $req_ot_in = "";
                                            $req_ot_out = "";
                                            $grace_period = 0;
                                            $is_flexi = "0";
                                            $required_hours = "0";
                                        }
                                    }else{
                                        if($department_sched_id["schedule_id"] != "0"){
                                            $sched_id = $department_sched_id["schedule_id"];
                                            $lib_sched = $this->get_schedule_data($sched_id, $day_name);
                                            if($lib_sched != "RD"){
                                                $req_am_in = $lib_sched["am_in"];
                                                $req_am_out = $lib_sched["am_out"];
                                                $req_pm_in = $lib_sched["pm_in"];
                                                $req_pm_out = $lib_sched["pm_out"];
                                                $req_ot_in = $lib_sched["ot_in"];
                                                $req_ot_out = $lib_sched["ot_out"];
                                                $grace_period = $lib_sched["grace_period"];
                                                $is_flexi = $lib_sched["is_flexi"];
                                                $required_hours = $lib_sched["required_hours"];
                                            }else{
                                                $req_am_in = $lib_sched;
                                                $req_am_out = "";
                                                $req_pm_in = "";
                                                $req_pm_out = "";
                                                $req_ot_in = "";
                                                $req_ot_out = "";
                                                $grace_period = 0;
                                                $is_flexi = "0";
                                                $required_hours = "0";
                                            }
                                        }else{
                                            if($branch_sched_id["schedule_id"] != "0"){
                                                $sched_id = $branch_sched_id["schedule_id"];
                                                $lib_sched = $this->get_schedule_data($sched_id, $day_name);
                                                if($lib_sched != "RD"){
                                                    $req_am_in = $lib_sched["am_in"];
                                                    $req_am_out = $lib_sched["am_out"];
                                                    $req_pm_in = $lib_sched["pm_in"];
                                                    $req_pm_out = $lib_sched["pm_out"];
                                                    $req_ot_in = $lib_sched["ot_in"];
                                                    $req_ot_out = $lib_sched["ot_out"];
                                                    $grace_period = $lib_sched["grace_period"];
                                                    $is_flexi = $lib_sched["is_flexi"];
                                                    $required_hours = $lib_sched["required_hours"];
                                                }else{
                                                    $req_am_in = $lib_sched;
                                                    $req_am_out = "";
                                                    $req_pm_in = "";
                                                    $req_pm_out = "";
                                                    $req_ot_in = "";
                                                    $req_ot_out = "";
                                                    $grace_period = 0;
                                                    $is_flexi = "0";
                                                    $required_hours = "0";
                                                }
                                            }else{
                                                $req_am_in = "RD";
                                                $req_am_out = "";
                                                $req_pm_in = "";
                                                $req_pm_out = "";
                                                $req_ot_in = "";
                                                $req_ot_out = "";
                                                $grace_period = 0;
                                                $is_flexi = "0";
                                                $required_hours = "0";
                                            }
                                        }
                                    }
                                }
                        }
                    } //IF NO DAILY SET SCHEDLE
                   
                    if($req_am_in != "RD"){
                        if($is_flexi == "0"){
                            $am_in_req = strtotime($cur_date." ".$req_am_in);
                            $pm_out_req = strtotime($cur_date." ".$req_pm_out);
                            $converted_am_in = strtotime($am_in);
                            $converted_pm_out = strtotime($pm_out);
                            // default breakhours
                            $break_hours = 1;
                            $minimum_ot = 1;
    
                            if($converted_pm_out > $pm_out_req){
                                $ot_hours = (abs($converted_pm_out - $pm_out_req)/(60*60));
    
                                if($ot_hours >= $minimum_ot){
                                    // FILE OT 
    
                                    $check_filed_OT = DB::connection("intra_payroll")->table("tbl_ot_applied")
                                        ->where("date_target", $cur_date)
                                        ->where("emp_id", $log_id)
                                        ->first();
                                    if($check_filed_OT == null){
                                        $ot_arr = array(
                                            "emp_id" => $log_id,
                                            "ot_type" => "ROT",
                                            "date_target" => $cur_date,
                                            "time_from" => date("Y-m-d H:i:s", $pm_out_req),
                                            "time_to" => date("Y-m-d H:i:s", $converted_pm_out),
                                            "status" => "FILED", //OT approved
                                            "reason" => "AUTOMATIC FILE VIA LOG UPLOADING",
                                            "date_created" => date("Y-m-d H:i:s"),
                                            "user_approved" => 1, //OT approved
                                            "user_id" => Auth::user()->id
                                        );
    
                                        DB::connection("intra_payroll")->table("tbl_ot_applied")
                                            ->insert($ot_arr);
    
                                        return "OT FILED";
                                    }
    
                                }
    
                            }
                        }
                        
                        else{
                            // $am_in_req = strtotime($cur_date." ".$req_am_in);
                            // $pm_out_req = strtotime($cur_date." ".$req_pm_out);
                            $converted_am_in = strtotime($am_in);
                            $converted_pm_out = strtotime($pm_out);
                            // default breakhours
                            $break_hours = 1;
                            $minimum_ot = 1;
                            // $required_hours
                            if($converted_pm_out > $converted_am_in){
                                $work_hours = (abs($converted_pm_out - $converted_am_in)/(60*60));
                                $ot_hours = $work_hours - $required_hours;
                                if($ot_hours >= $minimum_ot){
                                    // FILE OT 
                                    // dd($ot_hours." ".$minimum_ot." ".$am_in."--".$pm_out."--".$required_hours."::".$work_hours);
                                    $ideal_out = date('Y-m-d H:i:s', strtotime($am_in . ' +'.$required_hours.' hours'));
                                    
                                    $ideal_out = strtotime($ideal_out);
                                    $check_filed_OT = DB::connection("intra_payroll")->table("tbl_ot_applied")
                                        ->where("date_target", $cur_date)
                                        ->where("emp_id", $log_id)
                                        ->first();
                                    if($check_filed_OT == null){
                                        $ot_arr = array(
                                            "emp_id" => $log_id,
                                            "ot_type" => "ROT",
                                            "date_target" => $cur_date,
                                            "time_from" => date("Y-m-d H:i:s", $ideal_out),
                                            "time_to" => date("Y-m-d H:i:s", $converted_pm_out),
                                            "status" => "FILED", //OT approved
                                            "reason" => "AUTOMATIC FILE VIA LOG UPLOADING",
                                            "date_created" => date("Y-m-d H:i:s"),
                                            "user_approved" => 1, //OT approved
                                            "user_id" => Auth::user()->id
                                        );
    
                                        DB::connection("intra_payroll")->table("tbl_ot_applied")
                                            ->insert($ot_arr);
    
                                        return "OT FILED";
                                    }
    
                                }
    
                            }
                        }
                      
                        
                    }
                }
            }
            return "Logs incomplete";
    }
   
    private function get_schedule_by_day($emp_id, $target_day){
        // default by daily_sched , employee, position, designation, department, branch, company
        // return json_encode($request->month_view);
        $target_date = date("Y-m-d", strtotime($target_day));
        $daily_sched = DB::connection("intra_payroll")->table("tbl_daily_schedule")->where("emp_id", $emp_id)->where("schedule_date", $target_date)->first();
        $daily_sched = json_decode(json_encode($daily_sched), true);
        
        $tbl_timecard = DB::connection("intra_payroll")->table("tbl_timecard")->where("emp_id", $emp_id)->where("target_date", $target_date)->first();
        $tbl_timecard = json_decode(json_encode($tbl_timecard), true);
        $tbl_employee = DB::connection("intra_payroll")->table("tbl_employee")->where("id", $emp_id)->first();
            $lib_position = DB::connection("intra_payroll")->table("lib_position")->where("id", $tbl_employee->position_id)->first();
            $lib_designation = DB::connection("intra_payroll")->table("lib_designation")->where("id", $tbl_employee->designation)->first();
            $tbl_department = DB::connection("intra_payroll")->table("tbl_department")->where("id", $tbl_employee->department)->first();
            $tbl_branch = DB::connection("intra_payroll")->table("tbl_branch")->where("id", $tbl_employee->branch_id)->first();
        $lib_schedule = DB::connection("intra_payroll")->table("lib_schedule")->where('is_active', 1)->get();
        $lib_schedule = json_decode(json_encode($lib_schedule),true);
        $lib_week_schedule = DB::connection("intra_payroll")->table("lib_week_schedule")->where('is_active', 1)->get();
        $lib_week_schedule = json_decode(json_encode($lib_week_schedule),true);
        if($daily_sched != null){
            $lib_sched = $this->search_multi_array($lib_schedule, "id", $data["schedule_id"]);
            if(isset($lib_sched["id"])){
                return $lib_sched;   
            }
        }
        if($tbl_employee->schedule_id !=0){
            $day_name = date('l', strtotime($target_date));
            $day_name = strtolower($day_name);
            $sched_id = $tbl_employee->schedule_id;
            $lib_week_sched = $this->search_multi_array($lib_week_schedule, "id", $sched_id);
            if(isset($lib_week_sched)){
                    $lib_sched = $this->search_multi_array($lib_schedule, "id", $lib_week_sched[$day_name]);
                    if(isset($lib_sched["id"])){
                        return $lib_sched;   
                    }
            }
        }
        
        if($lib_position->schedule_id != 0){
            $day_name = date('l', strtotime($target_date));
            $day_name = strtolower($day_name);
            $sched_id = $lib_position->schedule_id;
            $lib_week_sched = $this->search_multi_array($lib_week_schedule, "id", $sched_id);
            if(isset($lib_week_sched)){
                    $lib_sched = $this->search_multi_array($lib_schedule, "id", $lib_week_sched[$day_name]);
                    if(isset($lib_sched["id"])){
                        return $lib_sched;   
                    }
            }
        }
        if($lib_designation->schedule_id != 0){
            $day_name = date('l', strtotime($target_date));
            $day_name = strtolower($day_name);
            $sched_id = $lib_designation->schedule_id;
            $lib_week_sched = $this->search_multi_array($lib_week_schedule, "id", $sched_id);
            if(isset($lib_week_sched)){
                    $lib_sched = $this->search_multi_array($lib_schedule, "id", $lib_week_sched[$day_name]);
                    if(isset($lib_sched["id"])){
                        return $lib_sched;   
                    }
            }
        }
        if($tbl_department->schedule_id != 0){
            $day_name = date('l', strtotime($target_date));
            $day_name = strtolower($day_name);
            $sched_id = $tbl_department->schedule_id;
            $lib_week_sched = $this->search_multi_array($lib_week_schedule, "id", $sched_id);
            if(isset($lib_week_sched)){
                    $lib_sched = $this->search_multi_array($lib_schedule, "id", $lib_week_sched[$day_name]);
                    if(isset($lib_sched["id"])){
                        return $lib_sched;   
                    }
            }
        }
        if($tbl_branch->schedule_id != 0){
            $day_name = date('l', strtotime($target_date));
            $day_name = strtolower($day_name);
            $sched_id = $tbl_branch->schedule_id;
            $lib_week_sched = $this->search_multi_array($lib_week_schedule, "id", $sched_id);
            if(isset($lib_week_sched)){
                    $lib_sched = $this->search_multi_array($lib_schedule, "id", $lib_week_sched[$day_name]);
                    if(isset($lib_sched["id"])){
                        return $lib_sched;   
                    }
            }
        }
        if(Auth::user()->company["default_work_settings"] != 0){
            $day_name = date('l', strtotime($target_date));
            $day_name = strtolower($day_name);
            $sched_id = Auth::user()->company["default_work_settings"];
            $lib_week_sched = $this->search_multi_array($lib_week_schedule, "id", $sched_id);
            if(isset($lib_week_sched)){
                    $lib_sched = $this->search_multi_array($lib_schedule, "id", $lib_week_sched[$day_name]);
                    if(isset($lib_sched["id"])){
                        return $lib_sched;   
                    }
            }
        }
        return "false";
    }
    public function sched_by(Request $request){
        $page_permission = Auth::user()->access[$request->page]["access"];
        $schedule_list = DB::connection("intra_payroll")->table("lib_week_schedule")
            ->where("is_active", 1)
            ->get();
    
        if($request->sched_by == "sched_by_position"){
            $tbl_data = DB::connection("intra_payroll")->table("lib_position")
                ->where("is_active", 1)
                ->get();
        }
        elseif($request->sched_by == "sched_by_department"){
            $tbl_data = DB::connection("intra_payroll")->table("tbl_department")
                ->select("department as name", "schedule_id", "id")
                ->where("is_active", 1)
                ->get();
        }
        elseif($request->sched_by == "sched_by_branch"){
            $tbl_data = DB::connection("intra_payroll")->table("tbl_branch")
                ->select("branch as name", "schedule_id", "id")
                ->where("is_active", 1)
                ->get();
        }
        elseif($request->sched_by == "sched_by_designation"){
            $tbl_data = DB::connection("intra_payroll")->table("lib_designation")
             
                ->where("is_active", 1)
                ->get();
        }
        else{
            $tbl_data = array();
        }        
        $tbl_data = collect($tbl_data);
          return Datatables::of($tbl_data)
          ->addColumn('schedule', function($row) use ($page_permission, $request, $schedule_list){
            $btn = "";
            if(preg_match("/U/i", $page_permission)){
                if(Auth::user()->access[$request->page]["user_type"] != "employee"){
                    $btn .= "<select id='schedule_".$request->sched_by."_".$row->id."' onchange='change_sched(".$row->id.", this.value, ".'"'.$request->sched_by.'"'.")' class='form-control form-select'>";
                        $btn .= "<option value='0'>Select Schedule </option>";
                        foreach($schedule_list as $sched)
                        {
                            if($sched->id == $row->schedule_id){
                                $btn .= "<option value='".$sched->id."' selected>(".$sched->code.") ".$sched->name."</option>";
                            }else{
                                $btn .= "<option value='".$sched->id."'>(".$sched->code.") ".$sched->name."</option>";
                            }
                        }
                    $btn .= "</select>";
                }
            }
          
            return $btn;
            })
            ->rawColumns(['schedule'])
            ->make(true);
    }
    function update_schedule_by(Request $request){
        
        if($request->sched_by == "sched_by_position"){
            $tbl_data = "lib_position";
        }
        elseif($request->sched_by == "sched_by_department"){
            $tbl_data = "tbl_department";
        }
        elseif($request->sched_by == "sched_by_branch"){
            $tbl_data = "tbl_branch";
        }
        elseif($request->sched_by == "sched_by_designation"){
            $tbl_data = "lib_designation";
        }else{
            return json_encode("Sub Menu Undefined");
        }
       
        DB::beginTransaction();
        try {
            DB::connection("intra_payroll")->table($tbl_data)
            ->where("id", $request->id)
            ->update([
                "schedule_id"=> $request->sched_id
            ]);
                DB::commit();
            return json_encode("true");
        } catch (\Throwable $th) {
            DB::rollback();
            return json_encode($th->getMessage());
        }
    }
    public function get_schedule(Request $request){
        //hiearchy 
        // default by daily_sched , employee, position, designation, department, branch, company
        // return json_encode($request->month_view);
        $date_from = $request->month_view;
        $date_from_year = date("Y", strtotime($date_from));
        $date_from_month = date("m", strtotime($date_from));
        $date_from_day = date("d", strtotime($date_from));
        if($date_from_day > 1){
            $set_day_date_from = date("Y-m-01", strtotime($date_from));
            $new_str_time = strtotime($set_day_date_from . ' +1 month');
       
            $date_from = date("Y-m-01", $new_str_time);
         
        }
       
        
        $date_to = date("Y-m-t", strtotime($date_from));
        $data_days = array();
        $cur_day = $date_from;
        $daily_sched = DB::connection("intra_payroll")->table("tbl_daily_schedule")->where("emp_id", $request->emp_id)->whereBetween("schedule_date", [$date_from, $date_to])->get();
        $daily_sched = json_decode(json_encode($daily_sched), true);
        
        $tbl_timecard = DB::connection("intra_payroll")->table("tbl_timecard")->where("emp_id", $request->emp_id)->whereBetween("target_date", [$date_from, $date_to])->get();
        $tbl_timecard = json_decode(json_encode($tbl_timecard), true);
        $tbl_holiday = DB::connection("intra_payroll")->table("tbl_holiday")->whereBetween("holiday_date", [$date_from, $date_to])->get();
        $tbl_holiday = json_decode(json_encode($tbl_holiday), true);
        $lib_schedule = DB::connection("intra_payroll")->table("lib_schedule")->where('is_active', 1)->get();
        $lib_schedule = json_decode(json_encode($lib_schedule),true);
        $lib_week_schedule = DB::connection("intra_payroll")->table("lib_week_schedule")->where('is_active', 1)->get();
        $lib_week_schedule = json_decode(json_encode($lib_week_schedule),true);
        $leave_dates = array();
        $tbl_leave_used = DB::connection("intra_payroll")->table("tbl_leave_used")->where("emp_id", $request->emp_id)->where("leave_status", "APPROVED")->where("leave_year",$date_from_year )->get();
        foreach($tbl_leave_used as $leave_used){
            $begin = new DateTime($leave_used->leave_date_from);
            $leave_to_date = date("Y-m-d", strtotime($leave_used->leave_date_to .' +1 day'));
            
            $end = new DateTime($leave_to_date);
            $interval = new DateInterval('P1D'); // 1 day interval
            $daterange = new DatePeriod($begin, $interval ,$end);
            foreach($daterange as $date_leave){
                $leave_dates[$date_leave->format("Y-m-d")] = "LEAVE";
            }
        }
    
        $tbl_employee = DB::connection("intra_payroll")->table("tbl_employee")->where("id", $request->emp_id)->first();
        $lib_position = DB::connection("intra_payroll")->table("lib_position")->where("id", $tbl_employee->position_id)->first();
        $lib_designation = DB::connection("intra_payroll")->table("lib_designation")->where("id", $tbl_employee->designation)->first();
        $tbl_department = DB::connection("intra_payroll")->table("tbl_department")->where("id", $tbl_employee->department)->first();
        $tbl_branch = DB::connection("intra_payroll")->table("tbl_branch")->where("id", $tbl_employee->branch_id)->first();
        
        do{
            if(count($daily_sched)>0){
                $branch_id = "";
                $branch_name = "";
                $data = $this->search_multi_array($daily_sched, "schedule_date", $cur_day);
                if(isset($data["schedule_id"])){
                    $lib_sched = $this->search_multi_array($lib_schedule, "id", $data["schedule_id"]);
                    if(isset($lib_sched["id"])){
                        if(isset($data["branch_id"])){
                            $branch = DB::connection("intra_payroll")->table("tbl_branch")->where("id", $data["branch_id"])->first();
                            if($branch){
                                $branch_name = $branch->branch;
                            }
                        }
                        if($lib_sched["is_flexi"] == "1"){
                            $title = "Flexible ".$lib_sched["required_hours"]." hrs";
                        }else{
                            $title = date("g:i A",strtotime($lib_sched["am_in"]))." - ".date("g:i A",strtotime($lib_sched["pm_out"]));
                            if ($branch_name) {
                                $title .= "\n" . $branch_name;
                            }
                        }
                        
                        $color = "#57b385";
                        $my_sched_id = $data["schedule_id"];
                        $branch_id = $data["branch_id"];
                    }else{
                        $title = "NO SCHEDULE";
                        $color = "#ebaf38";
                        $my_sched_id = 0;
                    }
                    $is_daily_assigned = 1;
               
                }else{
                    $is_daily_assigned = 0;
                    if($tbl_employee->schedule_id !=0){
                        $day_name = date('l', strtotime($cur_day));
                        $day_name = strtolower($day_name);
                        $sched_id = $tbl_employee->schedule_id;
                        $lib_week_sched = $this->search_multi_array($lib_week_schedule, "id", $sched_id);
                            if(isset($lib_week_sched)){
                                $lib_sched = $this->search_multi_array($lib_schedule, "id", $lib_week_sched[$day_name]);
                                    if(isset($lib_sched["id"])){
                                        if($lib_sched["is_flexi"] == "1"){
                                            $title = "Flexible ".$lib_sched["required_hours"]." hrs";
                                        }else{
                                            $title = date("g:i A",strtotime($lib_sched["am_in"]))." - ".date("g:i A",strtotime($lib_sched["pm_out"]));
                                        }
                                        $color = "#53f563";
                                        $my_sched_id = $lib_week_sched[$day_name];
                                    }else{
                                        $title = "NO SCHEDULE";
                                        $color = "#ebaf38";
                                        $my_sched_id = 0;
                                    }
                            }else{
                                $title = "NO SCHEDULE";
                                $color = "#ebaf38";
                                $my_sched_id = 0;
                            }
                         
                    }else{
                        if($lib_position->schedule_id != 0){
                            $day_name = date('l', strtotime($cur_day));
                            $day_name = strtolower($day_name);
    
                            $sched_id = $lib_position->schedule_id;
                            $lib_week_sched = $this->search_multi_array($lib_week_schedule, "id", $sched_id);
                                if(isset($lib_week_sched)){
                                    $lib_sched = $this->search_multi_array($lib_schedule, "id", $lib_week_sched[$day_name]);
                                        if(isset($lib_sched["id"])){
                                            if($lib_sched["is_flexi"] == "1"){
                                                $title = "Flexible ".$lib_sched["required_hours"]." hrs";
                                            }else{
                                                $title = date("g:i A",strtotime($lib_sched["am_in"]))." - ".date("g:i A",strtotime($lib_sched["pm_out"]));
                                            }
                                            $color = "#53f563";
                                            $my_sched_id = $lib_week_sched[$day_name];
                                        }else{
                                            $title = "NO SCHEDULE";
                                            $color = "#ebaf38";
                                            $my_sched_id = 0;
                                        }
                                }else{
                                    $title = "NO SCHEDULE";
                                    $color = "#ebaf38";
                                    $my_sched_id = 0;
                                }
                          
                        }else{
                            if($lib_designation->schedule_id != 0){
                                $day_name = date('l', strtotime($cur_day));
                                $day_name = strtolower($day_name);
        
                                $sched_id = $lib_designation->schedule_id;
                                $lib_week_sched = $this->search_multi_array($lib_week_schedule, "id", $sched_id);
                                    if(isset($lib_week_sched)){
                                        $lib_sched = $this->search_multi_array($lib_schedule, "id", $lib_week_sched[$day_name]);
                                            if(isset($lib_sched["id"])){
                                                if($lib_sched["is_flexi"] == "1"){
                                                    $title = "Flexible ".$lib_sched["required_hours"]." hrs";
                                                }else{
                                                    $title = date("g:i A",strtotime($lib_sched["am_in"]))." - ".date("g:i A",strtotime($lib_sched["pm_out"]));
                                                }
                                                $color = "#53f563";
                                                $my_sched_id = $lib_week_sched[$day_name];
                                            }else{
                                                $title = "NO SCHEDULE";
                                                $color = "#ebaf38";
                                                $my_sched_id = 0;
                                            }
                                    }else{
                                        $title = "NO SCHEDULE";
                                        $color = "#ebaf38";
                                        $my_sched_id = 0;
                                    }
                                  
                            }else{
                                if($tbl_department->schedule_id != 0){
                                    $day_name = date('l', strtotime($cur_day));
                                    $day_name = strtolower($day_name);
            
                                    $sched_id = $tbl_department->schedule_id;
                                    $lib_week_sched = $this->search_multi_array($lib_week_schedule, "id", $sched_id);
                                        if(isset($lib_week_sched)){
                                            $lib_sched = $this->search_multi_array($lib_schedule, "id", $lib_week_sched[$day_name]);
                                                if(isset($lib_sched["id"])){
                                                    if($lib_sched["is_flexi"] == "1"){
                                                        $title = "Flexible ".$lib_sched["required_hours"]." hrs";
                                                    }else{
                                                        $title = date("g:i A",strtotime($lib_sched["am_in"]))." - ".date("g:i A",strtotime($lib_sched["pm_out"]));
                                                    }
                                                    $color = "#53f563";
                                                    $my_sched_id = $lib_week_sched[$day_name];
                                                }else{
                                                    $title = "NO SCHEDULE";
                                                    $color = "#ebaf38";
                                                    $my_sched_id = 0;
                                                }
                                        }else{
                                            $title = "NO SCHEDULE";
                                            $color = "#ebaf38";
                                            $my_sched_id = 0;
                                        }
                                }else{
                                    if($tbl_branch->schedule_id != 0){
                                        $day_name = date('l', strtotime($cur_day));
                                        $day_name = strtolower($day_name);
                
                                        $sched_id = $tbl_branch->schedule_id;
                                        $lib_week_sched = $this->search_multi_array($lib_week_schedule, "id", $sched_id);
                                            if(isset($lib_week_sched)){
                                                $lib_sched = $this->search_multi_array($lib_schedule, "id", $lib_week_sched[$day_name]);
                                                    if(isset($lib_sched["id"])){
                                                        if($lib_sched["is_flexi"] == "1"){
                                                            $title = "Flexible ".$lib_sched["required_hours"]." hrs";
                                                        }else{
                                                            $title = date("g:i A",strtotime($lib_sched["am_in"]))." - ".date("g:i A",strtotime($lib_sched["pm_out"]));
                                                        }
                                                        $color = "#53f563";
                                                        $my_sched_id = $lib_week_sched[$day_name];
                                                    }else{
                                                        $title = "NO SCHEDULE";
                                                        $color = "#ebaf38";
                                                        $my_sched_id = 0;
                                                    }
                                            }else{
                                                $title = "NO SCHEDULE";
                                                $color = "#ebaf38";
                                                $my_sched_id = 0;
                                            }
                                    }else{
                                        if(Auth::user()->company["default_work_settings"] != 0){
                                            $day_name = date('l', strtotime($cur_day));
                                            $day_name = strtolower($day_name);
                    
                                            $sched_id = Auth::user()->company["default_work_settings"];
                                            $lib_week_sched = $this->search_multi_array($lib_week_schedule, "id", $sched_id);
                                                if(isset($lib_week_sched)){
                                                    $lib_sched = $this->search_multi_array($lib_schedule, "id", $lib_week_sched[$day_name]);
                                                        if(isset($lib_sched["id"])){
                                                            if($lib_sched["is_flexi"] == "1"){
                                                                $title = "Flexible ".$lib_sched["required_hours"]." hrs";
                                                            }else{
                                                                $title = date("g:i A",strtotime($lib_sched["am_in"]))." - ".date("g:i A",strtotime($lib_sched["pm_out"]));
                                                            }
                                                            $color = "#53f563";
                                                            $my_sched_id = $lib_week_sched[$day_name];
                                                        }else{
                                                            $title = "NO SCHEDULE";
                                                            $color = "#ebaf38";
                                                            $my_sched_id = 0;
                                                        }
                                                }else{
                                                    $title = "NO SCHEDULE";
                                                    $color = "#ebaf38";
                                                    $my_sched_id = 0;
                                                }
                                                array_push($data_days,array(
                                                    'title' => $title,
                                                    'start' => $cur_day,
                                                    'color' => $color,
                                                    'extendedProps' => array( "dailyAssigned" => '0','sched_id' => $my_sched_id, 'branch_id' => $branch_id ) 
                                                ));
                                        }else{
                                            $title = "NO SCHEDULE";
                                            $color = "#ebaf38";
                                            $my_sched_id = 0;
                                         
                                        }
                                    }
                                }
                            }
                        }
                    }
                    
                }
                if(isset($leave_dates[$cur_day])){
                    array_push($data_days,array(
                        'title' => $leave_dates[$cur_day],
                        'start' => $cur_day,
                        'color'  => "#159ca1",
                        
                    ));
                 
                    
                }else{
                    array_push($data_days,array(
                        'title' => $title,
                        'start' => $cur_day,
                        'color'  => $color,
                        'extendedProps' => array(
                            "dailyAssigned" => $is_daily_assigned,
                            'sched_id' => $my_sched_id,
                            'branch_id' => $branch_id
                        )
                    ));
                }
            }else{
                $branch_id = "";
                $is_daily_assigned = 0;
                if($tbl_employee->schedule_id !=0){
                    $day_name = date('l', strtotime($cur_day));
                    $day_name = strtolower($day_name);
                    $sched_id = $tbl_employee->schedule_id;
                    $lib_week_sched = $this->search_multi_array($lib_week_schedule, "id", $sched_id);
                        if(isset($lib_week_sched)){
                            $lib_sched = $this->search_multi_array($lib_schedule, "id", $lib_week_sched[$day_name]);
                                if(isset($lib_sched["id"])){
                                    if($lib_sched["is_flexi"] == "1"){
                                        $title = "Flexible ".$lib_sched["required_hours"]." hrs";
                                    }else{
                                        $title = date("g:i A",strtotime($lib_sched["am_in"]))." - ".date("g:i A",strtotime($lib_sched["pm_out"]));
                                    }
                                    $color = "#53f563";
                                    $my_sched_id = $lib_week_sched[$day_name];
                                }else{
                                    $title = "NO SCHEDULE";
                                    $color = "#ebaf38";
                                    $my_sched_id = 0;
                                }
                        }else{
                            $title = "NO SCHEDULE";
                            $color = "#ebaf38";
                            $my_sched_id = 0;
                        }
                       
                }else{
                    if($lib_position->schedule_id != 0){
                        $day_name = date('l', strtotime($cur_day));
                        $day_name = strtolower($day_name);
                        $sched_id = $lib_position->schedule_id;
                        $lib_week_sched = $this->search_multi_array($lib_week_schedule, "id", $sched_id);
                            if(isset($lib_week_sched)){
                                $lib_sched = $this->search_multi_array($lib_schedule, "id", $lib_week_sched[$day_name]);
                                    if(isset($lib_sched["id"])){
                                        if($lib_sched["is_flexi"] == "1"){
                                            $title = "Flexible ".$lib_sched["required_hours"]." hrs";
                                        }else{
                                            $title = date("g:i A",strtotime($lib_sched["am_in"]))." - ".date("g:i A",strtotime($lib_sched["pm_out"]));
                                        }
                                        $color = "#53f563";
                                        $my_sched_id = $lib_week_sched[$day_name];
                                    }else{
                                        $title = "NO SCHEDULE";
                                        $color = "#ebaf38";
                                        $my_sched_id = 0;
                                    }
                            }else{
                                $title = "NO SCHEDULE";
                                $color = "#ebaf38";
                                $my_sched_id = 0;
                            }
                           
                    }else{
                        if($lib_designation->schedule_id != 0){
                            $day_name = date('l', strtotime($cur_day));
                            $day_name = strtolower($day_name);
    
                            $sched_id = $lib_designation->schedule_id;
                            $lib_week_sched = $this->search_multi_array($lib_week_schedule, "id", $sched_id);
                                if(isset($lib_week_sched)){
                                    $lib_sched = $this->search_multi_array($lib_schedule, "id", $lib_week_sched[$day_name]);
                                        if(isset($lib_sched["id"])){
                                            if($lib_sched["is_flexi"] == "1"){
                                                $title = "Flexible ".$lib_sched["required_hours"]." hrs";
                                            }else{
                                                $title = date("g:i A",strtotime($lib_sched["am_in"]))." - ".date("g:i A",strtotime($lib_sched["pm_out"]));
                                            }
                                            $color = "#53f563";
                                            $my_sched_id = $lib_week_sched[$day_name];
                                        }else{
                                            $title = "NO SCHEDULE";
                                            $color = "#ebaf38";
                                            $my_sched_id = 0;
                                        }
                                }else{
                                    $title = "NO SCHEDULE";
                                    $color = "#ebaf38";
                                    $my_sched_id = 0;
                                }
                                
                        }else{
                            if($tbl_department->schedule_id != 0){
                                $day_name = date('l', strtotime($cur_day));
                                $day_name = strtolower($day_name);
        
                                $sched_id = $tbl_department->schedule_id;
                                $lib_week_sched = $this->search_multi_array($lib_week_schedule, "id", $sched_id);
                                    if(isset($lib_week_sched)){
                                        $lib_sched = $this->search_multi_array($lib_schedule, "id", $lib_week_sched[$day_name]);
                                            if(isset($lib_sched["id"])){
                                                if($lib_sched["is_flexi"] == "1"){
                                                    $title = "Flexible ".$lib_sched["required_hours"]." hrs";
                                                }else{
                                                    $title = date("g:i A",strtotime($lib_sched["am_in"]))." - ".date("g:i A",strtotime($lib_sched["pm_out"]));
                                                }
                                                $color = "#53f563";
                                                $my_sched_id = $lib_week_sched[$day_name];
                                            }else{
                                                $title = "NO SCHEDULE";
                                                $color = "#ebaf38";
                                                $my_sched_id = 0;
                                            }
                                    }else{
                                        $title = "NO SCHEDULE";
                                        $color = "#ebaf38";
                                        $my_sched_id = 0;
                                    }
                                  
                            }else{
                                if($tbl_branch->schedule_id != 0){
                                    $day_name = date('l', strtotime($cur_day));
                                    $day_name = strtolower($day_name);
            
                                    $sched_id = $tbl_branch->schedule_id;
                                    $lib_week_sched = $this->search_multi_array($lib_week_schedule, "id", $sched_id);
                                        if(isset($lib_week_sched)){
                                            $lib_sched = $this->search_multi_array($lib_schedule, "id", $lib_week_sched[$day_name]);
                                                if(isset($lib_sched["id"])){
                                                    if($lib_sched["is_flexi"] == "1"){
                                                        $title = "Flexible ".$lib_sched["required_hours"]." hrs";
                                                    }else{
                                                        $title = date("g:i A",strtotime($lib_sched["am_in"]))." - ".date("g:i A",strtotime($lib_sched["pm_out"]));
                                                    }
                                                    $color = "#53f563";
                                                    $my_sched_id = $lib_week_sched[$day_name];
                                                }else{
                                                    $title = "NO SCHEDULE";
                                                    $color = "#ebaf38";
                                                    $my_sched_id = 0;
                                                }
                                        }else{
                                            $title = "NO SCHEDULE";
                                            $color = "#ebaf38";
                                            $my_sched_id = 0;
                                        }
                                       
                                }else{
                                    if(Auth::user()->company["default_work_settings"] != 0){
                                        $day_name = date('l', strtotime($cur_day));
                                        $day_name = strtolower($day_name);
                
                                        $sched_id = Auth::user()->company["default_work_settings"];
                                        $lib_week_sched = $this->search_multi_array($lib_week_schedule, "id", $sched_id);
                                            if(isset($lib_week_sched)){
                                                $lib_sched = $this->search_multi_array($lib_schedule, "id", $lib_week_sched[$day_name]);
                                                    if(isset($lib_sched["id"])){
                                                        if($lib_sched["is_flexi"] == "1"){
                                                            $title = "Flexible ".$lib_sched["required_hours"]." hrs";
                                                        }else{
                                                            $title = date("g:i A",strtotime($lib_sched["am_in"]))." - ".date("g:i A",strtotime($lib_sched["pm_out"]));
                                                        }
                                                        $color = "#53f563";
                                                        $my_sched_id = $lib_week_sched[$day_name];
                                                    }else{
                                                        $title = "NO SCHEDULE";
                                                        $color = "#ebaf38";
                                                        $my_sched_id = 0;
                                                    }
                                            }else{
                                                $title = "NO SCHEDULE";
                                                $color = "#ebaf38";
                                                $my_sched_id = 0;
                                            }
                                            
                                    }else{
                                        $title = "NO SCHEDULE";
                                        $color = "#ebaf38";
                                    }
                                }
                            }
                        }
                    }
                }
                if(isset($leave_dates[$cur_day])){
                    array_push($data_days,array(
                        'title' => $leave_dates[$cur_day],
                        'start' => $cur_day,
                        'color'  => "#159ca1",
                        
                    ));
                 
                    
                }else{
                    array_push($data_days,array(
                        'title' => $title,
                        'start' => $cur_day,
                        'color'  => $color,
                        'extendedProps' => array(
                            "dailyAssigned" => $is_daily_assigned,
                            'sched_id' => $my_sched_id,
                            'branch_id' => $branch_id
                        )
                    ));
                }
                
            }
          
            $data_holiday =$this->search_multi_array($tbl_holiday, "holiday_date", $cur_day);
            
            if(isset($data_holiday)){
                array_push($data_days,array(
                    'title' => $data_holiday["holiday_name"],
                    'start' => $data_holiday["holiday_date"],
                    'eventColor' => '#ed9b8a',
                    "eventTextColor" => "#000000"
                ));
                    
            }
            $data_timecard = $this->search_multi_array($tbl_timecard, "target_date", $cur_day);
            if(isset($data_timecard)){
                $state="AM_IN";
                if($data_timecard[$state] != "" || $data_timecard[$state] != NULL){
                    array_push($data_days,array(
                        'title' => $state,
                        'start' => $data_timecard[$state],
                        'eventColor' => '#a893e6',
                        "eventTextColor" => "#000000"
                    ));
                }
                $state="AM_OUT";
                if($data_timecard[$state] != "" || $data_timecard[$state] != NULL){
                    array_push($data_days,array(
                        'title' => $state,
                        'start' => $data_timecard[$state],
                        'eventColor' => '#a893e6',
                        "eventTextColor" => "#000000"
                    ));
                }
             
                $state="PM_IN";
                if($data_timecard[$state] != "" || $data_timecard[$state] != NULL){
                    array_push($data_days,array(
                        'title' => $state,
                        'start' => $data_timecard[$state],
                        'eventColor' => '#a893e6',
                        "eventTextColor" => "#000000"
                    ));
                }
                $state="PM_OUT";
                if($data_timecard[$state] != "" || $data_timecard[$state] != NULL){
                    array_push($data_days,array(
                        'title' => $state,
                        'start' => $data_timecard[$state],
                        'eventColor' => '#a893e6',
                        "eventTextColor" => "#000000"
                    ));
                }
                $state="OT_IN";
                if($data_timecard[$state] != "" || $data_timecard[$state] != NULL){
                    array_push($data_days,array(
                        'title' => $state,
                        'start' => $data_timecard[$state],
                        'eventColor' => '#a893e6',
                        "eventTextColor" => "#000000"
                    ));
                }
                $state="OT_OUT";
                if($data_timecard[$state] != "" || $data_timecard[$state] != NULL){
                    array_push($data_days,array(
                        'title' => $state,
                        'start' => $data_timecard[$state],
                        'eventColor' => '#a893e6',
                        "eventTextColor" => "#000000"
                    ));
                }
            }
        
            $cur_day = date('Y-m-d', strtotime($cur_day . ' +1 day'));
        }while(strtotime($cur_day) <= strtotime($date_to));
    
        return response()->json($data_days);
    }
    public function delete_manual_log(Request $request){
        $id = $request->id;
        $tbl = "tbl_timecard";
        
        DB::beginTransaction();
            try {
                DB::connection("intra_payroll")->table($tbl)
                    ->where("id", $id)
                    ->delete();
                DB::commit();
                return json_encode("Log's Deleted");
            } catch (\Throwable $th) {
                DB::rollback();
                return json_encode($th->getMessage());
            }
    }
    
    // add delete in tk
    public function delete_ot(Request $request){
        $type = $request->type;
        $tbl = "";
        if($type == "ot_request"){
            $tbl = "tbl_ot_applied";
        }else{
            $tbl = "lib_ot_table";
        }
        DB::beginTransaction();
            try {
                DB::connection("intra_payroll")->table($tbl)
                    ->where("id", $request->id)
                    ->delete();
                DB::commit();
                return json_encode("Deleted");
            } catch (\Throwable $th) {
                DB::rollback();
                return json_encode($th->getMessage());
            }
    }
    // add export btn
    public function exportLogs($emp_id = 0, Request $request)
    {
        $emp = DB::connection("intra_payroll")->table("tbl_employee")
            ->where("id", $emp_id)
            ->first();
        $bio_id = $emp ? $emp->bio_id : "0";
        $logs = DB::connection("intra_payroll")->table("tbl_raw_logs")
            ->where("biometric_id", $bio_id);
        // Filter by date range
        if ($request->filled('date_range')) {
            [$start_date, $end_date] = explode(' - ', $request->date_range);
            $logs->whereBetween('logs', [$start_date . ' 00:00:00', $end_date . ' 23:59:59']);
        }
        $logs = $logs->orderBy("logs", "DESC")->get();
        // Group logs by date
        $groupedLogs = [];
        foreach ($logs as $log) {
            $date = date('Y-m-d', strtotime($log->logs));
            $state = $log->state;
            $log_time = date('H:i:s', strtotime($log->logs));
            if (!isset($groupedLogs[$date])) {
                $employeeData = DB::connection("intra_payroll")->table('tbl_employee')
                    ->where('bio_id', $log->biometric_id)
                    ->first();
                $groupedLogs[$date] = [
                    'time_in'    => [],
                    'time_out'   => [],
                    'am_in_loc'  => null,
                    'pm_out_loc' => null,
                    'first_loc'  => null, // fallback
                    'employee_name' => $employeeData->first_name . ' ' . $employeeData->last_name,
                ];
            }
            // save first location of the day
            if ($groupedLogs[$date]['first_loc'] === null) {
                $groupedLogs[$date]['first_loc'] = $log->location;
            }
            // Save times
            if (in_array($state, ['AM_IN', 'PM_IN', 'OT_IN'])) {
                $groupedLogs[$date]['time_in'][] = $log_time;
            } elseif (in_array($state, ['AM_OUT', 'PM_OUT', 'OT_OUT'])) {
                $groupedLogs[$date]['time_out'][] = $log_time;
            }
            // capture AM_IN location (first only)
            if ($state === 'AM_IN' && $groupedLogs[$date]['am_in_loc'] === null) {
                $groupedLogs[$date]['am_in_loc'] = $log->location;
            }
            // capture PM_OUT location (first only)
            if ($state === 'PM_OUT' && $groupedLogs[$date]['pm_out_loc'] === null) {
                $groupedLogs[$date]['pm_out_loc'] = $log->location;
            }
        }
        // Final formatting
        $finalLogs = [];
        foreach ($groupedLogs as $date => $times) {
            $am_loc = $times['am_in_loc'];
            $pm_loc = $times['pm_out_loc'];
            // Default fallback
            $location = $times['first_loc'];
            // AM_IN & PM_OUT logic
            if ($am_loc && $pm_loc) {
                if ($am_loc === $pm_loc) {
                    $location = $am_loc;
                } else {
                    $location = $am_loc . ', ' . $pm_loc;
                }
            } elseif ($am_loc) {
                $location = $am_loc;
            } elseif ($pm_loc) {
                $location = $pm_loc;
            }
            $finalLogs[] = [
                'employee_name' => $times['employee_name'],
                'date'          => $date,
                'time_in'       => implode(', ', $times['time_in']),
                'time_out'      => implode(', ', $times['time_out']),
                'location'      => $location,
            ];
        }
        return Excel::download(new LogsExport(collect($finalLogs)), 'logs_export_' . time() . '.xlsx');
    }
    public function exportAllEmployees(Request $request)
    {
        $logs = DB::connection("intra_payroll")->table("tbl_raw_logs");
        // Filter by date range
        if ($request->filled('date_range')) {
            [$start_date, $end_date] = explode(' - ', $request->date_range);
            $logs->whereBetween('logs', [$start_date . ' 00:00:00', $end_date . ' 23:59:59']);
        }
        //add per branch
        if ($request->filled('branch_id')) {
            $employeeIds = DB::connection("intra_payroll")->table('tbl_employee')
                ->where("branch_id", $request->branch_id)
                ->pluck('bio_id')
                ->toArray();
    
            if (!empty($employeeIds)) {
                $logs->whereIn('biometric_id', $employeeIds);
            } else {
                return response()->json(['error' => 'No employees found for this branch'], 404);
            }
        }
        $logs = $logs->orderBy("logs", "DESC")->get();
        // Group logs by employee and date
        $groupedLogs = [];
        foreach ($logs as $log) {
            $employee = DB::connection("intra_payroll")->table('tbl_employee')
                ->where('bio_id', $log->biometric_id)
                ->first();
            $employee_name = $employee ? "{$employee->first_name} {$employee->last_name}" : "Unknown Employee";
            $date = date('Y-m-d', strtotime($log->logs));
            $state = $log->state;
            $log_time = date('H:i:s', strtotime($log->logs));
            if (!isset($groupedLogs[$employee_name][$date])) {
                $groupedLogs[$employee_name][$date] = [
                    'time_in' => [], 
                    'time_out' => [],
                    'am_in_loc'  => null, 
                    'pm_out_loc' => null, 
                    'first_loc'  => null, 
                ];
            }
            if ($groupedLogs[$employee_name][$date]['first_loc'] === null) {
                $groupedLogs[$employee_name][$date]['first_loc'] = $log->location;
            }
            if (in_array($state, ['AM_IN', 'PM_IN', 'OT_IN'])) {
                $groupedLogs[$employee_name][$date]['time_in'][] = $log_time;
            } elseif (in_array($state, ['AM_OUT', 'PM_OUT', 'OT_OUT'])) {
                $groupedLogs[$employee_name][$date]['time_out'][] = $log_time;
            }
            if ($state === 'AM_IN' && $groupedLogs[$employee_name][$date]['am_in_loc'] === null) {
                $groupedLogs[$employee_name][$date]['am_in_loc'] = $log->location;
            }
            // Capture PM_OUT location (only first)
            if ($state === 'PM_OUT' && $groupedLogs[$employee_name][$date]['pm_out_loc'] === null) {
                $groupedLogs[$employee_name][$date]['pm_out_loc'] = $log->location;
            }
        }
        $finalLogs = [];
        foreach ($groupedLogs as $employee_name => $dates) {
            foreach ($dates as $date => $times) {
                if($employee_name != 'Unknown Employee'){
                    $am_loc = $times['am_in_loc'];
                    $pm_loc = $times['pm_out_loc'];
                    $location = $times['first_loc'];
                    if ($am_loc && $pm_loc) {
                        if ($am_loc === $pm_loc) {
                            $location = $am_loc; // same → show one
                        } else {
                            // different → show both (AM_IN first, then PM_OUT)
                            $location = $am_loc . ', ' . $pm_loc;
                        }
                    } elseif ($am_loc) {
                        $location = $am_loc;
                    } elseif ($pm_loc) {
                        $location = $pm_loc;
                    }
                    
                    $finalLogs[] = [
                        'employee_name' => $employee_name,
                        'date' => $date,
                        'time_in' => implode(', ', $times['time_in']),
                        'time_out' => implode(', ', $times['time_out']),
                        'location'      => $location, 
                    ];
                }
            }
        }
        return Excel::download(new LogsExport(collect($finalLogs)), 'all_employees_logs_' . time() . '.xlsx');
    }
    //add export in file OT
    public function export_ot_apply(Request $request)
    {
        $otApplications = DB::connection("intra_payroll")->table("tbl_ot_applied")
        ->join("tbl_employee", "tbl_ot_applied.emp_id", "tbl_employee.id")
        ->select(
            "tbl_employee.last_name",
            "tbl_employee.first_name",
            "tbl_employee.middle_name",
            "tbl_employee.ext_name",
            "tbl_ot_applied.*"
        )
        ->orderBy("tbl_ot_applied.date_target", "DESC")
        ->get();
        // Format Data for Export
        $OTAppliedData = [];
        foreach ($otApplications as $row) {
            $OTAppliedData[] = [
                'employee_name' => $row->last_name . ', ' . $row->first_name . ' ' . $row->middle_name . ' ' . $row->ext_name,
                'ot_type' => ($row->ot_type == "ROT") ? "Regular OT" : "Special OT",
                'date_target' => $row->date_target,
                'time_from' => $row->time_from,
                'time_to' => $row->time_to,
                'status' => $row->status,
            ];
        }
        return Excel::download(new OTApplyExport(collect($OTAppliedData)), 'OT_Applications_' . time() . '.xlsx');
    }
    public function manual_in_out_inline(Request $request) {
        $id = $request->id;
        $column = $request->time_type;
        $timeOnly = $request->time_value;
        $row = DB::connection("intra_payroll")
            ->table("tbl_timecard")
            ->where("id", $id)
            ->first();
        if (!$row) {
            return response()->json("Row not found", 400);
        }
        // Default to target_date as base date
        $datePart = $row->target_date;
        // If updating PM_OUT and AM_IN exists
        if ($column === 'PM_OUT' && $row->AM_IN) {
            $amInTime = date("H:i", strtotime($row->AM_IN));
            $amInDate = date("Y-m-d", strtotime($row->AM_IN));
            // Combine with input time to compare full datetime logic
            if (!empty($row->PM_OUT)) {
                $pmOutTime = $timeOnly;
                if ($pmOutTime < $amInTime) {
                    // PM_OUT is earlier than AM_IN, assume it's next day
                    $datePart = date("Y-m-d", strtotime($amInDate . ' +1 day'));
                } else {
                    $datePart = $amInDate;
                }
            } else {
                // PM_OUT is being added for first time
                if ($timeOnly < $amInTime) {
                    $datePart = date("Y-m-d", strtotime($amInDate . ' +1 day'));
                } else {
                    $datePart = $amInDate;
                }
            }
        }
        // Combine date and time
        $combined = $datePart . ' ' . $timeOnly . ':00';
        // Update the record
        DB::connection("intra_payroll")->table("tbl_timecard")
            ->where("id", $id)
            ->update([
                $column => $combined,
                'is_manual' => 1
            ]);
        return response()->json("Success");
    }
    public function manualUpdateSchedule(Request $request)
    {
        $validated = $request->validate([
            'emp_id' => 'required|integer',
            'schedule_date' => 'required|date',
            'schedule_id' => 'required|integer'
        ]);
        DB::connection('intra_payroll')->table('tbl_daily_schedule')->updateOrInsert(
            [
                'emp_id' => $validated['emp_id'],
                'schedule_date' => $validated['schedule_date'],
            ],
            [
                'schedule_id' => $validated['schedule_id'],
            ]
        );
        return response()->json(['status' => 'success']);
    }
    public function getEmployeesByBranch(Request $request)
    {
        $role_id = Auth::user()->role_id;
        $branch_id = $request->branch_id;
        $employees = DB::connection("intra_payroll")
            ->table("tbl_employee")
            ->select("id", "first_name", "middle_name", "last_name", "ext_name","emp_code")
            ->where("is_active", 1);
        if ($role_id === 4) { // HR Group D
            $employees = $employees->where("hr_group", "group_a");
        } elseif ($role_id === 5) { // HR Group B,C,E
            $employees = $employees->whereIn("hr_group", ["group_b","group_c","group_e"]);
        } elseif ($role_id === 14) { // HR Group B,C
            $employees = $employees->whereIn("hr_group", ["group_b","group_c"]);
        } elseif ($role_id === 15) { // HR Group C
            $employees = $employees->whereIn("hr_group", ["group_c","group_e"]);
        } 
        // elseif ($role_id === 22) { // HR Group E
        //     $employees = $employees->where("hr_group", "group_e");
        // }
            $employees = $employees->orderBy("last_name", "asc")
            ->get();
        return response()->json($employees);
    }
    public function exportRawLogs(Request $request)
    {
        $logs = DB::connection("intra_payroll")->table("tbl_raw_logs");
        // Filter by date range
        if ($request->filled('date_range')) {
            [$start_date, $end_date] = explode(' - ', $request->date_range);
            $logs->whereBetween('logs', [$start_date . ' 00:00:00', $end_date . ' 23:59:59']);
        }
        $logs = $logs->orderBy("logs", "DESC")->get();
        // Map logs with employee names
        $finalLogs = [];
        foreach ($logs as $log) {
            $employee = DB::connection("intra_payroll")->table('tbl_employee')
                ->where('bio_id', $log->biometric_id)
                ->first();
            $employee_name = $employee ? "{$employee->first_name} {$employee->last_name}" : "Unknown Employee";
            $finalLogs[] = [
                'employee_name' => $employee_name,
                'biometric_id'  => $log->biometric_id,
                'state'         => $log->state,
                'logs'          => $log->logs,
                'location'      => $log->location ?? 'N/A',
            ];
        }
        return Excel::download(new RawLogsExport(collect($finalLogs)), 'raw_logs_export_' . time() . '.xlsx');
    }

    public function delete_holiday(Request $request)
    {
        // Validate the holiday ID
        $holiday_id = $request->holiday_id;
        if (!$holiday_id) {
            return response()->json(['error' => 'Holiday ID not provided'], 400);
        }

        // Delete the holiday from the database
        $deleted = DB::connection("intra_payroll")->table("tbl_holiday")
                    ->where('id', $holiday_id)
                    ->delete();

        if ($deleted) {
            return response()->json(['success' => 'Holiday deleted successfully']);
        } else {
            return response()->json(['error' => 'Holiday not found or unable to delete'], 400);
        }
    }
}
