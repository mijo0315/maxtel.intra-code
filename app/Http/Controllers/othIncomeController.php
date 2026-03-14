<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use DB;
use Storage;
use Yajra\DataTables\DataTables;

class othIncomeController extends Controller
{

    function search_multi_array($array, $key, $value) {
        foreach ($array as $subarray) {
            if (isset($subarray[$key]) && $subarray[$key] == $value) {
                return $subarray;
            }
        }
        return null;
    }
    

    public function income_management(){
   

            $tbl_employee = json_decode(json_encode(DB::connection("intra_payroll")->table("tbl_employee")->where("is_active", 1)->orderby("last_name")->orderby("first_name")->orderby("middle_name")->get()),true);
            $lib_bir_non_taxable = json_decode(json_encode(DB::connection("intra_payroll")->table("lib_bir_non_taxable")->where("is_active", 1)->get()),true);
            
            $lib_bir_taxable = json_decode(json_encode(DB::connection("intra_payroll")->table("lib_bir_taxable")->where("is_active", 1)->get()),true);
            $lib_income = json_decode(json_encode(DB::connection("intra_payroll")->table("lib_income")->where("is_active", 1)->get()),true);
       
            return view("other_income.index")
                ->with("lib_bir_non_taxable", $lib_bir_non_taxable)
                ->with("lib_bir_taxable", $lib_bir_taxable)
                ->with("lib_income", $lib_income)
                ->with("tbl_employee", $tbl_employee)
            ;  
    }

    public function employee_array(){
        $tbl_employee = DB::connection("intra_payroll")->table("tbl_employee")->where("is_active", 1)->orderby("last_name")->orderby("first_name")->orderby("middle_name")->get();

        return json_encode($tbl_employee);
        

    }

    public function get_employee_list(Request $request){
        $role_id = Auth::user()->role_id;
        $tbl_employee = DB::connection("intra_payroll")->table("tbl_employee")->where("is_active", 1);
        
        if(Auth::user()->access[$request->page]["user_type"] == "employee" && $role_id != 11){
            $tbl_employee = $tbl_employee->where("id",Auth::user()->company["linked_employee"]["id"]);
        }
        
        $tbl_employee = $tbl_employee->orderby("last_name")->orderby("first_name")->orderby("middle_name")->get();

        return json_encode($tbl_employee);
        

    }

    public function emp_other_income_data(Request $request){
       $data = DB::connection("intra_payroll")->table("tbl_income_file")
            ->where("income_id", $request->id)
            ->get();

        return json_encode($data);


    }

