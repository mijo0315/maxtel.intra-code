<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use DB;
use Storage;
use Yajra\DataTables\DataTables;

class loanController extends Controller
{
    function search_multi_array($array, $key, $value) {
        foreach ($array as $subarray) {
            if (isset($subarray[$key]) && $subarray[$key] == $value) {
                return $subarray;
            }
        }
        return null;
    }

    public function delete_loan_employee(Request $request){
            //CHECK LOAN 
            DB::beginTransaction();
            try {
                $check_loan = DB::connection("intra_payroll")->table("tbl_payroll_deduction")
                ->where("type", $request->file_id)
                ->first();
                if($check_loan != null){
                  
                    return json_encode("Loan already processed \n Unable to delete");
                }else{
                    $loan_details = DB::connection("intra_payroll")->table("tbl_loan_file")
                        ->where("id", $request->file_id)
                        ->first();

                        if($loan_details == null){
                            return json_encode("This is already deleted");
                        }else{
                            if($loan_details->is_done == "1" ){
                                return json_encode("This Loan is already paid");
                            }else{
                                DB::connection("intra_payroll")->table("tbl_loan_file")
                                ->where("id", $request->file_id)
                                ->delete();
                                return json_encode("Deletion Success");

                            }
                        }


                }

                DB::commit();
            } catch (\Throwable $th) {
                DB::rollback();
    
                return json_encode($th->getMessage());
            }

           





    }

    public function loan_management(){
   

        $tbl_employee = json_decode(json_encode(DB::connection("intra_payroll")->table("tbl_employee")->where("is_active", 1)->orderby("last_name")->orderby("first_name")->orderby("middle_name")->get()),true);
        $lib_loan = json_decode(json_encode(DB::connection("intra_payroll")->table("lib_loans")->where("is_active", 1)->get()),true);
        



        $lib_loan_type = json_decode(json_encode(DB::connection("intra_payroll")->table("lib_loan_type")->where("is_active", 1)->get()),true);
        $loan_type = DB::table("lib_loans")->where("is_regular", 1)->where("is_active", 1);

        if(Auth::user()->access["loan_management"]["user_type"] == "employee"){
            $loan_type = $loan_type->whereIn("type",["OTH","OP_Allowance","Request_Fund"]);
        }
        
        $loan_type = $loan_type->get(); //update loan

        return view("loan.index")
            ->with("tbl_employee", $tbl_employee)
            ->with("lib_loan", $lib_loan)
            ->with("lib_loan_type", $lib_loan_type)
            ->with("loan_type", $loan_type) //update loan
        ;  
    }

    public function save_loan_library(Request $request){
        if($request->id == "new"){
          $check_code=  DB::connection("intra_payroll")->table("lib_loans")
                ->where("code", $request->lib_code)
                ->first();
            if($check_code != null){
                return json_encode("duplicate");
            }

    
                DB::beginTransaction();
                try {
                    $ins_array = array(
                        "type" => $request->loan_type,
                        "code" => $request->lib_code,
                        "name" => $request->lib_name,
                        "description" => $request->lib_desc,
                        "is_active" => $request->lib_is_active,
                        "is_regular" => $request->is_regular,
                        
                        "user_id" => Auth::user()->id,
                        "date_created" => date("Y-m-d H:i:s")
                    );
                    
                    DB::connection("intra_payroll")->table("lib_loans")
                        ->insert($ins_array);


                    DB::commit();
                    return json_encode("true");
                } catch (\Throwable $th) {
                    DB::rollback();
        
                    return json_encode($th->getMessage());
                }


        }else{
            //update
            $check_code=  DB::connection("intra_payroll")->table("lib_loans")
            ->where("code", $request->lib_code)
            ->where("id", "!=",$request->id)
            ->first();
            if($check_code != null){
                return json_encode("duplicate");
            }   


            DB::beginTransaction();
            try {
                $update_array = array(
                    "type" => $request->loan_type,
                    "code" => $request->lib_code,
                    "name" => $request->lib_name,
                    "description" => $request->lib_desc,
                    "is_active" => $request->lib_is_active,
                    "is_regular" => $request->is_regular,
                    "user_id" => Auth::user()->id
                );
                
                DB::connection("intra_payroll")->table("lib_loans")
                    ->where('id', $request->id)
                    ->update($update_array);


                DB::commit();
                return json_encode("true");
            } catch (\Throwable $th) {
                DB::rollback();
    
                return json_encode($th->getMessage());
            }


        }


    }

