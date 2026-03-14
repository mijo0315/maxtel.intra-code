<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Auth;
use DB;
use Storage;
use Yajra\DataTables\DataTables;
use DateTime;
use DateInterval;
use DatePeriod;
use Carbon\Carbon; //OT ALL
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class payrollController extends Controller
{
    function search_multi_array($array, $key, $value) {
        foreach ($array as $subarray) {
            if (isset($subarray[$key]) && $subarray[$key] == $value) {
                return $subarray;
            }
        }
        return null;
    }
    
    public function payroll_management(){
        $role_id = Auth::user()->role_id;
        $query = DB::connection("intra_payroll")->table("tbl_employee")
            ->where("is_active", 1);
         if ($role_id === 4) { // HR Group D
            $query->where("hr_group", "group_d");
        } elseif ($role_id === 5) { // HR Group B,C,E
            $query->whereIn("hr_group", ["group_b","group_c","group_e"]);
        } elseif ($role_id === 14) { // HR Group B,C
            $query->whereIn("hr_group", ["group_b","group_c"]);
        } elseif ($role_id === 15) { // HR Group C
            $query->whereIn("hr_group", ["group_c","group_e"]);
        }
        //  elseif ($role_id === 22) { // HR Group E
        //     $query->where("hr_group", "group_e");
        // }
        $tbl_employee = json_decode(json_encode(
            $query->orderBy("last_name")
                ->orderBy("first_name")
                ->orderBy("middle_name")
                ->get()
        ), true);
        // $tbl_employee = json_decode(json_encode(DB::connection("intra_payroll")->table("tbl_employee")->where("is_active", 1)->orderBy("last_name","asc")->get()),true);
        $lib_income = json_decode(json_encode(DB::connection("intra_payroll")->table("lib_income")->where("is_regular", 1)->where("is_active", 1)->orderBy("name","asc")->get()), true);
        $lib_loan = json_decode(json_encode(DB::connection("intra_payroll")->table("lib_loans")->where("is_active", 1)->orderBy("name","asc")->get()), true);
        return view("payroll.index")
            ->with("tbl_employee", $tbl_employee)
            ->with("lib_income", $lib_income)
            ->with("lib_loan", $lib_loan)
        
            ;  
    }
    public function tag_emp_modal(Request $request){
           // status
                        // OPEN => on create
                        // ADDED => added employee & timekeeping
                        // PROCESS => COMPUTED
                        // FINALIZE => close to add timekeeping and other data and wait for approval
                        // CLOSE => approved by payroll manager
        $payroll_info = DB::connection("intra_payroll")->table("tbl_payroll")
            ->where("id", $request->pay_id)
            ->first();
            if($payroll_info != null){
                if($payroll_info->payroll_status == "FINALIZE" ){
                    return json_encode("Payroll Already for Posting");
                }elseif($payroll_info->payroll_status == "CLOSE"){
                    return json_encode("Payroll Already Close");
                }else{
                    return json_encode("success");
                }
            }else{
                return json_encode("Payroll Information Unreachable");
            }
        
    }
    public function get_employee_list_of_payroll_tk(Request $request){
        $payroll_info = DB::connection("intra_payroll")->table("tbl_payroll")
            ->where("id", $request->pay_id)
            ->first();
        if($payroll_info != null){
            if($payroll_info->payroll_status == "FINALIZE" ){
                return json_encode("Payroll Already for Posting");
            }elseif($payroll_info->payroll_status == "CLOSE"){
                return json_encode("Payroll Already Close");
            }else{
                    $emp_id = array();    
                    $employee = $payroll_info->employee;
                    $employee_list = explode(";", $employee);
                    foreach($employee_list as $emp){
                        array_push($emp_id, str_replace("|","", $emp));
                    }
                    
                    $list = DB::connection("intra_payroll")->table("tbl_employee")
                        ->whereIn("id", $emp_id)
                        ->orderBy("last_name","asc")
                        ->get();
                        
                    return json_encode($list);
            }
        }else{
            return json_encode("Payroll Information Unreachable");
        }
        
    }
    public function tagged_employee_tk(Request $request){
   $page_permission = Auth::user()->access[$request->page]["access"];
        $tbl_employee = json_decode(json_encode(DB::connection("intra_payroll")->table("tbl_employee")->get()),true);
        $data = DB::connection("intra_payroll")->table("tbl_payroll")
            ->where("id", $request->pay_id)
            ->where("employee", "!=", "")
            ->first();
      
            $table_arr = array();
            if($data != null){
                $cover_from = $data->cover_from;
         
                $employee = $data->employee;
                $employee = explode(";", $employee);
          
                foreach($employee as $emp){
                    $emp_id = str_replace("|","",$emp);
                    $btn = "";
                    $data = $this->search_multi_array($tbl_employee, "id", $emp_id);
                    $employee_name = $data["emp_code"]." - ".$data["last_name"].", ".$data["first_name"]." ".$data["middle_name"];
                  
                    $tk = DB::connection("intra_payroll")->table("tbl_timekeeping")
                        
                        ->where("emp_id", $emp_id)
                        ->where("is_manual", 1)
                        ->where("date_target", $cover_from)
                        ->first();
               
                    if($tk != null){
                        $btn = "";
                        $tk_id = $tk->id;
                        $pay_id = $request->pay_id;
                        if(preg_match("/U/i", $page_permission)){
                            $btn = "<a class='btn btn-sm btn-warning' onclick='remove_this_tk(".$tk->id.",".$pay_id.")' ><i class='fas fa-times-circle'></i> REMOVE </a>";
                        }
                      
                        array_push($table_arr, array(
                            'name' => $employee_name,
                            'regular_work' => $tk->regular_work,
                            'lates' => $tk->lates,
                            'regular_ot' => $tk->regular_ot,
                            'special_ot' => $tk->special_ot,
                            'night_diff' => $tk->night_diff,
                            'regular_leave' => $tk->regular_leave,
                            'sick_leave' => $tk->sick_leave,
                            'special_leave' => $tk->special_leave,
                            'regular_holiday' => $tk->regular_holiday,
                            'special_holiday' => $tk->special_holiday,
                            'action' => $btn
                        ));
    
                    }
                   
                   
                  
                }
            }
             $table_arr = collect($table_arr);
            return Datatables::of($table_arr)
                ->make(true);
    }
    public function remove_tagged_employee_tk(Request $request){
        DB::connection("intra_payroll")->table("tbl_timekeeping")
            ->where("id", $request->tk_id)
            ->delete();
        return json_encode("Deleted");
    }
    public function get_employee_list_of_payroll(Request $request)
    {
        $user = Auth::user();
        $role_id = $user->role_id;
        $payroll_info = DB::connection("intra_payroll")->table("tbl_payroll")
            ->where("id", $request->pay_id)
            ->first();
        if ($payroll_info == null) {
            return json_encode("Payroll Information Unreachable");
        }
        if ($payroll_info->payroll_status == "FINALIZE") {
            return json_encode("Payroll Already for Posting");
        } elseif ($payroll_info->payroll_status == "CLOSE") {
            return json_encode("Payroll Already Close");
        }
        $filter = $request->filter;
        $salary_type = $payroll_info->type == "MONTHLY" ? "MONTHLY" : null;
        // Base employee query with active filter
        $employeeQuery = DB::connection("intra_payroll")->table("tbl_employee")
            ->where('is_active', 1);
        // Apply salary type if monthly
        if ($salary_type) {
            $employeeQuery->where("salary_type", $salary_type);
        }
        // Apply HR group filtering based on role
         if ($role_id === 4) {
            $employeeQuery->where("hr_group", "group_d");
        } elseif ($role_id === 5) {
            $employeeQuery->whereIn("hr_group", ["group_b","group_c","group_e"]);
        } elseif ($role_id === 14) {
            $employeeQuery->whereIn("hr_group", ["group_b","group_c"]);
        } elseif ($role_id === 15) {
            $employeeQuery->whereIn("hr_group", ["group_c","group_e"]);
        } 
        // elseif ($role_id === 22) {
        //     $employeeQuery->where("hr_group", "group_e");
        // }
        switch ($filter) {
            case "department":
                $ids = $employeeQuery->select("department")->groupBy("department")->get();
                $ids = json_decode(json_encode($ids), true);
                $list = DB::connection("intra_payroll")->table("tbl_department")
                    ->whereIn("id", $ids)
                    ->get();
                return json_encode($list);
            case "branch":
                // branch_id from employees
                $employeeBranches = $employeeQuery
                    ->select("branch_id")
                    ->groupBy("branch_id")
                    ->pluck("branch_id")
                    ->toArray();

                // branch_id from daily schedule
                $scheduleBranches = DB::connection("intra_payroll")
                    ->table("tbl_daily_schedule")
                    ->select("branch_id")
                    ->groupBy("branch_id")
                    ->pluck("branch_id")
                    ->toArray();

                // merge and remove duplicates
                $ids = array_unique(array_merge($employeeBranches, $scheduleBranches));

                // get branch list
                $list = DB::connection("intra_payroll")->table("tbl_branch")
                    ->whereIn("id", $ids)
                    ->get();

                return json_encode($list);
            case "designation":
                $ids = $employeeQuery->select("designation")->groupBy("designation")->get();
                $ids = json_decode(json_encode($ids), true);
                $list = DB::connection("intra_payroll")->table("lib_designation")
                    ->whereIn("id", $ids)
                    ->get();
                return json_encode($list);
            case "agency":
                $list = $employeeQuery->select("agency_name")->groupBy("agency_name")->get();
                return json_encode($list);
            case "custom":
                $list = $employeeQuery->orderBy("last_name", "asc")->get();
                return json_encode($list);
            case "salary_type":
                $list = $employeeQuery->select("salary_type")->groupBy("salary_type")->get();
                return json_encode($list);
            default:
                return json_encode("Undefined Filter");
        }
    }
    public function get_employee_list_of_payroll_old(Request $request){
        $payroll_info = DB::connection("intra_payroll")->table("tbl_payroll")
            ->where("id", $request->pay_id)
            ->first();
        if($payroll_info != null){
            if($payroll_info->payroll_status == "FINALIZE" ){
                return json_encode("Payroll Already for Posting");
            }elseif($payroll_info->payroll_status == "CLOSE"){
                return json_encode("Payroll Already Close");
            }else{
                $filter = $request->filter;
                if($payroll_info->type == "MONTHLY"){
                    if($filter == "department"){
                        $ids = DB::connection("intra_payroll")->table("tbl_employee")
                            ->select("department")
                            ->where("salary_type", "MONTHLY")
                            ->where('is_active',1)
                            ->groupBy("department")
                            ->get();
                            
                        $ids = json_decode(json_encode($ids), true);
                        $list = DB::connection("intra_payroll")->table("tbl_department")
                            ->whereIn("id", $ids)
                            ->get();
                        return json_encode($list);
                    }elseif($filter=="branch"){
                        $ids = DB::connection("intra_payroll")->table("tbl_employee")
                        ->select("branch_id")
                        ->where("salary_type", "MONTHLY")
                        ->where('is_active',1)
                        ->groupBy("branch_id")
                        ->get();
                        
                        $ids = json_decode(json_encode($ids), true);
                        $list = DB::connection("intra_payroll")->table("tbl_branch")
                            ->whereIn("id", $ids)
                            ->get();
                        return json_encode($list);
                    }elseif($filter=="designation"){
                        $ids = DB::connection("intra_payroll")->table("tbl_employee")
                        ->select("designation")
                        ->where("salary_type", "MONTHLY")
                        ->where('is_active',1)
                        ->groupBy("designation")
                        ->get();
                        
                        $ids = json_decode(json_encode($ids), true);
                        $list = DB::connection("intra_payroll")->table("lib_designation")
                            ->whereIn("id", $ids)
                            ->get();
                        return json_encode($list);
                    }elseif($filter=="agency"){
                        $list = DB::connection("intra_payroll")->table("tbl_employee")
                        ->select("agency_name")
                        ->where("salary_type", "MONTHLY")
                        ->where('is_active',1)
                        ->groupBy("agency_name")
                        ->get();
                        
                        return json_encode($list);
                    }elseif($filter=="custom"){
                        $list = DB::connection("intra_payroll")->table("tbl_employee")
                        ->where("salary_type", "MONTHLY")
                        ->where('is_active',1)
                        ->orderBy("last_name", "asc")
                        ->get();
                        
                        return json_encode($list);
                    }elseif($filter=="salary_type"){
                        $list = DB::connection("intra_payroll")->table("tbl_employee")
                        ->select("salary_type")
                        ->where("salary_type", "MONTHLY")
                        ->where('is_active',1)
                        ->groupBy("salary_type")
                        ->get();
                        
                        return json_encode($list);
                    }else{
                        return json_encode("Undefine Filter");
                    }
                    
                }else{  
                  
                    if($filter == "department"){
                        $ids = DB::connection("intra_payroll")->table("tbl_employee")
                            ->select("department")
                            ->where('is_active',1)
                            ->groupBy("department")
                            ->get();
                            
                        $ids = json_decode(json_encode($ids), true);
                        $list = DB::connection("intra_payroll")->table("tbl_department")
                            ->whereIn("id", $ids)
                            ->get();
                        return json_encode($list);
                    }elseif($filter=="branch"){
                        $ids = DB::connection("intra_payroll")->table("tbl_employee")
                        ->select("branch_id")
                        ->where('is_active',1)
                        ->groupBy("branch_id")
                        ->get();
                        
                        $ids = json_decode(json_encode($ids), true);
                        $list = DB::connection("intra_payroll")->table("tbl_branch")
                            ->whereIn("id", $ids)
                            ->get();
                        return json_encode($list);
                    }elseif($filter=="designation"){
                        $ids = DB::connection("intra_payroll")->table("tbl_employee")
                        ->select("designation")
                        ->where('is_active',1)
                        ->groupBy("designation")
                        ->get();
                        
                        $ids = json_decode(json_encode($ids), true);
                        $list = DB::connection("intra_payroll")->table("lib_designation")
                            ->whereIn("id", $ids)
                            ->get();
                        return json_encode($list);
                    }elseif($filter=="agency"){
                        $list = DB::connection("intra_payroll")->table("tbl_employee")
                        ->select("agency_name")
                        ->where('is_active',1)
                        ->groupBy("agency_name")
                        ->get();
                        
                        return json_encode($list);
                    }elseif($filter=="custom"){
                        $list = DB::connection("intra_payroll")->table("tbl_employee")
                        ->orderBy("last_name","asc")
                        ->where('is_active',1)
                        ->get();
                        
                        return json_encode($list);
                    }elseif($filter=="salary_type"){
                        $list = DB::connection("intra_payroll")->table("tbl_employee")
                        ->select("salary_type")
                        ->where('is_active',1)
                        ->groupBy("salary_type")
                        ->get();
                        
                        return json_encode($list);
                    }else{
                        return json_encode("Undefine Filter");
                    }
                }
              
                
            }
        }else{
            return json_encode("Payroll Information Unreachable");
        }
        
    }
    public function manual_add_tk_to_payroll(Request $request){
        $filter = $request->filter;
        $pay_id = $request->pay_id;
        $data = $request->selected;
        $regular_manual = $request->regular_manual;
        $lates = $request->lates;
        $rot = $request->rot;
        $sot = $request->sot;
        $nd = $request->nd;
        $vl = $request->vl;
        $sl = $request->sl;
        $spl_leave = $request->spl_leave;
        $reg_hol = $request->reg_hol;
        $spl_hol = $request->spl_hol;
       $tbl_employee = json_decode(json_encode(DB::connection("intra_payroll")->table("tbl_employee")->get()),true);
        $not_success = "Employee With Concern";
        $included = "";
      
        $emp_list = $data;
        $payroll_info = DB::connection("intra_payroll")->table("tbl_payroll")
        ->where("id", $pay_id)
        ->first();
        if($payroll_info != null){
            $date_from = $payroll_info->cover_from;
            foreach($emp_list as $emp){
                
                $data = $this->search_multi_array($tbl_employee, "id", $emp);
                $emp_code =  $data["emp_code"];
                $input_arr = array(
                    "emp_id" => $emp,
                    "emp_code" => $emp_code,
                    "date_target" => $date_from,
                    "is_manual" => 1,
                    "regular_work" => $regular_manual,
                    "lates" =>$lates,
                    "regular_ot" =>$rot,
                    "special_ot" =>$sot,
                    "night_diff" =>$nd,
                    "regular_leave" =>$vl,
                    "sick_leave" =>$sl,
                    "special_leave" =>$spl_leave,
                    "regular_holiday" =>$reg_hol,
                    "special_holiday" =>$spl_hol,
                );
                DB::connection("intra_payroll")->table("tbl_timekeeping")
                    ->where("date_target", $date_from)
                    ->where("emp_id", $emp)
                    ->delete();
                DB::connection("intra_payroll")->table("tbl_timekeeping")   
                    ->insert($input_arr);
            }
        }else{
            return json_encode("Payroll Unreachable");
        }
        return json_encode("Success");
    }
    public function tag_employee_to_payroll(Request $request)
    {
        $filter = $request->filter;
        $pay_id = $request->pay_id;
        $data = $request->selected;
        $user = Auth::user();
        $role_id = $user->role_id;
        // employee query
        $employeeQuery = DB::connection("intra_payroll")->table("tbl_employee")
            ->where('is_active', 1);
        if ($role_id === 4) { // HR Group D
            $employeeQuery->where("hr_group", "group_d");
        } elseif ($role_id === 5) { // HR Group B,C,E
            $employeeQuery->whereIn("hr_group", ["group_b","group_c","group_e"]);
        } elseif ($role_id === 14) { // HR Group B,C
            $employeeQuery->whereIn("hr_group", ["group_b","group_c"]);
        } elseif ($role_id === 15) { // HR Group C
            $employeeQuery->whereIn("hr_group", ["group_c","group_e"]);
        } 
        // elseif ($role_id === 22) { // HR Group E
        //     $employeeQuery->where("hr_group", "group_e");
        // }
        $tbl_employee = json_decode(json_encode($employeeQuery->get()), true);
        $not_success = "Employee With Concern";
        $included = "";
        // Determine which employees to tag
        if ($filter == "custom") {
            $emp_list = $data;
        } else {
            $empQuery = DB::connection("intra_payroll")->table("tbl_employee")
                ->select("id")
                ->where('is_active', 1);
            // Apply same role filter here
             if ($role_id === 4) {
                $empQuery->where("hr_group", "group_d");
            } elseif ($role_id === 5) {
                $empQuery->whereIn("hr_group", ["group_b","group_c","group_e"]);
            } elseif ($role_id === 14) {
                $empQuery->whereIn("hr_group", ["group_b","group_c"]);
            } elseif ($role_id === 15) {
                $empQuery->whereIn("hr_group", ["group_c","group_e"]);
            } 
            // elseif ($role_id === 22) {
            //     $empQuery->where("hr_group", "group_e");
            // }
            // Apply the filter condition
            switch ($filter) {
                case "department":
                    $empQuery->where("department", $data);
                    break;
                case "branch":
                      // get payroll coverage updated by Mifz Feb6 2026
                        $payroll = DB::connection("intra_payroll")
                            ->table("tbl_payroll")
                            ->where("id", $pay_id)
                            ->first();

                        if ($payroll) {

                            $cover_from = $payroll->cover_from;
                            $cover_to   = $payroll->cover_to;

                             //update payroll branch_id dito
                            DB::connection("intra_payroll")
                                ->table("tbl_payroll")
                                ->where("id", $pay_id)
                                ->update([
                                    "branch_id" => $data
                                ]);

                            $empQuery->where(function ($q) use ($data, $cover_from, $cover_to) {

                                // 1. employee main branch match
                                $q->where("branch_id", $data)

                                // 2. OR employee has schedule in that branch within payroll period
                                ->orWhereIn("id", function ($sub) use ($data, $cover_from, $cover_to) {

                                    $sub->select("emp_id")
                                        ->from("tbl_daily_schedule")
                                        ->where("branch_id", $data)
                                        ->whereBetween("schedule_date", [$cover_from, $cover_to]);

                                });

                            });

                        } else {
                            $empQuery->where("branch_id", $data);
                        }
                    break;
                case "designation":
                    $empQuery->where("designation", $data);
                    break;
                case "agency":
                    $empQuery->where("agency_name", $data);
                    break;
                case "salary_type":
                    $empQuery->where("salary_type", $data);
                    break;
                default:
                    $empQuery->whereRaw("1=0"); // empty result if unknown filter
                    break;
            }
            $emp_list = $empQuery->get();
        }
        // Process each employee and tag to payroll
        foreach ($emp_list as $emp) {
            $emp_id_arr = ($filter == "custom") ? $emp : $emp->id;
            $check_first = DB::connection("intra_payroll")->table("tbl_payroll")
                ->where("id", $pay_id)
                ->where("employee", "LIKE", "%|" . $emp_id_arr . "|%")
                ->first();
            if ($check_first != null) {
                if ($not_success != '') {
                    $not_success .= "<br>";
                }
                $data_emp = $this->search_multi_array($tbl_employee, "id", $emp_id_arr);
                if (!empty($data_emp)) {
                    $not_success .= $data_emp["emp_code"] . " - " . $data_emp["last_name"] . ", " . $data_emp["first_name"] . " " . $data_emp["middle_name"];
                }
            } else {
                if ($included != "") {
                    $included .= ";";
                }
                $included .= "|" . $emp_id_arr . "|";
            }
        }
        // Update payroll record
        if ($included != "") {
            $payroll_data = DB::connection("intra_payroll")->table("tbl_payroll")
                ->where("id", $pay_id)
                ->first();
            if ($payroll_data == null) {
                $not_success = "Payroll Info Unreachable";
            } else {
                $emp_data = $payroll_data->employee;
                if ($emp_data != "") {
                    $emp_data .= ";";
                }
                $emp_data .= $included;
                DB::beginTransaction();
                try {
                    DB::connection("intra_payroll")->table("tbl_payroll")
                        ->where("id", $pay_id)
                        ->update([
                            "employee" => $emp_data,
                            "payroll_status" => "ADDED"
                        ]);
                    DB::commit();
                } catch (\Throwable $th) {
                    DB::rollback();
                    return json_encode($th->getMessage());
                }
            }
        }
        if ($not_success == "Employee With Concern") {
            $not_success = "Success Tagging";
        }
        return json_encode($not_success);
    }
    public function tag_employee_to_payroll_old(Request $request){
        $filter = $request->filter;
        $pay_id = $request->pay_id;
        $data = $request->selected;
       $tbl_employee = json_decode(json_encode(DB::connection("intra_payroll")->table("tbl_employee")->where('is_active',1)->get()),true);
        $not_success = "Employee With Concern";
        $included = "";
        if($filter == "custom"){
            $emp_list = $data;
        }else{
            if($filter == "department"){
                $emp_list = DB::connection("intra_payroll")->table("tbl_employee")
                    ->select("id")
                    ->where("department", $data)
                    ->where('is_active',1)
                    ->get();
            }elseif($filter == "branch"){
                $emp_list = DB::connection("intra_payroll")->table("tbl_employee")
                ->select("id")
                ->where("branch_id", $data)
                ->where('is_active',1)
                ->get();
            }elseif($filter == "designation"){
                $emp_list = DB::connection("intra_payroll")->table("tbl_employee")
                ->select("id")
                ->where("designation", $data)
                ->where('is_active',1)
                ->get();
            }elseif($filter == "agency"){
                $emp_list = DB::connection("intra_payroll")->table("tbl_employee")
                ->select("id")
                ->where("agency_name", $data)
                ->where('is_active',1)
                ->get();
            }elseif($filter == "salary_type"){
                $emp_list = DB::connection("intra_payroll")->table("tbl_employee")
                ->select("id")
                ->where("salary_type", $data)
                ->where('is_active',1)
                ->get();
            }else{
                $get_emp = array();
            }
        }
        foreach($emp_list as $emp){
            if($filter == "custom" ){
                $emp_id_arr = $emp;
            }else{
                $emp_id_arr = $emp->id;
            }
            $check_first = DB::connection("intra_payroll")->table("tbl_payroll")
                ->where("id", $pay_id)
                ->where("employee", "LIKE","%|".$emp_id_arr."|%")
                ->first();
                if($check_first != null){
                    if($not_success != ''){$not_success .= "<br>";}
                    $data = $this->search_multi_array($tbl_employee, "id", $emp_id_arr);
                    $not_success .= $data["emp_code"]." - ".$data["last_name"].", ".$data["first_name"]." ".$data["middle_name"];
                }else{
                    if($included != ""){$included .= ";";}
                    $included .= "|".$emp_id_arr."|";
                }
             
                
        }
        
        if($included != ""){
            $payroll_data = DB::connection("intra_payroll")->table("tbl_payroll")
                ->where("id", $pay_id)
                ->first();
                if($payroll_data == null){
                    $not_success = "Payroll Info Unreachable";
                }else{
                    $emp_data = $payroll_data->employee;
                    if($emp_data != ""){$emp_data .= ";";}
                    $emp_data .= $included;
                    DB::beginTransaction();
                    try {
                        DB::connection("intra_payroll")->table("tbl_payroll")
                        ->where("id", $pay_id)
                        ->update([
                            "employee" => $emp_data,
                            "payroll_status" => "ADDED"
                        ]);
                        DB::commit();
                    } catch (\Throwable $th) {
                        DB::rollback();
                        return json_encode($th->getMessage());
                    }
                }
        }
        if($not_success == "Employee With Concern"){
            $not_success = "Success Tagging";
        }
        
        return json_encode($not_success);
    }
    public function payroll_one_time_inc(Request $request){
        $data = DB::connection("intra_payroll")->table("lib_income")
            ->where("is_regular", 0)
            ->where("is_active", 1)
            ->orderBy("name","asc")
            ->get();
        return json_encode($data);
    }
    public function payroll_one_time_ded(Request $request){
        $data = DB::connection("intra_payroll")->table("lib_loans")
            ->where("is_regular", 0)
            ->where("is_active", 1)
            ->orderBy("name","asc")
            ->get();
        return json_encode($data);
    }
    
    public function tagged_employee(Request $request){
        $page_permission = Auth::user()->access[$request->page]["access"];
        $tbl_employee = json_decode(json_encode(DB::connection("intra_payroll")->table("tbl_employee")->get()),true);
        $data = DB::connection("intra_payroll")->table("tbl_payroll")
            ->where("id", $request->pay_id)
            ->where("employee", "!=", "")
            ->first();
      
            $table_arr = array();
            if($data != null){
              
                $employee = $data->employee;
                $employee = explode(";", $employee);
                foreach($employee as $emp){
                    $emp_id = str_replace("|","",$emp);
                    $btn = "";
                    $data = $this->search_multi_array($tbl_employee, "id", $emp_id);
                    if($data){
                        $employee_name = $data["emp_code"]." - ".$data["last_name"].", ".$data["first_name"]." ".$data["middle_name"];
                        $basic_pay = number_format($data["salary_rate"],2)." ".$data["salary_type"];
                    }else{
                        $employee_name = 'N/A';
                        $basic_pay = 'N/A';
                    }
                    if(preg_match("/U/i", $page_permission)){
                        // $btn = "<a class='btn btn-sm btn-success mr-1' 
                        // data-toggle='modal' 
                        // data-target='#payroll_inc_modal'
                        // data-emp_id='".$emp_id."'
                        // data-inc_pay_id='".$request->pay_id."'
                        // ><i class='fas fa-plus-circle'></i> Income </a>";
                        // $btn .= "<a class='btn btn-sm btn-danger mr-1'
                        // data-toggle='modal' 
                        // data-target='#payroll_ded_modal'
                        // data-emp_id='".$emp_id."'
                        // data-inc_pay_id='".$request->pay_id."'
                        // ><i class='fas fa-minus-circle'></i> Deduction </a>";
    
                        $btn .= "<a class='btn btn-sm btn-warning' onclick='remove_this(".'"'.$emp_id.'"'.")' ><i class='fas fa-times-circle'></i> REMOVE </a>";
                    }
                  
                    array_push($table_arr, array(
                        "name" => $employee_name,
                        "basic_pay" => $basic_pay,
                        "action" => $btn
                    ));
                }
            }
             $table_arr = collect($table_arr);
            return Datatables::of($table_arr)
                ->make(true);
    }
    public function payroll_deduction_tbl(Request $request){
        $page_permission = Auth::user()->access[$request->page]["access"];
        $tbl_employee = json_decode(json_encode(DB::connection("intra_payroll")->table("tbl_employee")->get()),true);
        $lib_loans = json_decode(json_encode(DB::connection("intra_payroll")->table("lib_loans")->get()),true);
        
            
            $table_arr = DB::connection("intra_payroll")->table("tbl_payroll_deduction")
                ->where("payroll_id", $request->pay_id)
                ->where("type", "!=", "SSS")
                ->where("type", "!=", "HDMF")
                ->where("type", "!=", "PH")
                ->where("type", "!=", "TAX")
                ->where("type", "!=", "LATE")
                ->where("type", "!=", "ABSENT")
                ->orderBy("date_updated","desc")
                ->get();
             $table_arr = collect($table_arr);
            return Datatables::of($table_arr)
                ->addColumn('name', function($row) use ($tbl_employee){
                    $data = $this->search_multi_array($tbl_employee, "id", $row->emp_id);
                    $employee_name = $data["emp_code"]." - ".$data["last_name"].", ".$data["first_name"]." ".$data["middle_name"];
                
                    return $employee_name;
                })
                ->addColumn('type', function($row) use ($lib_loans){
                        $row->type =  str_replace("R_","",$row->type);
                        $data = $this->search_multi_array($lib_loans, "id", $row->type);
                        $info = $data["name"];
                        return $info;
                })
                ->addColumn('amount', function($row){
                    return number_format($row->amount,2);
                })
                ->addColumn('action', function($row) use ($page_permission){
                    $btn = "";
                    if(preg_match("/U/i", $page_permission)){
                    $btn .= "<button class='btn btn-warning btn-sm' onclick='remove_deduction(".$row->id.")'> <i class='fas fa-times-circle'></i> REMOVE </button>";
                    }
                    return $btn;
                 })
                 ->rawColumns(['action'])
                 ->make(true);
    }
    public function payroll_income_tbl(Request $request){
        $page_permission = Auth::user()->access[$request->page]["access"];
        $tbl_employee = json_decode(json_encode(DB::connection("intra_payroll")->table("tbl_employee")->get()),true);
        $lib_income = json_decode(json_encode(DB::connection("intra_payroll")->table("lib_income")->get()),true);
        
            
            $table_arr = DB::connection("intra_payroll")->table("tbl_payroll_income")
                ->where("payroll_id", $request->pay_id)
                ->where("type", "!=", "BP")
                ->where("type", "!=", "ROT")
                ->where("type", "!=", "SOT")
                ->where("type", "!=", "ND")
                ->where("type", "!=", "RH")
                ->where("type", "!=", "SH")
                // ->where('type', "NOT LIKE", "R_%")
                ->orderBy("date_updated","desc")
                ->get();
                $table_arr = collect($table_arr);
            
            return Datatables::of($table_arr)
                ->addColumn('name', function($row) use ($tbl_employee){
                    $data = $this->search_multi_array($tbl_employee, "id", $row->emp_id);
                    $employee_name = $data["emp_code"]." - ".$data["last_name"].", ".$data["first_name"]." ".$data["middle_name"];
                
                    return $employee_name;
                })
                ->addColumn('type', function($row) use ($lib_income){
                    $row->type =  str_replace("R_","",$row->type);
                    
                        $data = $this->search_multi_array($lib_income, "id", $row->type);
                        $info = $data["name"];
                        return $info;
                })
                ->addColumn('amount', function($row){
                    return number_format($row->amount,2);
                })
                ->addColumn('action', function($row) use ($page_permission){
                    $btn = "";
                    if(preg_match("/U/i", $page_permission)){
                    $btn .= "<button class='btn btn-warning btn-sm' onclick='remove_oth_inc(".$row->id.")'> <i class='fas fa-times-circle'></i> REMOVE </button>";
                    }
                    return $btn;
                 })
                 ->rawColumns(['action'])
                 ->make(true);
    }
    public function delete_oth_inc_payroll(Request $request){
        DB::beginTransaction();
        try {
            $oth_data = DB::connection("intra_payroll")->table("tbl_payroll_income")
            ->where('id', $request->id)
            ->delete();
            DB::commit();
            return json_encode("Deleted");
        } catch (\Throwable $th) {
            DB::rollback();
            return json_encode($th->getMessage());
        }
    }
    public function delete_deduction_payroll(Request $request){
        DB::beginTransaction();
        try {
            $oth_data = DB::connection("intra_payroll")->table("tbl_payroll_deduction")
            ->where('id', $request->id)
            ->delete();
            DB::commit();
            return json_encode("Deleted");
        } catch (\Throwable $th) {
            DB::rollback();
            return json_encode($th->getMessage());
        }
    }
    
    public function add_oth_ded_payroll(Request $request){
        DB::beginTransaction();
        if(isset($request->emp_list)){
        }else{
            return json_encode("No Employee Selected");
        }
        try {
            foreach($request->emp_list as $emp){
                $oth_data = DB::connection("intra_payroll")->table("tbl_payroll_deduction")
                    ->where("payroll_id", $request->ded_pay_id)
                    ->where("type", $request->ded_one_time)
                    ->where("emp_id", $emp)
                    ->first();
    
                if($oth_data != null){
                    DB::connection("intra_payroll")->table("tbl_payroll_deduction")
                        ->where('id', $oth_data->id)
                        ->update([
                            "amount" => $request->ded_amount,
                            "user_id" => Auth::user()->id,
                        ]);
                }else{
                    DB::connection("intra_payroll")->table("tbl_payroll_deduction")
                        ->insert([
                            "payroll_id" => $request->ded_pay_id,
                            "type" => $request->ded_one_time,
                            "emp_id" => $emp,
                            "amount" => $request->ded_amount,
                            "user_id" => Auth::user()->id,
                            "date_created" => date("Y-m-d")
                        ]);
                }
    
            }
            DB::commit();
            return json_encode("Success");
        } catch (\Throwable $th) {
            DB::rollback();
            return json_encode($th->getMessage());
        }
    
}
    public function add_oth_inc_payroll(Request $request){
            DB::beginTransaction();
            if(isset($request->emp_list)){
            }else{
                return json_encode("No Employee Selected");
            }
            try {
                foreach($request->emp_list as $emp){
                    $oth_data = DB::connection("intra_payroll")->table("tbl_payroll_income")
                        ->where("payroll_id", $request->inc_pay_id)
                        ->where("type", $request->inc_one_time)
                        ->where("emp_id", $emp)
                        ->first();
        
                    if($oth_data != null){
                        DB::connection("intra_payroll")->table("tbl_payroll_income")
                            ->where('id', $oth_data->id)
                            ->update([
                                "amount" => $request->inc_amount,
                                "user_id" => Auth::user()->id,
                            ]);
                    }else{
                        DB::connection("intra_payroll")->table("tbl_payroll_income")
                            ->insert([
                                "payroll_id" => $request->inc_pay_id,
                                "type" => $request->inc_one_time,
                                "emp_id" => $emp,
                                "amount" => $request->inc_amount,
                                "user_id" => Auth::user()->id,
                                "date_created" => date("Y-m-d")
                            ]);
                    }
        
                }
                DB::commit();
                return json_encode("Success");
            } catch (\Throwable $th) {
                DB::rollback();
                return json_encode($th->getMessage());
            }
        
    }
    public function payroll_tagged_list(Request $request){
       $emp_list = DB::connection("intra_payroll")->table("tbl_payroll")
            ->select(DB::raw("REPLACE(employee,'|','') as employee"))
            ->where("id", $request->pay_id)
            ->first();
            if($emp_list != null){
                $employee = explode(";",$emp_list->employee);
                    $employee_data = DB::connection("intra_payroll")->table("tbl_employee")
                        ->whereIn("id", $employee)
                        ->orderBy("last_name","asc")
                        ->get();
                    return json_encode($employee_data);
                 
            }else{
                return json_encode("Payroll Info Unreachable");
            }
    }
    public function remove_tagged_employee(Request $request){
        DB::beginTransaction();
        try {
            $data = DB::connection("intra_payroll")->table("tbl_payroll")
                ->where("id", $request->pay_id)
                ->where("employee", "like", "%|".$request->emp_id."|%")
                ->first();
            if($data != null){
                $data_emp = $data->employee;
                $employ = "";
                $explode = explode(";",$data_emp);
                    foreach($explode as $exp){
                        
                        if($exp == "|".$request->emp_id."|"){
                            $data_info = "";
                        }else{
                            $data_info = $exp;
                            if($employ != ""){$employ .= ";";}
                            $employ .= $data_info;
                        }
                       
                    }
                    DB::connection("intra_payroll")->table("tbl_payroll")
                        ->where("id", $request->pay_id)
                        ->update([
                            "employee" => $employ
                        ]);
                $check = DB::connection("intra_payroll")->table("tbl_payroll")
                    ->where("id", $request->pay_id)
                    ->first();
                if($check->employee == ""){
                    DB::connection("intra_payroll")->table("tbl_payroll")
                    ->where("id", $check->id)
                    ->update([
                        "payroll_status" => "OPEN"
                    ]);
                }
            }
            DB::connection("intra_payroll")->table("tbl_payroll_income")
                ->where("payroll_id", $request->pay_id)
                ->where("emp_id", $request->emp_id)
                ->delete();
            DB::connection("intra_payroll")->table("tbl_payroll_deduction")
                ->where("payroll_id", $request->pay_id)
                ->where("emp_id", $request->emp_id)
                ->delete();
            DB::commit();
            return json_encode("Success");
        } catch (\Throwable $th) {
            DB::rollback();
            return json_encode($th->getMessage());
        }
      
      
    }
    public function payroll_process(Request $request){
           // status
            // OPEN => on create
            // ADDED => added employee & timekeeping
            // PROCESS => COMPUTED
            // FINALIZE => close to add timekeeping and other data and wait for approval
            // CLOSE => approved by payroll manager
        $tbl_employee = json_decode(json_encode(DB::connection("intra_payroll")->table("tbl_employee")->get()),true);
        
        $data = DB::connection("intra_payroll")->table("tbl_payroll")
            ->where("id", $request->pay_id)
            ->first();
            // dd($data);
            if($data != null){
                if($data->payroll_status=="FINALIZE"){
                    return json_encode("Payroll is for Approval");
                }elseif($data->payroll_status == "CLOSE"){
                    return json_encode("Payroll is already Closed");
                }else{
                   
                        if($data->employee != "" || $data->employee != null){
                            $employee_list = explode(";",$data->employee);
                            if($data->process_type == "RP"){
                              return json_encode($this->regular_payroll($employee_list, $request, $data, $tbl_employee));
                            } //Regular Payroll
                            elseif($data->process_type == "13"){
                                return json_encode($this->payroll_process_13thmonth($employee_list, $request, $data, $tbl_employee));
                            } //13th Month
                            elseif($data->process_type == "BP"){
                                return json_encode($this->payroll_process_bonus($employee_list, $request, $data, $tbl_employee));
                            }elseif($data->process_type == "SP"){
                                return json_encode($this->payroll_process_special($employee_list, $request, $data, $tbl_employee));    
                            }elseif($data->process_type == "LC"){
                                return json_encode($this->payroll_process_leave_credits($employee_list, $request, $data, $tbl_employee));
                            }
                            else{
                                return json_encode("Payroll Type Undefine");
                            }
                        }else{
                            return json_encode("No Employee Tagged");
                        }
                    
                    
                    
                    
                }
            }
            else{
                return json_encode("Payroll Info Unreachable");
            }
    }
    private function getSchedule($emp_id){
        //first on employee level
        $emp_data = DB::connection("intra_payroll")->table("tbl_employee")->where("id", $emp_id)->first();
        if($emp_data->schedule_id != null || $emp_data->schedule_id != 0){   return $emp_data->schedule_id;}
        //BY POSITION
        $lib_position = DB::connection("intra_payroll")->table("lib_position")->where("id", $emp_data->position_id)->value("schedule_id");
        if($lib_position != null || $lib_position != 0){   return $lib_position;}
        //BY DESIGNATION
        $lib_designation = DB::connection("intra_payroll")->table("lib_designation")->where("id", $emp_data->designation)->value("schedule_id");
        if($lib_designation != null || $lib_designation != 0){   return $lib_designation;}
        //BY DEPARTMETN
        $tbl_department = DB::connection("intra_payroll")->table("tbl_department")->where("id", $emp_data->department)->value("schedule_id");
        if($tbl_department != null || $tbl_department != 0){   return $tbl_department;}
        //BY DEPARTMETN
        $tbl_branch = DB::connection("intra_payroll")->table("tbl_branch")->where("id", $emp_data->branch_id)->value("schedule_id");
        if($tbl_branch != null || $tbl_branch != 0){   return $tbl_branch;}
        $default_work_settings = DB::connection("intra_payroll")->table("tbl_site_config")->value("default_work_settings");
        return $default_work_settings;
    }
    private function get_totalRD($emp_id, $date_from, $date_to){
        $rd_count = 0;
        $default_work_schedule =  $this->getSchedule($emp_id);
        // WEEKLY SCHEDULE
        $get_weekly_schedule = DB::connection("intra_payroll")->table("lib_week_schedule")->where("id", $default_work_schedule)->first();
        $weekly_schedule = array();
        if($get_weekly_schedule != null){
            $weekly_schedule[1] = $get_weekly_schedule->monday;
            $weekly_schedule[2] = $get_weekly_schedule->tuesday;
            $weekly_schedule[3] = $get_weekly_schedule->wednesday;
            $weekly_schedule[4] = $get_weekly_schedule->thursday;
            $weekly_schedule[5] = $get_weekly_schedule->friday;
            $weekly_schedule[6] = $get_weekly_schedule->saturday;
            $weekly_schedule[7] = $get_weekly_schedule->sunday;
        }else{
            $weekly_schedule[1] = "NO SCHED";
            $weekly_schedule[2] = "NO SCHED";
            $weekly_schedule[3] = "NO SCHED";
            $weekly_schedule[4] = "NO SCHED";
            $weekly_schedule[5] = "NO SCHED";
            $weekly_schedule[6] = "NO SCHED";
            $weekly_schedule[7] = "NO SCHED";
        }
        $startDate = new DateTime($date_from); // Start date
        $endDate = new DateTime($date_to);   // End date
        $endDate->modify('+1 day');
        
        
        for ($date = $startDate; $date < $endDate; $date->modify('+1 day')) {
            $loop_date = $date->format('Y-m-d');
            $numeric_day =  $date->format('N');
            
        $tbl_daily_schedule = DB::connection("intra_payroll")->table("tbl_daily_schedule")->where("emp_id", $emp_id)->where("schedule_id", 0)->where("schedule_date", $loop_date)   ->first();
            if($tbl_daily_schedule != null){
                $rd_count ++;
            }else{
                if($weekly_schedule[$numeric_day] == 0){
                    $rd_count ++;
                }
            }
   
        }
  
        return $rd_count;
    }
      private function regular_payroll($employee_list, $request, $data, $tbl_employee){
        $time_keeping = json_decode(json_encode(DB::connection("intra_payroll")->table("tbl_timekeeping")
        ->select(DB::raw("SUM(regular_work) as regular_work"), DB::raw("SUM(rd_ot) as rd_ot"), DB::raw("SUM(rd_ot_rh) as rd_ot_rh"), 
            DB::raw("SUM(rd_ot_sh) as rd_ot_sh"), DB::raw("SUM(lates) as lates"), DB::raw("SUM(undertime) as undertime"), 
            DB::raw("SUM(rd) as rd"),
            DB::raw("SUM(rd_sh) as rd_sh"),
            DB::raw("SUM(rd_rh) as rd_rh"),
            DB::raw("SUM(rh_ot) as rh_ot"),
            DB::raw("SUM(sh_ot) as sh_ot"),
            DB::raw("SUM(absent) as absent"), DB::raw("SUM(regular_ot) as regular_ot"), DB::raw("SUM(nd_ot) as nd_ot"), 
            DB::raw("SUM(special_ot) as special_ot"), DB::raw("SUM(night_diff) as night_diff"), 
            DB::raw("SUM(regular_leave) as regular_leave"), DB::raw("SUM(sick_leave) as sick_leave"), 
            DB::raw("SUM(special_leave) as special_leave") , DB::raw("SUM(regular_holiday) as regular_holiday") , 
            DB::raw("SUM(special_holiday) as special_holiday"), DB::raw("SUM(IF(regular_work > 0,1,0)) as present_days"),
            DB::raw("SUM(IF(regular_work-((undertime+lates)/60) > 0,IF(regular_work-((undertime+lates)/60)<=5, 0.5,1),0)) as present_days"), "emp_id" ) 
            ->whereBetween("date_target", [$data->cover_from, $data->cover_to])
            ->where("payroll_id", $data->id)
            ->groupBy("emp_id")
            ->get()),true);
 
        $target_from = date("Y-m-d", strtotime($data->target_year.'-'.$data->target_month."-01"));
        $target_to = date("Y-m-t", strtotime($target_from));
        $lib_ot_table = json_decode(json_encode(DB::connection("intra_payroll")->table("lib_ot_table")->get()),true);
     
            $payroll_income = array();
            $payroll_deduction = array();
            $payroll_statutory = array();
            $date_from = strtotime($data->cover_from);
            $date_to = strtotime($data->cover_to);
            $number_of_days = round(($date_to - $date_from) / (60*60*24)) + 1;
           
        foreach($employee_list as $emp){
            $rd = 0;
            $emp_info = str_replace("|", "",$emp);
            $emp_check = $this->search_multi_array($tbl_employee, "id", $emp_info);
            $emp_rate = $emp_check["salary_rate"];
            $emp_rate_type = $emp_check["salary_type"];
            $is_active = $emp_check["is_active"];
            $emp_mwe = $emp_check["is_mwe"];
            $emp_sss = $emp_check["sss_number"];
            $emp_ph = $emp_check["philhealth_number"];
            $emp_hdmf = $emp_check["hdmf_number"];
            $emp_tin = $emp_check["tin_number"];
            $fix_divisor = $emp_check["fix_divisor"];
            $yearly_divisor = $emp_check["yearly_divisor"];
            $fix_sss = $emp_check["fix_sss"];
            $fix_philhealth = $emp_check["fix_philhealth"];
            $fix_hdmf = $emp_check["fix_hdmf"];
            $fix_tax_rate = $emp_check["fix_tax_rate"];
            $total_income = 0;
            
            $time_keep = $this->search_multi_array($time_keeping, "emp_id", $emp_info);
            $daily_divisor = Auth::user()->company['daily_divisor'];
            //DAILY RATE
                   //GET RD
            $getRD= $this->get_totalRD($emp_info,$data->cover_from,$data->cover_to);
            
            if($emp_rate_type == "MONTHLY"){
                if($fix_divisor <= 0 || $fix_divisor == null){
                    $divisor = Auth::user()->company['divisor'];
                }else{
                    $divisor = $fix_divisor;
                }
                $emp_daily_rate = $emp_rate / $divisor;
                if(!empty($yearly_divisor) && $yearly_divisor > 0){
                    $divisor = $yearly_divisor;
                    $emp_yearly_rate = $emp_rate*12;
                    $emp_daily_rate = $emp_yearly_rate / $divisor;
                }
            }else{
                $emp_daily_rate = $emp_rate;
            }
       
            $emp_hourly_rate = $emp_daily_rate / $daily_divisor;
            
            if($time_keep != null){
                // 85.227272727273
                
                if($emp_rate_type == "MONTHLY"){
                    $present_days = $time_keep["present_days"];
                    $present_days = $present_days + $time_keep["regular_leave"] + $time_keep["sick_leave"] + $time_keep["special_leave"];
                }else{
                    $present_days = $time_keep["regular_work"];
              
                                        
                    $additional_present_days =  $time_keep["regular_leave"] + $time_keep["sick_leave"] + $time_keep["special_leave"];
                    // $additional_present_days = $additional_present_days / $daily_divisor;
                    $present_days = $present_days + $additional_present_days;
                }
                $lates =  $time_keep["lates"];
                $regular_ot = $time_keep["regular_ot"];
                $special_ot = $time_keep["special_ot"];
                $undertime = $time_keep["undertime"];
                $night_diff = $time_keep["night_diff"];
                $nd_ot = $time_keep["nd_ot"];
                $regular_holiday = $time_keep["regular_holiday"];
                $special_holiday = $time_keep["special_holiday"];
                $rd_ot = $time_keep["rd_ot"];
                $rd_ot_rh = $time_keep["rd_ot_rh"];
                $rd_ot_sh = $time_keep["rd_ot_sh"];
                $rd = $time_keep["rd"];
                $rd_rh = $time_keep["rd_rh"];
                $rd_sh = $time_keep["rd_sh"];
                $rh_ot = $time_keep["rh_ot"];
                $sh_ot = $time_keep["sh_ot"];
               
           
                $absent_days = $time_keep["absent"] / $daily_divisor;
                $real_present_days = $time_keep["present_days"] - $absent_days;
            }else{
                
                $present_days = 0;
                $absent_days = 0;
                 $real_present_days  = 0;
                if($emp_rate_type == "MONTHLY"){
                    $present_days = $number_of_days;
                    $absent_days =  $number_of_days - $present_days;
                    $real_present_days  = $present_days - $absent_days;                    
                }
                $lates = 0;
                $undertime = 0;
                $regular_ot  = 0;
                $special_ot = 0;
                $nd_ot  = 0;
                $night_diff = 0;
                $regular_holiday = 0;
                $special_holiday = 0;
                $rd_ot = 0;
                $rd_ot_rh = 0;
                $rd_ot_sh = 0;
                $rd = 0;
                $rd_rh = 0;
                $rd_sh = 0;
                $rh_ot = 0;
                $sh_ot = 0;
            }
            // $absent_days -= $getRD;
            // dd($absent_days);
            $total_absent = 0; 
            $special_leave_deduction = 0;
            $special_leave = $time_keep['special_leave'] ?? 0;
            
            // RP 13 BP SP
            //REGULAR PAYROLL
       
                //BASIC PAY
                if($data->type == "SEMI"){
                    if($emp_rate_type == "MONTHLY"){
                        $basic_pay = $emp_rate / 2;
                        
                        //ABSENT
                        $total_absent = $emp_daily_rate * $absent_days;
                        $special_leave_deduction =  ($special_leave / $daily_divisor) * $emp_daily_rate;
                    }else{
                        $special_leave_deduction =  ($special_leave / $daily_divisor) * $emp_daily_rate;
                        $basic_pay = $emp_hourly_rate * $present_days;
                        $total_absent = $emp_daily_rate * $absent_days;
                    }
                }elseif($data->type=="WEEKLY"){
                    if($emp_rate_type == "MONTHLY"){
                        $basic_pay = $emp_daily_rate * $present_days;
                    }else{
                        $basic_pay = $emp_hourly_rate * $present_days;
                    }
                }
                elseif($data->type == "MONTHLY"){
                    if($emp_rate_type == "MONTHLY"){
                        $basic_pay = $emp_rate;
                        //ABSENT
                        $total_absent = $emp_daily_rate * $absent_days;
                    }else{
                        $basic_pay = $emp_hourly_rate * $present_days;
                    }
                }else{
                    continue;
                }
                array_push($payroll_income, array(
                    "payroll_id" => $request->pay_id,
                    "emp_id" => $emp_info,
                    "type" => "BP",
                    "amount" => $basic_pay,
                    "date_created" => date("Y-m-d"),
                    "user_id" => Auth::user()->id
                ));
                $total_income += $basic_pay;
                //LATES $total_lates = ($emp_hourly_rate)  * ($lates/60);
                $total_lates = ($emp_hourly_rate/60)  * $lates;
                if($total_lates > 0){
                    array_push($payroll_deduction, array(
                        "payroll_id" => $request->pay_id,
                        "emp_id" => $emp_info,
                        "loan_file_id" => NULL,
                        "is_skipped" => NULL,
                        "type" => "LATE",
                        "amount" => $total_lates,
                        "date_created" => date("Y-m-d"),
                        "user_id" => Auth::user()->id
                    )); 
                }
                $total_ut = ($emp_hourly_rate/60)  * ($undertime);
                  
                if($total_ut > 0){
                 // if($emp_info == 20){
                 //     dd($total_ut);
                 // }
                    array_push($payroll_deduction, array(
                        "payroll_id" => $request->pay_id,
                        "emp_id" => $emp_info,
                        "loan_file_id" => NULL,
                        "is_skipped" => NULL,
                        "type" => "UT",
                        "amount" => $total_ut,
                        "date_created" => date("Y-m-d"),
                        "user_id" => Auth::user()->id
                    )); 
                     
                }
                if($total_absent > 0){
                    array_push($payroll_deduction, array(
                        "payroll_id" => $request->pay_id,
                        "emp_id" => $emp_info,
                        "loan_file_id" => NULL,
                        "is_skipped" => NULL,
                        "type" => "ABSENT",
                        "amount" => $total_absent,
                        "date_created" => date("Y-m-d"),
                        "user_id" => Auth::user()->id
                    )); 
                }
                if($special_leave_deduction > 0){
                    array_push($payroll_deduction, array(
                        "payroll_id" => $request->pay_id,
                        "emp_id" => $emp_info,
                        "loan_file_id" => NULL,
                        "is_skipped" => NULL,
                        "type" => "Leave Without Pay",
                        "amount" => $special_leave_deduction,
                        "date_created" => date("Y-m-d"),
                        "user_id" => Auth::user()->id
                    )); 
                }
                if($regular_ot > 0){
                    $ot_rate = $this->search_multi_array($lib_ot_table, "code", "ROT");
                    $ot_amount = ($emp_hourly_rate * $ot_rate["rate"]) * $regular_ot  ;
                        array_push($payroll_income, array(
                            "payroll_id" => $request->pay_id,
                            "emp_id" => $emp_info,
                            "type" => "ROT",
                            "amount" => $ot_amount,
                            "date_created" => date("Y-m-d"),
                            "user_id" => Auth::user()->id
                        ));
                }
                if($special_ot > 0){
                    $ot_rate = $this->search_multi_array($lib_ot_table, "code", "SOT");
                    $ot_amount = ($emp_hourly_rate * $ot_rate["rate"]) * $special_ot ;
                        array_push($payroll_income, array(
                            "payroll_id" => $request->pay_id,
                            "emp_id" => $emp_info,
                            "type" => "SOT",
                            "amount" => $ot_amount,
                            "date_created" => date("Y-m-d"),
                            "user_id" => Auth::user()->id
                        ));
                }
                if($nd_ot > 0){
                    $ot_rate = $this->search_multi_array($lib_ot_table, "code", "NDOT");
                    $ot_amount = ($emp_hourly_rate * $ot_rate["rate"]) * $nd_ot  ;
                        array_push($payroll_income, array(
                            "payroll_id" => $request->pay_id,
                            "emp_id" => $emp_info,
                            "type" => "NDOT",
                            "amount" => $ot_amount,
                            "date_created" => date("Y-m-d"),
                            "user_id" => Auth::user()->id
                        ));
                }
                if($night_diff > 0){
                    $ot_rate = $this->search_multi_array($lib_ot_table, "code", "ND");
                    $ot_amount = ($emp_hourly_rate * $night_diff) * $ot_rate["rate"] ;
                        array_push($payroll_income, array(
                            "payroll_id" => $request->pay_id,
                            "emp_id" => $emp_info,
                            "type" => "ND",
                            "amount" => $ot_amount,
                            "date_created" => date("Y-m-d"),
                            "user_id" => Auth::user()->id
                        ));
                }
                if($rd > 0){
                    $ot_rate = $this->search_multi_array($lib_ot_table, "code", "RD");
                    $rd_rate = $ot_rate["rate"];
                    // if($emp_rate_type == "MONTHLY"){
                    //     $rd_rate += 1;
                    // }
                    $ot_amount = ($emp_hourly_rate * $rd) * $rd_rate ;
                    array_push($payroll_income, array(
                        "payroll_id" => $request->pay_id,
                        "emp_id" => $emp_info,
                        "type" => "RD",
                        "amount" => $ot_amount,
                        "date_created" => date("Y-m-d"),
                        "user_id" => Auth::user()->id
                    ));
                }
                if($rd_ot > 0){
                    $ot_rate = $this->search_multi_array($lib_ot_table, "code", "RDOT");
                    $ot_amount = ($emp_hourly_rate * $rd_ot) * $ot_rate["rate"];
                    array_push($payroll_income, array(
                        "payroll_id" => $request->pay_id,
                        "emp_id" => $emp_info,
                        "type" => "RDOT",
                        "amount" => $ot_amount,
                        "date_created" => date("Y-m-d"),
                        "user_id" => Auth::user()->id
                    ));
                }
                if($rh_ot > 0){
                    $ot_rate = $this->search_multi_array($lib_ot_table, "code", "RH_OT");
                    $ot_amount = ($emp_hourly_rate * $rh_ot) * $ot_rate["rate"];
                    array_push($payroll_income, array(
                        "payroll_id" => $request->pay_id,
                        "emp_id" => $emp_info,
                        "type" => "RH_OT",
                        "amount" => $ot_amount,
                        "date_created" => date("Y-m-d"),
                        "user_id" => Auth::user()->id
                    ));
                }
                if($sh_ot > 0){
                    $ot_rate = $this->search_multi_array($lib_ot_table, "code", "SH_OT");
                    $ot_amount = ($emp_hourly_rate * $sh_ot) * $ot_rate["rate"];
                    array_push($payroll_income, array(
                        "payroll_id" => $request->pay_id,
                        "emp_id" => $emp_info,
                        "type" => "SH_OT",
                        "amount" => $ot_amount,
                        "date_created" => date("Y-m-d"),
                        "user_id" => Auth::user()->id
                    ));
                }
                if($rd_rh > 0){
                    $ot_rate = $this->search_multi_array($lib_ot_table, "code", "RDRH");
                    $rd_rate = $ot_rate["rate"];
                    // if($emp_rate_type == "MONTHLY"){
                    //     $rd_rate += 1;
                    // }
                    $ot_amount = ($emp_hourly_rate * $rd_rh) * $rd_rate ;
                    array_push($payroll_income, array(
                        "payroll_id" => $request->pay_id,
                        "emp_id" => $emp_info,
                        "type" => "RDRH",
                        "amount" => $ot_amount,
                        "date_created" => date("Y-m-d"),
                        "user_id" => Auth::user()->id
                    ));
                }
                if($rd_ot_rh > 0){
                    $ot_rate = $this->search_multi_array($lib_ot_table, "code", "RD_RH_OT");
                    $ot_amount = ($emp_hourly_rate * $rd_ot_rh) * $ot_rate["rate"];
                    array_push($payroll_income, array(
                        "payroll_id" => $request->pay_id,
                        "emp_id" => $emp_info,
                        "type" => "RD_RH_OT",
                        "amount" => $ot_amount,
                        "date_created" => date("Y-m-d"),
                        "user_id" => Auth::user()->id
                    ));
                }
                if($rd_sh > 0){
                    $ot_rate = $this->search_multi_array($lib_ot_table, "code", "RDSH");
                    $rd_rate = $ot_rate["rate"];
                    // if($emp_rate_type == "MONTHLY"){
                    //     $rd_rate += 1;
                    // }
                    $ot_amount = ($emp_hourly_rate * $rd_sh) * $rd_rate ;
                    array_push($payroll_income, array(
                        "payroll_id" => $request->pay_id,
                        "emp_id" => $emp_info,
                        "type" => "RDSH",
                        "amount" => $ot_amount,
                        "date_created" => date("Y-m-d"),
                        "user_id" => Auth::user()->id
                    ));
                }
                if($rd_ot_sh > 0){
                    $ot_rate = $this->search_multi_array($lib_ot_table, "code", "RD_SH_OT");
                    $ot_amount = ($emp_hourly_rate * $rd_ot_sh) * $ot_rate["rate"];
                    array_push($payroll_income, array(
                        "payroll_id" => $request->pay_id,
                        "emp_id" => $emp_info,
                        "type" => "RD_SH_OT",
                        "amount" => $ot_amount,
                        "date_created" => date("Y-m-d"),
                        "user_id" => Auth::user()->id
                    ));
                }
                if($regular_holiday > 0){
                    $ot_rate = $this->search_multi_array($lib_ot_table, "code", "RH");
                    $ot_amount = ($emp_hourly_rate * $ot_rate["rate"]) * $regular_holiday ;
                        array_push($payroll_income, array(
                            "payroll_id" => $request->pay_id,
                            "emp_id" => $emp_info,
                            "type" => "RH",
                            "amount" => $ot_amount,
                            "date_created" => date("Y-m-d"),
                            "user_id" => Auth::user()->id
                        ));
                }
                if($special_holiday > 0){
                    $ot_rate = $this->search_multi_array($lib_ot_table, "code", "SH");
                
                    $ot_amount = ($emp_hourly_rate * $ot_rate["rate"]) * $special_holiday  ;
                        array_push($payroll_income, array(
                            "payroll_id" => $request->pay_id,
                            "emp_id" => $emp_info,
                            "type" => "SH",
                            "amount" => $ot_amount,
                            "date_created" => date("Y-m-d"),
                            "user_id" => Auth::user()->id
                        ));
                }
                //OPERATIONAL ALLOWANCE
                $operational_allowance = DB::connection("intra_payroll")->table("tbl_allowance_request")
                    ->where("emp_id",$emp_info)
                    ->where("status","APPROVED")
                    ->whereBetween("date_filed", [$data->cover_from, $data->cover_to])
                    ->sum("amount");

                if($operational_allowance > 0){
                    array_push($payroll_income, array(
                        "payroll_id" => $request->pay_id,
                        "emp_id" => $emp_info,
                        "type" => "OPERATIONAL ALLOWANCE",
                        "amount" => $operational_allowance,
                        "date_created" => date("Y-m-d"),
                        "user_id" => Auth::user()->id
                    ));
                }
     //OTHER INCOME
                if($data->other_income != "" || $data->other_income != null){
                    $oth_inc_list = explode(";", $data->other_income);
                    
                        foreach($oth_inc_list as $oth_income){
                            $oth_file = DB::connection("intra_payroll")->table("tbl_income_file")
                                ->where("emp_id", $emp_info)
                                ->where("income_id", $oth_income)
                                ->first();
                                if($oth_file != null){
                                    $oth_amount = 0;
                                    if($oth_file->income_type == "DAILY"){
                                        $oth_amount = $oth_file->amount;
                                        $oth_amount = $oth_amount * $real_present_days;
                                    }elseif($oth_file->income_type == "WEEKLY"){
                                        if($data->type_info == 1){
                                            $oth_amount = $oth_file->amount;
                                        }elseif($data->type_info == 2){
                                            $oth_amount = $oth_file->amount_2;
                                        }elseif($data->type_info == 3){
                                            $oth_amount = $oth_file->amount_3;
                                        }elseif($data->type_info == 4){
                                            $oth_amount = $oth_file->amount_4;
                                        }elseif($data->type_info == 5){
                                            $oth_amount = $oth_file->amount_5;
                                        }else{
                                            $oth_amount= 0;
                                        }
                                    }elseif($oth_file->income_type == "SEMI"){
                                        if($data->type_info == 1){
                                            $oth_amount = $oth_file->amount;
                                        }elseif($data->type_info == 2){
                                            $oth_amount = $oth_file->amount_2;
                                        }else{
                                            $oth_amount= 0;
                                        }
                                    }elseif($oth_file->income_type == "MONTHLY"){
                                        $oth_amount = $oth_file->amount;
                                    }
                                        if($oth_amount>0){
                                         
                                            array_push($payroll_income, array(
                                                "payroll_id" => $request->pay_id,
                                                "emp_id" => $emp_info,
                                                "type" => "R_".$oth_file->income_id,
                                                "amount" => $oth_amount,
                                                "date_created" => date("Y-m-d"),
                                                "user_id" => Auth::user()->id
                                            ));
                                        }
                                    
                                }
                        }
                }
    //LOAN
            if($data->lib_loan != "" || $data->lib_loan != null){
                $loan_list = explode(";", $data->lib_loan);
                foreach($loan_list as $loan){
                    $loan_file = DB::connection("intra_payroll")->table("tbl_loan_file")
                        ->where("emp_id", $emp_info)
                        ->where("loan_id", $loan)
                        ->first();
                    $loan_to_paid = 0;
                    if(!empty($loan_file)){
                        if($loan_file->loan_status === 1){
                            if($loan_file->balance > 0){
                                $loan_to_paid = $loan_file->amount_to_pay;
                                $emp_basic_pay = $basic_pay;
                                if ($emp_basic_pay < $loan_to_paid) {
                                    $loan_to_paid = 0;
                                } 
                            }
                             
                        }
                    }
                    if($loan_to_paid>0){
                         $same_payroll_month = DB::connection("intra_payroll")->table("tbl_payroll")
                            ->join("tbl_payroll_deduction", "tbl_payroll.id", "=", "tbl_payroll_deduction.payroll_id")
                            ->where("tbl_payroll_deduction.emp_id", $emp_info)
                            ->where("tbl_payroll_deduction.type", "R_" . $loan)
                            ->whereIn("tbl_payroll.payroll_status", ["PROCESS","COMPUTED","CLOSE","FINALIZE"])
                            ->where("tbl_payroll.id", $data->id)
                            ->first();
                        if(empty($same_payroll_month)){
                            $update_loan_bal = DB::connection("intra_payroll")->table("tbl_loan_file")
                            ->where("loan_id", $loan)
                            ->where("emp_id", $emp_info)
                            ->first();
                            if ($update_loan_bal && $update_loan_bal->balance >= $loan_to_paid) {
                                DB::connection("intra_payroll")->table("tbl_loan_file")
                                    ->where("loan_id", $loan)
                                    ->where("emp_id", $emp_info)
                                    ->update([
                                        "balance" => $update_loan_bal->balance - $loan_to_paid
                                    ]);
                            }
                        }
                       
                        array_push($payroll_deduction, array(
                            "payroll_id" => $request->pay_id,
                            "emp_id" => $emp_info,
                            "loan_file_id" => NULL,
                            "is_skipped" => NULL,
                            "type" => "R_".$loan_file->loan_id,
                            "amount" => $loan_to_paid,
                            "date_created" => date("Y-m-d"),
                            "user_id" => Auth::user()->id
                        ));
                    }
                }
            }
                
            $hdmf_emp_amount=0; 
            $ph_emp_amount=0; 
            $sss_emp_amount=0;
            if($total_income > 0){
                    $sss_emp_amount = 0;
                    if($data->sss == "1"){
                        $sss_emp_amount = 0;
                        $sss_com_amount = 0;
                        
                        if($fix_sss > 0){
                            $sss_com_amount = $fix_sss;
                            $sss_emp_amount = $fix_sss;
                        }
                        // else{
                        //     $total_income_sss = $total_income;
                        //     if($data->type == "SEMI" || $data->type == "WEEKLY"){
                        //         if($data->type_info == "2" || $data->type_info == "4" || $data->type_info == "5"){
                        //             $same_payroll_month = DB::connection("intra_payroll")->table("tbl_payroll")
                        //             ->select(DB::raw("SUM(tbl_payroll_income.amount) as stat_bpay"))
                        //             ->join("tbl_payroll_income", "tbl_payroll.id", "=", "tbl_payroll_income.payroll_id")
                        //             ->where("tbl_payroll.target_month", $data->target_month)
                        //             ->where("tbl_payroll.target_year", $data->target_year)
                        //             ->where("tbl_payroll.type_info", "1")
                        //             ->where("tbl_payroll_income.emp_id", $emp_info)
                        //             ->where("tbl_payroll_income.type", "BP")
                        //             ->where("tbl_payroll.payroll_status", "COMPUTED")
                        //             ->orWhere("tbl_payroll.payroll_status", "FINALIZE")
                        //             ->orWhere("tbl_payroll.payroll_status", "CLOSE")
                        //             ->where("tbl_payroll.id", "!=", $data->id)
                        //             ->first();
                        //             if($same_payroll_month){
                        //                 $total_income_sss = $total_income + $same_payroll_month->stat_bpay;
                        //             }
                        //         }
                                
                        //     }
                            
                            
                        //     $sss_data = DB::connection("intra_payroll")->table("lib_sss")
                        //     ->where("salary_from", "<=", $total_income_sss)
                        //     ->where("salary_to", ">=", $total_income_sss)
                        //     ->first();
                        
                        //     if($sss_data != null){
                        //         $sss_com_amount = $sss_data->regular_er + $sss_data->ec + $sss_data->wisp_er;
                        //         $sss_emp_amount = $sss_data->regular_ee + $sss_data->wisp_ee;
                        //     }
                        //     if($data->type == "SEMI" || $data->type == "WEEKLY"){
                        //         if($data->type_info == "2" || $data->type_info == "4" || $data->type_info == "5"){
                        //             $sss_com_amount = $sss_com_amount;
                        //             $sss_emp_amount = $sss_emp_amount;
                        //         }else{
                        //            if($is_active == 0){
                        //                 $sss_com_amount = $sss_com_amount;
                        //                 $sss_emp_amount = $sss_emp_amount;
                        //             }else{
                        //                 $sss_emp_amount = 0;
                        //                 $sss_com_amount = 0;
                        //             }
                        //         }
                        //     }
                        // }
                        if($sss_emp_amount > 0){
                            if($emp_sss != "" || $emp_sss != null){
                                array_push($payroll_deduction, array(
                                    "payroll_id" => $request->pay_id,
                                    "emp_id" => $emp_info,
                                    "loan_file_id" => NULL,
                                    "is_skipped" => NULL,
                                    "type" => "SSS",
                                    "amount" => $sss_emp_amount,
                                    "date_created" => date("Y-m-d"),
                                    "user_id" => Auth::user()->id
                                ));
                                array_push($payroll_statutory, array(
                                    "payroll_id" => $request->pay_id,
                                    "emp_id" => $emp_info,
                                    "type" => "SSS",
                                    "amount" => $sss_com_amount,
                                    "date_created" => date("Y-m-d"),
                                    "user_id" => Auth::user()->id
                                ));
                            }
                           
                        }
                    }
                    $ph_emp_amount = 0;
                    if($data->ph == "1"){
                        $ph_emp_amount = 0;
                        $ph_com_amount = 0;
                  
                        if($fix_philhealth > 0){
                            $ph_com_amount = $fix_philhealth;
                            $ph_emp_amount = $fix_philhealth;
                                
                        }
                        // else{
                        //     $ph_data = DB::connection("intra_payroll")->table("lib_philhealth")
                        //     ->where("salary_from", "<=", $total_income)
                        //     ->where("salary_to", ">=", $total_income)
                        //     ->first();
                        //     if($ph_data != null){
                        //         $ph_com_amount = $total_income * $ph_data->rate_employer;
                        //         $ph_emp_amount = $total_income * $ph_data->rate_employee;
                        //     }
                        // }
                        if($ph_emp_amount > 0){
                            if($emp_ph != "" || $emp_ph != null){
                                array_push($payroll_deduction, array(
                                    "payroll_id" => $request->pay_id,
                                    "emp_id" => $emp_info,
                                    "loan_file_id" => NULL,
                                    "is_skipped" => NULL,
                                    "type" => "PH",
                                    "amount" => $ph_emp_amount,
                                    "date_created" => date("Y-m-d"),
                                    "user_id" => Auth::user()->id
                                ));
                                array_push($payroll_statutory, array(
                                    "payroll_id" => $request->pay_id,
                                    "emp_id" => $emp_info,
                                    "type" => "PH",
                                    "amount" => $ph_com_amount,
                                    "date_created" => date("Y-m-d"),
                                    "user_id" => Auth::user()->id
                                ));
                            }
                           
                        }
                    }
                    $hdmf_emp_amount = 0;
                    if($data->hdmf == "1"){
                        $hdmf_emp_amount = 0;
                        $hdmf_com_amount = 0;
                        if($fix_hdmf > 0){
                            $hdmf_com_amount = $fix_hdmf ;
                            $hdmf_emp_amount = $fix_hdmf ;
                        }
                        // else{
                        //     $hdmf_data = DB::connection("intra_payroll")->table("lib_hdmf")
                        //     ->where("salary_from", "<=", $total_income)
                        //     ->where("salary_to", ">=", $total_income)
                        //     ->first();
                        //     if($hdmf_data != null){
                        //         $hdmf_com_amount = $total_income * $hdmf_data->rate_employer;
                        //         $hdmf_emp_amount = $total_income * $hdmf_data->rate_employee;
                        //     }
                        // }
                        if($hdmf_emp_amount > 0){
                            if($emp_hdmf != "" || $emp_hdmf != null){
                                array_push($payroll_deduction, array(
                                    "payroll_id" => $request->pay_id,
                                    "emp_id" => $emp_info,
                                    "loan_file_id" => NULL,
                                    "is_skipped" => NULL,
                                    "type" => "HDMF",
                                    "amount" => $hdmf_emp_amount,
                                    "date_created" => date("Y-m-d"),
                                    "user_id" => Auth::user()->id
                                ));
                                array_push($payroll_statutory, array(
                                    "payroll_id" => $request->pay_id,
                                    "emp_id" => $emp_info,
                                    "type" => "HDMF",
                                    "amount" => $hdmf_com_amount,
                                    "date_created" => date("Y-m-d"),
                                    "user_id" => Auth::user()->id
                                ));
                            }
                           
                        }
                    }
            }
            //TAX
            if($emp_mwe != "1"){
                $tax_amount = 0;
                $same_payroll_month = DB::connection("intra_payroll")->table("tbl_payroll")
                    ->select(DB::raw("SUM(tbl_payroll_deduction.amount) as paid_amount"))
                    ->join("tbl_payroll_deduction", "tbl_payroll.id", "=", "tbl_payroll_deduction.payroll_id")
                    ->where("tbl_payroll.target_month", $data->target_month)
                    ->where("tbl_payroll.target_year", $data->target_year)
                    ->where("tbl_payroll_deduction.emp_id", $emp_info)
                    ->where("tbl_payroll_deduction.type", "TAX")
                    ->where("tbl_payroll.payroll_status", "CLOSE")
                    ->where("tbl_payroll.id", "!=", $data->id)
                    ->first();
                if($same_payroll_month != null){
                    $paid_amount = $same_payroll_month->paid_amount;
                }else{
                    $paid_amount = 0;
                }
                if($fix_tax_rate > 0){
                    $tax_info = DB::connection("intra_payroll")->table("lib_tax_table")
                    ->where("salary_from", "<=", $fix_tax_rate)
                    ->where("salary_to", ">=", $fix_tax_rate)
                    ->where("type", $data->type)
                    ->first();
                if($tax_info != null){
                    $tax_amount += $tax_info->fix_amount;
                    $additional_tax =  ($fix_tax_rate - $tax_info->rate_over) * $tax_info->rate;
                        if($additional_tax > 0){
                            $tax_amount += $additional_tax;
                        }
                }
                $tax_amount -= $paid_amount;
                }else{
                   $tax_bpay = $total_income - $hdmf_emp_amount - $ph_emp_amount - $sss_emp_amount;
                    $tax_info = DB::connection("intra_payroll")->table("lib_tax_table")
                    ->where("salary_from", "<=", $tax_bpay)
                    ->where("salary_to", ">=", $tax_bpay)
                    ->where("type", $data->type)
                    ->first();
                if($tax_info != null){
                    $tax_amount += $tax_info->fix_amount;
                    $additional_tax =  ($tax_bpay - $tax_info->rate_over) * $tax_info->rate;
                        if($additional_tax > 0){
                            $tax_amount += $additional_tax;
                        }
                }
                $tax_amount -= $paid_amount;
                }
                
                if($tax_amount > 0){
                    array_push($payroll_deduction, array(
                        "payroll_id" => $request->pay_id,
                        "emp_id" => $emp_info,
                        "loan_file_id" => NULL,
                        "is_skipped" => NULL,
                        "type" => "TAX",
                        "amount" => $tax_amount,
                        "date_created" => date("Y-m-d"),
                        "user_id" => Auth::user()->id
                    ));
                }
            }
        } //EMPLOYEE
        DB::beginTransaction();
        try {
            DB::connection("intra_payroll")->table("tbl_payroll")
                ->where("id", $request->pay_id)
                ->update([
                    "payroll_status" => "COMPUTED"
                ]);
                DB::connection("intra_payroll")->table("tbl_payroll_income")
                    ->where("type", "BP")
                    ->where("payroll_id", $request->pay_id)
                    ->orWhere("type", "ROT")
                    ->where("payroll_id", $request->pay_id)
                    ->orWhere("type", "SOT")
                    ->where("payroll_id", $request->pay_id)
                    ->orWhere("type", "ND")
                    ->where("payroll_id", $request->pay_id)
                    ->orWhere("type", "NDOT")
                    ->where("payroll_id", $request->pay_id)
                    ->orWhere("type", "RH")
                    ->where("payroll_id", $request->pay_id)
                    ->orWhere("type", "SH")
                    ->where("payroll_id", $request->pay_id)
                    ->orWhere("type", "LIKE","R_%")
                    ->where("payroll_id", $request->pay_id)
                    ->orWhere("type", "SH_OT")
                    ->where("payroll_id", $request->pay_id)
                    ->orWhere("type", "RH_OT")
                    ->where("payroll_id", $request->pay_id)
                     ->orWhere("type", "RDOT")
                    ->where("payroll_id", $request->pay_id)
                     ->orWhere("type", "RD")
                    ->where("payroll_id", $request->pay_id)
                    ->orWhere("type", "RDRH")
                    ->where("payroll_id", $request->pay_id)
                    ->orWhere("type", "RDSH")
                    ->where("payroll_id", $request->pay_id)
                    ->orWhere("type", "RD_RH_OT")
                    ->where("payroll_id", $request->pay_id)
                    ->orWhere("type", "RD_SH_OT")
                    ->where("payroll_id", $request->pay_id)
                    ->orWhere("type", "OPERATIONAL ALLOWANCE")
                    ->where("payroll_id", $request->pay_id)
                    ->delete();
                DB::connection("intra_payroll")->table("tbl_payroll_deduction")
                    ->where("type", "LATE")
                    ->where("payroll_id", $request->pay_id)
                    ->orWhere("type", "ABSENT")
                    ->where("payroll_id", $request->pay_id)
                    ->orWhere("type", "SSS")
                    ->where("payroll_id", $request->pay_id)
                    ->orWhere("type", "PH")
                    ->where("payroll_id", $request->pay_id)
                    ->orWhere("type", "TAX")
                    ->where("payroll_id", $request->pay_id)
                    ->orWhere("type", "LIKE","R_%")
                    ->where("payroll_id", $request->pay_id)
                    ->orWhere("type", "HDMF")
                    ->where("payroll_id", $request->pay_id)
                    ->orWhere("type", "UT")
                    ->where("payroll_id", $request->pay_id)
                    ->delete();
                DB::connection("intra_payroll")->table("tbl_statutory_company")
                ->where("payroll_id", $request->pay_id)
                ->delete();
                    
                    DB::connection("intra_payroll")->table("tbl_payroll_income")
                        ->insert($payroll_income);
                    DB::connection("intra_payroll")->table("tbl_payroll_deduction")
                        ->insert($payroll_deduction);
                    DB::connection("intra_payroll")->table("tbl_statutory_company")
                        ->insert($payroll_statutory);
                        
            DB::commit();
            return "Success";
        } catch (\Throwable $th) {
            DB::rollback();
            return $th->getMessage();
        }
    }
    //get week count
    private function get_week_count($date)
    {
       
        $monthMap = [
            'JAN' => 1, 'FEB' => 2, 'MAR' => 3,
            'APR' => 4, 'MAY' => 5, 'JUN' => 6,
            'JUL' => 7, 'AUG' => 8, 'SEP' => 9,
            'OCT' => 10, 'NOV' => 11, 'DEC' => 12
        ];
        [$monthAbbr, $year] = explode(' ', strtoupper($date));
        $month = $monthMap[$monthAbbr] ?? null;
        if (!$month || !is_numeric($year)) {
            return 0;
        }
        $startDate = Carbon::createFromDate($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();
        $weekStart = $startDate->copy()->startOfWeek(Carbon::SUNDAY);
        $weekEnd = $endDate->copy()->endOfWeek(Carbon::SATURDAY);
        $totalDays = $weekStart->diffInDays($weekEnd) + 1;
        $weekCount = ceil($totalDays / 7);
        return $weekCount;
    }
    private function payroll_process_13thmonth($employee_list,$request,$data,$tbl_employee){
        $payroll_income = array();
        foreach($employee_list as $emp){
            $emp_info = str_replace("|", "",$emp);
            $emp_check = $this->search_multi_array($tbl_employee, "id", $emp_info);
            $emp_rate = $emp_check["salary_rate"];
            $emp_rate_type = $emp_check["salary_type"];
            $emp_mwe = $emp_check["is_mwe"];
            $emp_sss = $emp_check["sss_number"];
            $emp_ph = $emp_check["philhealth_number"];
            $emp_hdmf = $emp_check["hdmf_number"];
            $emp_tin = $emp_check["tin_number"];
            $fix_divisor = $emp_check["fix_divisor"];
            $fix_sss = $emp_check["fix_sss"];
            $fix_philhealth = $emp_check["fix_philhealth"];
            $fix_hdmf = $emp_check["fix_hdmf"];
            $fix_tax_rate = $emp_check["fix_tax_rate"];
            
            //GET ALL POSSIBLE BP on target range
            $startDate = new DateTime($data->cover_from);
            $endDate = new DateTime($data->cover_to);
          
            
            $payroll_id = DB::connection("intra_payroll")->table("tbl_payroll")
                ->select("id")
                ->where("process_type", "RP")
                ->whereRaw("STR_TO_DATE('APR-01-2023', '%b-%d-%Y') between '".$data->cover_from."' and '".$data->cover_to."'")
                ->where("payroll_status", "CLOSE")
                ->get();
            
            $payroll_ids = json_decode(json_encode($payroll_id), true);
            $basic_pays =  DB::connection("intra_payroll")->table("tbl_payroll_income")
                ->whereIn("payroll_id", $payroll_ids)
                ->where("emp_id",$emp_info)
                ->where("type", "BP")
                ->sum("amount");
            
            $tardi =  DB::connection("intra_payroll")->table("tbl_payroll_deduction")
            ->whereIn("payroll_id", $payroll_ids)
            ->where("emp_id",$emp_info)
            ->where("type", "LATE")
            ->orwhere("emp_id", $emp_info)
            ->where("type", "ABSENT")
            ->whereIn("payroll_id", $payroll_ids)
            ->sum("amount");
            $over_all_basic = $basic_pays - $tardi;
            $endpay = $over_all_basic / 12;
            array_push($payroll_income, array(
                "payroll_id" => $request->pay_id,
                "emp_id" => $emp_info,
                "type" => "13TH",
                "amount" => $endpay,
                "date_created" => date("Y-m-d"),
                "user_id" => Auth::user()->id
            ));
         
        }
        DB::beginTransaction();
        try {
            DB::connection("intra_payroll")->table("tbl_payroll")
                ->where("id", $request->pay_id)
                ->update([
                    "payroll_status" => "COMPUTED"
                ]);
                DB::connection("intra_payroll")->table("tbl_payroll_income")
                    ->orWhere("type", "LIKE","13TH")
                    ->where("payroll_id", $request->pay_id)
                    ->delete();
                    DB::connection("intra_payroll")->table("tbl_payroll_income")
                        ->insert($payroll_income);
                        
            DB::commit();
            return "Success";
        } catch (\Throwable $th) {
            DB::rollback();
            return $th->getMessage();
        }
    }
    private function payroll_process_bonus($employee_list,$request,$data,$tbl_employee){
        return "Special Process Depends on company";
    }
    private function payroll_process_special($employee_list,$request,$data,$tbl_employee){
        return "Special Process Depends on company";
    }
    public function check_payroll_data(Request $request){
        $data = DB::connection("intra_payroll")->table("tbl_payroll")
            ->where("id", $request->pay_id)
            ->first();
            if($data != null){
                if($data->employee == "" || $data->employee == null){
                    return json_encode("Unable to Process <br> No Employee to Process");
                }else{
                    if($data->process_type == "13")
                    {
                        return json_encode("success");
                    }
                    else{
                        $date_from = $data->cover_from;
                        $date_to = $data->cover_to;
    
                        $check_timekeeping = DB::connection("intra_payroll")->table("tbl_timekeeping")
                            ->whereBetween("date_target", [$date_from, $date_to])
                            ->first();
    
                            if($check_timekeeping == null){
                                return json_encode("timekeeping_404");
                            }else{
                                return json_encode("success");
                            }
                    }
                   
                }
            }else{
                return json_encode("Unable to Process <br> Payroll Info Unreachable");
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
    
    public function payroll_process_timecard(Request $request){
        DB::beginTransaction();
        $payroll_data = DB::connection("intra_payroll")->table("tbl_payroll")
            ->where("id", $request->pay_id)
            ->first();
        
            if($payroll_data != null){
                //PAYROLL INFO
                $payroll_branch_id = $payroll_data->branch_id;
                $date_from = $payroll_data->cover_from;
                $date_to = $payroll_data->cover_to;
                $employee_list = $payroll_data->employee;
                $tbl_employee = DB::connection("intra_payroll")->table("tbl_employee")->where("is_active", 1)->get();
                $tbl_employee = json_decode(json_encode($tbl_employee), true);
                $tbl_leave_credits = json_decode(json_encode(DB::connection("intra_payroll")->table("tbl_leave_credits")->get()), true);
                $tbl_leave_types = json_decode(json_encode(DB::connection("intra_payroll")->table("tbl_leave_types")->get()), true);
                
                $lib_schedule = DB::connection("intra_payroll")->table("lib_schedule")->where('is_active', 1)->get();
                $lib_schedule = json_decode(json_encode($lib_schedule),true);
               
                $lib_position = json_decode(json_encode(DB::connection("intra_payroll")->table("lib_position")->get()),true);
                $lib_designation = json_decode(json_encode(DB::connection("intra_payroll")->table("lib_designation")->get()),true);
                $tbl_department = json_decode(json_encode(DB::connection("intra_payroll")->table("tbl_department")->get()),true);
                $tbl_branch = json_decode(json_encode(DB::connection("intra_payroll")->table("tbl_branch")->get()),true);
                
                
                $employee_list = explode(";",$employee_list);
                
                foreach($employee_list as $emp){
                
                    $emp_id = str_replace("|","",$emp);
                    
                    $timekeeping_data = array();
                    $timekeeping_data["payroll_id"] = $request->pay_id;
                    $emp_data = $this->search_multi_array($tbl_employee, "id", $emp_id);
                        if(isset($emp_data["id"])){
                            $timekeeping_data["emp_id"] = $emp_data["id"];
                            $timekeeping_data["emp_code"] = $emp_data["emp_code"];
                         
                            
                        }else{
                            continue;
                        } 
                        $applied_ot = DB::connection("intra_payroll")->table("tbl_ot_applied")->where("status", "APPROVED")->where("emp_id", $emp_data["id"])->whereBetween("date_target", [$date_from, $date_to])->get();
                        $applied_ot = json_decode(json_encode($applied_ot), true);
                        $daily_sched = DB::connection("intra_payroll")->table("tbl_daily_schedule")->where("emp_id", $emp_data["id"])->whereBetween("schedule_date", [$date_from, $date_to])->get();
                        $daily_sched = json_decode(json_encode($daily_sched), true);
                    $begin = new DateTime($date_from);
                
                    $cover_to = date("Y-m-d", strtotime($date_to .' +1 day'));
                    $end = new DateTime($cover_to);
                    
                    $interval = new DateInterval('P1D'); // 1 day interval
                    $daterange = new DatePeriod($begin, $interval ,$end);
                        // dd($daterange);
                    //OT ALL
                    $last_day_of_month = date("Y-m-t", strtotime($date_to));
                    foreach($daterange as $date_cover){
                        $timekeeping_data["regular_work"] = 0;
                        $timekeeping_data["absent"] = 0;
                        $timekeeping_data["lates"] = 0;
                        $timekeeping_data["undertime"] = 0;
                        
                        $timekeeping_data["regular_ot"] = 0;
                        $timekeeping_data["special_ot"] = 0;
                        $timekeeping_data["night_diff"] = 0;
                        $timekeeping_data["nd_ot"] = 0;
                        $timekeeping_data["regular_leave"] = 0;
                        $timekeeping_data["sick_leave"] = 0;
                        $timekeeping_data["special_leave"] = 0;
                        $timekeeping_data["regular_holiday"] = 0;
                        $timekeeping_data["special_holiday"] = 0;
                        $timekeeping_data["rd"] = 0;
                        $timekeeping_data["rd_ot"] = 0;
                        $timekeeping_data["rh_ot"] = 0;
                        $timekeeping_data["sh_ot"] = 0;
                        $timekeeping_data["rd_rh"] = 0;
                        $timekeeping_data["rd_sh"] = 0;
                        $timekeeping_data["rd_ot_rh"] = 0;
                        $timekeeping_data["rd_ot_sh"] = 0;
                        $late_lunch = 0;
                        $late = 0;
                        
                        $cur_date = $date_cover->format("Y-m-d");
                        $timekeeping_data["date_target"] = $cur_date;
                        //CHECK IF ON LEAVE
                        $query_raw_leave = "'".$cur_date."' BETWEEN leave_date_from and leave_date_to";
                        $leave_day = DB::connection("intra_payroll")->table("tbl_leave_used")
                            ->where("emp_id", $emp_id)
                            ->where("leave_status", "APPROVED")
                            ->whereRaw($query_raw_leave)
                            ->first();
                        if($leave_day != null){
                            $credit_type = $this->search_multi_array($tbl_leave_credits, "leave_id", $leave_day->leave_source_id);
                                if(isset($credit_type["leave_id"])){
                                    $leave_type = $this->search_multi_array($tbl_leave_types, "id", $credit_type["leave_id"]);
                                    if(isset($leave_type["leave_type"])){
                                        if($leave_type["is_with_credits"] == 1){
                                            if($leave_type["leave_type"] == "SL"){
                                                $leave_type_tc ="sick_leave";
                                            }else{
                                                $leave_type_tc ="regular_leave";
                                            }
                                        }else{
                                            $leave_type_tc ="special_leave";
                                        }                                        
                                    }else{
                                        $leave_type_tc = "special_leave";
                                    }
                                }else{
                                    $leave_type = $this->search_multi_array($tbl_leave_types, "id", $leave_day->leave_source_id);
                                    if(isset($leave_type["leave_type"])){
                                        if($leave_type["id"] == 16){  // Approved Official Business with pay
                                            $leave_type_tc ="regular_leave";
                                        }else{
                                            $leave_type_tc = "special_leave";
                                        }
                                    }else{
                                        $leave_type_tc = "special_leave";
                                    }
                                }
                            $timekeeping_data[$leave_type_tc] =Auth::user()->company["daily_divisor"];
                        }else{
                            //CHECK TIMECARD
                            //GET SCHEDULE
                            // default by daily_sched , employee, position, designation, department, branch, company
                            $sched_default = 0;
                            //DAILY SCHED
                            $day_sched = $this->search_multi_array($daily_sched, "schedule_date", $cur_date);
                            $worked_other_branch = false;

                            if (isset($day_sched["branch_id"])) {
                                if ($day_sched["branch_id"] != $payroll_branch_id) {
                                    $worked_other_branch = true;
                                }
                            }
                            if(isset($day_sched["schedule_id"])){
                                //update here Sept 12 2025
                                if($day_sched["schedule_id"] === 0){
                                    $req_am_in = "RD";
                                    $req_am_out = "";
                                    $req_pm_in = "";
                                    $req_pm_out = "";
                                    $req_ot_in = "";
                                    $req_ot_out = "";
                                    $grace_period = 0;
                                    $is_flexi = "0";
                                    $required_hours = "0";
                                }else{
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
                                $absent = false;
                                $timecard_data = DB::connection("intra_payroll")->table("tbl_timecard")
                                ->where("emp_id", $emp_id)
                                ->where("target_date", $cur_date)
                                ->first();
                                if($is_flexi == "0"){
                                   if($timecard_data != null){
                                        $nxt_day = date("Y-m-d", strtotime($cur_date." +1 day"));
                                        $nd_start = date("Y-m-d H:i:s", strtotime($cur_date." 22:00:00"));
                                        $nd_end = date("Y-m-d H:i:s", strtotime($nxt_day." 06:00:00"));
                                        
                                        $nd_start = strtotime($nd_start);
                                        $nd_end = strtotime($nd_end);
                                        
    
                                        if($timecard_data->AM_IN == null || $timecard_data->PM_OUT == "" || $timecard_data->PM_OUT == null || $timecard_data->AM_IN == "" ){
                                            
                                           $absent = true;
                                            // $timekeeping_data["regular_work"] = "0";
                                            // dd("aa");
                                        }else{
                                            $night_diff = 0;
                                            $actual_am_in = strtotime($timecard_data->AM_IN);
                                            $am_in = strtotime("-".$grace_period." minutes", strtotime($timecard_data->AM_IN));
                                            
                                            $actual_am_in = $am_in;
                                            $pm_out = strtotime($timecard_data->PM_OUT);
    
                                       
    
                                            $am_in_req = strtotime($cur_date." ".$req_am_in);
                                            $am_out_req = strtotime($cur_date." ".$req_am_out);
                                            $pm_in_req = strtotime($cur_date." ".$req_pm_in);
    
                                            if(strtotime($req_pm_out) < strtotime($req_am_in)){
                                                $pm_out_req = strtotime($nxt_day." ".$req_pm_out);
                                            }else{
                                                $pm_out_req = strtotime($cur_date." ".$req_pm_out);
                                            }
    
                                            $late_lunch = 0;
    
                                            if($actual_am_in >= $nd_start && $actual_am_in <= $nd_end){
                                                $nd_com_start = $actual_am_in;
                                                    if($pm_out < $nd_end){
                                                        $nd_com_end = $pm_out;
                                                    }else{
                                                        $nd_com_end = $nd_end;
                                                    }
    
                                                    $night_diff += abs($nd_com_end - $nd_com_start)/(60*60);
                                            }
    
    
                                            if($pm_out >= $nd_start && $pm_out <= $nd_end){
                                                $nd_com_start = $pm_out;
                                                if($pm_out < $nd_end){
                                                    $nd_com_end = $pm_out;
                                                }else{
                                                    $nd_com_end = $nd_end;
                                                }
                                                    $night_diff += abs($nd_com_end - $nd_com_start)/(60*60);
                                            }
    
    
    
                                            if(Auth::user()->company["required_lunch_in_out"] == 1){
                                            
                                                $am_out = strtotime($timecard_data->AM_OUT);
                                                $pm_in = strtotime($timecard_data->PM_IN);
    
                                                if($am_out > $am_out_req ){
                                                    $am_out = $am_out_req;
                                                }
                                                $break_hours = abs($pm_in - $am_out)/(60*60);
    
    
                                                if($pm_in > $pm_in_req){
                                                    $late_lunch =  (abs($pm_in - $pm_in_req)/(60*60)) * 60;
                                                }
    
    
                                            }else{
                                                // $break_in_req = strtotime($cur_date." 12:00:00");
                                                // if($break_in_req > $actual_am_in){
                                                    $req_am_out = strtotime($lib_sched["am_out"]);
                                                    $req_pm_in =  strtotime($lib_sched["pm_in"]);
                                                    $break_hours = abs($req_pm_in - $req_am_out)/(60*60);
                                                // }else{
                                                //     $break_hours = 0;
                                                // }
    
                                               
                                            }
    
                                            if($am_in_req > $am_in){
                                                $am_in = $am_in_req;
                                            }
                                            
                                            if($pm_out > $pm_out_req){
                                                $pm_out = $pm_out_req;
                                            }
    
    
    
                                            // $work_hours = (abs($pm_out - $am_in)/(60*60)) - $break_hours ;
                                            // get all work hours
                                            $work_hours = (abs($pm_out_req - $am_in_req)/(60*60)) - $break_hours ;
                                            // if($emp_id == 1){
                                            //     if($cur_date == "2025-03-05"){
                                            //         dd(Auth::user()->company["required_lunch_in_out"] );
                                            //     }
                                            // }
                                            //8am - 8pm
                                            if($req_am_in === '08:00:00' && $req_pm_out === '20:00:00'){
                                                $work_hours = 8;
                                                $night_diff = 0;
                                                $timekeeping_data["regular_ot"] = 3;
                                            }
                                            //8pm - 8am
                                            if($req_am_in === '20:00:00' && $req_pm_out === '08:00:00'){
                                                $work_hours = 8;
                                                $night_diff = 6;
                                                $timekeeping_data["regular_ot"] = 2;
		                                        $timekeeping_data["nd_ot"] = 1;
                                            }
                                            //5am - 2am
                                            if($req_am_in === '05:00:00' && $req_pm_out === '02:00:00'){
                                                $work_hours = 8;
                                                $night_diff = 1;
                                                $timekeeping_data["regular_ot"] = 8;
		                                        $timekeeping_data["nd_ot"] = 4;
                                            }
                                            // $timekeeping_data["regular_work"] = $work_hours;
                                            if ($worked_other_branch) {
                                                $timekeeping_data["regular_work"] = 0;
                                            } else {
                                                $timekeeping_data["regular_work"] = $work_hours;
                                            }
                                            $timekeeping_data["night_diff"] = $night_diff;
                                            
                                            
    
    
                                            //COMPUTE LATE 
                                            
                                                if($am_in > $am_in_req){
                                                    $late = (abs($am_in - $am_in_req)/(60*60)) * 60;
                                                }else{
                                                    $late = 0;
                                                }
    
                                                $late += $late_lunch;
                                            
                                                
    
                                            //COMPUTE UNDERTIME 
                                                // if($pm_out < $pm_in_req){
                                                //     $undertime = (abs($pm_out_req - $pm_out)/(60*60)) * 60;
                                                // }else{
                                                //     $undertime = 0;
                                                // }
                                                if($pm_out < $pm_out_req){
                                                    //$test = 0;
                                                    if($pm_out <= $pm_in_req){
                                                        $undertime = (abs($pm_out_req - $pm_in_req)/(60*60)) * 60;
                                                        
                                                        // Sept 05, Bug
                                                        // if mas maaga sya nag out compute din natin yung
                                                        if ($pm_out < $am_out_req) {
                                                            $undertime_morning = (abs($am_out_req - $pm_out)/(60*60)) * 60;
                                                            //dd($undertime_morning, $am_out_req, $pm_out);
                                                            $undertime += $undertime_morning;
                                                        }
                                                    }else{
                                                        $undertime = (abs($pm_out_req - $pm_out)/(60*60)) * 60;
                                                        //$test = 2;
                                                    }
                                                    //dd($undertime);
                                                }else{
                                                    $undertime = 0;
                                                }
                                                //dd($undertime);
                                                
                                                if($late > 0){
                                                    if($req_am_in == '08:00:00'){
                                                        $actual_time_in = date("H:i:s", strtotime($timecard_data->AM_IN));
                                                        if($actual_time_in >= '08:16:00' && $actual_time_in <= '08:59:00'){
                                                            $late = 60.00; //1 hr late
                                                        }elseif($actual_time_in >= '09:00:00' && $actual_time_in <= '09:59:00'){
                                                            $late = 120.00; //2 hr late
                                                        }elseif($actual_time_in >= '10:00:00' && $actual_time_in <= '10:59:00'){
                                                            $late = 180.00; //3 hr late
                                                        }
                                                    }
                                                }

                                                if($timekeeping_data["regular_work"] == 0){
                                                    $late = 0;
                                                    $undertime = 0;
                                                }
                                            
                                                if($absent == false){
                                                    $timekeeping_data["lates"] = $late;
                                                    $timekeeping_data["undertime"] = $undertime;
                                                    $timekeeping_data["absent"] = 0;
                                                }else{
                                                    $timekeeping_data['absent'] = $work_hours;
                                                    $timekeeping_data["lates"] = 0;
                                                    $timekeeping_data["undertime"] = 0;
                                                }
    
                                            
                                            // $check_applied_ot = $this->search_multi_array($applied_ot, "date_target", $cur_date);
                                            // if(isset($check_applied_ot["id"])){
                                            //     // dd("aaa");
                                            //     if($timecard_data->AM_IN != null || $timecard_data->PM_OUT != null || $timecard_data->AM_IN != "" || $timecard_data->OT_OUT != "" ){
                                            //         //PAYROLL INFO
                                            //         $ot_from = strtotime($check_applied_ot["time_from"]);
                                            //         $ot_to = strtotime($check_applied_ot["time_to"]);
                                                    
                                            //         $ot_in = strtotime($timecard_data->AM_IN);
                                            //         $ot_out = strtotime($timecard_data->PM_OUT);
                                                    
                                            //         if($ot_in < $ot_from){
                                            //             $ot_in = $ot_from;
                                            //         }
    
                                            //         if($ot_out > $ot_to){
                                            //             $ot_out = $ot_to;
                                            //         }
                                                    
                                            //         $ot_hours = abs($ot_to - $ot_from)/(60*60);
    
                                            //         if($check_applied_ot["ot_type"] == "ROT"){
                                            //             $timekeeping_data["regular_ot"] = $ot_hours;
                                            //         }elseif($check_applied_ot["ot_type"] == "SOT"){
                                            //             $timekeeping_data["special_ot"] = $ot_hours;
                                            //         }
                                                    
        
                                            //     }
    
                                            // }
                                            $daily_applied_ot = array_filter($applied_ot, function($ot) use ($cur_date) {
                                                return $ot["date_target"] == $cur_date;
                                            });
                                            foreach ($daily_applied_ot as $applied) {
                                                if($timecard_data->AM_IN != null || $timecard_data->PM_OUT != null || $timecard_data->AM_IN != "" || $timecard_data->OT_OUT != "" ){
                                                    $ot_from = strtotime($applied["time_from"]);
                                                    $ot_to = strtotime($applied["time_to"]);
                                                    
                                                    $act_in = strtotime($timecard_data->AM_IN);
                                                    $act_out = strtotime($timecard_data->PM_OUT);
                                                    $ot_hours = abs($ot_to - $ot_from)/(60*60);
                                                    if ($applied["ot_type"] == "ROT") {
                                                        // Actual OT starts after scheduled 5pm
                                                        $actual_ot_start = $pm_out_req;        // 17:00:00
                                                        $actual_ot_end   = $act_out;           // actual PM_OUT (ex: 19:00:00)
                                                        // Find overlap between requested OT and actual OT
                                                        $start = max($ot_from, $actual_ot_start);
                                                        $end   = min($ot_to, $actual_ot_end);
                                                        if ($end > $start) {
                                                            $rot_hours = ($end - $start) / 3600;
                                                        } else {
                                                            $rot_hours = 0;
                                                        }
                                                        $timekeeping_data["regular_ot"] =
                                                            ($timekeeping_data["regular_ot"] ?? 0) + $rot_hours;
                                                        continue;
                                                    }
                                                    // if ($applied["ot_type"] == "ROT") {
                                                    //     $timekeeping_data["regular_ot"] = ($timekeeping_data["regular_ot"] ?? 0) + $ot_hours;
                                                    // } else
                                                    if ($applied["ot_type"] == "SOT") {
                                                        $timekeeping_data["special_ot"] = ($timekeeping_data["special_ot"] ?? 0) + $ot_hours;
                                                    } elseif ($applied["ot_type"] == "NDOT") {
                                                        $timekeeping_data["nd_ot"] = ($timekeeping_data["nd_ot"] ?? 0) + $ot_hours;
                                                    }elseif ($applied["ot_type"] == "RD") {
                                                        if ($ot_hours >= 5) {
                                                            $ot_hours -= 1;
                                                        }
                                                        $ot_hours = min($ot_hours, 8); // cap at 8 hrs
                                                        $timekeeping_data["rd"] = ($timekeeping_data["rd"] ?? 0) + $ot_hours;
                                                    }elseif ($applied["ot_type"] == "RDOT") {
                                                        $timekeeping_data["rd_ot"] = ($timekeeping_data["rd_ot"] ?? 0) + $ot_hours;
                                                    }elseif ($applied["ot_type"] == "SH_OT") {
                                                        $timekeeping_data["sh_ot"] = ($timekeeping_data["sh_ot"] ?? 0) + $ot_hours;
                                                    }elseif ($applied["ot_type"] == "RH_OT") {
                                                        $timekeeping_data["rh_ot"] = ($timekeeping_data["rh_ot"] ?? 0) + $ot_hours;
                                                    }elseif ($applied["ot_type"] == "RD_RH_OT") {
                                                        $timekeeping_data["rd_ot_rh"] = ($timekeeping_data["rd_ot_rh"] ?? 0) + $ot_hours;
                                                    }elseif ($applied["ot_type"] == "RD_SH_OT") {
                                                        $timekeeping_data["rd_ot_sh"] = ($timekeeping_data["rd_ot_sh"] ?? 0) + $ot_hours;
                                                    }
                                                }
                                            }
    
    
    
                                            
    
    
    
                                        } //WITH IN AND OUT
    
                                        
    
    
    
                                    }else{
                                        // NO TIMECARD
    
    
                                        $am_in_req = strtotime($req_am_in);
                                        $pm_out_req = strtotime($req_pm_out);
    
                                        $req_am_out = strtotime($lib_sched["am_out"]);
                                        $req_pm_in =  strtotime($lib_sched["pm_in"]);
    
                                        $break_hours = abs($req_pm_in - $req_am_out)/(60*60);
    
    
                                        $work_hours = (abs($pm_out_req - $am_in_req)/(60*60)) - $break_hours ;
                                        if($emp_data["salary_type"] == 'DAILY'){
                                            $timekeeping_data["regular_work"] = 0;
                                            $timekeeping_data['absent'] = 0;
                                        }else{
                                            $timekeeping_data["regular_work"] = $work_hours;
                                            $timekeeping_data['absent'] = $work_hours;
                                        }
                                        // $timekeeping_data["regular_work"] = $work_hours;
                                        $timekeeping_data["night_diff"] = 0;
                                        
                                        // $timekeeping_data['absent'] = $work_hours;
                                        $timekeeping_data["lates"] = 0;
                                        $timekeeping_data["undertime"] = 0;
    
                                    }
                                }elseif($is_flexi == "1"){
                                    // FLEXIiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii
                                    if($timecard_data != null){
                                        $nxt_day = date("Y-m-d", strtotime($cur_date." +1 day"));
                                        $nd_start = date("Y-m-d H:i:s", strtotime($cur_date." 22:00:00"));
                                        $nd_end = date("Y-m-d H:i:s", strtotime($nxt_day." 06:00:00"));
                                        
                                        $nd_start = strtotime($nd_start);
                                        $nd_end = strtotime($nd_end);
                                        if($timecard_data->AM_IN == null || $timecard_data->PM_OUT == "" || $timecard_data->PM_OUT == null || $timecard_data->AM_IN == "" ){
                                           $absent = true;
                                        }else{
                                            $night_diff = 0;
                                            $actual_am_in = strtotime($timecard_data->AM_IN);
                                            $am_in = strtotime("-".$grace_period." minutes", strtotime($timecard_data->AM_IN));
                                            
                                            $actual_am_in = $am_in;
                                            $pm_out = strtotime($timecard_data->PM_OUT);
    
                                            
                                            $late_lunch = 0;
    
                                            if($actual_am_in >= $nd_start && $actual_am_in <= $nd_end){
                                                $nd_com_start = $actual_am_in;
                                                    if($pm_out < $nd_end){
                                                        $nd_com_end = $pm_out;
                                                    }else{
                                                        $nd_com_end = $nd_end;
                                                    }
    
                                                    $night_diff += abs($nd_com_end - $nd_com_start)/(60*60);
                                            }
    
    
                                            if($pm_out >= $nd_start && $pm_out <= $nd_end){
                                                $nd_com_start = $pm_out;
                                                if($pm_out < $nd_end){
                                                    $nd_com_end = $pm_out;
                                                }else{
                                                    $nd_com_end = $nd_end;
                                                }
                                                    $night_diff += abs($nd_com_end - $nd_com_start)/(60*60);
                                            }
    
                                            if($required_hours >= 6){
                                                $break_hours = 1;
                                            }else{
                                                $break_hours = 0;
                                            }
                                            
    
                                            // $work_hours = (abs($pm_out - $am_in)/(60*60)) - $break_hours ;
                                            // get all work hours
                                            // $work_hours = (abs($pm_out - $actual_am_in)/(60*60)) - $break_hours ;
                                            $work_hours = $required_hours - $break_hours;
                                            $timekeeping_data["regular_work"] = $work_hours;
                                            $night_diff = 0;
                                            $timekeeping_data["night_diff"] = $night_diff;
                                            
                                            
                                             $late = 0;
                                             $real_work_hours = (abs($pm_out - $actual_am_in)/(60*60)) - $break_hours ;
    
                                          	 if($real_work_hours <= $work_hours){
                                             	$undertime = $work_hours - $real_work_hours;
                                             }else{
                                             	$undertime = 0;
                                             }
                                             //dd($undertime);
                                             
                                                if($absent == false){
                                                    $timekeeping_data["lates"] = $late;
                                                    $timekeeping_data["undertime"] = $undertime;
                                                    $timekeeping_data["absent"] = 0;
                                                }else{
                                                    $timekeeping_data['absent'] = $work_hours;
                                                    $timekeeping_data["lates"] = 0;
                                                    $timekeeping_data["undertime"] = 0;
                                                }
                                        }
                                    }else{
                                        // NO TIMECARD
                                        if($required_hours >= 6){   $break_hours = 1;}else{   $break_hours = 0;  }
                                        $work_hours = $required_hours - $break_hours;
                                        if($emp_data["salary_type"] == 'DAILY'){
                                            $timekeeping_data["regular_work"] = 0;
                                            $timekeeping_data['absent'] = 0;
                                        }else{
                                            $timekeeping_data["regular_work"] = $work_hours;
                                            $timekeeping_data['absent'] = $work_hours;
                                        }
                                        
                                        // $timekeeping_data["regular_work"] = $work_hours;
                                        $timekeeping_data["night_diff"] = 0;
                                        // $timekeeping_data['absent'] = $work_hours;
                                        $timekeeping_data["lates"] = 0;
                                        $timekeeping_data["undertime"] = 0;
                                    }
                                } //FLEXI
                            }
                             //IF REST DAY
                            if ($req_am_in === "RD") {
                                $week_sched = DB::connection("intra_payroll")
                                    ->table("lib_week_schedule")
                                    ->where("id", $emp_data["schedule_id"])
                                    ->first();
                                $days = ["monday", "tuesday", "wednesday", "thursday", "friday", "saturday", "sunday"];
                                $ref_sched = null;
                                if ($week_sched) {
                                    foreach ($days as $day) {
                                        $sched_id = $week_sched->$day;
                                        if ($sched_id != 0) {
                                            $ref_sched = DB::connection("intra_payroll")
                                                ->table("lib_schedule")
                                                ->where("id", $sched_id)
                                                ->first();
                                            break;
                                        }
                                    }
                                }
                                if ($ref_sched) {
                                    $req_am_in2 = $ref_sched->am_in;
                                    $req_am_out = $ref_sched->am_out ?? '12:00:00';
                                    $req_pm_in = $ref_sched->pm_in ?? '13:00:00';
                                    $req_pm_out = $ref_sched->pm_out;
                                    $grace_period = $ref_sched->grace_period ?? 0;
                                    $required_hours = $ref_sched->required_hours ?? 8;
                                    $is_flexi = $ref_sched->is_flexi ?? 0;
                                    $timecard_data = DB::connection("intra_payroll")
                                        ->table("tbl_timecard")
                                        ->where("emp_id", $emp_id)
                                        ->where("target_date", $cur_date)
                                        ->first();
                                    if ($timecard_data && $timecard_data->AM_IN && $timecard_data->PM_OUT) {
                                        $actual_am_in = strtotime($timecard_data->AM_IN);
                                        $actual_pm_out = strtotime($timecard_data->PM_OUT);
                                        $sched_am_in = strtotime($cur_date . " " . $req_am_in2);
                                        $sched_pm_out = strtotime($cur_date . " " . $req_pm_out);
                                        if (strtotime($req_pm_out) <= strtotime($req_am_in2)) {
                                            $sched_pm_out = strtotime($cur_date . " " . $req_pm_out . " +1 day");
                                        }
                                        $sched_am_out = strtotime($cur_date . " " . $req_am_out);
                                        $sched_pm_in = strtotime($cur_date . " " . $req_pm_in);
                                        if (strtotime($req_pm_in) <= strtotime($req_am_out)) {
                                            $sched_pm_in = strtotime($cur_date . " " . $req_pm_in . " +1 day");
                                        }
                                        $break_minutes = ($sched_pm_in - $sched_am_out) / 60;
                                        // $worked_minutes = ($actual_pm_out - $actual_am_in) / 60 - $break_minutes;
                                        $worked_minutes = ($sched_pm_out - $sched_am_in) / 60 - $break_minutes;
                                        $worked_hours = round($worked_minutes / 60, 2);
                                        $late = ($actual_am_in > $sched_am_in) ? ($actual_am_in - $sched_am_in) / 60 : 0;
                                        $undertime = ($actual_pm_out < $sched_pm_out) ? ($sched_pm_out - $actual_pm_out) / 60 : 0;
                                        //dd($undertime, $actual_pm_out, $sched_pm_out);
                                        $nd_start = strtotime($cur_date . " 22:00:00");
                                        $nd_end = strtotime($cur_date . " +1 day 06:00:00");
                                        $nd_overlap = max(0, min($actual_pm_out, $nd_end) - max($actual_am_in, $nd_start));
                                        $night_diff = round($nd_overlap / 3600, 2);
                                        $timekeeping_data["regular_work"] = $worked_hours;
                                        //$timekeeping_data["rd"] = $worked_hours;
                                        // $timekeeping_data["lates"] = round($late, 2);
                                        // $timekeeping_data["undertime"] = round($undertime, 2);
                                        $timekeeping_data["night_diff"] = $night_diff;
                                        $daily_applied_ot = array_filter($applied_ot, function($ot) use ($cur_date) {
                                            return $ot["date_target"] == $cur_date;
                                        });
                                        foreach ($daily_applied_ot as $applied) {
                                            if($timecard_data->AM_IN != null || $timecard_data->PM_OUT != null || $timecard_data->AM_IN != "" || $timecard_data->OT_OUT != "" ){
                                                $ot_from = strtotime($applied["time_from"]);
                                                $ot_to = strtotime($applied["time_to"]);
                                                
                                                $ot_hours = abs($ot_to - $ot_from)/(60*60);
                                                if ($applied["ot_type"] == "ROT") {
                                                    $timekeeping_data["regular_ot"] = ($timekeeping_data["regular_ot"] ?? 0) + $ot_hours;
                                                } elseif ($applied["ot_type"] == "SOT") {
                                                    $timekeeping_data["special_ot"] = ($timekeeping_data["special_ot"] ?? 0) + $ot_hours;
                                                } elseif ($applied["ot_type"] == "NDOT") {
                                                    $timekeeping_data["nd_ot"] = ($timekeeping_data["nd_ot"] ?? 0) + $ot_hours;
                                                }elseif ($applied["ot_type"] == "RD") {
                                                    if ($ot_hours >= 5) {
                                                        $ot_hours -= 1;
                                                    }
                                                    $ot_hours = min($ot_hours, 8); // cap at 8 hrs
                                                    $timekeeping_data["rd"] = ($timekeeping_data["rd"] ?? 0) + $ot_hours;
                                                }elseif ($applied["ot_type"] == "RDOT") {
                                                    $timekeeping_data["rd_ot"] = ($timekeeping_data["rd_ot"] ?? 0) + $ot_hours;
                                                }elseif ($applied["ot_type"] == "SH_OT") {
                                                    $timekeeping_data["sh_ot"] = ($timekeeping_data["sh_ot"] ?? 0) + $ot_hours;
                                                }elseif ($applied["ot_type"] == "RH_OT") {
                                                    $timekeeping_data["rh_ot"] = ($timekeeping_data["rh_ot"] ?? 0) + $ot_hours;
                                                }elseif ($applied["ot_type"] == "RD_RH_OT") {
                                                    $timekeeping_data["rd_ot_rh"] = ($timekeeping_data["rd_ot_rh"] ?? 0) + $ot_hours;
                                                }elseif ($applied["ot_type"] == "RD_SH_OT") {
                                                    $timekeeping_data["rd_ot_sh"] = ($timekeeping_data["rd_ot_sh"] ?? 0) + $ot_hours;
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                           //END FOR REST DAY
                          
                            
                        }
                        
                        //CHECK IF HOLIDAY
                        $check_holiday = DB::connection("intra_payroll")->table("tbl_holiday")
                            ->where('holiday_date', $cur_date)
                            ->first();
                        $timecard_data = DB::connection("intra_payroll")->table("tbl_timecard")
                            ->where("emp_id", $emp_id)
                            ->where("target_date", $cur_date)
                            ->first();
                        $prev_date = date('Y-m-d', strtotime($cur_date . ' -1 day'));
                        $timecard_data_yesterday = DB::connection("intra_payroll")->table("tbl_timecard")
                            ->where("emp_id", $emp_id)
                            ->where("target_date", $prev_date)
                            ->first();
                        if($check_holiday != null){
                            $daily_divisor = Auth::user()->company["daily_divisor"];
                            if($check_holiday->holiday_type == "RH"){
                                $regular_holiday = 0;
                                $regular_work = 0;
                                if($emp_data["salary_type"] == "MONTHLY"){
                                    if($timecard_data != null){
                                        if($timecard_data->AM_IN != null || $timecard_data->PM_OUT != "" || $timecard_data->PM_OUT != null || $timecard_data->AM_IN != "" ){
                                            $regular_holiday = $daily_divisor;
                                            $regular_work = $daily_divisor;
                                            if($timekeeping_data["rd"] > 0){
                                                // $timekeeping_data["rd_rh"] = $timekeeping_data["rd"];
                                                $timekeeping_data["rd_rh"] = 0;
                                                $timekeeping_data["rd"] = 0;
                                            }
                                        }
                                    }
                                }else{
                                    if($timecard_data != null){
                                        if($timecard_data->AM_IN != null || $timecard_data->PM_OUT != "" || $timecard_data->PM_OUT != null || $timecard_data->AM_IN != "" ){
                                            $regular_holiday = $daily_divisor;
                                            $regular_work = $daily_divisor;
                                            if($timekeeping_data["rd"] > 0){
                                                // $timekeeping_data["rd_rh"] = $timekeeping_data["rd"];
                                                $timekeeping_data["rd_rh"] = 0;
                                                $timekeeping_data["rd"] = 0;
                                            }
                                        }
                                    }
                                }
                                if($timekeeping_data['absent'] > 0){
                                    $timekeeping_data['absent'] = 0;
                                }
                                if($req_am_in != "RD" && ($timecard_data_yesterday != null || $timecard_data_yesterday != "" )){
                                    $regular_work = $daily_divisor;
                                }

                                if($worked_other_branch) {
                                    $regular_work = 0;
                                }
                               
                                $timekeeping_data['regular_work'] = $regular_work;
                                $timekeeping_data["regular_holiday"] = 0;
                            }else{
                                $regular_work = 0;
                                $special_holiday = 0;
                                if($emp_data["salary_type"] == "MONTHLY"){
                                    if($timecard_data != null){
                                        if($timecard_data->AM_IN != null || $timecard_data->PM_OUT != "" || $timecard_data->PM_OUT != null || $timecard_data->AM_IN != "" ){
                                            $special_holiday = $daily_divisor;
                                            $regular_work = $daily_divisor;
                                            if($timekeeping_data["rd"] > 0){
                                                // $timekeeping_data["rd_sh"] = $timekeeping_data["rd"];
                                                $timekeeping_data["rd_sh"] = 0;
                                                $timekeeping_data["rd"] = 0;
                                            }
                                        }
                                    }
                                }else{
                                    if($timecard_data != null){
                                        if($timecard_data->AM_IN != null || $timecard_data->PM_OUT != "" || $timecard_data->PM_OUT != null || $timecard_data->AM_IN != "" ){
                                            $special_holiday = $daily_divisor;
                                            $regular_work = $daily_divisor;
                                            if($timekeeping_data["rd"] > 0){
                                                // $timekeeping_data["rd_sh"] = $timekeeping_data["rd"];
                                                $timekeeping_data["rd_sh"] = 0;
                                                $timekeeping_data["rd"] = 0;
                                            }
                                        }
                                    }
                                }
                                if($timekeeping_data['absent'] > 0){
                                    $timekeeping_data['absent'] = 0;
                                }

                                if($worked_other_branch) {
                                    $regular_work = 0;
                                }
                               
                                $timekeeping_data['regular_work'] = $regular_work;
                                $timekeeping_data["special_holiday"] = 0;
                                
                            }
                        }
                           $is_manual =  DB::connection("intra_payroll")->table("tbl_timekeeping")
                                ->where("payroll_id", $request->pay_id)     
                                ->where("emp_id", $emp_data["id"])
                                ->where("date_target", $cur_date)
                                ->where("is_manual", 1)
                                ->first();
                            
                            if($is_manual == null){
                                DB::connection("intra_payroll")->table("tbl_timekeeping")
                                ->where("payroll_id", $request->pay_id)
                                ->where("emp_id", $emp_data["id"])
                                ->where("date_target", $cur_date)
                                ->delete();
                                DB::connection("intra_payroll")->table("tbl_timekeeping")
                                    ->insert($timekeeping_data);
                                
                            }
                            
                         
                    } //PER DAY
                   
                   
               
                          
                } //EMPLOYEE
                try {
                    DB::connection("intra_payroll")->table("tbl_payroll")
                    ->where("id", $request->pay_id)
                    ->update([
                        "payroll_status" => "PROCESS"
                    ]);
                    DB::commit();
                    return json_encode("Timesheet Processed");
                } catch (\Throwable $th) {
                    DB::rollback();
                    return json_encode($th->getMessage());
                }
               
            
            }else{
                return json_encode("Payroll Info Unreachable");
            }
            
    }
    public function payroll_process_timecard_old_new(Request $request){
        DB::beginTransaction();
        $payroll_data = DB::connection("intra_payroll")->table("tbl_payroll")
            ->where("id", $request->pay_id)
            ->first();
        
            if($payroll_data != null){
                //PAYROLL INFO
                $date_from = $payroll_data->cover_from;
                $date_to = $payroll_data->cover_to;
                $employee_list = $payroll_data->employee;
                $tbl_employee = DB::connection("intra_payroll")->table("tbl_employee")->where("is_active", 1)->get();
                $tbl_employee = json_decode(json_encode($tbl_employee), true);
                $tbl_leave_credits = json_decode(json_encode(DB::connection("intra_payroll")->table("tbl_leave_credits")->get()), true);
                $tbl_leave_types = json_decode(json_encode(DB::connection("intra_payroll")->table("tbl_leave_types")->get()), true);
                
                $lib_schedule = DB::connection("intra_payroll")->table("lib_schedule")->where('is_active', 1)->get();
                $lib_schedule = json_decode(json_encode($lib_schedule),true);
               
                $lib_position = json_decode(json_encode(DB::connection("intra_payroll")->table("lib_position")->get()),true);
                $lib_designation = json_decode(json_encode(DB::connection("intra_payroll")->table("lib_designation")->get()),true);
                $tbl_department = json_decode(json_encode(DB::connection("intra_payroll")->table("tbl_department")->get()),true);
                $tbl_branch = json_decode(json_encode(DB::connection("intra_payroll")->table("tbl_branch")->get()),true);
                
                
                $employee_list = explode(";",$employee_list);
                
                foreach($employee_list as $emp){
                
                    $emp_id = str_replace("|","",$emp);
                    
                    $timekeeping_data = array();
                    $emp_data = $this->search_multi_array($tbl_employee, "id", $emp_id);
                        if(isset($emp_data["id"])){
                            $timekeeping_data["emp_id"] = $emp_data["id"];
                            $timekeeping_data["emp_code"] = $emp_data["emp_code"];
                         
                            
                        }else{
                            continue;
                        } 
                            $applied_ot = DB::connection("intra_payroll")->table("tbl_ot_applied")->where("status", "APPROVED")->where("emp_id", $emp_data["id"])->whereBetween("date_target", [$date_from, $date_to])->get();
                            $applied_ot = json_decode(json_encode($applied_ot), true);
                        
                        
             
                        $daily_sched = DB::connection("intra_payroll")->table("tbl_daily_schedule")->where("emp_id", $emp_data["id"])->whereBetween("schedule_date", [$date_from, $date_to])->get();
                        $daily_sched = json_decode(json_encode($daily_sched), true);
                    $begin = new DateTime($date_from);
                
                    $cover_to = date("Y-m-d", strtotime($date_to .' +1 day'));
                    $end = new DateTime($cover_to);
                    
                    $interval = new DateInterval('P1D'); // 1 day interval
                    $daterange = new DatePeriod($begin, $interval ,$end);
                    foreach($daterange as $date_cover){
                        $timekeeping_data["regular_work"] = 0;
                        $timekeeping_data["lates"] = 0;
                        $timekeeping_data["regular_ot"] = 0;
                        $timekeeping_data["special_ot"] = 0;
                        $timekeeping_data["night_diff"] = 0;
                        $timekeeping_data["regular_leave"] = 0;
                        $timekeeping_data["sick_leave"] = 0;
                        $timekeeping_data["special_leave"] = 0;
                        $timekeeping_data["regular_holiday"] = 0;
                        $timekeeping_data["special_holiday"] = 0;
                        $late_lunch = 0;
                        $late = 0;
                        
                        $cur_date = $date_cover->format("Y-m-d");
                        $timekeeping_data["date_target"] = $cur_date;
                        //CHECK IF ON LEAVE
                        $query_raw_leave = "'".$cur_date."' BETWEEN leave_date_from and leave_date_to";
                        $leave_day = DB::connection("intra_payroll")->table("tbl_leave_used")
                            ->where("emp_id", $emp_id)
                            ->where("leave_status", "APPROVED")
                            ->whereRaw($query_raw_leave)
                            ->first();
                        if($leave_day != null){
                            $credit_type = $this->search_multi_array($tbl_leave_credits, "id", $leave_day->leave_source_id);
                                if(isset($credit_type["leave_id"])){
                                    $leave_type = $this->search_multi_array($tbl_leave_types, "id", $credit_type["leave_id"]);
                                    if(isset($leave_type["leave_type"])){
                                        if($leave_type["leave_type"] == "VL"){
                                            $leave_type_tc = "regular_leave";
                                        }elseif($leave_type["leave_type"] == "SL"){
                                            $leave_type_tc ="sick_leave";
                                        }else{
                                            $leave_type_tc ="special_leave";
                                        }                                        
                                    }else{
                                        $leave_type_tc = "special_leave";
                                    }
                                }else{
                                    $leave_type_tc = "special_leave";
                                }
                            $timekeeping_data[$leave_type_tc] =Auth::user()->company["daily_divisor"];
                        }else{
                            //CHECK TIMECARD
                            //GET SCHEDULE
                            // default by daily_sched , employee, position, designation, department, branch, company
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
                                    }else{
                                        $req_am_in = $lib_sched;
                                        $req_am_out = "";
                                        $req_pm_in = "";
                                        $req_pm_out = "";
                                        $req_ot_in = "";
                                        $req_ot_out = "";
                                        $grace_period = 0;
                                    }
                                    
                
                                }else{
                                    $position_sched_id = $this->search_multi_array($lib_position, "id", $emp_data["position_id"]);
                                    $designation_sched_id = $this->search_multi_array($lib_designation, "id", $emp_data["designation"]);
                                    $department_sched_id = $this->search_multi_array($tbl_department, "id", $emp_data["department"]);
                                    $branch_sched_id = $this->search_multi_array($tbl_branch, "id", $emp_data["branch_id"]);
                                 
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
                                            }else{
                                                $req_am_in = $lib_sched;
                                                $req_am_out = "";
                                                $req_pm_in = "";
                                                $req_pm_out = "";
                                                $req_ot_in = "";
                                                $req_ot_out = "";
                                                $grace_period = 0;
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
                                                }else{
                                                    $req_am_in = $lib_sched;
                                                    $req_am_out = "";
                                                    $req_pm_in = "";
                                                    $req_pm_out = "";
                                                    $req_ot_in = "";
                                                    $req_ot_out = "";
                                                    $grace_period = 0;
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
                                                    }else{
                                                        $req_am_in = $lib_sched;
                                                        $req_am_out = "";
                                                        $req_pm_in = "";
                                                        $req_pm_out = "";
                                                        $req_ot_in = "";
                                                        $req_ot_out = "";
                                                        $grace_period = 0;
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
                                                        }else{
                                                            $req_am_in = $lib_sched;
                                                            $req_am_out = "";
                                                            $req_pm_in = "";
                                                            $req_pm_out = "";
                                                            $req_ot_in = "";
                                                            $req_ot_out = "";
                                                            $grace_period = 0;
                                                        }
                                                    }else{
                                                        $req_am_in = "RD";
                                                        $req_am_out = "";
                                                        $req_pm_in = "";
                                                        $req_pm_out = "";
                                                        $req_ot_in = "";
                                                        $req_ot_out = "";
                                                        $grace_period = 0;
                                                    }
                                                }
                                            }
                                        }
                                    
                               
                                }
                            } //IF NO DAILY SET SCHEDLE
                          
                            if($req_am_in != "RD"){
                                
                                $timecard_data = DB::connection("intra_payroll")->table("tbl_timecard")
                                    ->where("emp_id", $emp_id)
                                    ->where("target_date", $cur_date)
                                    ->first();
                                if($timecard_data != null){
                                    // dd($timecard_data);
                                    // $req_am_in = $lib_sched["am_in"];
                                    // $req_am_out = $lib_sched["am_out"];
                                    // $req_pm_in = $lib_sched["pm_in"];
                                    // $req_pm_out = $lib_sched["pm_out"];
                                    // $req_ot_in = $lib_sched["ot_in"];
                                    // $req_ot_out = $lib_sched["ot_out"];
                                    $nxt_day = date("Y-m-d", strtotime($cur_date." +1 day"));
                                    $nd_start = date("Y-m-d H:i:s", strtotime($cur_date." 22:00:00"));
                                    $nd_end = date("Y-m-d H:i:s", strtotime($nxt_day." 06:00:00"));
                                    
                                    $nd_start = strtotime($nd_start);
                                    $nd_end = strtotime($nd_end);
                                    
                                    if($timecard_data->AM_IN == null || $timecard_data->PM_OUT == "" || $timecard_data->PM_OUT == null || $timecard_data->AM_IN == "" ){
                                        $timekeeping_data["regular_work"] = "0";
                                        // dd("aa");
                                    }else{
                                        $night_diff = 0;
                                        $actual_am_in = strtotime($timecard_data->AM_IN);
                                        $am_in = strtotime("-".$grace_period." minutes", strtotime($timecard_data->AM_IN));
                                        
                                        $actual_am_in = $am_in;
                                        $pm_out = strtotime($timecard_data->PM_OUT);
                                   
                                        $am_in_req = strtotime($cur_date." ".$req_am_in);
                                        $am_out_req = strtotime($cur_date." ".$req_am_out);
                                        $pm_in_req = strtotime($cur_date." ".$req_pm_in);
                                        
                                        if(strtotime($req_pm_out) < strtotime($req_am_in)){
                                            $pm_out_req = strtotime($nxt_day." ".$req_pm_out);
                                        }else{
                                            $pm_out_req = strtotime($cur_date." ".$req_pm_out);
                                        }
                                        $late_lunch = 0;
                                        if($actual_am_in >= $nd_start && $actual_am_in <= $nd_end){
                                            $nd_com_start = $actual_am_in;
                                                if($pm_out < $nd_end){
                                                    $nd_com_end = $pm_out;
                                                }else{
                                                    $nd_com_end = $nd_end;
                                                }
                                                $night_diff += abs($nd_com_end - $nd_com_start)/(60*60);
                                        }
                                        if($pm_out >= $nd_start && $pm_out <= $nd_end){
                                            $nd_com_start = $pm_out;
                                            $nd_com_end = $nd_end;
                                                $night_diff += abs($nd_com_end - $nd_com_start)/(60*60);
                                        }
                                        if(Auth::user()->company["required_lunch_in_out"] == 1){
                                        
                                            $am_out = strtotime($timecard_data->AM_OUT);
                                            $pm_in = strtotime($timecard_data->PM_IN);
                                            if($am_out > $am_out_req ){
                                                $am_out = $am_out_req;
                                            }
                                            $break_hours = abs($pm_in - $am_out)/(60*60);
                                            if($pm_in > $pm_in_req){
                                                $late_lunch =  (abs($pm_in - $pm_in_req)/(60*60)) * 60;
                                            }
                                        }else{
                                            $break_in_req = strtotime($cur_date." 12:00:00");
                                            if($break_in_req > $actual_am_in){
                                                $break_hours = 1;
                                            }else{
                                                $break_hours = 0;
                                            }
                                           
                                        }
                                        if($am_in_req > $am_in){
                                            $am_in = $am_in_req;
                                        }
                                        
                                        if($pm_out > $pm_out_req){
                                            $pm_out = $pm_out_req;
                                        }
                                        $work_hours = (abs($pm_out - $am_in)/(60*60)) - $break_hours ;
                                   
                                        $timekeeping_data["regular_work"] = $work_hours;
                                        $timekeeping_data["night_diff"] = $night_diff;
                                        //COMPUTE LATE FOR MONTHLY ONLY
                                        if($emp_data["salary_type"] == "MONTHLY"){
                                            if($am_in > $am_in_req){
                                                $late = (abs($am_in - $am_in_req)/(60*60)) * 60;
                                            }else{
                                                $late = 0;
                                            }
                                            $late += $late_lunch;
                                        
                                            $timekeeping_data["lates"] = $late;
                                        }
                              
                                        $check_applied_ot = $this->search_multi_array($applied_ot, "date_target", $cur_date);
                                        if(isset($check_applied_ot["id"])){
                                            // dd("aaa");
                                            if($timecard_data->OT_IN != null || $timecard_data->OT_OUT != null || $timecard_data->OT_IN != "" || $timecard_data->OT_OUT != "" ){
                                                //PAYROLL INFO
                                                $ot_from = strtotime($check_applied_ot["time_from"]);
                                                $ot_to = strtotime($check_applied_ot["time_to"]);
                                                
                                                $ot_in = strtotime($timecard_data->OT_IN);
                                                $ot_out = strtotime($timecard_data->OT_OUT);
                                                
                                                if($ot_in < $ot_from){
                                                    $ot_in = $ot_from;
                                                }
                                                if($ot_out > $ot_to){
                                                    $ot_out = $ot_to;
                                                }
                                                
                                                $ot_hours = abs($ot_out - $ot_in)/(60*60);
                                                if($check_applied_ot["ot_type"] == "ROT"){
                                                    $timekeeping_data["regular_ot"] = $ot_hours;
                                                }elseif($check_applied_ot["ot_type"] == "SOT"){
                                                    $timekeeping_data["special_ot"] = $ot_hours;
                                                }
                                                
    
                                            }
                                        }
                                        
                                    } //WITH IN AND OUT
                                    
                                }
                            }
                            
                        }
                        
                        //CHECK IF HOLIDAY
                        $check_holiday = DB::connection("intra_payroll")->table("tbl_holiday")
                            ->where('holiday_date', $cur_date)
                            ->first();
                            if($check_holiday != null){
                                if($check_holiday->holiday_type == "RH"){
                                   
                                    $timekeeping_data["regular_holiday"] = Auth::user()->company["daily_divisor"];
                                    
                                   
                                }else{
                                    if(isset($work_hours)){
                                        if($work_hours>0){
                                            $timekeeping_data["special_holiday"] = Auth::user()->company["daily_divisor"];
                                        }
                                    }
                                   
                                }
                            }else{
                            }
                         
                           $is_manual =  DB::connection("intra_payroll")->table("tbl_timekeeping")
                                ->where("emp_id", $emp_data["id"])
                                ->where("date_target", $cur_date)
                                ->where("is_manual", 1)
                                ->first();
                            
                            if($is_manual == null){
                                DB::connection("intra_payroll")->table("tbl_timekeeping")
                                ->where("emp_id", $emp_data["id"])
                                ->where("date_target", $cur_date)
                                ->delete();
                                DB::connection("intra_payroll")->table("tbl_timekeeping")
                                    ->insert($timekeeping_data);
                                
                            }
                            
                         
                    } //PER DAY
               
                          
                } //EMPLOYEE
                try {
                    DB::connection("intra_payroll")->table("tbl_payroll")
                    ->where("id", $request->pay_id)
                    ->update([
                        "payroll_status" => "PROCESS"
                    ]);
                    DB::commit();
                    return json_encode("Timesheet Processed");
                } catch (\Throwable $th) {
                    DB::rollback();
                    return json_encode($th->getMessage());
                }
               
            
            }else{
                return json_encode("Payroll Info Unreachable");
            }
            
    }
    public function payroll_list(Request $request){
        $role_id = Auth::user()->role_id;
        $page_permission = Auth::user()->access[$request->page]["access"];
        $process_type_array = array(
            "RP" => "Regular",
            "13" => "13th Month",
            "BP" => "Bonus",
            "SP" => "Special",
            "LC" => "Leave Credits",
        );
        $data = DB::connection("intra_payroll")->table("tbl_payroll");
        if ($role_id === 4) { // HR Group D Citas
            $data->where(function ($q) {
                $q->where('hr_group', 'group_d')
                ->orWhere(function ($q2) {
                    $q2->where('hr_group', 'group_e')
                        ->whereIn('payroll_status', ['FINALIZE','CLOSE']);
                });
            });
        } elseif ($role_id === 5) { // HR Group C,E Monique
            $data = $data->whereIn("hr_group", ["group_c","group_e"]);
        } elseif ($role_id === 14) { // HR Group B,C aimee
            $data->where(function ($q) {
                $q->where('hr_group', 'group_b')
                ->orWhere(function ($q2) {
                    $q2->where('hr_group', 'group_c')
                        ->whereIn('payroll_status', ['FINALIZE','CLOSE']);
                });
            });
        } elseif ($role_id === 15) { // HR Group C JBY
            $data = $data->whereIn("hr_group", ["group_c","group_e"])
            ->whereIn('payroll_status', ['FINALIZE','CLOSE']);
        } elseif ($role_id === 11) { // HR Group D approver Camille
            $data = $data->where("hr_group", "group_d")
            ->whereIn('payroll_status', ['FINALIZE','CLOSE']);
        } 
        // elseif ($role_id === 22) { // HR Group E
        //     $data = $data->where("hr_group", "group_e");
        // }
            $data = $data->orderBy("date_updated", "DESC")
            ->get();
                $data = collect($data);
                return Datatables::of($data)
                ->addColumn('payroll_status', function($row){
                    if($row->payroll_status == "OPEN"){
                        $btn = "<label class='w-100' > Open </label>";
                    }elseif($row->payroll_status == "ADDED"){
                        $btn = "<label class='w-100' > ADDED </label>";
                    }elseif($row->payroll_status == "PROCESS"){
                        $btn = "<label class='w-100' > TIMECARD <br> PROCESSED </label>";
                    }elseif($row->payroll_status == "COMPUTED"){
                        $btn = "<label class='w-100' > PAYROLL <br> COMPUTED </label>";
                    }elseif($row->payroll_status == "FINALIZE"){
                        $btn = "<label class='w-100' > FOR APPROVAL </label>";
                    }
                    else{
                        $btn = "<label class='w-100' > PAYROLL COMPLETED </label>";
                    }
                    return $btn;
                })
                ->addColumn('info', function($row) use ($process_type_array){
                    $info = "";
                    $info.= "Type:".$process_type_array[$row->process_type]." (".$row->type.")";
                
                    return $info;
                })
                ->addColumn('coverage', function($row) use ($process_type_array){
                    $info = "";
                    $info.= " From:<strong>".$row->cover_from."</strong> <br> To:<strong>".$row->cover_to."</strong>";
                
                    return $info;
                })
                ->addColumn('action', function($row) use ($page_permission, $role_id){
                    $btn = "";
                    if(preg_match("/U/i", $page_permission)){
                        // status
                        // OPEN => on create
                        // ADDED => added employee & timekeeping
                        // PROCESS => COMPUTED
                        // FINALIZE => close to add timekeeping and other data and wait for approval
                        // CLOSE => approved by payroll manager
                        if ($row->process_type == "LC") {
                            $btn .= "<a class='btn btn-sm btn-info mb-1 w-100'
                                data-toggle='modal' 
                                data-id='".$row->id."'
                                data-code='".$row->code."'
                                data-name='".$row->name."'
                                data-target_month='".$row->target_month."'
                                data-target_year='".$row->target_year."'
                                data-gsis='".$row->gsis."'
                                data-sss='".$row->sss."'
                                data-ph='".$row->ph."'
                                data-hdmf='".$row->hdmf."'
                                data-date_start='".$row->cover_from."'
                                data-date_end='".$row->cover_to."'
                                data-process_type='".$row->process_type."'
                                data-hr_group='".$row->hr_group."'
                                data-payroll_type='".$row->type."'
                                data-oth_income='".$row->other_income."'
                                data-lib_loan='".$row->lib_loan."'
                                data-payroll_status='".$row->payroll_status."'
                                data-type_info='".$row->type_info."'
                                data-target='#payroll_modal'
                                > Edit </a>";
                            $btn .= "<br> <a class='btn btn-sm btn-warning mb-1 w-100' 
                                data-toggle='modal' 
                                data-pay_id='".$row->id."'
                                data-target='#tag_employee'
                                > Add Employee </a>";
                            $btn .= "<br><button onclick='process_payroll(".$row->id.")' class='btn btn-sm btn-success mb-1 w-100' 
                                > Process Payroll </button>";
                            if($row->payroll_status == "COMPUTED"){
                                $btn .= "<br><button  onclick='export_payroll($row->id)' class='export_payroll btn btn-success btn-sm w-100 mb-1'>Payroll Report</button>";
                            }
                            $btn .= "<br><button 
                                class='btn btn-sm btn-danger w-100'
                                onclick='delete_payroll(" . $row->id . ")'
                                > Delete </button>";
                        }else{
                            $disable_btn = 'disabled';
                            $disable_del_btn = 'disabled';
                            $group_b_c = ["group_b","group_c"];//aimee
                            $group_d_e = ["group_d","group_e"];//citas
                            $group_c_e = ["group_c","group_e"];//jby
                            if($row->hr_group == "group_b" && $role_id == 14){ //aimee only approver of group B
                                $disable_btn = '';
                            }
                            if($row->hr_group == "group_e" && $role_id == 4){ //citas group E approver only
                                $disable_btn = '';
                            }
                            if($row->hr_group == "group_d" && $role_id == 11){ //camille group D approver only
                                $disable_btn = '';
                            }
                            if(in_array($row->hr_group,$group_c_e) && $role_id == 15){ //JBY
                                $disable_btn = '';
                            }
                            if($row->payroll_status == "OPEN" || $row->payroll_status == "ADDED" || $row->payroll_status == "PROCESS" || $row->payroll_status == "COMPUTED" ){
                                    $disable_del_btn = '';
                                    $btn .= "<a class='btn btn-sm btn-info mb-1 w-100'
                                    data-toggle='modal' 
                                    data-id='".$row->id."'
                                    data-code='".$row->code."'
                                    data-name='".$row->name."'
                                    data-target_month='".$row->target_month."'
                                    data-target_year='".$row->target_year."'
                                    data-gsis='".$row->gsis."'
                                    data-sss='".$row->sss."'
                                    data-ph='".$row->ph."'
                                    data-hdmf='".$row->hdmf."'
                                    data-date_start='".$row->cover_from."'
                                    data-date_end='".$row->cover_to."'
                                    data-process_type='".$row->process_type."'
                                    data-hr_group='".$row->hr_group."'
                                    data-payroll_type='".$row->type."'
                                    data-oth_income='".$row->other_income."'
                                    data-lib_loan='".$row->lib_loan."'
                                    data-payroll_status='".$row->payroll_status."'
                                    data-type_info='".$row->type_info."'
                                    data-target='#payroll_modal'
                                    > Edit </a>";
                            
                                            $btn .= "<br> <a class='btn btn-sm btn-warning mb-1 w-100' 
                                            data-toggle='modal' 
                                            data-pay_id='".$row->id."'
                                            data-target='#tag_employee'
                                            > Add Employee </a>";
                                            $btn .= "<br> <a class='btn btn-sm btn-warning mb-1 w-100' 
                                            data-toggle='modal' 
                                            data-pay_id='".$row->id."'
                                            data-target='#uploadEmployeeOtherIncome'
                                            > Upload Other Income</a>";
                                            
                                            $btn .= "<br> <a class='btn btn-sm btn-warning mb-1 w-100' 
                                            data-toggle='modal' 
                                            data-pay_id='".$row->id."'
                                            data-target='#uploadEmployeeDeductionOneTime'
                                            > Upload Deduction</a>";
                                            //STOPPED HERE
                                            if($row->payroll_status == "ADDED"){
                    
                                                // $btn .= "<br> <a class='btn btn-sm btn-success mb-1 w-100' 
                                                //         data-toggle='modal' 
                                                //         data-pay_id='".$row->id."'
                                                //         data-target='#manual_timekeeping'
                                                //         > Add Manual Total Time </a>";
                                                    // $btn .= "<br><button onclick='process_payroll(".$row->id.")' class='btn btn-sm btn-success mb-1 w-100' 
                                                    //     > Process Payroll </button>";
                                                $btn .= "<br><button onclick='process_timecard(".$row->id.")' class='btn btn-sm btn-success mb-1 w-100' 
                                                
                                                // > Process Timecard </button>";
                                                // UPDATE STATUS INTO PROCESS
                                            }
                                            
                                            if($row->payroll_status == "PROCESS" || $row->payroll_status == "COMPUTED"){
                                            
                                            
                                                // $btn .= "<br> <a class='btn btn-sm btn-success mb-1 w-100' 
                                                //         data-toggle='modal' 
                                                //         data-pay_id='".$row->id."'
                                                //         data-target='#manual_timekeeping'
                                                //         > Add Manual Total Time </a>";
                                                $btn .= "<br><button onclick='process_timecard(".$row->id.")' class='btn btn-sm btn-info mb-1 w-100' 
                                                
                                                > Process Timecard </button>";
                                                $btn .= "<br><button onclick='download_timecard(".$row->id.")' class='btn btn-sm btn-info mb-1 w-100' 
                                                
                                                > Download Timecard </button>";
                                                $btn .= "<br><button onclick='process_payroll(".$row->id.")' class='btn btn-sm btn-success mb-1 w-100' 
                                                    > Process Payroll </button>";
                                                
                                            }
                                            if($row->payroll_status == "COMPUTED"){
                                                $btn .= "<br><button onclick='push_for_approval(".$row->id.")' class='btn btn-sm btn-info mb-1 w-100' 
                                                > Post for Approval </button>";
                                                $btn .= "<br><button  onclick='export_payroll($row->id)' class='export_payroll btn btn-success btn-sm w-100 mb-1'>Payroll Report</button>";
                                                // $btn .= "<br><button onclick='export_payroll_pdf($row->id)' class='btn btn-danger btn-sm w-100  mb-1'>Export PDF</button>";
                                            }
                                        
                                
                                }elseif($row->payroll_status == "FINALIZE" || $row->payroll_status == "CLOSE"){
                                    $btn .= "<br><button onclick='re_open(".$row->id.")' class='btn btn-sm btn-warning mb-1 w-100' 
                                    > Re-Open </button>";
                                    $btn .= "<br><button  onclick='export_payroll($row->id)' class='export_payroll btn btn-success btn-sm w-100 mb-1'>Payroll Report</button>";
                                    if($row->payroll_status == "FINALIZE"){
                                        $btn .= "<br><button onclick='approve_payroll(".$row->id.")' class='btn btn-sm btn-success mb-1 w-100' 
                                        $disable_btn> Approve </button>";
                                        
                                    }
                                }
                                $btn .= " <br><button 
                                class='btn btn-sm btn-danger w-100'
                                onclick='delete_payroll(" . $row->id . ")'
                                $disable_del_btn>
                                Delete
                                </button>"; 
                        
                            }
                        }
                    return $btn;
                })
                ->rawColumns(['payroll_status', 'action', 'coverage'])
                ->make(true);
    }
    
    public function save_payroll_info(Request $request){
           
        DB::beginTransaction();
        
        $oth_inc = "";
        if(isset($request->reg_oth_inc)){
            foreach($request->reg_oth_inc as $inc)  {  if($oth_inc != ""){ $oth_inc .= ";";  }  $oth_inc .= $inc;  }
        }
        
        $loan_data = "";
        if(isset($request->lib_loan)){
            foreach($request->lib_loan as $loan)  {  if($loan_data != ""){ $loan_data .= ";";  }  $loan_data .= $loan;  }
        }
        try {
            $role_id = Auth::user()->role_id;
            // $hr_group = "";
            // if ($role_id === 4) { // HR Group A
            //     $hr_group = "group_a";
            // } elseif ($role_id === 5) { // HR Group B
            //     $hr_group = "group_b";
            // } elseif ($role_id === 14) { // HR Group C
            //     $hr_group = "group_c";
            // } elseif ($role_id === 15) { // HR Group D
            //     $hr_group = "group_d";
            // } elseif ($role_id === 22) { // HR Group E
            //     $hr_group = "group_e";
            // }
            if($request->id == "new"){
                    $array = array(
                        "code" => $request->payroll_code,
                        "name" => $request->payroll_name,
                        "target_month" => $request->target_month,
                        "target_year" => $request->target_year,
                        
                        "cover_from" => $request->start_date,
                        "cover_to" => $request->end_date,
                        "process_type" => $request->process_type,
                        "type" => $request->payroll_type,
                        "type_info" => $request->type_info,
                        "payroll_status" => "OPEN",
                        "date_created" => date("Y-m-d"),
                        "user_id" => Auth::user()->id,
                        "other_income" => $oth_inc,
                        "lib_loan" => $loan_data,
                        "gsis" => $request->gsis,
                        "sss" => $request->sss,
                        "ph" => $request->ph,
                        "hdmf" => $request->hdmf,
                        "hr_group" =>  $request->hr_group
                    );
                    DB::connection("intra_payroll")->table("tbl_payroll")
                        ->insert($array);
            }else{
                
                $array = array(
                    "code" => $request->payroll_code,
                    "name" => $request->payroll_name,
                    "target_month" => $request->target_month,
                    "target_year" => $request->target_year,
                    "cover_from" => $request->start_date,
                    "cover_to" => $request->end_date,
                    "process_type" => $request->process_type,
                    "type" => $request->payroll_type,
                    "type_info" => $request->type_info,
                    // "payroll_status" => $request->payroll_status,
                    "date_created" => date("Y-m-d"),
                    "user_id" => Auth::user()->id,
                    "other_income" => $oth_inc,
                    "lib_loan" => $loan_data,
                    "gsis" => $request->gsis,
                    "sss" => $request->sss,
                    "ph" => $request->ph,
                    "hdmf" => $request->hdmf,
                    "hr_group" =>  $request->hr_group
                );
                DB::connection("intra_payroll")->table("tbl_payroll")
                    ->where("id", $request->id)
                    ->update($array);
            }
            
            DB::commit();
            return json_encode("Success");
        } catch (\Throwable $th) {
            DB::rollback();
            return json_encode($th->getMessage());
            //throw $th;
        }
    }
    function payroll_process_for_approval(Request $request){
        DB::beginTransaction();
        try {
            $check_payroll = DB::connection("intra_payroll")->table("tbl_payroll")
                ->where("id", $request->pay_id)
                ->first();
            if($check_payroll != null){
                DB::connection("intra_payroll")->table("tbl_payroll")
                    ->where("id", $request->pay_id)
                    ->update(["payroll_status" => "FINALIZE"]);
            }else{
                return json_encode("Payroll Info Unreachable");
            }
            DB::commit();
            return json_encode("Success");
        } catch (\Throwable $th) {
            DB::rollback();
            return json_encode($th->getMessage());
        }
    }
    function re_open_payroll(Request $request){
        DB::beginTransaction();
        try {
            $check_payroll = DB::connection("intra_payroll")->table("tbl_payroll")
                ->where("id", $request->pay_id)
                ->first();
            if($check_payroll != null){
                DB::connection("intra_payroll")->table("tbl_payroll")
                    ->where("id", $request->pay_id)
                    ->update(["payroll_status" => "PROCESS"]);
            }else{
                return json_encode("Payroll Info Unreachable");
            }
            DB::commit();
            return json_encode("Success");
        } catch (\Throwable $th) {
            DB::rollback();
            return json_encode($th->getMessage());
        }
    }
    function approve_payroll(Request $request){
        DB::beginTransaction();
        try {
            $check_payroll = DB::connection("intra_payroll")->table("tbl_payroll")
                ->where("id", $request->pay_id)
                ->first();
            if($check_payroll != null){
                DB::connection("intra_payroll")->table("tbl_payroll")
                    ->where("id", $request->pay_id)
                    ->update(["payroll_status" => "CLOSE"]);
            }else{
                return json_encode("Payroll Info Unreachable");
            }
            DB::commit();
            return json_encode("Success");
        } catch (\Throwable $th) {
            DB::rollback();
            return json_encode($th->getMessage());
        }
    }
    public function delete_payroll(Request $request){
        DB::beginTransaction();
            try {
                DB::connection("intra_payroll")->table("tbl_payroll")
                    ->where("id", $request->id)
                    ->delete();
                DB::connection("intra_payroll")->table("tbl_payroll_income")
                    ->where("payroll_id", $request->id)
                    ->delete();
                DB::connection("intra_payroll")->table("tbl_payroll_deduction")
                    ->where("payroll_id", $request->id)
                    ->delete();
                DB::commit();
                return json_encode("Deleted");
            } catch (\Throwable $th) {
                DB::rollback();
                return json_encode($th->getMessage());
            }
    }
    //Leave Credits Payroll
    private function payroll_process_leave_credits($employee_list, $request, $data, $tbl_employee)
    {
        $payroll_income = [];
        foreach ($employee_list as $emp) {
            $emp_id = str_replace("|", "", $emp);
            $emp_data = $this->search_multi_array($tbl_employee, "id", $emp_id);
            if (!$emp_data) continue;
            $daily_rate = $emp_data["salary_rate"];
            $salary_type = $emp_data["salary_type"];
            if (strtoupper($salary_type) == "MONTHLY") {
                $divisor =  Auth::user()->company['divisor'];
                $daily_rate = $daily_rate / $divisor;
            }
            // Get leave credits (with credit types only)
            $leave_types = DB::connection("intra_payroll")->table("tbl_leave_types")
                ->where("is_with_credits", 1)
                ->pluck("id")
                ->toArray();
            $credits = DB::connection("intra_payroll")->table("tbl_leave_credits")
                ->where("emp_id", $emp_id)
                ->whereIn("leave_id", $leave_types)
                ->where("year_given", $data->target_year)
                ->get();
            $used = DB::connection("intra_payroll")->table("tbl_leave_used")
                ->where("emp_id", $emp_id)
                ->where("leave_status", "APPROVED")
                ->where("leave_year", $data->target_year)
                ->get();
            $remaining_credits = 0;
            foreach ($credits as $credit) {
                $used_count = $used->where("leave_source_id", $credit->leave_id)->sum("leave_count");
                $available = $credit->leave_count - $used_count;
                $remaining_credits += max($available, 0);
            }
            $amount = round($daily_rate * $remaining_credits, 2);
            if ($amount > 0) {
                $payroll_income[] = [
                    "payroll_id" => $request->pay_id,
                    "emp_id" => $emp_id,
                    "type" => "LEAVE_CREDIT",
                    "amount" => $amount,
                    "date_created" => date("Y-m-d"),
                    "user_id" => Auth::user()->id
                ];
            }
        }
        DB::beginTransaction();
        try {
            DB::connection("intra_payroll")->table("tbl_payroll")
                ->where("id", $request->pay_id)
                ->update(["payroll_status" => "COMPUTED"]);
            DB::connection("intra_payroll")->table("tbl_payroll_income")
                ->where("payroll_id", $request->pay_id)
                ->where("type", "LEAVE_CREDIT")
                ->delete();
            if (!empty($payroll_income)) {
                DB::connection("intra_payroll")->table("tbl_payroll_income")
                    ->insert($payroll_income);
            }
            DB::commit();
            return "Success";
        } catch (\Throwable $th) {
            DB::rollback();
            return $th->getMessage();
        }
    }
    public function uploadEmployeeDeductionOneTime(Request $request){
        $request->validate([
            'excel' => 'required|file|mimes:xlsx',
        ]);
        $file = $request->file('excel');
        
        $pay_id = $request->pay_id_upload_deduction;
        // CHECK THE PAYROLL IS EXISTING
        $payroll = DB::connection("intra_payroll")->table("tbl_payroll")
            ->where("id", $pay_id)
            ->first();
        if($payroll == null){
            return back()->with('error', 'Payroll Not Found');
        }
        
        $data = Excel::toArray([], $file); // Returns array of sheets → each is a 2D array
        // Example: Access first sheet
        $firstSheet = $data[0];
        foreach($firstSheet as $key=> $row){
            
            if($key == 0){continue;}
            $ded_row = array();
            $ded_row['payroll_id'] = $pay_id;
            
            $emp_id =  $row[0];
            $deduction_name = $row[2];
            $amount = $row[3];
            
            $check_user = DB::connection("intra_payroll")->table("tbl_employee")
                ->where("emp_code", $emp_id)
                ->first();
            if($check_user == null){
                continue;
            }else{
                $emp_id = $check_user->id;
            }
            $payroll_emp_id = "|".$emp_id."|";
            // CHECK IF IN PAYROLL
            $onPayRoll = DB::connection("intra_payroll")->table("tbl_payroll")
                ->where("employee", "LIKE", "%".$payroll_emp_id."%")
                ->first();
            if($onPayRoll == null){
                continue;
            }
            $ded_row['emp_id'] = $emp_id;
            $ded_row['type'] = $deduction_name;
            $lib_loan = DB::connection("intra_payroll")->table("lib_loans")
            ->where("name", $deduction_name)
            ->first();
            if($lib_loan != null){
                $ded_row['type'] = "R_".$lib_loan->id;
            }
            if($amount ==null){
                $amount = 0;
            }
            $ded_row['amount'] = $amount;
            $ded_row['date_created'] = date("Y-m-d H:i:s");
            $ded_row['User_id'] = Auth::user()->id;
          
            DB::connection("intra_payroll")->table("tbl_payroll_deduction")
                ->where("emp_id", $emp_id)
                ->where("payroll_id", $pay_id)
                ->where("type", $ded_row['type'])
                ->delete();
            DB::connection("intra_payroll")->table("tbl_payroll_deduction")
                ->insert($ded_row);
        }
        return back()->with('success', 'Excel uploaded successfully.');
    }
    public function uploadEmployeeOtherIncome(Request $request){
        $request->validate([
            'excel' => 'required|file|mimes:xlsx',
        ]);
        $file = $request->file('excel');
        
        $pay_id = $request->pay_id_upload_income;
        // CHECK THE PAYROLL IS EXISTING
        $payroll = DB::connection("intra_payroll")->table("tbl_payroll")
            ->where("id", $pay_id)
            ->first();
        if($payroll == null){
            return back()->with('error', 'Payroll Not Found');
        }
        
        $data = Excel::toArray([], $file); // Returns array of sheets → each is a 2D array
        // Example: Access first sheet
        $firstSheet = $data[0];
        foreach($firstSheet as $key=> $row){
            
            if($key == 0){continue;}
            $inc_row = array();
            $inc_row['payroll_id'] = $pay_id;
            
            $emp_id =  $row[0];
            $income_name = $row[2];
            $amount = $row[3];
            
            $check_user = DB::connection("intra_payroll")->table("tbl_employee")
                ->where("emp_code", $emp_id)
                ->first();
            if($check_user == null){
                continue;
            }else{
                $emp_id = $check_user->id;
            }
            $payroll_emp_id = "|".$emp_id."|";
            // CHECK IF IN PAYROLL
            $onPayRoll = DB::connection("intra_payroll")->table("tbl_payroll")
                ->where("employee", "LIKE", "%".$payroll_emp_id."%")
                ->first();
            if($onPayRoll == null){
                continue;
            }
            $inc_row['emp_id'] = $emp_id;
            $inc_row['type'] = $income_name;
          
            $lib_loan = DB::connection("intra_payroll")->table("lib_loans")
            ->where("name", $income_name)
            ->first();
            if($lib_loan != null){
                $inc_row['type'] = "R_".$lib_loan->id;
            }
            if($amount ==null){
                $amount = 0;
            }
            $inc_row['amount'] = $amount;
            $inc_row['date_created'] = date("Y-m-d H:i:s");
            $inc_row['user_id'] = Auth::user()->id;
          
            DB::connection("intra_payroll")->table("tbl_payroll_income")
                ->where("emp_id", $emp_id)
                ->where("payroll_id", $pay_id)
                ->where("type", $inc_row['type'])
                ->delete();
            DB::connection("intra_payroll")->table("tbl_payroll_income")
                ->insert($inc_row);
        }
        return back()->with('success', 'Excel uploaded successfully.');
    }
}