    public function save_other_income(Request $request){
        $insert_array = array();
        $other_income_id = $request->other_income_id;
        $amount = $request->amount;
        $amount_2 = $request->amount_2;
        $amount_3 = $request->amount_3;
        $amount_4 = $request->amount_4;
        $amount_5 = $request->amount_5;
        
        $income_type = $request->income_type;
        $user_encoder = Auth::user()->id;
        $todate = date("Y-m-d H:i:s");
        $selected_emp = $request->select_emp;
        if($request->select_emp == "custom_emp"){
          	if($request->delimited != null){
            $list = explode("|", $request->delimited);
           	
                foreach($list as $emp_list){
                  
                    $data = explode(";", $emp_list);
                   
                    $ins_arr = array(
                        "emp_id" => $data[0],
                        "income_id" => $other_income_id,
                        "income_type" => $data[1],
                        "date_created" => $todate,
                        "user_id" => $user_encoder,
                        "selected_emp" => $selected_emp
                    );
                   
                    if($data[1] == "DAILY" || $data[1] == "MONTHLY" ){
                        $ins_arr["amount"] = $data[2];
                        $ins_arr["amount_2"] = "0";
                        $ins_arr["amount_3"] = "0";
                        $ins_arr["amount_4"] = "0";
                        $ins_arr["amount_5"] = "0";
                    }elseif($data[1] == "SEMI"){
                        $ins_arr["amount"] = $data[2];
                        $ins_arr["amount_2"] = $data[3];
                        $ins_arr["amount_3"] = "0";
                        $ins_arr["amount_4"] = "0";
                        $ins_arr["amount_5"] = "0";
                    }elseif($data[1] == "WEEKLY"){
                        $ins_arr["amount"] = $data[2];
                        $ins_arr["amount_2"] = $data[3];
                        $ins_arr["amount_3"] = $data[4];
                        $ins_arr["amount_4"] = $data[5];
                        $ins_arr["amount_5"] = $data[6];
                        
                    }

                    array_push($insert_array, $ins_arr); 
                }
              
             }
          
          
          
              
        }elseif($request->select_emp == "all_emp"){
            $list = DB::connection("intra_payroll")->table("tbl_employee")
            ->select("id as emp_id", DB::raw("CONCAT('".$other_income_id."') as income_id"), DB::raw("CONCAT('".$amount."') as amount"), DB::raw("CONCAT('".$amount_2."') as amount_2"), DB::raw("CONCAT('".$amount_3."') as amount_3"), DB::raw("CONCAT('".$amount_4."') as amount_4"), DB::raw("CONCAT('".$amount_5."') as amount_5"), DB::raw("CONCAT('".$income_type."') as income_type") , DB::raw("CONCAT('".$todate."') as date_created"), DB::raw("CONCAT('".$user_encoder."') as user_id"), DB::raw("CONCAT('".$selected_emp."') as selected_emp") )
            
            ->where("is_active", 1)
            ->get();
            $insert_array = json_decode(json_encode($list), true);
        }elseif($request->select_emp == "daily_emp"){
            $list = DB::connection("intra_payroll")->table("tbl_employee")
            ->select("id as emp_id", DB::raw("CONCAT('".$other_income_id."') as income_id"), DB::raw("CONCAT('".$amount."') as amount"), DB::raw("CONCAT('".$amount_2."') as amount_2"), DB::raw("CONCAT('".$amount_3."') as amount_3"), DB::raw("CONCAT('".$amount_4."') as amount_4"), DB::raw("CONCAT('".$amount_5."') as amount_5"), DB::raw("CONCAT('".$income_type."') as income_type") , DB::raw("CONCAT('".$todate."') as date_created"), DB::raw("CONCAT('".$user_encoder."') as user_id"), DB::raw("CONCAT('".$selected_emp."') as selected_emp") )
            ->where('salary_type', 'DAILY')
            ->where("is_active", 1)
            ->get();
            $insert_array = json_decode(json_encode($list), true);

        }elseif($request->select_emp == "monthly_emp"){
            $list = DB::connection("intra_payroll")->table("tbl_employee")
            ->select("id as emp_id", DB::raw("CONCAT('".$other_income_id."') as income_id"), DB::raw("CONCAT('".$amount."') as amount"), DB::raw("CONCAT('".$amount_2."') as amount_2"), DB::raw("CONCAT('".$amount_3."') as amount_3"), DB::raw("CONCAT('".$amount_4."') as amount_4"), DB::raw("CONCAT('".$amount_5."') as amount_5"), DB::raw("CONCAT('".$income_type."') as income_type") , DB::raw("CONCAT('".$todate."') as date_created"), DB::raw("CONCAT('".$user_encoder."') as user_id"), DB::raw("CONCAT('".$selected_emp."') as selected_emp") )
            ->where('salary_type', 'MONTHLY')
            ->where("is_active", 1)
            ->get();
            $insert_array = json_decode(json_encode($list), true);
        }else{
            return json_encode("error");
        }

        DB::beginTransaction();
        try {
            
            DB::connection("intra_payroll")->table("tbl_income_file")
                ->where("income_id", $other_income_id)
                ->delete();
			
          	if(count($insert_array)>0){
            	DB::connection("intra_payroll")->table("tbl_income_file")  
                	->insert($insert_array); 
            }
            

            DB::commit();
            return json_encode("success");
        } catch (\Throwable $th) {
            DB::rollback();

            return json_encode($th->getMessage());
        }


       

     
    }

    



