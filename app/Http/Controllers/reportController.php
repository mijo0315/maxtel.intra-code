<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use DB;
use Yajra\DataTables\DataTables;
use App\Exports\exportExcel;
use App\Exports\PayrollExportExcel;
use Maatwebsite\Excel\Facades\Excel;
use Dompdf\Dompdf;

class reportController extends Controller
{
    public function report_management(){
        $role_id = Auth::user()->role_id;
        $employee_list = DB::connection("intra_payroll")->table("tbl_employee");
        if ($role_id === 4) { // HR Group D
            $employee_list = $employee_list->where("hr_group", "group_d");
        } elseif ($role_id === 5) { // HR Group B,C,E
            $employee_list = $employee_list->whereIn("hr_group", ["group_b","group_c","group_e"]);
        } elseif ($role_id === 14) { // HR Group B,C
            $employee_list = $employee_list->whereIn("hr_group", ["group_b","group_c"]);
        } elseif ($role_id === 15) { // HR Group C
            $employee_list = $employee_list->whereIn("hr_group", ["group_c","group_e"]);
        }
        $employee_list = $employee_list->get();
        return view("report.index")
            ->with("employee_list", $employee_list)
        ;
    }
    public function report_pay_list(Request $request){
           // update payroll
            $role_id = Auth::user()->role_id;
           if(Auth::user()->access[$request->page]["user_type"] == "employee"){
            $emp_id = Auth::user()->company["linked_employee"]["id"];
            $payroll_data = DB::connection("intra_payroll")->table("tbl_payroll");
            if($role_id == 11){  //dorcas hr group d at e
                $payroll_data = $payroll_data->where(function($query) {
                    $query->where("payroll_status", "CLOSE");
                })
                ->whereIn("hr_group", ["group_d","group_e"]);
            }else{
                $payroll_data = $payroll_data->where(function($query) {
                    $query->where("payroll_status", "CLOSE");
                })
                ->where('employee', 'LIKE', '%|' . $emp_id . '|%');
            }
            $payroll_data = $payroll_data->orderBy("date_updated", "DESC")
            ->get();
        }else{
           
            $payroll_data = DB::connection("intra_payroll")->table("tbl_payroll");
            if ($role_id === 4) { // HR Group D E
                $payroll_data = $payroll_data->whereIn("hr_group", ["group_d","group_e"])
                ->where("payroll_status", "CLOSE");
            } elseif ($role_id === 5 || $role_id === 15) { // HR Group C,E
                $payroll_data = $payroll_data->whereIn("hr_group", ["group_c","group_e"])
                ->where("payroll_status", "CLOSE");
            } elseif ($role_id === 14) { // HR Group B,C
                $payroll_data = $payroll_data->whereIn("hr_group", ["group_b","group_c"])
                 ->where("payroll_status", "CLOSE");
            } elseif ($role_id === 22) { // HR Group D
                $payroll_data = $payroll_data->where("hr_group", "group_d")
                 ->where("payroll_status", "CLOSE");
            }else{
                $payroll_data = $payroll_data->where("payroll_status", "COMPUTED")
                ->orWhere("payroll_status", "FINALIZE")
                ->orWhere("payroll_status", "CLOSE");
            }
            // $payroll_data = $payroll_data->where("payroll_status", "COMPUTED")
            // ->orWhere("payroll_status", "FINALIZE")
            // ->orWhere("payroll_status", "CLOSE")
            $payroll_data = $payroll_data->orderBy("date_updated", "DESC")
            ->get();
        }
        $data = collect($payroll_data);
        $page_permission = Auth::user()->access[$request->page]["access"];
        $process_type_array = array(
            "RP" => "Regular",
            "13" => "13th Month",
            "BP" => "Bonus",
            "SP" => "Special",
            "LC" => "Leave Credit",
        );
        return Datatables::of($data)
            ->addColumn('name', function($row){
                return "(".$row->code.") ".$row->name;
            })
            ->addColumn('info', function($row) use ($process_type_array){
                $info = $row->target_month." ".$row->target_year."<br>";
                
                $info.= $process_type_array[$row->process_type]." (".$row->type.")";
                
                    return $info;
            })
            ->addColumn('status', function($row){
                if($row->payroll_status == "OPEN"){
                    $btn = "<label > Open </label>";
                }elseif($row->payroll_status == "ADDED"){
                    $btn = "<label > ADDED </label>";
                }elseif($row->payroll_status == "PROCESS"){
                    $btn = "<label > TIMECARD <br> PROCESSED </label>";
                }elseif($row->payroll_status == "COMPUTED"){
                    $btn = "<label > PAYROLL <br> COMPUTED </label>";
                }elseif($row->payroll_status == "FINALIZE"){
                    $btn = "<label > FOR APPROVAL </label>";
                }
                else{
                    $btn = "<label > PAYROLL COMPLETED </label>";
                }
                return $btn;
            })
        ->addColumn('action', function($row) use ($page_permission, $request, $role_id ){
            $btn = "";
            if(preg_match("/U/i", $page_permission)){
                if(Auth::user()->access[$request->page]["user_type"] != "employee"){
                    $btn .= "<button  onclick='export_payroll($row->id)' class='export_payroll btn btn-success btn-sm w-100 mb-1'>Payroll Report</button> <br>";
                    $btn .= "<button class='btn btn-info btn-sm w-100' onclick='download_payslip($row->id)'>Payslip</button>";
                    
                }else{
                    //employee
                    if($role_id == 11){  //dorcas
                        $btn .= "<button  onclick='export_payroll($row->id)' class='export_payroll btn btn-success btn-sm w-100 mb-1'>Payroll Report</button> <br>";
                        $btn .= "<button class='btn btn-info btn-sm w-100' onclick='download_payslip($row->id)'>Payslip</button>";
                    }else{
                        $btn .= "<button class='btn btn-info btn-sm w-100' onclick='download_payslip($row->id)'>Payslip</button>";
                    }
                    
                }
            }
          
            return $btn;
        })
        ->rawColumns(['action','status','info'])
        ->make(true);
    }
    private function search_to_array($array, $key, $value) {
        $results = array();
    
        if (is_array($array)) {
            if (isset($array[$key]) && $array[$key] == $value) {
                $results[] = $array;
            }
    
            foreach ($array as $subarray) {
                $results = array_merge($results, $this->search_to_array($subarray, $key, $value));
            }
        }
    
        return $results;
    }
    public function payroll_payslip(Request $request){
        $dompdf = new Dompdf();
        $pay_id = $request->pay_id;
        $payroll_data = DB::connection("intra_payroll")->table("tbl_payroll")
            ->where('id', $pay_id)
            ->first();
        $income_list = json_decode(json_encode(DB::connection("intra_payroll")->table("tbl_payroll_income")->where("payroll_id", $pay_id)->get()), true);
            $lib_income = json_decode(json_encode(DB::connection("intra_payroll")->table("lib_income")->get()), true);
        $deduction_list = json_decode(json_encode(DB::connection("intra_payroll")->table("tbl_payroll_deduction")->where("payroll_id", $pay_id)->get()), true);
            $lib_loans = json_decode(json_encode(DB::connection("intra_payroll")->table("lib_loans")->get()), true);
        $tbl_employee = json_decode(json_encode(DB::connection("intra_payroll")->table("tbl_employee")->get()), true);
        
        $income_header = array(
            "BP" => "Basic Pay",
            "RH" => "Regular Holiday",
            "ROT" => "Regular Overtime",
            "SOT" => "Special Overtime",
            "13TH" => "13th Month Pay",
            "SH" => "Special Holiday",
            "ND" => "Night Differential",
            "RD" => "Rest Day",
            "RDRH" => "Rest Day RH",
            "RDSH" => "Rest Day SH",
            "RDOT" => "Rest Day OT",
            "RD_RH_OT" => "Rest Day RH OT",
            "RD_SH_OT" => "Rest Day SH OT",
            "SH_OT" => "Special Holiday OT",
            "RH_OT" => "Regular Holiday OT",
            "LEAVE_CREDIT" => "Leave Credit Conversion",
        );
        $deduction_header = array(
            "SSS" => "SSS",
            "HDMF" => "PAGIBIG I (HDMF)",
            "PH" => "PhilHealth",
            "TAX" => "WIHHOLDING TAX",
            "LATE" => "LATE",
            "ABSENT" => "ABSENT",
            "UT" => "UNDERTIME",
            "Leave Without Pay" => "Leave Without Pay"
        );
        if(Auth::user()->access["report_management"]["user_type"] != "employee"){
            $reg_emp_id = "all";
        }else{
            $reg_emp_id = Auth::user()->company["linked_employee"]["id"];
        }
        $payslip_data = array();
        
        if($payroll_data != null){
            $emp_data = explode(";",$payroll_data->employee);
            foreach($emp_data as $emp){
                $payslip_income = array();
                $payslip_deduction = array();
                
                $emp_id = str_replace("|","", $emp);
                
                if($reg_emp_id != "all"){
                    if($reg_emp_id != $emp_id){continue;}
                }
                
                $employee = $this->search_to_array($tbl_employee, "id",$emp_id);
                $departmentId = $employee[0]['department'] ?? null;
                $departmentData = $departmentId
                    ? DB::connection("intra_payroll")
                        ->table("tbl_department")
                        ->select("department")
                        ->where("id", $departmentId)
                        ->first()
                    : null;
                
                $departmentName = $departmentData->department ?? null;
                $branchId = $payroll_data->branch_id 
                    ?? ($employee[0]['branch_id'] ?? null);
                $branchName = null;
                if ($branchId) {
                    $branchData = DB::connection("intra_payroll")
                        ->table("tbl_branch")
                        ->select("branch")
                        ->where("id", $branchId)
                        ->first();
                    $branchName = $branchData->branch ?? null;
                }
                if(count($employee)==1){
                    $emp_data = array(
                        "id" => $employee[0]["id"],
                        "department" => $departmentName,
                        "branch" => $branchName,
                        "employee_code" =>  strtoupper($employee[0]["emp_code"]),
                        "last_name" =>  strtoupper($employee[0]["last_name"]),
                        "first_name" =>  strtoupper($employee[0]["first_name"]),
                        "middle_name" =>  substr(strtoupper($employee[0]["middle_name"]),0,1) ,
                        "ext_name" =>  strtoupper($employee[0]["ext_name"])
                    );
                    // $total_incomes = 0;
                    $income =  $this->search_to_array($income_list, "emp_id", $emp_id);
                    foreach($income as $inc){
                        $income_type = $inc["type"];
                        $income_name =  $this->search_to_array($lib_income, "id", str_replace("R_","", $inc["type"]));
                        if(count($income_name) == 1){
                            $inc_name = $income_name[0]["name"];
                        }else{
                            if(isset($income_header[$inc["type"]])){
                                $inc_name = $income_header[$inc["type"]];
                            }else{
                                $inc_name = $income_type;
                            }
                            
                        }
                        // $total_incomes += floatval($inc["amount"]);
                       $inc_data = array(
                            "emp_id" => $emp_id,
                            "income_name" => $inc_name,
                            "amount" => $inc["amount"]
                       );
                    
                       $payslip_income[] = $inc_data;
                    }
                    usort($payslip_income, function ($a, $b) use ($lib_income) {
                        if ($a['income_name'] === 'Basic Pay') return -1;
                        if ($b['income_name'] === 'Basic Pay') return 1;
                    
                        // check if income_name is in lib_income
                        $a_in_lib = in_array($a['income_name'], array_column($lib_income, 'name'));
                        $b_in_lib = in_array($b['income_name'], array_column($lib_income, 'name'));
                    
                        if ($a_in_lib && !$b_in_lib) return 1;
                        if (!$a_in_lib && $b_in_lib) return -1;
                    
                        return 0;
                    });
                    
                    // $total_deduction = 0;
                    $deduction =  $this->search_to_array($deduction_list, "emp_id", $emp_id);
                    foreach($deduction as $ded){
                        $ded_type = $ded["type"];
                        $deduction_name =  $this->search_to_array($lib_loans, "id", str_replace("R_","", $ded["type"]));
                        if(count($deduction_name) == 1){
                            $ded_name = $deduction_name[0]["name"];
                        }else{
                            if(isset($deduction_header[$ded["type"]])){
                                $ded_name = $deduction_header[$ded["type"]];
                            }else{
                                $ded_name = $ded_type;
                            }
                            
                        }
                        // $total_deduction += floatval($ded["amount"]);
                       $ded_data = array(
                            "emp_id" => $emp_id,
                            "deduction_name" => $ded_name,
                            "amount" => $ded["amount"]
                       );
                      
                       array_push($payslip_deduction, $ded_data);
                    }
                }else{
                    continue;
                }
                $emp_data["incomes"] = $payslip_income;
                $emp_data["deductions"] = $payslip_deduction;
          
                array_push($payslip_data, $emp_data);
            }
            
        }
        $company_info = array(
            "company_name" => Auth::user()->company["company_name"],
            "logo_main" => asset(Auth::user()->company["logo_main"]),
            "address" => Auth::user()->company["address"],
            
        );
        
        $cover_from = $payroll_data->cover_from; //2025-01-01
        $cover_to = $payroll_data->cover_to; //2025-01-19
        $period = date('F d', strtotime($cover_from)) . '-' . date('d, Y', strtotime($cover_to));
      
        //dd($payslip_data);
        $dompdf->loadHtml(view('report.payslip', compact('company_info','payslip_data','period')));
        $dompdf->setPaper('A4', 'portrait');
        // $dompdf->setOptions(['isRemoteEnabled' => true]);
        $dompdf->render();
        
        return $dompdf->stream('document.pdf');
    }
    public function exportSalaryExpenses(Request $request){
        $date_from = $request->date_from;
      	$date_to = $request->date_to;
      
        $tbl_payroll = DB::connection("intra_payroll")->table("tbl_payroll")
            ->whereBetween("cover_to", [$date_from,$date_to])
            ->get();
        
        $header = array();
        array_push($header, "Payroll Name");
        array_push($header, "Target Month");
        array_push($header, "Target Year");
        array_push($header, "Payroll Type");
        array_push($header, "Processing Type");
        array_push($header, "Total incomes");
        array_push($header, "Total Deductions (ABSENT, LATE, LEAVE W/OUT PAY, UNDERTIME)");
        
        array_push($header, "Total Salary");
        $process_type_array = array(
            "RP" => "Regular Payroll",
            "13" => "13th Month",
            "BP" => "Bonus",
            "SP" => "Special",
        );
        $excel_data = array();
        foreach($tbl_payroll as $prl){
            $excel = array();
            
            $excel["name"] = $prl->name;
            $excel["target_month"] = $prl->target_month;
            $excel["target_year"] = $prl->target_year;
            $excel["payroll_type"] = $process_type_array[$prl->process_type];
            $excel["processing_type"] = $prl->type;
                $total_incomes = DB::connection("intra_payroll")->table("tbl_payroll_income")
                    ->where("payroll_id", $prl->id)
                    ->sum("amount");
                $total_deduction = DB::connection("intra_payroll")->table("tbl_payroll_deduction")
                    ->where("payroll_id", $prl->id)
                    ->whereIn("type", ["ABSENT", "LATE", "Leave Without Pay", "UT"])
                    ->sum("amount");
                $total_salary = $total_incomes - $total_deduction;
                $excel["total_incomes"] = number_format($total_incomes,2);
                $excel["total_deductions"] = number_format($total_deduction,2);
                $excel["salary_total"] = number_format($total_salary,2);
            array_push($excel_data, $excel);
        }
        
        $excel_data = collect($excel_data);
        return Excel::download(new exportExcel($excel_data,$header), "Salary_Expenses_as_of_".$request->from.".xlsx");
        
    }
    public function exportLoanBalances(Request $request){
        $emp_id = $request->emp_id;
        $as_of_date = $request->as_of_date;
            if($emp_id == "all"){
                $emp_id = "%";
            }
            $tbl_loans = DB::connection("intra_payroll")->table("tbl_loan_file as l")
                ->select("l.*", "e.last_name", "e.first_name", "e.middle_name", "e.emp_code", "m.name")
                ->join("tbl_employee as e", "e.id", "=", "l.emp_id")
                ->leftjoin("lib_loans as m", "m.id", "l.loan_id")
                ->where("l.date_updated", "<=", $as_of_date)
                ->where("l.emp_id", "LIKE", $emp_id)
                ->get();
            
            $header = array();
            
            array_push($header, "Employee Code");
            array_push($header, "Last Name");
            array_push($header, "First Name");
            array_push($header, "Middle Name");
            array_push($header, "Loan Name");
            array_push($header, "Total Loan");
            array_push($header, "Payment Type");
            array_push($header, "Ammortization");
            array_push($header, "Running Balance");
            array_push($header, "Loan Status");
            array_push($header, "Notes");
            array_push($header, "Loan Date From");
            array_push($header, "Loan Date To");
            array_push($header, "Filed date");
            
            
            $arr_status = array();
            $arr_status[0] = "Applied";
            $arr_status[1] = "Approved";
            $arr_status[2] = "Denied";
            $arr_status[3] = "Hold/Pause";
            $excel_data = array();
            foreach($tbl_loans as $loan){
                $excel = array();
                
                $excel["emp_code"] = $loan->emp_code;
                $excel["last_name"] = $loan->last_name;
                $excel["first_name"] = $loan->first_name;
                $excel["middle_name"] = $loan->middle_name;
                $excel["loan_name"] = $loan->name;
                $excel["total_loan"] = $loan->total_amount;
                $excel["payment_type"] = $loan->payment_type;
                $excel["amount_to_pay"] = $loan->amount_to_pay;
                $excel["balance"] = $loan->balance;
                $excel["loan_status"] = $arr_status[$loan->loan_status];
                $excel["notes"] = $loan->notes;
                $excel["date_from"] = $loan->date_from;
                $excel["date_to"] = $loan->date_to;
                $excel["date_created"] = $loan->date_created;
                
                array_push($excel_data, $excel);
            }
            
            $excel_data = collect($excel_data);
            return Excel::download(new exportExcel($excel_data,$header), "Loan_Info_as_of_".$request->from.".xlsx");
    }
    
