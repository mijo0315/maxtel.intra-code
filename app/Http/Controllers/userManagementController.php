<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use DB;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Hash;
class userManagementController extends Controller
{
    public function user_management(){
     
        $role = DB::connection("intra_payroll")->table("tbl_role_access")->where("is_active", 1)->get();

        return view("user_management.index")
            ->with("role", $role)
        ;


    }

    function search_multi_array($array, $key, $value) {
        foreach ($array as $subarray) {
            if (isset($subarray[$key]) && $subarray[$key] == $value) {
                return $subarray;
            }
        }
        return null;
    }

    public function update_link_emp(Request $request){

        DB::beginTransaction();
        $position = DB::connection("intra_payroll")->table("lib_position")->get();
        $position = json_decode(json_encode($position), true);

        try {
               //RESET
               DB::connection("intra_payroll")->table("tbl_employee")->where("user_id", $request->id)
               ->update([
                   "user_id" => 0
               ]);
               //UPDATE
               DB::connection("intra_payroll")->table("tbl_employee")->where("id", $request->employee)
               ->update([
                   "user_id" => $request->id
               ]);

               $emp = DB::connection("intra_payroll")->table("tbl_employee")->where("id", $request->employee)
                ->first();

                if($emp != null){
                    $pos = $this->search_multi_array($position, "id", $emp->position_id);
                    if(isset($pos["name"])){
                        $posit = $pos["name"];
                    }else{
                        $posit = "";
                    }
                    
                    DB::connection("intra_payroll")->table("users")
                        ->where('id', $request->id)
                        ->update([
                            "firstName" => $emp->first_name,
                            "middleName" => $emp->middle_name,
                            "lastName" => $emp->last_name,
                            "extName" => $emp->ext_name,
                            "position" => $posit
                        ]);

                }else{
                    DB::connection("intra_payroll")->table("users")
                    ->where('id', $request->id)
                    ->update([
                        "firstName" => "",
                        "middleName" => "",
                        "lastName" => "",
                        "extName" => "",
                        "position" => ""
                    ]);



                }




          DB::commit();
          return json_encode("Success");
        } catch (\Throwable $th) {
          DB::rollback();

          return json_encode($th->getMessage());
        }
          


       


    }


    public function load_user_list(Request $request){
        if(Auth::user()->access[$request->page]["user_type"] != "employee"){
            $user_list = DB::connection("intra_payroll")->table("users")
                ->get();
        }else{
            $user_list = DB::connection("intra_payroll")->table("users")
            ->where("id", Auth::user()->id)
            ->get();
        }

        $role = DB::connection("intra_payroll")->table("tbl_role_access")->get();
        $role = json_decode(json_encode($role),true);

        $employee = DB::connection("intra_payroll")->table("tbl_employee")->get();
        $employee = json_decode(json_encode($employee),true);

        $page_permission = Auth::user()->access[$request->page]["access"];

        $data = collect($user_list);
      
        return Datatables::of($data)
        ->addColumn('name', function($row){
            return $row->firstName." ".$row->lastName;
        })
        ->addColumn('position', function($row){
            return $row->position;
        })
        ->addColumn('role', function($row) use ($role){
            // return $row->role_id;
            $role_data = $this->search_multi_array($role, "id", $row->role_id);
                if(isset($role_data["name"])){
                    return $role_data["name"]." (".strtoupper($role_data["type"]).")";
                }else{
                    return "N/A";
                }
        })
        ->addColumn('email', function($row){
            return $row->username;
        })
        ->addColumn('action', function($row) use ($page_permission, $request,$employee ){
            $btn = "";
            if(preg_match("/U/i", $page_permission)){
                if(Auth::user()->access[$request->page]["user_type"] != "employee"){
                    $btn .= "<a class='btn btn-info btn-sm w-100 mb-1'
                    data-toggle='modal' 
                    data-target='#credential_modal'
                    data-id = '".$row->id."'
                    data-user_name = '".$row->username."'
                    ><i class='fas fa-user-edit'></i> Change Credential</a> <br>";

                    $btn .= "<a class='btn btn-success btn-sm w-100 mb-1'
                    data-toggle='modal' 
                    data-target='#change_role_modal'
                    data-id = '".$row->id."'
                    data-role_id = '".$row->role_id."'
                    ><i class='fas fa-exchange-alt'></i> Change Role</a> <br>";
                    

                    $emp = $this->search_multi_array($employee, "user_id", $row->id);
                    if(isset($emp["id"])){
                        $emp_id =  $emp["id"];
                    }else{
                        $emp_id = 0;
                    }


                    
                    $btn .= "<a class='btn btn-warning btn-sm w-100 mb-1'
                    data-toggle='modal' 
                    data-target='#link_modal'
                    data-id = '".$row->id."'
                    data-employee = '".$emp_id."'
                    ><i class='fas fa-link'></i> Link User</a> <br>";

                    // add delete in user
                    $btn .= " <button 
                    class='btn btn-sm btn-danger w-100'
                    onclick='delete_user(" . $row->id . ")'
                    ><i class='fas fa-trash'></i>
                    Delete
                    </button>";
                    
                    

                }else{
                    $btn .= "<a class='btn btn-info btn-sm w-100'
                    data-toggle='modal' 
                    data-target='#credential_modal'
                    data-id = '".$row->id."'
                    data-user_name = '".$row->username."'
                    ><i class='fas fa-user-edit'></i> Change Credential</a>";
                }
              

                
            }
          
            return $btn;
        })
        ->rawColumns(['action'])
        ->make(true);
    }


  