    public function oth_library_list(Request $request){
        $page_permission = Auth::user()->access[$request->page]["access"];
        $lib_bir_non_taxable = json_decode(json_encode(DB::connection("intra_payroll")->table("lib_bir_non_taxable")->where("is_active", 1)->get()),true);
            
        $lib_bir_taxable = json_decode(json_encode(DB::connection("intra_payroll")->table("lib_bir_taxable")->where("is_active", 1)->get()),true);


        $lib_bir_taxable = json_decode(json_encode(DB::connection("intra_payroll")->table("lib_bir_taxable")->where("is_active", 1)->get()),true);
        

        $data = DB::connection("intra_payroll")->table("lib_income")
            ->orderBy("name")
            ->get();

                $data = collect($data);

                return Datatables::of($data)
                ->addColumn('is_active', function($row){
                    if($row->is_active){
                        $btn = "<a class='btn btn-sm btn-success' > Active </a>";
                    }else{
                        $btn = "<a class='btn btn-sm btn-warning' > Inactive </a>";
                    }
                    return $btn;
                })

                ->addColumn('is_regular', function($row){
                    if($row->is_regular){
                        $btn = "<a class='btn btn-sm btn-success' > Regular </a>";
                    }else{
                        $btn = "<a class='btn btn-sm btn-warning' > One Time </a>";
                    }
                    return $btn;
                })
                  
                  
                
                ->addColumn('date_updated', function($row){
                  return date("F j, Y", strtotime($row->date_updated));
                })
                
                ->addColumn('tax_item', function($row) use ($lib_bir_non_taxable, $lib_bir_taxable){
                        if($row->tax_type == "NON"){
                            if($row->tax_item != "0"){
                                $data = $this->search_multi_array($lib_bir_non_taxable, "id", $row->tax_item);
                                return $data["name"];
                            }else{
                                return "Hidden Income";
                            }
                        }elseif($row->tax_type == "TAX"){
                            if($row->tax_item != "0"){
                                $data = $this->search_multi_array($lib_bir_taxable, "id", $row->tax_item);
                                return $data["name"];
                            }else{
                                return "Basic Pay";
                            }
                        }else{
                            return "Unknown";
                        }


                })
                ->addColumn('tax_type', function($row){
                    if($row->tax_type == "NON"){
                        return "NON TAXABLE";
                    }else if($row->tax_type == "TAX"){
                        return "TAXABLE";
                    }else{
                        return "Unknown";
                    }
                  })
                
                 ->addColumn('action', function($row) use ($page_permission){
                    if(preg_match("/U/i", $page_permission)){
                        $btn = "<a class='btn btn-sm btn-info' 
                        data-toggle='modal' 
                        data-id='".$row->id."'
                        data-code='".$row->code."'
                        data-name='".$row->name."'
                        
                        data-description='".$row->description."'
                        data-is_regular='".$row->is_regular."'
                        
                        data-tax_type='".$row->tax_type."'
                        data-tax_item='".$row->tax_item."'

                        data-is_active='".$row->is_active."'
                        data-target='#oth_library_modal'
                        
                        > Edit </a>";

                        if($row->is_regular){




                            $btn .= "<a class='ml-1 btn btn-sm btn-success' 
                            data-toggle='modal' 
                            data-id='".$row->id."'
                            data-code='".$row->code."'
                            data-name='".$row->name."'
                            
                            data-description='".$row->description."'
                            data-is_regular='".$row->is_regular."'
                            
                            data-tax_type='".$row->tax_type."'
                            data-tax_item='".$row->tax_item."'
    
                            data-is_active='".$row->is_active."'
                            data-target='#add_edit_employee'
                            
                            > Add/Edit Employee </a>";
                        }
                        // add delete in income
                        $btn .= " <button 
                        class='btn btn-sm btn-danger'
                        onclick='delete_income(" . $row->id . ")'
                        >
                        Delete
                        </button>";

                    }else{
                        $btn = "";
                    }
    
                   
                        return $btn;
                 })

                 ->rawColumns(['is_active','action','is_regular'])
                ->make(true);

    }