    public function exportRegIncome(Request $request){
        if(Auth::user()->access["report_management"]["user_type"] != "employee"){
            $income = DB::connection("intra_payroll")->table("tbl_income_file")
                ->whereBetween("date_created", [$request->from, $request->to])
                ->orderBy("emp_id")
                ->get();
        }else{
             $income = DB::connection("intra_payroll")->table("tbl_income_file")
                ->whereBetween("date_created", [$request->from, $request->to])
                ->where("emp_id", Auth::user()->company["linked_employee"]["id"])
                ->orderBy("emp_id")
                ->get();
        }
        
            $tbl_employee = DB::connection("intra_payroll")->table("tbl_employee")->get();
            $tbl_employee = json_decode(json_encode($tbl_employee), true);
    
            $lib_income = DB::connection("intra_payroll")->table("lib_income")->get();
            $lib_income = json_decode(json_encode($lib_income), true);
            $header = array();
            
            array_push($header, "Employee Code");
            array_push($header, "Last Name");
            array_push($header, "First Name");
            array_push($header, "Middle Name");
            array_push($header, "Extension Name");
            array_push($header, "Income");
            array_push($header, "Income Type");
            
            array_push($header, "Amount / 1");
            array_push($header, "Amount / 2");
            array_push($header, "Amount / 3");
            array_push($header, "Amount / 4");
            array_push($header, "Amount / 5");
            array_push($header, "Date Created");
            
            
            
            $excel_data = array();
            foreach($income as $inc){
                $excel = array();
                $emp_data = $this->search_to_array($tbl_employee, "id",$inc->emp_id);
                if(count($emp_data)==1){
                    $excel["emp_code"] = $emp_data[0]["emp_code"];
                    $excel["last_name"] = $emp_data[0]["last_name"];
                    $excel["first_name"] = $emp_data[0]["first_name"];
                    $excel["middle_name"] = $emp_data[0]["middle_name"];
                    $excel["ext_name"] = $emp_data[0]["ext_name"];
                    
                }else{
                    $excel["emp_code"] = "N/A";
                    $excel["last_name"] = "N/A";
                    $excel["first_name"] = "N/A";
                    $excel["middle_name"] = "N/A";
                    $excel["ext_name"] = "N/A";
                }
                $lib_data = $this->search_to_array($lib_income, "id",$inc->income_id);
                if(count($lib_data)==1){
                    $excel["income"] = $lib_data[0]["name"];
                }else{
                    // dd($lib_income);
                    // dd($inc->income_id);
                    $excel["income"] = "N/A";
                }
                $excel["inc_type"] = $inc->income_type;
                $excel["amount_1"] = $inc->amount;
                $excel["amount_2"] = $inc->amount_2;
                $excel["amount_3"] = $inc->amount_3;
                $excel["amount_4"] = $inc->amount_4;
                $excel["amount_5"] = $inc->amount_5;
                $excel["date_created"] = $inc->date_created;
               
                
                array_push($excel_data, $excel);
            }
            
            $excel_data = collect($excel_data);
            return Excel::download(new exportExcel($excel_data,$header), "Regular Income".$request->from."_".$request->to.".xlsx");
    }
    
