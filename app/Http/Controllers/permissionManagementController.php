<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use DB;
use Yajra\DataTables\DataTables;
class permissionManagementController extends Controller
{
    public function permission_management(){
        $tbl_role = DB::connection("intra_payroll")->table("tbl_role_access")
            ->get();


        return view("permission.index")
            ->with("tbl_role_access", $tbl_role)
            ;


    }

    public function check_role_data(Request $request){
        $arr = array(
            "id" => "new",
            "name" => "",
            "is_active" => "0",
            "type" => "Admin"
        );

        $role = DB::connection("intra_payroll")->table("tbl_role_access")
            ->where("id", $request->role)
            ->first();
            if($role != null){
                $arr = array(
                    "id" => $role->id,
                    "name" => $role->name,
                    "is_active" => $role->is_active,
                    "type" => $role->type
                );


            }


        return json_encode($arr);
    }
    
    public function load_page_access(Request $request){

        $access_array = array(
            "1" => "CRUD",
            "2" => "U",
            "3" => "R",
            "4" =>  ""
        );

        $permi_arr = array();

        $current_permission = "1|0;2|0;3|0;4|0;5|0;6|0;7|0;133|0;134|0;8|0;9|0;10|0;11|0;12|0;13|0;14|0;15|0;16|0;17|0;18|0;19|0;20|0;21|0";

        $role_data = DB::connection("intra_payroll")->table("tbl_role_access")
        ->where("id", $request->role)
        ->first();

        if($role_data != null){
            $explode  = explode(";",$role_data->permission);
    
                foreach($explode as $perm){
                
                    $page_acc = explode("|", $perm);
                    $permi_arr[$page_acc[0]] = $page_acc[1];
                }

                $pages = DB::connection("intra_payroll")->table("lib_permission")
                    ->get();
        }else{
            $explode  = explode(";",$current_permission);
            
            foreach($explode as $perm){
                $page_acc = explode("|", $perm);
                $permi_arr[$perm[0]] = $perm[1];
            }
                $pages = DB::connection("intra_payroll")->table("lib_permission")
                    ->where("id", "X")
                    ->get();
        }





        $page_permission = Auth::user()->access[$request->page]["access"];
        $data = collect($pages);

        return Datatables::of($data)
        ->addColumn('page', function($row){
            return $row->name;
          
            
        })
        ->addColumn('access', function($row) use ($page_permission, $request, $permi_arr){
            $btn = "";
            if(preg_match("/U/i", $page_permission)){
                if(Auth::user()->access[$request->page]["user_type"] != "employee"){
                    $selected_perm = array();
                    $selected_perm[0] = "";
                    $selected_perm[1] = "";
                    $selected_perm[2] = "";
                    $selected_perm[3] = "";
                    
                    $permission_value = isset($permi_arr[$row->id]) ? $permi_arr[$row->id] : 0;
                    $selected_perm[$permission_value] = "selected";
                    
               

                    $btn .= "<select onchange='permission(".$row->id.")' id='permission_".$row->id."' class='form-control form-select' > ";
                        $btn .= "<option value='0' ".$selected_perm[0].">No Access </option>";
                        $btn .= "<option value='1' ".$selected_perm[1].">Create, Read, Update, Delete </option>";
                        $btn .= "<option value='2' ".$selected_perm[2].">Update Only </option>";
                        $btn .= "<option value='3' ".$selected_perm[3].">Read Only </option>";
                    $btn .= "</select>";

                }
                
              

                
            }
          
            return $btn;
        })
        ->rawColumns(['access'])
        ->make(true);

    }

    function submit_role_data(Request $request){
    
        DB::beginTransaction();

        try {
            $role_data = DB::connection("intra_payroll")->table("tbl_role_access")
            ->where("id", $request->submit_role)
            ->first();
                if($role_data != null){
                    DB::connection("intra_payroll")->table("tbl_role_access")
                        ->where("id", $request->submit_role)
                        ->update([
                            "name" => $request->role_name,
                            "type" => $request->role_type,
                            "is_active" => $request->role_is_active
                        ]);
                        DB::commit();
                        return json_encode("Success");
                }else{
                   $data =  DB::connection("intra_payroll")->table("tbl_role_access")
                        ->insertGetId([
                            "name" => $request->role_name,
                            "type" => $request->role_type,
                            "is_active" => $request->role_is_active,
                            "permission" => "1|0;2|0;3|0;4|0;5|0;6|0;7|0;133|0;134|0;8|0;9|0;10|0;11|0;12|0;13|0;14|0;15|0;16|0;17|0;18|0;19|0;20|0;21|0"
                        ]);

                        $arr = array(
                            "id" => $data,
                            "name" => $request->role_name,
                            "is_active" => $request->role_is_active,
                            "type" => $request->role_type
                        );
                        DB::commit();
                        return json_encode($arr);
                }           
        } catch (\Throwable $th) {
            DB::rollback();
            return json_encode($th->getMessage());
        }

      


    }




    function update_access_status(Request $request){
        $role_data = DB::connection("intra_payroll")->table("tbl_role_access")
        ->where("id", $request->role)
        ->first();
        //1|1;2|1;3|1;4|1;5|1;6|1;7|1;8|1;9|1;10|1;11|1;12|1;13|1;14|1;16|
        if($role_data != null){
            $explode  = explode(";",$role_data->permission);
            $access = "";
            foreach($explode as $perm){
                if($access != ""){$access .= ";";}
                $page_acc = explode("|", $perm);
                if($page_acc[0] == $request->page){
                    $access .= $page_acc[0]."|".$request->access;
                }else{
                    $access .= $page_acc[0]."|".$page_acc[1];
                }

                
            }
            $role_data = DB::connection("intra_payroll")->table("tbl_role_access")
            ->where("id", $request->role)
            ->update([
                "permission" => $access
            ]);

        }

        return json_encode("success");


    }



}