    public function create_user_management(Request $request){
        DB::beginTransaction();
        $position = DB::connection("intra_payroll")->table("lib_position")->get();
        $position = json_decode(json_encode($position), true);
        try {
            $check_user = DB::connection("intra_payroll")->table("users")
            ->where("username", $request->user_name)
            ->first();

            if($check_user != null){    return json_encode("Username already Exist"); }

            $emp = DB::connection("intra_payroll")->table("tbl_employee")->where("id", $request->employee)
                ->where("user_id", 0)
                ->first();

            $email = $request->user_name;

            // Check if username is already an email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $email = $email . '@mail.com';
            }

            if($emp != null){
                $pos = $this->search_multi_array($position, "id", $emp->position_id);
                if(isset($pos["name"])){
                    $posit = $pos["name"];
                }else{
                    $posit = "";
                }

                

                
                $ins = array(
                    "firstName" => $emp->first_name,
                    "middleName" => $emp->middle_name,
                    "lastName" => $emp->last_name,
                    "extName" => $emp->ext_name,
                    "position" => $posit,
                    "role_id" => $request->role,
                    "email" => $email,
                    "username" => $request->user_name,
                    "password" => Hash::make($request->new_password)
                );



            }else{
               $ins = array(
                "firstName" => "",
                "middleName" => "",
                "lastName" => "",
                "extName" => "",
                "position" => "",
                "role_id" => $request->role,
                "email" => $email,
                "username" => $request->user_name,
                "password" => Hash::make($request->new_password)
               );


            }




            $user_inserted = DB::connection("intra_payroll")->table("users")
            ->insertGetId($ins);
                
            DB::connection("intra_payroll")->table("tbl_employee")->where("id", $request->employee)
                    ->update([
                        "user_id" => $user_inserted
                    ]);
            DB::commit();
            return json_encode("User Added");
        } catch (\Throwable $th) {
            DB::rollback();
            return json_encode($th->getMessage());

        }

       


    }


    public function user_get_employee(Request $request){
        $tbl_employee = DB::connection("intra_payroll")->table("tbl_employee")
            ->where("user_id",0)
            ->orWhere("user_id", $request->id)
            ->get();

        return json_encode($tbl_employee);
    }


    public function update_role(Request $request){
        DB::beginTransaction();
        try {
            
            DB::connection("intra_payroll")->table("users")
                ->where("id", $request->id)
                ->update([
                    "role_id" => $request->role_select
                ]);
                

            DB::commit();
            return json_encode("Updated");
        } catch (\Throwable $th) {
            DB::rollback();
            return json_encode($th->getMessage());
        }




    }



    public function update_credential(Request $request){
        DB::beginTransaction();
        try {
            
            $check_user_name = DB::connection("intra_payroll")->table("users")
                ->where("id", "!=",$request->id)
                ->where("username", $request->user_name)
                ->first();
                if($check_user_name != null){
                    return json_encode("Username already Exist");
                }else{
                    $email = $request->user_name;

                    // Check if username is already an email
                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $email = $email . '@mail.com';
                    }
                    if($request->password != "" || $request->password != null){
                        $update_arr = array(
                            "email" => $email,
                            "username" => $request->user_name,
                            "password" => Hash::make($request->password)
                        );
                    }else{
                        $update_arr = array(
                            "email" => $email,
                            "username" => $request->user_name,
                        );

                    }
                    
                    DB::connection("intra_payroll")->table("users")
                        ->where("id", $request->id)
                        ->update($update_arr);
                }

            DB::commit();
            return json_encode("Updated");
        } catch (\Throwable $th) {
            DB::rollback();
            return json_encode($th->getMessage());
        }


    }
    // add delete in user
    public function delete_user(Request $request){
        DB::beginTransaction();
            try {
                DB::connection("intra_payroll")->table("users")
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