    public function exporEmpList(Request $request){
        $lib_position = json_decode(json_encode(DB::connection("intra_payroll")->table("lib_position")->get()),true);
        $tbl_branch = json_decode(json_encode(DB::connection("intra_payroll")->table("tbl_branch")->get()),true);
        $tbl_department = json_decode(json_encode(DB::connection("intra_payroll")->table("tbl_department")->get()),true);
        $lib_designation = json_decode(json_encode(DB::connection("intra_payroll")->table("lib_designation")->get()),true);
        
        if(Auth::user()->access["report_management"]["user_type"] != "employee"){
            $tbl_employee = DB::connection("intra_payroll")->table("tbl_employee")
                ->whereBetween("date_created", [$request->from, $request->to])
                ->orderBy("last_name")
                ->orderBy("first_name")
                ->orderBy("middle_name")
                ->orderBy("ext_name")
                ->get();
        }else{
             $tbl_employee = DB::connection("intra_payroll")->table("tbl_employee")
                ->whereBetween("date_created", [$request->from, $request->to])
                 ->where("id", Auth::user()->company["linked_employee"]["id"])
                ->orderBy("last_name")
                ->orderBy("first_name")
                ->orderBy("middle_name")
                ->orderBy("ext_name")
                ->get();
        }
          
            $header = array();
            
            array_push($header, 'EMP CODE');
            array_push($header, 'BIO ID');
            array_push($header, 'FIRST NAME');
            array_push($header, 'MIDDLE NAME');
            array_push($header, 'LAST NAME');
            array_push($header, 'EXT NAME');
            array_push($header, 'CONTACT');
            array_push($header, 'POSITION');
            array_push($header, 'TYPE');
            array_push($header, 'RATE');
            array_push($header, 'IS MINIMUM WAGE EARNER');
            array_push($header, 'ADDRESS');
            array_push($header, 'department');
            array_push($header, 'branch_id');
            array_push($header, 'designation');
            array_push($header, 'DIRECT');
            array_push($header, 'AGENCY');
            array_push($header, 'SSS NO');
            array_push($header, 'PH NO');
            array_push($header, 'HDMF NO');
            array_push($header, 'TIN NO');
            array_push($header, 'Date Enrolled');
            
            $yesno = array(
                "0" => "NO",
                "1" => "YES",  
            );
            
            $excel_data = array();
            foreach($tbl_employee as $emp){
                $excel = array();
                
                $excel["emp_code"] = $emp->emp_code;
                $excel["bio_id"] = $emp->bio_id;
                $excel["first_name"] = $emp->first_name;
                $excel["middle_name"] = $emp->middle_name;
                $excel["last_name"] = $emp->last_name;
                $excel["ext_name"] = $emp->ext_name;
                $excel["contact_no"] = $emp->contact_no;
                $lib = $this->search_to_array($lib_position, "id", $emp->position_id);
                if(count($lib)==1){ $excel["position_id"] = $lib[0]["name"];}else{ $excel["position_id"] = "N/A";}
                $excel["salary_type"] = $emp->salary_type;
                $excel["salary_rate"] = $emp->salary_rate;
                $excel["is_mwe"] = $yesno[$emp->is_mwe];
                $excel["address"] = $emp->address;
                
                $lib = $this->search_to_array($tbl_department, "id", $emp->department);
                if(count($lib)==1){ $excel["department"] = $lib[0]["department"];}else{ $excel["department"] = "N/A";}
              
                $lib = $this->search_to_array($tbl_branch, "id", $emp->branch_id);
                if(count($lib)==1){ $excel["branch_id"] = $lib[0]["branch"];}else{ $excel["branch_id"] = "N/A";}
              
                $lib = $this->search_to_array($lib_designation, "id", $emp->designation);
                if(count($lib)==1){ $excel["designation"] = $lib[0]["name"];}else{ $excel["designation"] = "N/A";}
                $excel["is_direct"] = $yesno[$emp->is_direct];
                $excel["agency_name"] = $emp->agency_name;
                $excel["sss_number"] = $emp->sss_number;
                $excel["philhealth_number"] = $emp->philhealth_number;
                $excel["hdmf_number"] = $emp->hdmf_number;
                $excel["tin_number"] = $emp->tin_number;
                $excel["date_created"] = $emp->date_created;
                
                array_push($excel_data, $excel);
            }
            
            $excel_data = collect($excel_data);
            return Excel::download(new exportExcel($excel_data,$header), "Employee_list".$request->from."_".$request->to.".xlsx");
    }
    public function exportTimeKeeping(Request $request){
        if(Auth::user()->access["report_management"]["user_type"] != "employee"){
            $timecard = DB::connection("intra_payroll")->table("tbl_timekeeping")
                ->whereBetween("date_target", [$request->from, $request->to])
                ->orderBy("emp_id")
                ->get();
        }else{
             $timecard = DB::connection("intra_payroll")->table("tbl_timekeeping")
                ->whereBetween("date_target", [$request->from, $request->to])
                ->where("emp_id", Auth::user()->company["linked_employee"]["id"])
                ->orderBy("emp_id")
                ->get();
        }
            $tbl_employee = DB::connection("intra_payroll")->table("tbl_employee")->get();
            $tbl_employee = json_decode(json_encode($tbl_employee), true);
    
            $header = array();
            
            array_push($header, "Employee Code");
            array_push($header, "Last Name");
            array_push($header, "First Name");
            array_push($header, "Middle Name");
            array_push($header, "Extension Name");
            array_push($header, "Regular Work/Required hours");
            array_push($header, "Lates");
            array_push($header, "Regular Overtime");
            array_push($header, "Special OverTime");
            array_push($header, "Night Differential");
            array_push($header, "Regular Leave");
            array_push($header, "Sick Leave");
            array_push($header, "Special Leave");
            array_push($header, "Regular Holiday");
            array_push($header, "Special Holiday");
            
            $excel_data = array();
            foreach($timecard as $tc){
                $excel = array();
                $emp_data = $this->search_to_array($tbl_employee, "id",$tc->emp_id);
                if(count($emp_data)==1){
                    $excel["emp_code"] = $emp_data[0]["emp_code"];
                    $excel["last_name"] = $emp_data[0]["last_name"];
                    $excel["first_name"] = $emp_data[0]["first_name"];
                    $excel["middle_name"] = $emp_data[0]["middle_name"];
                    $excel["ext_name"] = $emp_data[0]["ext_name"];
                    
                }else{
                    $excel["emp_code"] = "N/A";
                    $excel["last_name"] = "N/A";
                    $excel["first_name"] = "N/A";
                    $excel["middle_name"] = "N/A";
                    $excel["ext_name"] = "N/A";
                }
                $excel["date"] = $tc->date_target;
                $excel["regular work"] = $tc->regular_work;
                $excel["lates"] = $tc->lates;
                $excel["regular_ot"] = $tc->regular_ot;
                $excel["special_ot"] = $tc->special_ot;
                $excel["night_diff"] = $tc->night_diff;
                $excel["regular_leave"] = $tc->regular_leave;
                $excel["sick_leave"] = $tc->sick_leave;
                $excel["special_leave"] = $tc->special_leave;
                $excel["regular_holiday"] = $tc->regular_holiday;
                $excel["special_holiday"] = $tc->special_holiday;
                
                array_push($excel_data, $excel);
            }
            
            $excel_data = collect($excel_data);
            return Excel::download(new exportExcel($excel_data,$header), "TimeCard".$request->from."_".$request->to.".xlsx");
    }
    
