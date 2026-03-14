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

class leaveController extends Controller
{
    public function leave_management(){
        $role_id = Auth::user()->role_id;
        $leave_types = DB::connection("intra_payroll")->table("tbl_leave_types")
            ->orderBy("leave_name")
            ->get();
        // dd(Auth::user()->access);
        if(Auth::user()->access["leave_management"]["user_type"] != "employee"){
            $emp_list = DB::connection("intra_payroll")->table("tbl_employee")
            ->where('is_active', 1);
            if ($role_id === 4) { // HR Group D
                $emp_list = $emp_list->where("hr_group", "group_d");
            } elseif ($role_id === 5) { // HR Group B,C,E
                $emp_list = $emp_list->whereIn("hr_group", ["group_b","group_c","group_e"]);
            } elseif ($role_id === 14) { // HR Group B,C
                $emp_list = $emp_list->whereIn("hr_group", ["group_b","group_c"]);
            } elseif ($role_id === 15) { // HR Group C,E
                $emp_list = $emp_list->whereIn("hr_group", ["group_c","group_e"]);
            } 
            // elseif ($role_id === 22) { // HR Group E
            //     $emp_list = $emp_list->where("hr_group", "group_e");
            // }
            $emp_list = $emp_list->orderBy("last_name")
            ->orderBy("first_name")
            ->orderBy("middle_name")
            ->get();
        
        }else{
            $query = DB::connection("intra_payroll")->table("tbl_employee")
            ->where('is_active', 1);
                if($role_id === 6){ //1st Approver - Joefran Aeon Tower/Mindanao, Cebu/CAD, Gensan, HM Tower, Howard Hubbard Hospital, Iloilo, JMALL
                    $query->whereIn("branch_id", [56,78,52,51,55,72,49,74])
                    ->orWhere("user_id", Auth::user()->id);
                }elseif($role_id === 7){ //1st Approver - Leo Banaran, Sta. Clara, Tawi-Tawi, Zamboanga
                    $query->whereIn("branch_id", [61,60,63,64])
                    ->orWhere("user_id", Auth::user()->id);
                }elseif($role_id === 8){ // 1st Approver - RA Jabson Batangas, Bicol 1, Bicol 2, Candido - Operations, Laguna, NCR, Palawan
                    // $query->whereIn("branch_id", [57,59,70,46,71,50,62])
                    $query->where("hr_group", "group_e")
                    ->orWhere("user_id", Auth::user()->id);
                }elseif($role_id === 9){ //1st Approver - Anafe (Warehouse)
                    $query->where("branch_id", 77)
                    ->orWhere("user_id", Auth::user()->id);
                }elseif($role_id === 10){ //Final App - James Brian
                    $query->whereNotIn("branch_id", [75,76])
                    ->orWhere("user_id", Auth::user()->id);
                }elseif($role_id === 11){ //Final App - Dorcas (FA)
                    $query->where("branch_id", 75)
                    ->orWhere("user_id", Auth::user()->id);
                }elseif($role_id === 12){ //Final App - Ajes (HRAD)
                    $query->where("branch_id", 76)
                    ->orWhere("user_id", Auth::user()->id);
                }elseif($role_id === 13){ //Final App - Aimee (Dorotea)
                    $query->where("branch_id", 82)
                    ->orWhere("user_id", Auth::user()->id);
                }else{//normal staff
                   $query->where("id",Auth::user()->company["linked_employee"]["id"]);
                }
                $emp_list = $query ->orderBy("last_name")
                ->orderBy("first_name")
                ->orderBy("middle_name")
                ->get();
         
            }
    
      
        return view("leave.index")
            ->with("leave_type", $leave_types)
            ->with("emp_list", $emp_list)
            ;
    }
    public function store_leave_type(Request $request){
        
        if($request->id == "new"){
            $tbl = DB::connection("intra_payroll")->table("tbl_leave_types")
            ->where("leave_name", "like", $request->leave_name)
            ->first();
            if($tbl != null) {
                return json_encode("Leave Name Already Taken");
            }else{
               $insert_array = array(
                "leave_type" => $request->leave_type,
                "leave_name" => $request->leave_name,
                "is_with_credits" => $request->require,
                "date_created" => date("Y-m-d H:i:s"),
                "user_id" => Auth::user()->id
                );
            }
        }else{
            $tbl = DB::connection("intra_payroll")->table("tbl_leave_types")
            ->where("leave_name", "like", $request->leave_name)
            ->where("id", "!=", $request->id)
            ->first();
            if($tbl != null) {
                return json_encode("Leave Name Already Taken");
            }else{
               $insert_array = array(
                "leave_type" => $request->leave_type,
                "leave_name" => $request->leave_name,
                "is_with_credits" => $request->require,
                "user_id" => Auth::user()->id
                );
            }
        }
        DB::beginTransaction();
        try {
            if($request->id == "new"){
                DB::connection("intra_payroll")->table("tbl_leave_types")
                ->insert($insert_array);
            }else{
                DB::connection("intra_payroll")->table("tbl_leave_types")
                ->where("id", $request->id)
                ->update($insert_array);
            }
         
            DB::commit();
            return json_encode("Success");
        } catch (\Throwable $th) {
            DB::rollback();
            return json_encode($th->getMessage());
        }
    }
    public function leave_type_tbl(Request $request){
        $page_permission = Auth::user()->access[$request->page]["access"];
        $types =array(
            "VL" => "Vacation Leave",
            "SL" => "Sick Leave",
            "OL" => "Special Leave",
            "LWP" => "Leave Without Pay"
        );
        $data = DB::connection("intra_payroll")->table("tbl_leave_types")
            ->orderBy("date_updated")
            ->get();
        $data = collect($data);
        return Datatables::of($data)
        ->addColumn('leave_type', function($row) use ($types){
            if(isset($types[$row->leave_type])){
                return $types[$row->leave_type];
            }else{
                return $types["OL"];
            }
            
        })
       
        ->addColumn('is_with_credits', function($row){
            if($row->is_with_credits ==1){
                return "<a class='btn btn-success btn-sm'>YES</a>";
            }else{
                return "<a class='btn btn-warning btn-sm'>NO</a>";
            }
        })
        ->addColumn('action', function($row) use ($page_permission){
            $btn = "";
            if(preg_match("/U/i", $page_permission)){
                // add delete in leave
                $type = "type";
                $btn .= "<a 
                class='btn btn-sm btn-success'
                data-id='".$row->id."' 
                data-type='".$row->leave_type."' 
                data-name='".$row->leave_name."' 
                data-require='".$row->is_with_credits."' 
                data-toggle='modal' 
                data-target='#leave_table_modal'
                >
                Edit
                </a>";
                // add delete in leave
                $btn .= " <button 
                class='btn btn-sm btn-danger'
                onclick='delete_leave(" . $row->id . ", \"" . $type . "\")'
                >
                Delete
                </button>";
            }
          
            return $btn;
        })
        ->rawColumns(['action', 'is_with_credits'])
        ->make(true);
    }
    function search_multi_array($array, $key, $value) {
        foreach ($array as $subarray) {
            if (isset($subarray[$key]) && $subarray[$key] == $value) {
                return $subarray;
            }
        }
        return null;
    }
    public function leave_credit_tbl(Request $request)
    {
        $user = Auth::user();
        $role_id = $user->role_id;
        $page_permission = $user->access[$request->page]["access"];
        // Employee list with role-based filter
        $employeeQuery = DB::connection("intra_payroll")->table("tbl_employee");
        if ($role_id === 4) { // HR Group D
            $employeeQuery->where("hr_group", "group_d");
        } elseif ($role_id === 5) { // HR Group B,C,E
            $employeeQuery->whereIn("hr_group", ["group_b","group_c","group_e"]);
        } elseif ($role_id === 14) { // HR Group B,C
            $employeeQuery->whereIn("hr_group", ["group_b","group_c"]);
        } elseif ($role_id === 15) { // HR Group C,E
            $employeeQuery->whereIn("hr_group", ["group_c","group_e"]);
        } 
        // elseif ($role_id === 22) { // HR Group D
        //     $employeeQuery->where("hr_group", "group_e");
        // }
        $employee = json_decode(json_encode($employeeQuery->get()), true);
        $leave_type = json_decode(json_encode(
            DB::connection("intra_payroll")->table("tbl_leave_types")->get()
        ), true);
        $types = array(
            "VL" => "Vacation Leave",
            "SL" => "Sick Leave",
            "OL" => "Special Leave"
        );
        // Leave credits data
        if ($user->access[$request->page]["user_type"] != "employee") {
            $data = DB::connection("intra_payroll")->table("tbl_leave_credits");
            // Apply same HR group restriction for non-employees
            if ($role_id === 4) {
                $data->whereIn("emp_id", function ($query) {
                    $query->select("id")
                        ->from("tbl_employee")
                        ->where("hr_group", "group_d");
                });
            } elseif ($role_id === 5) {
                $data->whereIn("emp_id", function ($query) {
                    $query->select("id")
                        ->from("tbl_employee")
                        ->whereIn("hr_group", ["group_b","group_c","group_e"]);
                });
            } elseif ($role_id === 14) {
                $data->whereIn("emp_id", function ($query) {
                    $query->select("id")
                        ->from("tbl_employee")
                        ->whereIn("hr_group", ["group_b","group_c"]);
                });
            } elseif ($role_id === 15) {
                $data->whereIn("emp_id", function ($query) {
                    $query->select("id")
                        ->from("tbl_employee")
                        ->whereIn("hr_group", ["group_c","group_e"]);
                });
            } 
            // elseif ($role_id === 22) {
            //     $data->whereIn("emp_id", function ($query) {
            //         $query->select("id")
            //             ->from("tbl_employee")
            //             ->where("hr_group", "group_e");
            //     });
            // }
            $data = $data->orderBy("date_updated")->get();
        } else {
            // Employee view
            $data = DB::connection("intra_payroll")->table("tbl_leave_credits")
                ->where("emp_id", $user->company["linked_employee"]["id"])
                ->orderBy("date_updated")
                ->get();
        }
        $data = collect($data);
        return Datatables::of($data)
            ->addColumn('emp_name', function ($row) use ($employee) {
                $data = $this->search_multi_array($employee, "id", $row->emp_id);
                if ($data) {
                    return "(" . $data["emp_code"] . ") " . $data["last_name"] . ", " . $data["first_name"] . " " . $data["middle_name"] . " " . $data["ext_name"];
                } else {
                    return "";
                }
            })
            ->addColumn('leave_type', function ($row) use ($types, $leave_type) {
                $data = $this->search_multi_array($leave_type, "id", $row->leave_id);
                if (count($data) > 0) {
                    return $types[$data["leave_type"]] ?? "";
                } else {
                    return "";
                }
            })
            ->addColumn('leave_name', function ($row) use ($leave_type) {
                $data = $this->search_multi_array($leave_type, "id", $row->leave_id);
                if (count($data) > 0) {
                    return $data["leave_name"];
                } else {
                    return "";
                }
            })
            ->addColumn('credit', function ($row) {
                return $row->leave_count;
            })
            ->addColumn('balance', function ($row) {
                // Calculate leave used for this employee and leave type
                $leave_used = DB::connection("intra_payroll")->table("tbl_leave_used")
                    ->where("emp_id", $row->emp_id)
                    ->where("leave_source_id", $row->leave_id)
                    ->where("leave_status", "APPROVED")
                    // ->where("leave_year", date("Y"))
                    ->sum("leave_count");
                
                $balance = $row->leave_count - $leave_used;
                return $balance;
            })
            ->addColumn('action', function ($row) use ($page_permission, $request) {
                $btn = "";
                if (preg_match("/U/i", $page_permission)) {
                    if (Auth::user()->access[$request->page]["user_type"] != "employee") {
                        $type = "credit";
                        $btn .= "<a 
                            class='btn btn-sm btn-success'
                            data-id='" . $row->id . "'
                            data-emp_id='" . $row->emp_id . "'
                            data-leave_id='" . $row->leave_id . "'
                            data-leave_count='" . $row->leave_count . "'
                            data-toggle='modal' 
                            data-target='#leave_credit_modal'
                        >
                            Edit
                        </a>";
                        $btn .= " <button 
                            class='btn btn-sm btn-danger'
                            onclick='delete_leave(" . $row->id . ", \"" . $type . "\")'
                        >
                            Delete
                        </button>";
                    }
                }
                return $btn;
            })
            ->rawColumns(['action'])
            ->make(true);
    }
    public function leave_credit_tbl_old(Request $request){
        $page_permission = Auth::user()->access[$request->page]["access"];
        $employee = json_decode(json_encode(
            DB::connection("intra_payroll")->table("tbl_employee")
                ->get()
        ), true);
        $leave_type = json_decode(json_encode(
            DB::connection("intra_payroll")->table("tbl_leave_types")
                ->get()
        ), true);
        $types =array(
            "VL" => "Vacation Leave",
            "SL" => "Sick Leave",
            "OL" => "Special Leave"
        );
        if(Auth::user()->access[$request->page]["user_type"] != "employee"){
            $data = DB::connection("intra_payroll")->table("tbl_leave_credits")
            ->orderBy("date_updated")
            ->get();
        }else{
            $data = DB::connection("intra_payroll")->table("tbl_leave_credits")
            ->where("emp_id", Auth::user()->company["linked_employee"]["id"])
            ->orderBy("date_updated")
            ->get();
            }
    
        
        $data = collect($data);
        return Datatables::of($data)
        ->addColumn('emp_name', function($row) use ($employee){
            $data = $this->search_multi_array($employee, "id", $row->emp_id);
            if(count($data)>0){
                return "(".$data["emp_code"] . ") ".$data["last_name"].", ".$data["first_name"]." ".$data["middle_name"]." ".$data["ext_name"];
            }else{
                return "";
            }
            
        })
       
        ->addColumn('leave_type', function($row) use ($types,$leave_type){
            $data = $this->search_multi_array($leave_type, "id", $row->leave_id);
            if(count($data)>0){
            return $types[$data["leave_type"]];
            }else{
                return "";
            }
        })
        ->addColumn('leave_name', function($row) use ($types,$leave_type){
            $data = $this->search_multi_array($leave_type, "id", $row->leave_id);
            if(count($data)>0){
            return $data["leave_name"];
            }else{
                return "";
            }
        })
       
        ->addColumn('credit', function($row){
          return $row->leave_count;
        })
        ->addColumn('action', function($row) use ($page_permission, $request){
            $btn = "";
            if(preg_match("/U/i", $page_permission)){
                if(Auth::user()->access[$request->page]["user_type"] != "employee"){
                    // add delete in leave
                    $type = "credit";
                    $btn .= "<a 
                    class='btn btn-sm btn-success'
                    data-id='".$row->id."'
                    data-emp_id='".$row->emp_id."'
                    data-leave_id='".$row->leave_id."'
                    data-leave_count='".$row->leave_count."'
                    data-toggle='modal' 
                    data-target='#leave_credit_modal'
                    >
                    Edit
                    </a>";
                    // add delete in leave
                    $btn .= " <button 
                    class='btn btn-sm btn-danger'
                    onclick='delete_leave(" . $row->id . ", \"" . $type . "\")'
                    >
                    Delete
                    </button>";
                }
               
            }
          
            return $btn;
        })
        ->rawColumns(['action'])
        ->make(true);
    }
    public function file_leave_tbl(Request $request)
    {
        $role_id = Auth::user()->role_id;
        $page_permission = Auth::user()->access[$request->page]["access"];
        $query = DB::connection("intra_payroll")
            ->table("tbl_leave_used as lu")
            ->join("tbl_employee as emp", "emp.id", "=", "lu.emp_id")
            ->join("tbl_leave_types as lt", "lt.id", "=", "lu.leave_source_id")
            ->select(
                "lu.*",
                "emp.first_name",
                "emp.middle_name",
                "emp.last_name",
                "emp.ext_name",
                "emp.department",
                "emp.hr_group",
                "emp.user_id",
                "lt.leave_name"
            );
        if (Auth::user()->access[$request->page]["user_type"] != "employee") {
            if ($role_id === 4) {
                // $query->where("emp.hr_group", "group_d");
                $query->where(function ($q) {
                    $q->where("emp.hr_group", "group_d")
                    ->orWhere("emp.user_id", Auth::user()->id);
                });
            } elseif ($role_id === 5) {
                // $query->where("emp.hr_group",["group_b","group_c","group_e"]);
                $query->where(function ($q) {
                    $q->whereIn("emp.hr_group", ["group_b","group_c","group_e"])
                    ->orWhere("emp.user_id", Auth::user()->id);
                });
            } elseif ($role_id === 14) { //dorotea aimee
                // $query->where(function ($q) {
                //     $q->where("emp.hr_group", "group_c")
                //     ->orWhere("emp.user_id", Auth::user()->id);
                // });
                $query->where(function($q) {
                    $q->where(function($q1) {
                        $q1->where("emp.branch_id", 82)
                        ->whereIn("lu.leave_status", ["1st_Approved","APPROVED"]);
                    });
                })
                ->orWhere("emp.user_id", Auth::user()->id);
            } elseif ($role_id === 15) { // james brian
                // $query->where(function ($q) {
                //     $q->where("emp.hr_group", "group_d")
                //     ->orWhere("emp.user_id", Auth::user()->id);
                // });
                $query->where(function($q) {
                    $q->where(function($q1) {
                        $q1->whereNotIn("emp.branch_id", [75,76])
                        ->whereIn("lu.leave_status", ["1st_Approved","APPROVED"]);
                    })
                    ->orWhere(function($q2) {
                        $q2->whereIn("emp.emp_code", ["3004","3018","3020","3010","3003","3034","3028","2015","2125"]) // managers
                         ->whereIn("lu.leave_status", ["1st_Approved","APPROVED"]);
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
             if($role_id === 6){ //1st Approver - Joefran Aeon Tower/Mindanao, Cebu/CAD, Gensan, HM Tower, Howard Hubbard Hospital, Iloilo, JMALL
                $query->where(function($q){
                    $q->whereIn("emp.branch_id", [56,78,52,51,55,72,49,74])
                    ->whereIn("lu.leave_status", ["FILED","APPROVED"]);
                })
                ->orWhere("emp.user_id", Auth::user()->id);
            }elseif($role_id === 7){ //1st Approver - Leo Banaran, Sta. Clara, Tawi-Tawi, Zamboanga
                $query->where(function($q){
                    $q->whereIn("emp.branch_id", [61,60,63,64])
                    ->whereIn("lu.leave_status", ["FILED","APPROVED"]);
                })
                ->orWhere("emp.user_id", Auth::user()->id);
            }elseif($role_id === 8){ // 1st Approver - RA Jabson Batangas, Bicol 1, Bicol 2, Candido - Operations, Laguna, NCR, Palawan
                $query->where(function($q){
                    // $q->whereIn("emp.branch_id", [57,59,70,46,71,50,62])
                    $q->where("emp.hr_group", "group_e")
                    ->whereIn("lu.leave_status", ["FILED","APPROVED"]);
                })
                ->orWhere("emp.user_id", Auth::user()->id);
            }elseif($role_id === 9){ //1st Approver - Anafe (Warehouse)
                $query->where(function($q){
                    $q->where("emp.branch_id", 77)
                    ->whereIn("lu.leave_status", ["FILED","APPROVED"]);
                })
                ->orWhere("emp.user_id", Auth::user()->id);
            }elseif($role_id === 10){ //Final App - James Brian
                $query->where(function($q) {
                    $q->where(function($q1) {
                        $q1->whereNotIn("emp.branch_id", [75,76])
                        ->whereIn("lu.leave_status", ["1st_Approved","APPROVED"]);
                    })
                    ->orWhere(function($q2) {
                        $q2->whereIn("emp.emp_code", ["3004","3018","3020","3010","3003","3034","3028","2015","2125"]) // managers
                         ->whereIn("lu.leave_status", ["1st_Approved","APPROVED"]);
                    });
                })
                ->orWhere("emp.user_id", Auth::user()->id);
            }elseif($role_id === 11){ //Final App - Dorcas (FA)
                $query->where(function($q){
                    $q->where("emp.branch_id", 75)
                     ->whereIn("lu.leave_status", ["1st_Approved","APPROVED"]);
                })
                ->orWhere("emp.user_id", Auth::user()->id);
            }elseif($role_id === 12){ // Final App - Ajes (HRAD)
                $query->where(function($q) {
                    $q->where(function($q1) {
                        $q1->where("emp.branch_id", 76)
                        ->whereIn("lu.leave_status", ["1st_Approved","APPROVED"]);
                    });
                })
                ->orWhere("emp.user_id", Auth::user()->id);
            }elseif($role_id === 13){ // Final App - Aime (Dorotea)
                $query->where(function($q) {
                    $q->where(function($q1) {
                        $q1->where("emp.branch_id", 82)
                        ->whereIn("lu.leave_status", ["1st_Approved","APPROVED"]);
                    });
                })
                ->orWhere("emp.user_id", Auth::user()->id);
            }else {
                // normal employee can only see their own leaves
                $query->where("lu.emp_id", Auth::user()->company["linked_employee"]["id"]);
            }
            
        }
        $list = $query->orderBy("lu.date_updated", "desc")->get();
        return Datatables::of($list)
            ->addColumn('emp_name', function($row) {
                return "{$row->last_name}, {$row->first_name} {$row->middle_name} {$row->ext_name}";
            })
            ->addColumn('date_filed', function($row) {
                return date("Y-m-d", strtotime($row->date_created));
            })
            ->addColumn('leave_type', function($row) {
                return $row->leave_name ?? "";
            })
            ->addColumn('dates', function($row) {
                $btn  = "<label class='badge badge-info mb-1 mr-1'>FROM</label>".$row->leave_date_from;
                $btn .= "<br><label class='badge badge-info mr-1'>TO</label>".$row->leave_date_to;
                return $btn;
            })
            ->addColumn('is_half_day', function($row) {
                return $row->half_day == "1" ? "YES" : "NO";
            })
            ->addColumn('rejoin_duty_on', function($row) {
                return $row->rejoin_duty_on;
            })
            ->addColumn('leave_count', function($row) {
                return $row->leave_count;
            })
            ->addColumn('leave_status', function($row) {
                $status = $row->leave_status;
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
                $role_id = Auth::user()->role_id;
                if (preg_match("/U/i", $page_permission)) {
                    if (Auth::user()->access[$request->page]["user_type"] != "employee") {
                        $btn .= "<a 
                            class='btn btn-sm btn-success mr-1'
                            data-id = '{$row->id}'
                            data-emp_id = '{$row->emp_id}'
                            data-leave_id = '{$row->leave_source_id}'
                            data-leave_from = '{$row->leave_date_from}'                        
                            data-leave_to = '{$row->leave_date_to}'    
                            data-rejoin_duty = '{$row->rejoin_duty_on}'                        
                            data-reason ='{$row->reason}'
                            data-leave_status = '{$row->leave_status}'
                            data-toggle='modal' 
                            data-target='#leave_file_modal'
                            data-half_day='{$row->half_day}'
                        >
                            Edit
                        </a>";
                        $btn .= "<button 
                            class='btn btn-sm btn-danger'
                            onclick='delete_file_leave({$row->id})'
                        >
                            Delete
                        </button>";
                    }else{
                        $class_disable = '';
                        if($row->leave_status === 'APPROVED' || ($row->leave_name === 'Suspension' && $role_id == 2)){
                            $class_disable = 'disabled';
                        }
                        $btn .= "<a 
                            class='btn btn-sm btn-success mr-1 $class_disable'
                            data-id = '{$row->id}'
                            data-emp_id = '{$row->emp_id}'
                            data-leave_id = '{$row->leave_source_id}'
                            data-leave_from = '{$row->leave_date_from}'                        
                            data-leave_to = '{$row->leave_date_to}'    
                            data-rejoin_duty = '{$row->rejoin_duty_on}'                        
                            data-reason ='{$row->reason}'
                            data-leave_status = '{$row->leave_status}'
                            data-toggle='modal' 
                            data-target='#leave_file_modal'
                            data-half_day='{$row->half_day}'
                        >
                            Edit
                        </a>";
                        $btn .= "<button 
                            class='btn btn-sm btn-danger'
                            onclick='delete_file_leave({$row->id})'
                            $class_disable
                        >
                            Delete
                        </button>";
                    }
                  
                }
                return $btn;
            })
            ->rawColumns(['action', 'dates','leave_status'])
            ->make(true);
    }
    public function file_leave_tbl_oldest(Request $request)
    {
        $user = Auth::user();
        $role_id = $user->role_id;
        $page_permission = $user->access[$request->page]["access"];
        // Employee list with role-based filter
        $employeeQuery = DB::connection("intra_payroll")->table("tbl_employee");
        if ($role_id === 4) { // HR Group A
            $employeeQuery->where("hr_group", "group_a");
        } elseif ($role_id === 5) { // HR Group B
            $employeeQuery->where("hr_group", "group_b");
        }
        $employee = json_decode(json_encode($employeeQuery->get()), true);
        // Leave types
        $leave_type = json_decode(json_encode(
            DB::connection("intra_payroll")->table("tbl_leave_types")->get()
        ), true);
        // Leave used data with same HR-based condition
        if ($user->access[$request->page]["user_type"] != "employee") {
            $data = DB::connection("intra_payroll")->table("tbl_leave_used");
            if ($role_id === 4) {
                $data->whereIn("emp_id", function ($query) {
                    $query->select("id")
                        ->from("tbl_employee")
                        ->where("hr_group", "group_a");
                });
            } elseif ($role_id === 5) {
                $data->whereIn("emp_id", function ($query) {
                    $query->select("id")
                        ->from("tbl_employee")
                        ->where("hr_group", "group_b");
                });
            }
            $data = $data->orderBy("date_updated", "desc")->get();
        } else {
            $data = DB::connection("intra_payroll")->table("tbl_leave_used")
                ->where("emp_id", $user->company["linked_employee"]["id"])
                ->orderBy("date_updated", "desc")
                ->get();
        }
        $data = collect($data);
        return Datatables::of($data)
            ->addColumn('emp_name', function ($row) use ($employee) {
                $data = $this->search_multi_array($employee, "id", $row->emp_id);
                if (count($data) > 0) {
                    return $data["last_name"] . ", " . $data["first_name"] . " " . $data["middle_name"] . " " . $data["ext_name"];
                } else {
                    return "";
                }
            })
            ->addColumn('date_filed', function ($row) {
                return date("Y-m-d", strtotime($row->date_created));
            })
            ->addColumn('leave_type', function ($row) use ($leave_type) {
                $data = $this->search_multi_array($leave_type, "id", $row->leave_source_id);
                if (count($data) > 0) {
                    return $data["leave_name"];
                } else {
                    return "";
                }
            })
            ->addColumn('dates', function ($row) {
                $btn = "<label class='badge badge-info mb-1 mr-1'>FROM</label>" . $row->leave_date_from;
                $btn .= "<br><label class='badge badge-info mr-1'>TO</label>" . $row->leave_date_to;
                return $btn;
            })
            ->addColumn('is_half_day', function ($row) {
                return $row->half_day == "1" ? "YES" : "NO";
            })
            ->addColumn('rejoin_duty_on', function ($row) {
                return $row->rejoin_duty_on;
            })
            ->addColumn('leave_count', function ($row) {
                return $row->leave_count;
            })
            ->addColumn('action', function ($row) use ($page_permission, $request) {
                $btn = "";
                if (preg_match("/U/i", $page_permission)) {
                    if (Auth::user()->access[$request->page]["user_type"] != "employee") {
                        $btn .= "<a 
                            class='btn btn-sm btn-success mr-1'
                            data-id='" . $row->id . "'
                            data-emp_id='" . $row->emp_id . "'
                            data-leave_id='" . $row->leave_source_id . "'
                            data-leave_from='" . $row->leave_date_from . "'                        
                            data-leave_to='" . $row->leave_date_to . "'    
                            data-rejoin_duty='" . $row->rejoin_duty_on . "'                        
                            data-reason='" . $row->reason . "'
                            data-leave_status='" . $row->leave_status . "'
                            data-toggle='modal' 
                            data-target='#leave_file_modal'
                            data-half_day='" . $row->half_day . "'
                        >
                            Edit
                        </a>";
                    }
                    $btn .= "<button 
                        class='btn btn-sm btn-danger'
                        onclick='delete_file_leave(" . $row->id . ")'
                    >
                        Delete
                    </button>";
                }
                return $btn;
            })
            ->rawColumns(['action', 'dates'])
            ->make(true);
    }
    public function file_leave_tbl_old(Request $request){
        $page_permission = Auth::user()->access[$request->page]["access"];
        $employee = json_decode(json_encode(
            DB::connection("intra_payroll")->table("tbl_employee")
                ->get()
        ), true);
        $leave_type = json_decode(json_encode(
            DB::connection("intra_payroll")->table("tbl_leave_types")
                ->get()
        ), true);
        
        if(Auth::user()->access[$request->page]["user_type"] != "employee"){
            $data = DB::connection("intra_payroll")->table("tbl_leave_used")
            ->orderBy("date_updated", "desc")
            ->get();
        }else{
        
            $data = DB::connection("intra_payroll")->table("tbl_leave_used")
            ->where("emp_id", Auth::user()->company["linked_employee"]["id"])
            ->orderBy("date_updated", "desc")
            ->get();
            }
    
        $data = collect($data);
        return Datatables::of($data)
        ->addColumn('emp_name', function($row) use ($employee){
            $data = $this->search_multi_array($employee, "id", $row->emp_id);
            if(count($data)>0){
                return $data["last_name"].", ".$data["first_name"]." ".$data["middle_name"]." ".$data["ext_name"];
            }else{
                return "";
            }
        })
        ->addColumn('date_filed', function($row){
            return date("Y-m-d", strtotime($row->date_created));
          })
        ->addColumn('leave_type', function($row) use ($leave_type){
            $data = $this->search_multi_array($leave_type, "id", $row->leave_source_id);
            if(count($data)>0){
            return $data["leave_name"];
            }else{
                return "";
            }
        })
        ->addColumn('dates', function($row){
            $btn = "<label class='badge badge-info mb-1 mr-1'>FROM</label>".$row->leave_date_from;
            $btn .= "<br>" . "<label class='badge badge-info mr-1'>TO</label>".$row->leave_date_to;
            return $btn;
          })
          ->addColumn('is_half_day', function($row){
            if($row->half_day == "1"){
                return "YES";
            }else{
                return "NO";
            }
          })
        ->addColumn('rejoin_duty_on', function($row){
            return $row->rejoin_duty_on;
          })
        ->addColumn('leave_count', function($row){
          return $row->leave_count;
        })
        ->addColumn('action', function($row) use ($page_permission, $request){
            $btn = "";
            if(preg_match("/U/i", $page_permission)){
                if(Auth::user()->access[$request->page]["user_type"] != "employee"){
                    $btn .= "<a 
                    class='btn btn-sm btn-success mr-1'
                    data-id = '".$row->id."'
                    data-emp_id = '".$row->emp_id."'
                    data-leave_id = '".$row->leave_source_id."'
                    data-leave_from = '".$row->leave_date_from."'                        
                    data-leave_to = '".$row->leave_date_to."'    
                    data-rejoin_duty = '".$row->rejoin_duty_on."'                        
                    data-reason ='".$row->reason."'
                    data-leave_status = '".$row->leave_status."'
                    data-toggle='modal' 
                    data-target='#leave_file_modal'
                    data-half_day='".$row->half_day."'
                    >
                    Edit
                    </a>";
                }
                
                $btn .= "<button 
                class='btn btn-sm btn-danger'
                onclick='delete_file_leave(".$row->id.")'
                >
                Delete
                </button>";
                
            }
          
            $today = strtotime(date("Y-m-d"));
            $date_from = strtotime($row->leave_date_from);
            // show action btn
            // if($today > $date_from){
            //     $btn ="";
            // }
            return $btn;
        })
        ->rawColumns(['action', 'dates'])
        ->make(true);
    }
    public function delete_filed_leave(Request $request){
        DB::beginTransaction();
            try {
                DB::connection("intra_payroll")->table("tbl_leave_used")
                    ->where("id", $request->id)
                    ->delete();
                DB::commit();
                return json_encode("Deleted");
            } catch (\Throwable $th) {
                DB::rollback();
                return json_encode($th->getMessage());
            }
    }
    // add delete in leave
    public function delete_leave(Request $request){
        $type = $request->type;
        $tbl = "";
        if($type == "credit"){
            $tbl = "tbl_leave_credits";
        }else{
            $tbl = "tbl_leave_types";
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
    public function store_leave_credit(Request $request){
        DB::beginTransaction();
        if(isset($request->emp_id)){
            try {
                $all_emp = 0;
                foreach($request->emp_id as $emp){
                    if($emp == "all"){
                        $all_emp = 1;
                    }
                }
                if($all_emp == 1){
                    if(Auth::user()->access["leave_management"]["user_type"] != "employee"){
                        $emp_list = DB::connection("intra_payroll")->table("tbl_employee")
                        ->select("id")
                        ->where('is_active', 1)
                        ->orderBy("last_name")
                        ->orderBy("first_name")
                        ->orderBy("middle_name")
                        ->get();
            
                    
                    }else{
                        $emp_list = DB::connection("intra_payroll")->table("tbl_employee")
                        ->select("id")
                        ->where('is_active', 1)
                        ->where("id",Auth::user()->company["linked_employee"]["id"])
                        ->orderBy("last_name")
                        ->orderBy("first_name")
                        ->orderBy("middle_name")
                        ->get();
                     
                        }
    
                    
                        $request->emp_id = json_decode(json_encode($emp_list), true);
                }
              
                foreach($request->emp_id as $emp){
                    if($all_emp == 1){
                        $emp_id =  $emp["id"];
                    }else{
                        $emp_id = $emp;
                    }
                  
                    $tbl = DB::connection("intra_payroll")->table("tbl_leave_credits")
                        ->where("emp_id", "like", $emp_id)
                        ->where("leave_id", $request->leave_type)
                        ->first();
                    if($tbl != null){
                        if($request->id != "new"){
                            $new_leave_credit = $request->leave_credit; //update leave credit
                        }else{
                            $new_leave_credit = $tbl->leave_count + $request->leave_credit; //update leave credit
                        }
                        
                        
                        $insert_array = array(
                            "emp_id" => $emp_id,
                            "leave_id" => $request->leave_type,
                            "leave_count" => $new_leave_credit, //update leave credit
                            "user_id" => Auth::user()->id,
                            "year_given" => date("Y-m-d")  // fix leave credit
                            );
                            DB::connection("intra_payroll")->table("tbl_leave_credits")
                            ->where("id", $tbl->id)
                            ->update($insert_array);
                    }else{
                        $insert_array = array(
                            "emp_id" => $emp_id,
                            "leave_id" => $request->leave_type,
                            "leave_count" => $request->leave_credit,
                            "date_created" => date("Y-m-d H:i:s"),
                            "user_id" => Auth::user()->id,
                            "year_given" => date("Y-m-d")  // fix leave credit
                            );
                    
                            DB::connection("intra_payroll")->table("tbl_leave_credits")
                            ->insert($insert_array);
                    }
                }
                DB::commit();
                return json_encode("Success");
            } catch (\Throwable $th) {
                DB::rollback();
                return json_encode($th->getMessage());
            }
        }else{
            return json_encode("No Employee Selected");
        }
    }
    public function leave_employee_list(Request $request){
        $role_id = Auth::user()->role_id;
        if(Auth::user()->access[$request->page]["user_type"] != "employee"){
          $employee_list = DB::connection("intra_payroll")->table("tbl_employee");
            if ($role_id === 4) { // HR Group D
                // $employee_list = $employee_list->where("hr_group", "group_a");
                $employee_list = $employee_list->where(function ($q) {
                    $q->where("hr_group", "group_d")
                      ->orWhere("user_id", Auth::user()->id); 
                });
            } elseif ($role_id === 5) { // HR Group B,C,E
                // $employee_list = $employee_list->where("hr_group", "group_b");
                $employee_list = $employee_list->where(function ($q) {
                    $q->whereIn("hr_group", ["group_b","group_c","group_e"])
                      ->orWhere("user_id", Auth::user()->id); 
                });
            } elseif ($role_id === 14) { // HR Group B,C aime
                $employee_list = $employee_list->where("branch_id", 82)
                ->orWhere("user_id", Auth::user()->id);
            } elseif ($role_id === 15) { // HR Group C james yu
                $employee_list = $employee_list->whereNotIn("branch_id", [75,76])
                ->orWhereIn("emp_code", ["3004","3018","3020","3010","3003","3034","3028","2015","2125"])
                ->orWhere("user_id", Auth::user()->id);
            } 
            // elseif ($role_id === 22) { // HR Group E
            //     $employee_list = $employee_list->where(function ($q) {
            //         $q->where("hr_group", "group_e")
            //           ->orWhere("user_id", Auth::user()->id); 
            //     });
            // }
            $employee_list = $employee_list->where("is_active", 1)
            ->get();
            return json_encode($employee_list);
        }else{
            //EMPLOYEE
            $query = DB::connection("intra_payroll")->table("tbl_employee")
            ->where('is_active', 1);
            if($role_id === 6){ //1st Approver - Joefran Aeon Tower/Mindanao, Cebu/CAD, Gensan, HM Tower, Howard Hubbard Hospital, Iloilo, JMALL
                $query->whereIn("branch_id", [56,78,52,51,55,72,49,74])
                ->orWhere("user_id", Auth::user()->id);
            }elseif($role_id === 7){ //1st Approver - Leo Banaran, Sta. Clara, Tawi-Tawi, Zamboanga
                $query->whereIn("branch_id", [61,60,63,64])
                ->orWhere("user_id", Auth::user()->id);
            }elseif($role_id === 8){ // 1st Approver - RA Jabson Batangas, Bicol 1, Bicol 2, Candido - Operations, Laguna, NCR, Palawan
                // $query->whereIn("branch_id", [57,59,70,46,71,50,62])
                $query->where("hr_group", "group_e")
                ->orWhere("user_id", Auth::user()->id);
            }elseif($role_id === 9){ //1st Approver - Anafe (Warehouse)
                $query->where("branch_id", 77)
                ->orWhere("user_id", Auth::user()->id);
            }elseif($role_id === 10){ //Final App - James Brian
                $query->whereNotIn("branch_id", [75,76])
                ->orWhere("user_id", Auth::user()->id);
            }elseif($role_id === 11){ //Final App - Dorcas (FA)
                $query->where("branch_id", 75)
                ->orWhere("user_id", Auth::user()->id);
            }elseif($role_id === 12){ //Final App - Ajes (HRAD)
                $query->where("branch_id", 76)
                ->orWhere("user_id", Auth::user()->id);
            }elseif($role_id === 13){ //Final App - Aimee (Dorotea)
                $query->where("branch_id", 82)
                ->orWhere("user_id", Auth::user()->id);
            }else{//normal staff
                $query->where("user_id", Auth::user()->id);
            }
            $employee_list = $query->get();
                return json_encode($employee_list); 
        }
    }
    public function get_leave_balance(Request $request){
        $target_year = date("Y");
        $chk_leave_type = DB::connection("intra_payroll")->table("tbl_leave_types")
        ->where("id", $request->file_leave_type)
        ->where("is_with_credits", "1")
        ->first();
        if($chk_leave_type != null){
            $is_with_credits = 1;
            $leave_credits = DB::connection("intra_payroll")->table("tbl_leave_credits")
            ->where("leave_id", $request->file_leave_type)
            // ->where("year_given", $target_year)
            ->where("emp_id", $request->file_emp_name)
            ->sum("leave_count");
            $leave_used = DB::connection("intra_payroll")->table("tbl_leave_used")
                // ->where("leave_year", $target_year)
                ->where("leave_source_id", $request->file_leave_type)
                ->where("emp_id", $request->file_emp_name)
                ->where("leave_status", 'APPROVED')
                ->sum("leave_count");
             $leave_balance = $leave_credits - $leave_used;
        }else{
           $leave_balance = "not required";
        }
        return json_encode($leave_balance);
    }
    public function store_filed_leave(Request $request){
        $is_with_credits = 0;
        $target_year  = date("Y", strtotime($request->file_from));
        $half_day = $request->half_day;
        $file_from = new DateTime($request->file_from);
        $file_to = new DateTime($request->file_to);
        $interval = $file_from->diff($file_to);
        $days = $interval->format('%d') + 1;
        
        if($half_day == "1"){
            $days = $days - 0.5;
        }
        if($request->id == "new"){
            $chk_if_already_filed = DB::connection("intra_payroll")->table("tbl_leave_used")
            ->where("leave_source_id", $request->file_leave_type)
            ->where("emp_id", $request->file_emp_name)
            ->whereBetween("leave_date_from",[$request->file_from, $request->file_to])
            ->orWhere("leave_source_id", $request->file_leave_type)
            ->where("emp_id", $request->file_emp_name)
            ->whereBetween("leave_date_from",[$request->file_from, $request->file_to])
            ->get();
            if(count($chk_if_already_filed)>0){
                return json_encode("Filling Date conflict");
            }
            $chk_leave_type = DB::connection("intra_payroll")->table("tbl_leave_types")
            ->where("id", $request->file_leave_type)
            ->where("is_with_credits", "1")
            ->first();
            if($chk_leave_type != null){
                $is_with_credits = 1;
            }
        if($is_with_credits == 1){
            
            $leave_used = DB::connection("intra_payroll")->table("tbl_leave_used")
                // ->where("leave_year", $target_year)
                ->where("leave_source_id", $request->file_leave_type)
                ->where("emp_id", $request->file_emp_name)
                ->sum("leave_count");
            $leave_credits = DB::connection("intra_payroll")->table("tbl_leave_credits")
                ->where("leave_id", $request->file_leave_type)
                // ->where("year_given", $target_year)
                ->where("emp_id", $request->file_emp_name)
                ->sum("leave_count");
            $leave_balance = $leave_credits - $leave_used;
            if($leave_balance >= $days){
                $ins_data = array(
                    "emp_id" => $request->file_emp_name,
                    "leave_source_id" => $request->file_leave_type,
                    "leave_year" => $target_year,
                    "leave_date_from" => $request->file_from,
                    "leave_date_to" => $request->file_to,
                    "rejoin_duty_on" => $request->rejoin_duty, 
                    "leave_status" => $request->leave_status,
                    "reason" => $request->file_reason,
                    "half_day" => $half_day,
                    "leave_count" => $days,
                    "date_created" => date("Y-m-d H:i:s"),
                    "user_id" => Auth::user()->id
                );
            }else{
                return json_encode("Leave Credits is not enough");
            }
        }else{
            $ins_data = array(
                "emp_id" => $request->file_emp_name,
                "leave_source_id" => $request->file_leave_type,
                "leave_year" => $target_year,
                "leave_date_from" => $request->file_from,
                "leave_date_to" => $request->file_to,
                "rejoin_duty_on" => $request->rejoin_duty, 
                "leave_status" => $request->leave_status,
                "leave_count" => $days,
                "half_day" => $half_day,
                "reason" => $request->file_reason,
                "date_created" => date("Y-m-d H:i:s"),
                "user_id" => Auth::user()->id
            );
        }
        }else{
            $ins_data = array(
                "emp_id" => $request->file_emp_name,
                "leave_source_id" => $request->file_leave_type,
                "leave_year" => $target_year,
                "leave_date_from" => $request->file_from,
                "leave_date_to" => $request->file_to,
                "rejoin_duty_on" => $request->rejoin_duty, 
                "leave_status" => $request->leave_status,
                "reason" => $request->file_reason,
                "leave_count" => $days,
                "half_day" => $half_day,
                "user_id" => Auth::user()->id
            );
        }
       
        
            DB::beginTransaction();
            try {
                
                if($request->id == "new"){
                    DB::connection("intra_payroll")->table("tbl_leave_used")
                    ->insert($ins_data);
                }else{
                    DB::connection("intra_payroll")->table("tbl_leave_used")
                    ->where('id', $request->id)
                    ->update($ins_data);
                }
                
                    DB::commit();
                return json_encode("Filling Success");
            } catch (\Throwable $th) {
                DB::rollback();
                return json_encode($th->getMessage());
            }
       
    }
     public function get_leaves(Request $request, $start_date)
    {
        $role_id = Auth::user()->role_id;
        $raw_date = $request->month_view;
        $date_from_year  = date("Y", strtotime($raw_date));
        $date_from_month = date("m", strtotime($raw_date));
        $date_from_day   = date("d", strtotime($raw_date));

        $date_from = date('Y-m-01', strtotime("$date_from_year-$date_from_month-01"));

        if ($date_from_day > 1) {
            $date_from = date('Y-m-01', strtotime($date_from . ' +1 month'));
        }

        $date_to = date('Y-m-t', strtotime($date_from));

        $tbl_leave_used = DB::connection("intra_payroll")
            ->table("tbl_leave_used")
            ->leftJoin("tbl_employee", "tbl_employee.id", "tbl_leave_used.emp_id")
            ->leftJoin("tbl_leave_types", "tbl_leave_types.id", "tbl_leave_used.leave_source_id")
            ->where("tbl_leave_used.leave_status", "APPROVED")
            ->whereDate("tbl_leave_used.leave_date_from", "<=", $date_to)
            ->whereDate("tbl_leave_used.leave_date_to", ">=", $date_from)
            ->select(
                "tbl_leave_used.*",
                "tbl_leave_types.leave_type",
                "tbl_employee.first_name",
                "tbl_employee.last_name"
            );
        if ($role_id === 4) {
            // HR Group D
            $tbl_leave_used->whereIn("tbl_employee.id", function ($query) {
                $query->select("id")
                    ->from("tbl_employee")
                    ->where("hr_group", "group_d");
            });

        } elseif ($role_id === 5) {
            // HR Group B, C, E
            $tbl_leave_used->whereIn("tbl_employee.id", function ($query) {
                $query->select("id")
                    ->from("tbl_employee")
                    ->whereIn("hr_group", ["group_b", "group_c", "group_e"]);
            });

        } elseif ($role_id === 14) {
            // HR Group B, C
            $tbl_leave_used->whereIn("tbl_employee.id", function ($query) {
                $query->select("id")
                    ->from("tbl_employee")
                    ->whereIn("hr_group", ["group_b", "group_c"]);
            });

        } elseif ($role_id === 15) {
            // HR Group C, E
            $tbl_leave_used->whereIn("tbl_employee.id", function ($query) {
                $query->select("id")
                    ->from("tbl_employee")
                    ->whereIn("hr_group", ["group_c", "group_e"]);
            });
        }


        $tbl_leave_used = $tbl_leave_used->get();

        $data_days = [];

        foreach ($tbl_leave_used as $leave_used) {

            $begin = new DateTime($leave_used->leave_date_from);
            $end   = new DateTime(date('Y-m-d', strtotime($leave_used->leave_date_to . ' +1 day')));
            $interval  = new DateInterval('P1D');
            $daterange = new DatePeriod($begin, $interval, $end);

            $leave_detail = $leave_used->leave_type . ' - ' .
                            $leave_used->last_name . ', ' .
                            $leave_used->first_name;

            foreach ($daterange as $date_leave) {

                $leave_date = $date_leave->format('Y-m-d');

                if ($leave_date < $date_from || $leave_date > $date_to) {
                    continue;
                }

                $data_days[] = [
                    'title' => $leave_detail,
                    'start' => $leave_date,
                    'color' => '#159ca1',
                    'extendedProps' => [
                        'name' => $leave_used->last_name . ', ' . $leave_used->first_name,
                        'type' => $leave_used->leave_type
                    ]
                ];
            }
        }

        return response()->json($data_days);
    }
    public function get_leavesXX(Request $request, $start_date)
    {
        $user = Auth::user();
        $role_id = $user->role_id;
        $date_from = $request->month_view;
        $date_from_year = date("Y", strtotime($date_from));
        $date_from_month = date("m", strtotime($date_from));
        $date_from_day = date("d", strtotime($date_from));
        if ($date_from_day > 1) {
            $date_from = date("Y-m-01", strtotime($date_from . ' +1 month'));
        }
        $date_to = date("Y-m-t", strtotime($date_from));
        $data_days = [];
        $leave_dates = [];
        // Get leave records (approved only)
        $tbl_leave_used_query = DB::connection("intra_payroll")->table("tbl_leave_used")
            ->where("leave_status", "APPROVED")
            ->where("leave_year", $date_from_year);
        if ($role_id === 4) {
            // HR Group A
            $tbl_leave_used_query->whereIn("emp_id", function ($query) {
                $query->select("id")
                    ->from("tbl_employee")
                    ->where("hr_group", "group_d");
            });
        } elseif ($role_id === 5) {
            // HR Group B
            $tbl_leave_used_query->whereIn("emp_id", function ($query) {
                $query->select("id")
                    ->from("tbl_employee")
                    ->whereIn("hr_group", ["group_b","group_c","group_e"]);
            });
        } elseif ($role_id === 14) {
            // HR Group B,C
            $tbl_leave_used_query->whereIn("emp_id", function ($query) {
                $query->select("id")
                    ->from("tbl_employee")
                    ->whereIn("hr_group", ["group_b","group_c"]);
            });
        } elseif ($role_id === 15) {
            // HR Group C
            $tbl_leave_used_query->whereIn("emp_id", function ($query) {
                $query->select("id")
                    ->from("tbl_employee")
                    ->whereIn("hr_group", ["group_c","group_e"]);
            });
        } 
        // elseif ($role_id === 22) {
        //     // HR Group E
        //     $tbl_leave_used_query->whereIn("emp_id", function ($query) {
        //         $query->select("id")
        //             ->from("tbl_employee")
        //             ->where("hr_group", "group_e");
        //     });
        // }
        $tbl_leave_used = $tbl_leave_used_query->get();
        foreach ($tbl_leave_used as $leave_used) {
            $begin = new DateTime($leave_used->leave_date_from);
            $leave_to_date = date("Y-m-d", strtotime($leave_used->leave_date_to . ' +1 day'));
            $tbl_employees = DB::connection("intra_payroll")->table("tbl_employee")
                ->select("id", "first_name", "last_name")
                ->where("is_active", 1)
                ->where("id", $leave_used->emp_id)
                ->first();
            if (!$tbl_employees) {
                continue;
            }
            $tbl_leave_source = DB::connection("intra_payroll")->table("tbl_leave_credits")
                ->where("leave_id", $leave_used->leave_source_id)
                ->first();
            if (!$tbl_leave_source) {
                continue;
            }
            $tbl_leave_type = DB::connection("intra_payroll")->table("tbl_leave_types")
                ->select("leave_type")
                ->where("id", $leave_used->leave_source_id)
                ->first();
            // Skip if no leave type found
            if (!$tbl_leave_type) {
                continue;
            }
            $end = new DateTime($leave_to_date);
            $interval = new DateInterval('P1D'); // 1-day interval
            $daterange = new DatePeriod($begin, $interval, $end);
            $leave_detail = $tbl_leave_type->leave_type . ' - ' . $tbl_employees->last_name . ', ' . $tbl_employees->first_name;
            foreach ($daterange as $date_leave) {
                $leave_dates[$date_leave->format("Y-m-d")][] = [
                    'title' => $leave_detail,
                    'start' => $date_leave->format("Y-m-d"),
                    'color' => "#159ca1",
                    'extendedProps' => [
                        'name' => $tbl_employees->last_name . ', ' . $tbl_employees->first_name,
                        'type' => $tbl_leave_type->leave_type
                    ]
                ];
            }
        }
        // 🔹 Combine multiple events per day
        foreach ($leave_dates as $date => $events) {
            foreach ($events as $event) {
                $data_days[] = $event;
            }
        }
        return response()->json($data_days);
    }
    public function get_leaves_old(Request $request, $start_date){
        $date_from = $request->month_view;
        $date_from_year = date("Y", strtotime($date_from));
        $date_from_month = date("m", strtotime($date_from));
        $date_from_day = date("d", strtotime($date_from));
        if($date_from_day > 1){
            $date_from = date("Y-m-01", strtotime($date_from . ' +1 month'));
        }
        $date_to = date("Y-m-t", strtotime($date_from));
        $data_days = array();
        $cur_day = $date_from;
    
        $leave_dates = array();
    
        $tbl_leave_used = DB::connection("intra_payroll")->table("tbl_leave_used")
            ->where("leave_status", "APPROVED")
            ->where("leave_year", $date_from_year)
            ->get();
    
        foreach($tbl_leave_used as $leave_used){
            $begin = new DateTime($leave_used->leave_date_from);
            $leave_to_date = date("Y-m-d", strtotime($leave_used->leave_date_to .' +1 day'));
    
            $tbl_employees = DB::connection("intra_payroll")->table("tbl_employee")
                ->select("id", "first_name", "last_name")
                ->where("is_active", 1)
                ->where("id", $leave_used->emp_id)
                ->first();
    
            $tbl_leave_source = DB::connection("intra_payroll")->table("tbl_leave_credits")
                ->where("leave_id", $leave_used->leave_source_id)
                ->first();
    
            $tbl_leave_type = DB::connection("intra_payroll")->table("tbl_leave_types")
                ->select("leave_type")
                ->where("id", $tbl_leave_source->leave_id)
                ->first();
    
            $end = new DateTime($leave_to_date);
            $interval = new DateInterval('P1D'); // 1 day interval
            $daterange = new DatePeriod($begin, $interval, $end);
    
            $leave_detail = $tbl_leave_type->leave_type . ' - ' . $tbl_employees->last_name . ', ' . $tbl_employees->first_name;
            
            foreach($daterange as $date_leave){
                $leave_dates[$date_leave->format("Y-m-d")][] = [
                    'title' => $leave_detail,
                    'start' => $date_leave->format("Y-m-d"),
                    'color' => "#159ca1",
                    'extendedProps' => [
                        'name' => $tbl_employees->last_name . ', ' . $tbl_employees->first_name,
                        'type' => $tbl_leave_type->leave_type
                    ]
                ];
            }
        }
    
        // Ensure multiple events for the same date
        $data_days = [];
        foreach($leave_dates as $date => $events) {
            foreach($events as $event) {
                $data_days[] = $event;
            }
        }
    
        return response()->json($data_days);
    }  
}