    public function loan_files_old(Request $request){
        $page_permission = Auth::user()->access[$request->page]["access"];

        $tbl_employee = json_decode(json_encode(DB::connection("intra_payroll")->table("tbl_employee")->where("is_active", 1)->orderby("last_name")->orderby("first_name")->orderby("middle_name")->get()),true);

        if(Auth::user()->access[$request->page]["user_type"] == "employee"){
            $emp_id = Auth::user()->company["linked_employee"]["id"];
            $data = DB::connection("intra_payroll")->table("tbl_loan_file")
            ->where("loan_id",$request->loan_id)
            ->where("emp_id", $emp_id)
            ->orderBy("date_updated")
            ->get();
        }else{
            $data = DB::connection("intra_payroll")->table("tbl_loan_file")
            ->where("loan_id",$request->loan_id)
            ->orderBy("date_updated")
            ->get();
        }

    

        $data = collect($data);

        return Datatables::of($data)
                ->addColumn('emp_name', function($row) use ($tbl_employee){
                    $data_emp = $this->search_multi_array($tbl_employee, "id", $row->emp_id);
                    if(count($data_emp)>0){
                        return "(".$data_emp["emp_code"].") <br>".$data_emp["last_name"].", ".$data_emp["first_name"];
                    }else{
                        return "";
                    }
                    
                })

                ->addColumn('total_amount', function($row){
                    return number_format($row->total_amount,2);
                })
                ->addColumn('amount_to_pay', function($row){
                    return number_format($row->amount_to_pay,2);
                })
                ->addColumn('balance', function($row){
                    return number_format($row->balance,2);
                })
                // ->addColumn('dates', function($row){
                //     return "<button style='width:5vw;' class='btn btn-success btn-sm' > Start </button> ".  date("F d, Y", strtotime($row->date_from)). "<br><button style='width:5vw;' class='btn btn-danger btn-sm' > End </button> ".date("F d, Y", strtotime($row->date_to));
                // })
                ->addColumn('loan_status', function($row){
                    if($row->is_done == 1){
                        return "<button class='btn btn-success btn-sm' >Loan Done </button>";
                    }else{
                        if($row->loan_status == "0"){
                            return "<button class='btn btn-warning btn-sm' >Waiting to Approve </button>";
                        }elseif($row->loan_status == "1"){
                            return "<button class='btn btn-success btn-sm' >Approved </button>";
                        }elseif($row->loan_status == "2"){
                            return "<button class='btn btn-danger btn-sm' >Denied </button>";
                        }else{
                            return "<button class='btn btn-info btn-sm' > Paused </button>";
                        }


                    }
                    

                })

                
                ->addColumn('action', function($row) use ($page_permission, $tbl_employee, $request){
                    if(preg_match("/U/i", $page_permission)){
                        $btn = "";
                        // $data = $this->search_multi_array($tbl_employee, "id", $row->emp_id);
                        if(Auth::user()->access[$request->page]["user_type"] != "employee"){
                        $data_collection = $row->loan_id.";".$row->id.";".$row->emp_id.';'.$row->payment_type.";".$row->total_amount.";".$row->amount_to_pay.";".$row->date_from.";".$row->date_to.";".$row->notes.";".$row->loan_status.";".$row->variance.";".$row->balance;

                        $btn = "<button class='btn btn-sm btn-info edit_loan_emp' 
                        onclick='edit_loan_info(".'"'.$data_collection.'"'.")'
                        
                        
                        > Edit </button>";

                        // add delete in loan files
                        $loan_id = $row->loan_id;
                        $btn .= " <button
                        class='btn btn-sm btn-danger'
                        onclick='delete_loan_file(" . $row->id . ", \"" . $loan_id . "\")'
                        >
                        Delete
                        </button>";   

                        }
                    }else{
                        $btn = "";
                    }
    
                   
                        return $btn;
                 })
                 ->rawColumns(['action','dates','loan_status',"emp_name"])
                ->make(true);


    }
    public function loan_files(Request $request){
        $page_permission = Auth::user()->access[$request->page]["access"];
        $role_id = Auth::user()->role_id;
        $tbl_employee = json_decode(json_encode(DB::connection("intra_payroll")->table("tbl_employee")->where("is_active", 1)->orderby("last_name")->orderby("first_name")->orderby("middle_name")->get()),true);

        if(Auth::user()->access[$request->page]["user_type"] == "employee" && $role_id != 11){
            $emp_id = Auth::user()->company["linked_employee"]["id"];
            $data = DB::connection("intra_payroll")->table("tbl_loan_file")
            ->where("loan_id",$request->loan_id)
            ->where("emp_id", $emp_id)
            ->orderBy("date_updated")
            ->get();
        }else{
            $data = DB::connection("intra_payroll")->table("tbl_loan_file")
            ->where("loan_id",$request->loan_id);

            // if($role_id == 15){
            //     $data = $data->whereIn('loan_status',[1,2,4]);
            // }else if($role_id == 11){
            //     $data = $data->whereIn('loan_status',[0,1,2]);
            // }
            $data = $data->orderBy("date_updated")
            ->get();
        }

    

        $data = collect($data);

        return Datatables::of($data)
                ->addColumn('emp_name', function($row) use ($tbl_employee){
                    $data_emp = $this->search_multi_array($tbl_employee, "id", $row->emp_id);
                    if(count($data_emp)>0){
                        return "(".$data_emp["emp_code"].") <br>".$data_emp["last_name"].", ".$data_emp["first_name"];
                    }else{
                        return "";
                    }
                    
                })

                ->addColumn('total_amount', function($row){
                    return number_format($row->total_amount,2);
                })
                ->addColumn('amount_to_pay', function($row){
                    return number_format($row->amount_to_pay,2);
                })
                ->addColumn('balance', function($row){
                    return number_format($row->balance,2);
                })
                // ->addColumn('dates', function($row){
                //     return "<button style='width:5vw;' class='btn btn-success btn-sm' > Start </button> ".  date("F d, Y", strtotime($row->date_from)). "<br><button style='width:5vw;' class='btn btn-danger btn-sm' > End </button> ".date("F d, Y", strtotime($row->date_to));
                // })
                ->addColumn('loan_status', function($row){
                    if($row->is_done == 1){
                        return "<button class='btn btn-success btn-sm' >Loan Done </button>";
                    }else{
                        if($row->loan_status == "0"){
                            return "<button class='btn btn-info btn-sm' >For Final Approval </button>";
                        }elseif($row->loan_status == "1"){
                            return "<button class='btn btn-success btn-sm' >Approved </button>";
                        }elseif($row->loan_status == "2"){
                            return "<button class='btn btn-danger btn-sm' >Denied </button>";
                        }elseif($row->loan_status == "3"){
                            return "<button class='btn btn-danger btn-sm' >Paused </button>";
                        }elseif($row->loan_status == "4"){
                            return "<button class='btn btn-info btn-sm' >For Final Approval </button>";
                        }


                    }
                    

                })

                
                ->addColumn('action', function($row) use ($page_permission, $tbl_employee, $request, $role_id){
                    if(preg_match("/U/i", $page_permission)){
                        $btn = "";
                        // $data = $this->search_multi_array($tbl_employee, "id", $row->emp_id);
                        if(Auth::user()->access[$request->page]["user_type"] != "employee" || $role_id == 11){
                        $data_collection = $row->loan_id.";".$row->id.";".$row->emp_id.';'.$row->payment_type.";".$row->total_amount.";".$row->amount_to_pay.";".$row->date_from.";".$row->date_to.";".$row->notes.";".$row->loan_status.";".$row->variance.";".$row->balance;

                        $btn = "<button class='btn btn-sm btn-info edit_loan_emp' 
                        onclick='edit_loan_info(".'"'.$data_collection.'"'.")'
                        
                        
                        > Edit </button>";

                        // add delete in loan files
                        $loan_id = $row->loan_id;
                        $btn .= " <button
                        class='btn btn-sm btn-danger'
                        onclick='delete_loan_file(" . $row->id . ", \"" . $loan_id . "\")'
                        >
                        Delete
                        </button>";   

                        }
                    }else{
                        $btn = "";
                    }
    
                   
                        return $btn;
                 })
                 ->rawColumns(['action','dates','loan_status',"emp_name"])
                ->make(true);


    }
    // update loan
    public function employee_loan_files(Request $request){

        $user_id = Auth::id();
        $emp_id = DB::table('tbl_employee')->where('user_id',$user_id)->first();
        
        if($emp_id){
            $data = DB::table("tbl_loan_file")
            ->leftjoin("lib_loans","tbl_loan_file.loan_id","lib_loans.id")
            ->select("tbl_loan_file.*","lib_loans.name")
            ->where("tbl_loan_file.emp_id", $emp_id->id)
            ->orderBy("tbl_loan_file.date_updated")
            ->get();
      
            $data = collect($data);

            return Datatables::of($data)
                    ->addColumn('emp_name', function($row){
                        return $row->name;
                    })

                    ->addColumn('total_amount', function($row){
                        return number_format($row->total_amount,2);
                    })
                    ->addColumn('amount_to_pay', function($row){
                        return number_format($row->amount_to_pay,2);
                    })
                    ->addColumn('balance', function($row){
                        return number_format($row->balance,2);
                    })
                    // ->addColumn('dates', function($row){
                    //     return "<button style='width:5vw;' class='btn btn-success btn-sm' > Start </button> ".  date("F d, Y", strtotime($row->date_from)). "<br><button style='width:5vw;' class='btn btn-danger btn-sm' > End </button> ".date("F d, Y", strtotime($row->date_to));
                    // })
                    ->addColumn('loan_status', function($row){
                        if($row->is_done == 1){
                            return "<button class='btn btn-success btn-sm' >Loan Done </button>";
                        }else{
                            if($row->loan_status == "0"){
                                return "<button class='btn btn-info btn-sm' >For Final Approval </button>";
                            }elseif($row->loan_status == "1"){
                                return "<button class='btn btn-success btn-sm' >Approved </button>";
                            }elseif($row->loan_status == "2"){
                                return "<button class='btn btn-danger btn-sm' >Denied </button>";
                            }elseif($row->loan_status == "3"){
                                return "<button class='btn btn-info btn-sm' >Paused </button>";
                            }elseif($row->loan_status == "4"){
                                return "<button class='btn btn-info btn-sm' >For Final Approval </button>";
                            }


                        }
                        

                    })
                    ->rawColumns(['action','dates','loan_status',"emp_name"])
                    ->make(true);
        }
    }