    public function exportStatutoryReport(Request $request){
        $month_year = $request->month_year;
        $type = $request->type;
        $month = date("M", strtotime($month_year));
        $year = date("Y", strtotime($month_year));
        $tbl_employee = DB::connection("intra_payroll")->table("tbl_employee")->get();
        $tbl_employee = json_decode(json_encode($tbl_employee), true);
        $header = array();
        
        if(Auth::user()->access["report_management"]["user_type"] != "employee"){
            $payroll_list = DB::connection("intra_payroll")->table("tbl_payroll")
            ->where("target_month", "LIKE",$month)
            ->where("target_year", $year)
            ->get();
            $reg_emp_id = "all";
        }else{
             $emp_id_logged = "|".Auth::user()->company["linked_employee"]["id"]."|";
            $reg_emp_id = Auth::user()->company["linked_employee"]["id"];
            $payroll_list = DB::connection("intra_payroll")->table("tbl_payroll")
            ->where("target_month", "LIKE",$month)
            ->where("target_year", $year)
            ->where("employee", "LIKE","%".$emp_id_logged."%")
            ->get();
        }
       
            array_push($header, "Payroll Name");
            array_push($header, "Month");
            array_push($header, "Year");
            
            array_push($header, "Employee Code");
            array_push($header, "Last Name");
            array_push($header, "First Name");
            array_push($header, "Middle Name");
            array_push($header, "Extension Name");
            array_push($header, $type);
                //  dd($payroll_list);
            $excel_data = array();
            foreach($payroll_list as $payroll){
                $lib_deduction =  DB::connection("intra_payroll")->table("tbl_payroll_deduction")
                    ->where("payroll_id", $payroll->id)
                    ->where("type", $type)
                    ->get();
                    foreach($lib_deduction as $ded){
                        $excel = array();
                        if($reg_emp_id != "all"){
                            if($ded->emp_id != $reg_emp_id){continue;}
                        } 
                        $excel["payname"] = $payroll->name;
                        $excel["month"] = $payroll->target_month;
                        $excel["year"] = $payroll->target_year;
                        $emp_data = $this->search_to_array($tbl_employee, "id",$ded->emp_id);
                        if(count($emp_data)==1){
                            $excel["emp_code"] = $emp_data[0]["emp_code"];
                            $excel["last_name"] = $emp_data[0]["last_name"];
                            $excel["first_name"] = $emp_data[0]["first_name"];
                            $excel["middle_name"] = $emp_data[0]["middle_name"];
                            $excel["ext_name"] = $emp_data[0]["ext_name"];
                            
                        }else{
                            $excel["emp_code"] = "N/A";
                            $excel["last_name"] = "N/A";
                            $excel["first_name"] = "N/A";
                            $excel["middle_name"] = "N/A";
                            $excel["ext_name"] = "N/A";
    
                        }
                        $excel[$type] = $ded->amount;
                        array_push($excel_data, $excel);
                    }
                
                    
            }
            
          
            $excel_data = collect($excel_data);
            return Excel::download(new exportExcel($excel_data,$header), "Statutory_".$type."_".$month."_".$year.".xlsx");
    }
    public function exportPayrollReport(Request $request)
    {
        
        $payroll_id = $request->payid;
        $payroll = DB::connection("intra_payroll")->table("tbl_payroll")
            ->where("id", $payroll_id)
            ->first();
            
        if($payroll != null){
            $tbl_employee = DB::connection("intra_payroll")->table("tbl_employee")->get();
            $tbl_employee = json_decode(json_encode($tbl_employee), true);
            $lib_position = json_decode(json_encode(DB::connection("intra_payroll")->table("lib_position")->get()),true);
            $tbl_branch = json_decode(json_encode(DB::connection("intra_payroll")->table("tbl_branch")->get()),true);
            $tbl_department = json_decode(json_encode(DB::connection("intra_payroll")->table("tbl_department")->get()),true);
            $lib_designation = json_decode(json_encode(DB::connection("intra_payroll")->table("lib_designation")->get()),true);
            
            $income_field = array();
            $deduct_field = array();
            $header = array();
            //INCOMES
            $data_incomes = DB::connection("intra_payroll")->table("tbl_payroll_income")->where("payroll_id", $payroll->id)->get();
            $data_incomes = json_decode(json_encode($data_incomes),true);
            
            $incomes = DB::connection("intra_payroll")->table("tbl_payroll_income")
                ->select("type")
                ->where("payroll_id", $payroll->id)
                ->groupBy("type")
                ->get();
                
            $income_header = array(
                "BP" => "Basic Pay",
                "RH" => "Regular Holiday",
                "ROT" => "Regular Overtime",
                "SOT" => "Special Overtime",
                "13TH" => "13th Month Pay",
                "SH" => "Special Holiday",
                "ND" => "Night Differential",
                "RD" => "Rest Day",
                "RDRH" => "Rest Day RH",
                "RDSH" => "Rest Day SH",
                "RDOT" => "Rest Day OT",
                "RD_RH_OT" => "Rest Day RH OT",
                "RD_SH_OT" => "Rest Day SH OT",
                "SH_OT" => "Special Holiday OT",
                "RH_OT" => "Regular Holiday OT",
                "LEAVE_CREDIT" => "Leave Credit Conversion",
            );
            foreach($incomes as $inc_type){
                $income_field[$inc_type->type] = $inc_type->type;
                if($inc_type != "BP" || $inc_type != "RH"
                    || $inc_type != "ROT" || $inc_type != "SOT"
                    || $inc_type != "13TH" || $inc_type != "SH"
                    || $inc_type != "ND" || $inc_type != "RD"
                    || $inc_type != "RDRH" || $inc_type != "RDSH"
                    || $inc_type != "RDOT" || $inc_type != "RD_RH_OT"
                    || $inc_type != "RD_SH_OT" || $inc_type != "SH_OT"
                    || $inc_type != "RH_OT" || $inc_type != "LEAVE_CREDIT"){
                    $income_header[$inc_type->type] = $inc_type->type;
                }
            }
            $lib_income  = DB::connection("intra_payroll")->table("lib_income")->get();
            $lib_income = json_decode(json_encode($lib_income),true);
           
       
                // array_push($header, "Employee Code");
                // array_push($header, "Employee Name");
                $header = [
                    "Employee Code",
                    "Employee Name",
                    "Basic Pay",
                    "Total Days",
                    "ABSENT",
                    "LATE",
                    "UT",
                    "Total Basic Pay"
                ];
                
                
                //ARRAY HEADER
                foreach($income_field as $column){
                    if ($column === 'BP') continue;
                    $column_id = str_replace("R_","",$column);
                    $col_name = $this->search_to_array($lib_income,"id", $column_id);
                        if(count($col_name)>0){
                           $column = $col_name[0]["name"];
                        }else{
                           $column = $income_header[$column];
                        }    
                    array_push($header, $column);
                }
                array_push($header, "Total Gross Pay");
                
        // DEDUCTION
            $data_deductions = DB::connection("intra_payroll")->table("tbl_payroll_deduction")->where("payroll_id", $payroll->id)->get();
            $data_deductions = json_decode(json_encode($data_deductions),true);
            
            $deductions = DB::connection("intra_payroll")->table("tbl_payroll_deduction")
                ->select("type")
                ->where("payroll_id", $payroll->id)
                ->groupBy("type")
                ->get();
            $deduction_header = array(
                "SSS" => "SSS",
                "HDMF" => "PAGIBIG I (HDMF)",
                "PH" => "PhilHealth",
                "TAX" => "WIHHOLDING TAX",
                "LATE" => "LATE",
                "UT" => "UNDERTIME",
                "Leave Without Pay" => "Leave Without Pay"
            );
             foreach($deductions as $ded_type){
                    $deduct_field[$ded_type->type] = $ded_type->type;
                    if($ded_type != "SSS" || $ded_type != "HDMF"
                        || $ded_type != "PH" || $ded_type != "TAX"
                        || $ded_type != "LATE" || $ded_type != "ABSENT"
                        || $ded_type != "UT" || $ded_type != "Leave Without Pay"
                        ){
                        $deduction_header[$ded_type->type] = $ded_type->type;
                    }
            }
            $lib_loans  = DB::connection("intra_payroll")->table("lib_loans")->get();
            $lib_loans = json_decode(json_encode($lib_loans),true);
           
       
                //ARRAY HEADER
                foreach($deduct_field as $ded_col){
                    if (in_array($ded_col, ['LATE', 'UT', 'ABSENT'])) {
                        continue; // Skip LATE and UT from deductions
                    }
                    $ded_col_id = str_replace("R_","",$ded_col);
                    $ded_col_name = $this->search_to_array($lib_loans,"id", $ded_col_id);
                        if(count($ded_col_name)>0){
                           $column = $ded_col_name[0]["name"];
                        }else{
                           $column = $deduction_header[$ded_col];
                        }    
                    array_push($header, $column);
                }
                array_push($header, "Total Deduction");
            array_push($header, "Net Pay");   
            $excel_data = array();    
            $employee = explode(";",$payroll->employee);
                foreach($employee as $emp){
                    $excel = array();
                    $emp_id = str_replace("|", "", $emp);
                    $emp_data = $this->search_to_array($tbl_employee, "id",$emp_id);
                        if(count($emp_data)==1){
                            
                            $excel["emp_code"] = $emp_data[0]["emp_code"];
                            $excel["emp_name"] = $emp_data[0]["last_name"].','.$emp_data[0]["first_name"].','.$emp_data[0]["middle_name"];
                            
                            // $lib = $this->search_to_array($lib_designation, "id", $emp_data[0]["designation"]);
                            // if(count($lib)==1){ $excel["designation"] = $lib[0]["name"];}else{ $excel["designation"] = "N/A";}
                            // $lib = $this->search_to_array($lib_position, "id", $emp_data[0]["position_id"]);
                            // if(count($lib)==1){ $excel["position"] = $lib[0]["name"];}else{ $excel["position"] = "N/A";}
                            // $lib = $this->search_to_array($tbl_department, "id", $emp_data[0]["department"]);
                            // if(count($lib)==1){ $excel["department"] = $lib[0]["department"];}else{ $excel["department"] = "N/A";}
                            // $lib = $this->search_to_array($tbl_branch, "id", $emp_data[0]["branch_id"]);
                            // if(count($lib)==1){ $excel["branch"] = $lib[0]["branch"];}else{ $excel["branch"] = "N/A";}                          
                          
                          
                        }else{
                            $excel["emp_code"] = "N/A";
                            $excel["emp_name"] = "N/A";
                        }
                   
                    $total_income = 0;
                    $total_deduction = 0;
                    //INCOME
                    $inc_data = $this->search_to_array($data_incomes, "emp_id",$emp_id);
                    // BASIC PAY
                    $basic_pay_amount = 0;
                    $bp_days = 0;
                    $daily_rate = floatval($emp_data[0]["salary_rate"]);
                    $yearly_divisor = $emp_data[0]["yearly_divisor"];
                    if($emp_data[0]["salary_type"] == "MONTHLY"){
                        $emp_yearly_rate = $emp_data[0]["salary_rate"]*12;
                        $daily_rate = $emp_yearly_rate / $yearly_divisor;
                    }
                    $bp = $this->search_to_array($inc_data,"type", "BP");
                    if (count($bp) > 0) {
                        $basic_pay_amount = $bp[0]["amount"];
                        if ($daily_rate > 0) {
                            $bp_days = round($basic_pay_amount / $daily_rate, 2);
                        }
                    }
                    $excel["Basic Pay"] = $basic_pay_amount;
                    $excel["Total Days"] = $bp_days;
                    // ABSENT
                    $deduction_data = $this->search_to_array($data_deductions, "emp_id", $emp_id);
                    $absent_amt = $this->search_to_array($deduction_data, "type", "ABSENT");
                    $absent_amt_val = count($absent_amt) > 0 ? $absent_amt[0]["amount"] : 0;
                    $excel["ABSENT"] = $absent_amt_val;
                    // LATE
                    $late_data = $this->search_to_array($data_deductions, "emp_id", $emp_id);
                    $late_amt = $this->search_to_array($late_data,"type", "LATE");
                    $late_amt_val = count($late_amt) > 0 ? $late_amt[0]["amount"] : 0;
                    $excel["LATE"] = $late_amt_val;
                    // UNDERTIME
                    $ut_amt = $this->search_to_array($late_data,"type", "UT");
                    $ut_amt_val = count($ut_amt) > 0 ? $ut_amt[0]["amount"] : 0;
                    $excel["UT"] = $ut_amt_val;
                    $total_basic_pay = $basic_pay_amount - $absent_amt_val - $late_amt_val - $ut_amt_val;
                    $excel["Total Basic Pay"] = $total_basic_pay;
                    $total_income = $total_basic_pay;
                    foreach($income_field as $key => $dat){
                        if ($dat == "BP") continue;
                        $inc_amount = $this->search_to_array($inc_data,"type", $dat);
                        if(count($inc_amount) > 0){
                            $total_income += $inc_amount[0]["amount"];
                            $excel[$dat] = $inc_amount[0]["amount"];
                        }else{
                            $excel[$dat] = 0;
                        }
                           
                    }
                    $excel['Total Gross Pay'] = $total_income;
                    //DEDUCTION
                    $ded_data = $this->search_to_array($data_deductions, "emp_id",$emp_id);
                    foreach($deduct_field as $key => $dat_ded){
                        if (in_array($dat_ded, ['LATE', 'UT' , 'ABSENT'])) continue;
                        $ded_amount = $this->search_to_array($ded_data,"type", $dat_ded);
                        if(count($ded_amount) > 0){
                            $total_deduction += $ded_amount[0]["amount"];
                            $excel[$dat_ded] = $ded_amount[0]["amount"];
                        }else{
                            $excel[$dat_ded] = 0;
                        }
                           
                    }
                    $excel['Total Deduction'] = $total_deduction;
                    $excel['net_pay'] = $total_income - $total_deduction;
                    array_push($excel_data, $excel);
                }
                $with_rice_allowance = in_array($payroll->branch_id, [59,90,57]); //Bicol,Laguna(Team Dante), Batangas
                $rice_allowance_amount = 1500;
                $excel_data = collect($excel_data);
                $total_net_pay = $excel_data->sum('net_pay');
                if($with_rice_allowance){
                    $rice_row = [];
                    foreach ($header as $head) {
                        if (strtolower($head) === "total deduction") {
                            $rice_row[$head] = "Rice Allowance";
                        }
                        elseif (strtolower($head) === "net pay") {
                            $rice_row[$head] = $rice_allowance_amount;
                        }
                        else{
                            $rice_row[$head] = "";
                        }
                    }
                    $excel_data->push($rice_row);
                    // add allowance to total
                    $total_net_pay += $rice_allowance_amount;
                }
                $total_row = [];
                foreach ($header as $head) {
                    if (strtolower($head) === "net pay") {
                        $total_row[$head] = $total_net_pay;
                    }
                    elseif (strtolower($head) === "employee code") {
                        $total_row[$head] = "TOTAL";
                    }
                    else{
                        $total_row[$head] = "";
                    }
                }
                $excel_data->push($total_row);
                $tbl_site_config = DB::table('tbl_site_config')->first();
                $company_name = $tbl_site_config->company_name ?? 'Company Name';
                $cover_from = $payroll->cover_from;
                $cover_to = $payroll->cover_to;
                $period = 'Period: ' . date('F d', strtotime($cover_from)) . ' - ' . date('F d, Y', strtotime($cover_to));
                $pay_date = 'Pay Date: ' . date('F d, Y');
                return Excel::download(
                    new PayrollExportExcel($excel_data, $header, $company_name, $period, $pay_date),
                    $payroll->name . ".xlsx"
                );
        }
        
    }
    public function export_tc(Request $request)
    {
        
        $payroll_id = $request->payid;
        $payroll = DB::connection("intra_payroll")->table("tbl_payroll")
            ->where("id", $payroll_id)
            ->first();
            
        if($payroll != null){
            $tbl_employee = DB::connection("intra_payroll")->table("tbl_employee")->get();
            $tbl_employee = json_decode(json_encode($tbl_employee), true);
            $lib_position = json_decode(json_encode(DB::connection("intra_payroll")->table("lib_position")->get()),true);
            $tbl_branch = json_decode(json_encode(DB::connection("intra_payroll")->table("tbl_branch")->get()),true);
            $tbl_department = json_decode(json_encode(DB::connection("intra_payroll")->table("tbl_department")->get()),true);
            $lib_designation = json_decode(json_encode(DB::connection("intra_payroll")->table("lib_designation")->get()),true);
            
            $header = array();
           
                array_push($header, "Employee Code");
                array_push($header, "Last Name");
                array_push($header, "First Name");
                array_push($header, "Middle Name");
                array_push($header, "Extension Name");
                
                array_push($header, "Designation");
                array_push($header, "Position");
                array_push($header, "Department");
                array_push($header, "Branch");
                array_push($header, "Date");
                array_push($header, "Regular hours");
                array_push($header, "Absent");
                array_push($header, "Lates");
                array_push($header, "Undertime");
                array_push($header, "Regular OT");
                array_push($header, "Special OT");
                array_push($header, "Night Differential");
                array_push($header, "ND OT");
                array_push($header, "Regular Leave");
                array_push($header, "Sick Leave");
                array_push($header, "Special Leave");
                array_push($header, "Regular Holiday");
                array_push($header, "RH OT");
                array_push($header, "Special Holiday");
                array_push($header, "SH OT");
                array_push($header, "RD");
                array_push($header, "RD OT");
                array_push($header, "RD RH");
                array_push($header, "RD OT RH");
                array_push($header, "RD SH");
                array_push($header, "RD OT SH");
              
              
               
             
        
            $excel_data = array();    
            $employee = explode(";",$payroll->employee);
                foreach($employee as $emp){
                    $excel = array();
                    $emp_id = str_replace("|", "", $emp);
                    $emp_data = $this->search_to_array($tbl_employee, "id",$emp_id);
                        if(count($emp_data)==1){
                            
                            $excel["emp_code"] = $emp_data[0]["emp_code"];
                            $excel["last_name"] = $emp_data[0]["last_name"];
                            $excel["first_name"] = $emp_data[0]["first_name"];
                            $excel["middle_name"] = $emp_data[0]["middle_name"];
                            $excel["ext_name"] = $emp_data[0]["ext_name"];
                            
                            $lib = $this->search_to_array($lib_designation, "id", $emp_data[0]["designation"]);
                            if(count($lib)==1){ $excel["designation"] = $lib[0]["name"];}else{ $excel["designation"] = "N/A";}
                            $lib = $this->search_to_array($lib_position, "id", $emp_data[0]["position_id"]);
                            if(count($lib)==1){ $excel["position"] = $lib[0]["name"];}else{ $excel["position"] = "N/A";}
                            $lib = $this->search_to_array($tbl_department, "id", $emp_data[0]["department"]);
                            if(count($lib)==1){ $excel["department"] = $lib[0]["department"];}else{ $excel["department"] = "N/A";}
                            $lib = $this->search_to_array($tbl_branch, "id", $emp_data[0]["branch_id"]);
                            if(count($lib)==1){ $excel["branch"] = $lib[0]["branch"];}else{ $excel["branch"] = "N/A";}                          
                          
                          
                        }else{
                            $excel["emp_code"] = "N/A";
                            $excel["last_name"] = "N/A";
                            $excel["first_name"] = "N/A";
                            $excel["middle_name"] = "N/A";
                            $excel["ext_name"] = "N/A";
                            
                            $excel["designation"] = "N/A";
                            $excel["position"] = "N/A";
                            $excel["department"] = "N/A";
                            $excel["branch"] = "N/A";
                        }
                 //PAYROLL INFO
                $date_from = $payroll->cover_from;
                $date_to = $payroll->cover_to;
                        $timecard = DB::connection("intra_payroll")->table("tbl_timekeeping")
                        ->where("payroll_id", $payroll_id)
                        ->where("emp_id", $emp_id)
                        ->whereBetween("date_target", [$date_from, $date_to])
                        ->orderBy("date_target")
                        ->get();
                        foreach($timecard as $tc){
                            $excel['Date']= $tc->date_target;
                            $excel['Regular_hours']= $tc->regular_work;
                            $excel['Absent']= $tc->absent;
                            $excel['Lates']= $tc->lates;
                            $excel['UnderTime']= $tc->undertime;
                            $excel['Regular_OT']= $tc->regular_ot;
                            $excel['Special_OT']= $tc->special_ot;
                            $excel['Night_Differential']= $tc->night_diff;
                            $excel['ND_OT']= $tc->nd_ot;
                            $excel['Regular_Leave']= $tc->regular_leave;
                            $excel['Sick_Leave']= $tc->sick_leave;
                            $excel['Special_Leave']= $tc->special_leave;
                            $excel['Regular_Holiday']= $tc->regular_holiday;
                            $excel['RH_OT']= $tc->rh_ot;
                            $excel['Special_Holiday']= $tc->special_holiday;
                            $excel['SH_OT']= $tc->sh_ot;
                            $excel['RD']= $tc->rd;
                            $excel['RD_OT']= $tc->rd_ot;
                            $excel['RD_RH']= $tc->rd_rh;
                            $excel['RD_OT_RH']= $tc->rd_ot_rh;
                            $excel['RD_SH']= $tc->rd_sh;
                            $excel['RD_OT_SH']= $tc->rd_ot_sh;
                            array_push($excel_data, $excel);
                        }
                   
                }
                $excel_data = collect($excel_data);
                return Excel::download(new exportExcel($excel_data,$header), $payroll->name."_TIMECARD.xlsx");
        }
        
    }
    //export pdf
    public function exportPayrollReportPDF(Request $request) //shit
    {
        $dompdf = new Dompdf();
        $payroll_id = $request->payid;
        $payroll = DB::connection("intra_payroll")->table("tbl_payroll")
            ->where("id", $payroll_id)
            ->first();
        
        if (!$payroll) {
            return back()->with('error', 'Payroll not found');
        }
        $tbl_employee = DB::connection("intra_payroll")->table("tbl_employee")->get();
        $tbl_employee = json_decode(json_encode($tbl_employee), true);
        $lib_position = DB::connection("intra_payroll")->table("lib_position")->get()->keyBy('id')->toArray();
        $tbl_department = DB::connection("intra_payroll")->table("tbl_department")->get()->keyBy('id')->toArray();
        $tbl_company = DB::connection("intra_payroll")->table("tbl_site_config")->get()->keyBy('id')->toArray();
        $lib_income = DB::connection("intra_payroll")->table("lib_income")->get()->keyBy('id');
        
        $lib_loans = DB::connection("intra_payroll")->table("lib_loans")->get()->keyBy('id');
        // Fetch and process incomes
        $income_field = DB::connection("intra_payroll")->table("tbl_payroll_income")
            ->where("payroll_id", $payroll->id)
            ->pluck('type')
            ->unique()
            ->toArray();
        
        $income_header = [
            "BP" => "Basic Pay", 
            "RH" => "Regular Holiday", 
            "ROT" => "Regular Overtime", 
            "SOT" => "Special Overtime", 
            "13TH" => "13th Month Pay", 
            "SH" => "Special Holiday", 
            "ND" => "Night Differential",
            "RD" => "Rest Day",
            "RDRH" => "Rest Day RH",
            "RDSH" => "Rest Day SH",
            "RDOT" => "Rest Day OT",
            "RD_RH_OT" => "Rest Day RH OT",
            "RD_SH_OT" => "Rest Day SH OT",
            "SH_OT" => "Special Holiday OT",
            "RH_OT" => "Regular Holiday OT",
        ];
        
        // Fetch and process deductions
        $deduct_field = DB::connection("intra_payroll")->table("tbl_payroll_deduction")
            ->where("payroll_id", $payroll->id)
            ->pluck('type')
            ->unique()
            ->toArray();
        
        $deduction_header = [
            "SSS" => "SSS", 
            "HDMF" => "PAGIBIG I (HDMF)", 
            "PH" => "PhilHealth", 
            "TAX" => "WITHHOLDING TAX", 
            "LATE" => "LATE", 
            "ABSENT" => "ABSENT",
            "UT" => "UNDERTIME",
            "Leave Without Pay" => "Leave Without Pay"
        ];
        
        // Build header array
        $header = ["Employee Code", "Last Name", "First Name", "Middle Name", "Position", "Department"];
        foreach ($income_field as $inc) {
            
            $column_id = str_replace("R_","",$inc);
            $header[] = $lib_income[$column_id]->name ?? ($income_header[$inc] ?? $inc);
        }
        
        $header[] = "Total Gross Pay";
        foreach ($deduct_field as $ded) {
            $header[] = $lib_loans[$ded]->name ?? ($deduction_header[$ded] ?? $ded);
        }
        $header[] = "Total Deduction";
        $header[] = "Net Pay";
        // Process payroll data
        $payrollData = [];
        foreach (explode(";", $payroll->employee) as $emp) {
           
            $emp_id = str_replace("|", "", $emp);
            $emp_data = collect($tbl_employee)->where("id", $emp_id)->first();
            if (!$emp_data) continue;
            $row = [
                "Employee Code" => $emp_data["emp_code"] ?? "N/A",
                "Last Name" => $emp_data["last_name"] ?? "N/A",
                "First Name" => $emp_data["first_name"] ?? "N/A",
                "Middle Name" => $emp_data["middle_name"] ?? "N/A",
                "Position" => $lib_position[$emp_data["position_id"]]->name ?? "N/A",
                "Department" => $tbl_department[$emp_data["department"]]->department ?? "N/A",
            ];
            
            $total_income = 0;
            foreach ($income_field as $inc) {
                $amount = DB::connection("intra_payroll")->table("tbl_payroll_income")
                    ->where([['payroll_id', $payroll->id], ['emp_id', $emp_id], ['type', $inc]])
                    ->value('amount') ?? 0;
                $total_income += $amount;
                $column_id = str_replace("R_","",$inc);
                
                $header_name = $lib_income[$column_id]->name ?? ($income_header[$inc] ?? $inc);
                $row[$header_name] = $amount;
            }
            $row['Total Gross Pay'] = $total_income;
            
            $total_deduction = 0;
            foreach ($deduct_field as $ded) {
                $amount = DB::connection("intra_payroll")->table("tbl_payroll_deduction")
                    ->where([['payroll_id', $payroll->id], ['emp_id', $emp_id], ['type', $ded]])
                    ->value('amount') ?? 0;
                $total_deduction += $amount;
                $header_name = $lib_loans[$ded]->name ?? ($deduction_header[$ded] ?? $ded);
                $row[$header_name] = $amount;
            }
            $row['Total Deduction'] = $total_deduction;
            $row['Net Pay'] = $total_income - $total_deduction;
            
            $payrollData[] = $row;
        }
        $cover_from = $payroll->cover_from; //2025-01-01
        $cover_to = $payroll->cover_to; //2025-01-19
        $period = date('F d', strtotime($cover_from)) . '-' . date('d, Y', strtotime($cover_to));
        $companyId = 1; // Replace with the actual company ID you want to access
        // Check if the company exists in the array
        if (isset($tbl_company[$companyId])) {
            $company = $tbl_company[$companyId]; // Get the company object
            $companyName = $company->company_name; // Access the company_name property
        } else {
            // Handle the case where the company does not exist
            $companyName = 'N/A'; // or some default value
        }
        $dompdf->loadHtml(view('report.payroll_report', compact('payroll', 'header', 'payrollData','period','companyName')));
        $dompdf->setPaper('A4','landscape');
        // $dompdf->setOptions(['isRemoteEnabled' => true]);
        $dompdf->render();
        
        return $dompdf->stream("Payroll_Report_{$payroll->id}.pdf");
    }
}