    function save_library(Request $request){
        $save_library = $request->save_library;
        $lib_code = $request->lib_code;
        $lib_name = $request->lib_name;
        $lib_desc = $request->lib_desc;
        $tax_type = $request->tax_type;
        $tax_item_non = $request->tax_item_non;
        $tax_item_tax = $request->tax_item_tax;
        $is_active = $request->is_active;
        $lib_is_regular = $request->lib_is_regular;

        if($tax_type == "NON"){
            $item = $tax_item_non;
        }elseif($tax_type == "TAX"){
            $item = $tax_item_tax;
        }else{
            $item = 0;
        }


        DB::beginTransaction();
        try {
            if($save_library == "new"){
                $check = DB::connection("intra_payroll")->table("lib_income")
                    ->where("code", $lib_code)
                    ->first();
                if($check != null){
                    return json_encode("duplicate");
                }
            
                
                $insert_array = array(
                    "code" => $lib_code,
                    "name" => $lib_name,
                    "description" => $lib_desc,
                    "is_regular" => $lib_is_regular,

                    "tax_type" => $tax_type,
                    "tax_item" => $item,
                    "is_active" => $is_active,
                    "date_created" => date("Y-m-d H:i:s"),
                    "user_id" => Auth::user()->id
                );
                DB::connection("intra_payroll")->table("lib_income")
                ->insert($insert_array);
            }else{
                $check = DB::connection("intra_payroll")->table("lib_income")
                    ->where("code", $lib_code)
                    ->where("id", "!=",$save_library)
                    ->first();
                if($check != null){
                    return json_encode("duplicate");
                }

                $update_array = array(
                    "code" => $lib_code,
                    "name" => $lib_name,
                    "description" => $lib_desc,
                    "is_regular" => $lib_is_regular,
                    "tax_type" => $tax_type,
                    "tax_item" => $item,
                    "is_active" => $is_active,
                    "user_id" => Auth::user()->id
                );
                DB::connection("intra_payroll")->table("lib_income")
                    ->where("id", $save_library)
                    ->update($update_array);


            }




            DB::commit();
            return json_encode("true");
        } catch (\Throwable $th) {
            DB::rollback();
            return json_encode($th->getMessage());
            //throw $th;
        }


        


    }
    // add delete in income
    public function delete_income(Request $request){
        DB::beginTransaction();
            try {
                DB::connection("intra_payroll")->table("lib_income")
                    ->where("id", $request->id)
                    ->delete();
                DB::commit();
                return json_encode("Deleted");
            } catch (\Throwable $th) {
                DB::rollback();
                return json_encode($th->getMessage());
            }

    }