    public function view_employee_data(Request $request){
       
        $employee_list = json_decode(json_encode(DB::connection("intra_payroll")->table("tbl_employee")->where("is_active", 1)->where("id", $request->emp_id)->orderby("last_name")->orderby("first_name")->orderby("middle_name")->get()),true);

        return json_encode($employee_list);


    }


    public function save_loan_employee(Request $request){
       

        $data = DB::connection("intra_payroll")->table("tbl_loan_file")
            ->where("emp_id", $request->emp_id)
            ->where("loan_id", $request->loan_id)
            ->first();
        $file_id = $request->id;
        if($data != null){
            $file_id = $data->id;

            $array = array(
                "emp_id" => $request->emp_id,
                "loan_id" => $request->loan_id,
                "total_amount" => $request->principal_amount,
                "amount_to_pay" => $request->deduction_amount,
                "balance" => $request->balance_amount,
                "variance" => $request->payment_variance,
                "payment_type" => $request->payment_type,
                "is_done" => "0",
                "date_from" => $request->start_date,
                "date_to" => $request->end_date,
                "notes" => $request->notes,
                "user_id" => Auth::user()->id,
                "loan_status" => $request->loan_status,
            );

        }else{
            if(Auth::user()->access["loan_management"]["user_type"] == "employee"){

                $loan_status = 0;

            }else{
                $loan_status = 1;
            }


            $array = array(
                "emp_id" => $request->emp_id,
                "loan_id" => $request->loan_id,
                "total_amount" => $request->principal_amount,
                "amount_to_pay" => $request->deduction_amount,
                "balance" => $request->balance_amount,
                "variance" => $request->payment_variance,
                "payment_type" => $request->payment_type,
                "is_done" => "0",
                "date_from" => $request->start_date,
                "date_to" => $request->end_date,
                "notes" => $request->notes,
                "user_id" => Auth::user()->id,
                "loan_status" => $loan_status,
                "date_created" => date("Y-m-d H:i:s")
            );
        }

        DB::beginTransaction();
        try {
            if($file_id == "new"){
                DB::connection("intra_payroll")->table("tbl_loan_file")
                ->insert($array);
            }else{
                DB::connection("intra_payroll")->table("tbl_loan_file")
                ->where("id", $file_id)
                ->update($array);
            }

      


            DB::commit();
            return json_encode("true");
        } catch (\Throwable $th) {
            DB::rollback();

            return json_encode($th->getMessage());
        }


    }