    public function allowance_request_tbl(Request $request){
        $role_id = Auth::user()->role_id;
        $page_permission = Auth::user()->access[$request->page]["access"];
        $lib_bir_non_taxable = json_decode(json_encode(DB::connection("intra_payroll")->table("lib_bir_non_taxable")->where("is_active", 1)->get()),true);
            
        $lib_bir_taxable = json_decode(json_encode(DB::connection("intra_payroll")->table("lib_bir_taxable")->where("is_active", 1)->get()),true);


        $lib_bir_taxable = json_decode(json_encode(DB::connection("intra_payroll")->table("lib_bir_taxable")->where("is_active", 1)->get()),true);
        

        $data = DB::connection("intra_payroll")->table("tbl_allowance_request")
            ->leftjoin('tbl_employee','tbl_employee.id','tbl_allowance_request.emp_id')
            ->select('tbl_employee.emp_code','tbl_employee.first_name','tbl_employee.middle_name','tbl_employee.last_name','tbl_allowance_request.*');
        if(Auth::user()->access[$request->page]["user_type"] == "employee" && $role_id != 11){
           $data =  $data->where("tbl_allowance_request.emp_id",Auth::user()->company["linked_employee"]["id"]);
        }elseif($role_id == 11){
            $data =  $data->whereIn("tbl_allowance_request.status",["FILED","APPROVED","REJECT"]);
        }elseif($role_id == 15){
            $data =  $data->whereIn("tbl_allowance_request.status",["1st_Approved","APPROVED","REJECT"]);
        }
            
            $data =  $data->get();

                $data = collect($data);

                return Datatables::of($data)
                ->addColumn('name', function($row){
                    return '('.$row->emp_code.') '.$row->last_name.', '.$row->first_name.' '.$row->middle_name;
                })

                ->addColumn('amount', function($row){
                    return number_format($row->amount,2);
                })
                ->addColumn('date_filed', function($row){
                    return date("F d,Y", strtotime($row->date_filed));
                })
                ->addColumn('remarks', function($row){
                    return $row->remarks ?? '';
                })
                ->addColumn('status', function($row){
                    $status = $row->status;
                    if ($status === "FILED") {
                        $status = "<span class='badge badge-warning'>WAITING FOR 1ST APPROVAL</span>";
                    } elseif ($status === "1st_Approved") {
                        $status = "<span class='badge badge-info'>FOR FINAL APPROVAL</span>";
                    } elseif ($status === "APPROVED") {
                        $status = "<span class='badge badge-success'>APPROVED</span>";
                    }
                    return $status;
                })
                
                 ->addColumn('action', function($row) use ($page_permission, $role_id, $request){
                    if(preg_match("/U/i", $page_permission)){
                        $class_disabled = '';
                        if(Auth::user()->access[$request->page]["user_type"] == "employee" && $role_id != 11){
                            if($row->status == "APPROVED"){
                                $class_disabled = 'disabled';
                            }
                        }
                        $btn = "<a class='btn btn-sm btn-info $class_disabled' 
                        data-toggle='modal' 
                        data-id='".$row->id."'
                        data-emp_id='".$row->emp_id."'
                        data-amount='".$row->amount."'
                        data-date_filed='".$row->date_filed."'
                        data-remarks='".$row->remarks."'
                        data-status='".$row->status."'
                        data-target='#allowance_request_modal'
                        > Edit </a>";
                        // add delete in income
                        $btn .= " <button 
                        class='btn btn-sm btn-danger'
                        onclick='delete_allowance(" . $row->id . ")'
                        $class_disabled>
                        Delete
                        </button>";

                    }else{
                        $btn = "";
                    }
    
                   
                        return $btn;
                 })

                 ->rawColumns(['action','status'])
                ->make(true);

    }

    public function save_allowance_request(Request $request)
    {
        $emp_id  = $request->allowance_emp_name;
        $amount  = $request->amount;
        $status  = $request->status;
        $date_filed  = $request->date_filed;
        $remarks  = $request->remarks;

        DB::beginTransaction();
        try {

            if ($request->id === "new") {

                // check if employee already filed allowance request
                $chk_if_already_filed = DB::connection("intra_payroll")
                    ->table("tbl_allowance_request")
                    ->where("emp_id", $emp_id)
                    ->where("date_filed", $date_filed)
                    ->exists();

                if ($chk_if_already_filed) {
                    DB::rollback();
                    return json_encode("Employee have already submitted allowance for this date.");
                }

                $ins_data = [
                    "emp_id"    => $emp_id,
                    "income_id" => 26, // operational allowance
                    "amount"    => $amount,
                    "date_filed" => $date_filed,
                    "remarks" => $remarks,
                    "status"    => $status
                ];

                DB::connection("intra_payroll")
                    ->table("tbl_allowance_request")
                    ->insert($ins_data);

            } else {

                $upd_data = [
                    "emp_id" => $emp_id,
                    "amount" => $amount,
                    "date_filed" => $date_filed,
                    "remarks" => $remarks,
                    "status" => $status
                ];

                DB::connection("intra_payroll")
                    ->table("tbl_allowance_request")
                    ->where("id", $request->id)
                    ->update($upd_data);
            }

            DB::commit();
            return json_encode("Filing Success");

        } catch (\Throwable $th) {
            DB::rollback();
            return json_encode($th->getMessage());
        }
    }

    // add delete in allowance
    public function delete_allowance(Request $request){
        DB::beginTransaction();
            try {
                DB::connection("intra_payroll")->table("tbl_allowance_request")
                    ->where("id", $request->id)
                    ->delete();
                DB::commit();
                return json_encode("Deleted");
            } catch (\Throwable $th) {
                DB::rollback();
                return json_encode($th->getMessage());
            }

    }

}