    public function employee_loan_array(Request $request){
        if(Auth::user()->access["loan_management"]["user_type"] == "employee"){
            $emp_id = Auth::user()->company["linked_employee"]["id"];
            //ACTIVE LOANS
            $employee_ids =   DB::connection("intra_payroll")->table("tbl_loan_file")
            ->select("emp_id")
            ->where("loan_id", $request->loan_id)
            ->where("is_done", 0)
            ->where("loan_status", "!=",2)
            ->groupBy("emp_id")
            ->get();
            $employee_ids = json_decode(json_encode($employee_ids), true);

            $employee_list = json_decode(json_encode(DB::connection("intra_payroll")->table("tbl_employee")->where("is_active", 1)->where("id", $emp_id)->whereNotIn("id", $employee_ids)->orderby("last_name")->orderby("first_name")->orderby("middle_name")->get()),true);



        }else{
            $employee_ids =   DB::connection("intra_payroll")->table("tbl_loan_file")
            ->select("emp_id")
            ->where("loan_id", $request->loan_id)
            ->where("is_done", 0)
            ->where("loan_status", "!=",2)
            
            
            ->groupBy("emp_id")
            ->get();
        $employee_ids = json_decode(json_encode($employee_ids), true);

        $employee_list = json_decode(json_encode(DB::connection("intra_payroll")->table("tbl_employee")->where("is_active", 1)->whereNotIn("id", $employee_ids)->orderby("last_name")->orderby("first_name")->orderby("middle_name")->get()),true);



        }
   
        return json_encode($employee_list);

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

    public function loan_library_list(Request $request){
        $page_permission = Auth::user()->access[$request->page]["access"];


        if(Auth::user()->access[$request->page]["user_type"] == "employee"){
            $data = DB::connection("intra_payroll")->table("lib_loans")
            ->where('is_regular', 1)
            ->orderBy("name")
            ->get();
        }else{
            $data = DB::connection("intra_payroll")->table("lib_loans")
            ->orderBy("name")
            ->get();
        }

        $tbl_loan_file = DB::connection("intra_payroll")->table("tbl_loan_file")
            ->where("is_done", 0)
            ->where("loan_status", "!=",2)
            ->get();
        $tbl_loan_file = json_decode(json_encode($tbl_loan_file), true);

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
                ->addColumn('action', function($row) use ($page_permission, $request, $tbl_loan_file){
                    if(preg_match("/U/i", $page_permission)){
                        $btn = "";
                        if(Auth::user()->access[$request->page]["user_type"] != "employee"){
                            $btn = "<a class='btn btn-sm btn-info' 
                            data-toggle='modal' 
                            data-id='".$row->id."'
                            data-code='".$row->code."'
                            data-name='".$row->name."'
                            data-type='".$row->type."'
                            data-description='".$row->description."'
                            data-is_regular='".$row->is_regular."'
                            data-is_active='".$row->is_active."'
                            data-target='#loan_lib_modal'
                            > Edit </a>";
                        }
                        

                   
                        if($row->is_regular == 1){

                            if(Auth::user()->access[$request->page]["user_type"] == "employee"){
                                $emp_id = Auth::user()->company["linked_employee"]["id"];
                                $loan_filed =  $this->search_to_array($tbl_loan_file,"emp_id",$emp_id);
     
                                 if(count($loan_filed)>0){
                                     $btn .= "<a class='ml-1 btn btn-sm btn-success' 
                                     data-toggle='modal' 
                                     data-id='".$row->id."'
                                     data-file_id='new'
                                     data-emp_id='0'
                                     data-pay_type='0'
                                     data-total_amount=''
                                     data-amount_to_pay=''
                                     data-date_start=''
                                     data-date_to=''
                                     data-notes=''
         
                                     data-target='#add_edit_employee'
                                     
                                     > Already Applied </a>";
                                 }else{
                                    $btn .= "<a class='ml-1 btn btn-sm btn-success' 
                                    data-toggle='modal' 
                                    data-id='".$row->id."'
                                    data-file_id='new'
                                    data-emp_id='0'
                                    data-pay_type='0'
                                    data-total_amount=''
                                    data-amount_to_pay=''
                                    data-date_start=''
                                    data-date_to=''
                                    data-notes=''
        
                                    data-target='#add_edit_employee'
                                    
                                    > Apply Loan </a>";

                                 }
     
                            }else{
                                
                                     $btn .= "<a class='ml-1 btn btn-sm btn-success' 
                                     data-toggle='modal' 
                                     data-id='".$row->id."'
                                     data-file_id='new'
                                     data-emp_id='0'
                                     data-pay_type='0'
                                     data-total_amount=''
                                     data-amount_to_pay=''
                                     data-date_start=''
                                     data-date_to=''
                                     data-notes=''
         
                                     data-target='#add_edit_employee'
                                     
                                     > Add/Edit Employee </a>";
                                 
     
                            }

                           
                           


                        }
                        // add delete loan library
                        $tbl_loan_file = DB::table("tbl_loan_file")
                            ->where("loan_id",$row->id)->count();
                        $class = "";
                        if($tbl_loan_file > 0){
                            $class = "disabled";
                        }
                        $btn .= " <button $class
                        class='btn btn-sm btn-danger'
                        onclick='delete_loan_library(" . $row->id . ")'
                        >
                        Delete
                        </button>";   
                        

                    }else{
                        $btn = "";
                    }
    
                   
                        return $btn;
                 })

                 ->rawColumns(['is_active','action'])
                ->make(true);


    }
    // add delete loan library
    public function delete_loan_library(Request $request){
        DB::beginTransaction();
            try {
                DB::connection("intra_payroll")->table("lib_loans")
                    ->where("id", $request->id)
                    ->delete();
                DB::commit();
                return json_encode("Deleted");
            } catch (\Throwable $th) {
                DB::rollback();
                return json_encode($th->getMessage());
            }

    }
     // add delete in loan files
     public function delete_loan_file(Request $request){
        DB::beginTransaction();
            try {
                DB::connection("intra_payroll")->table("tbl_loan_file")
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
