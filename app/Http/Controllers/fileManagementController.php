<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Auth;
use Storage;
use Yajra\DataTables\DataTables;
class fileManagementController extends Controller
{
    public function file_management(){
        $role_id = Auth::user()->role_id;
        if(Auth::user()->access["file_management"]["user_type"] != "employee"){
            $tbl_employee = DB::connection("intra_payroll")->table("tbl_employee");

            if ($role_id === 4) { // HR Group D
                $tbl_employee = $tbl_employee->where("hr_group", "group_d");
            } elseif ($role_id === 5) { // HR Group B,C,E
                $tbl_employee = $tbl_employee->whereIn("hr_group", ["group_b","group_c","group_e"]);
            } elseif ($role_id === 14) { // HR Group B,C
                $tbl_employee = $tbl_employee->whereIn("hr_group", ["group_b","group_c"]);
            } elseif ($role_id === 15) { // HR Group C,E
                $tbl_employee = $tbl_employee->whereIn("hr_group", ["group_c","group_e"]);
            } 
            // elseif ($role_id === 22) { // HR Group E
            //     $tbl_employee = $tbl_employee->where("hr_group", "group_e");
            // }
            
            $tbl_employee = $tbl_employee->where("is_active",1)->get();
            $lib_file_type = DB::connection("intra_payroll")->table("lib_file_type")->get();
            
        }else{
            

            $tbl_employee = DB::connection("intra_payroll")->table("tbl_employee")->where("id",Auth::user()->company["linked_employee"]["id"])->get();
            $lib_file_type = DB::connection("intra_payroll")->table("lib_file_type")->get();
        }


       


        return view("file_management.index")
        ->with("tbl_employee", $tbl_employee)
        ->with("lib_file_type", $lib_file_type)
        
        ;

    }

    public function emp_upload_file(Request $request){

        $file_name = $request->file_name;
        $file_type = $request->file_type;
        $emp_id = $request->emp_id;
        if ($request->hasFile('file')) {
            DB::beginTransaction();

            try {
                    $file = $request->file('file');
                    $safeName = "upload_images/".$emp_id."/file";
                    // Store the file using the Storage facade
                    // $path = Storage::putFile('public/'.$emp_id.'/file', $file);
                  $path =  Storage::disk('public')->put($safeName, $file);

                DB::connection("intra_payroll")->table("tbl_file")
                    ->insert([
                        "emp_id" => $emp_id,
                        "file_name" => $file_name,
                        "id_type" => $file_type,
                        "upload_path" => $path,
                        "date_created" => date("Y-m-d H:i:s"),
                        "user_id" => Auth::user()->id
                    ]);


                DB::commit();

                return json_encode("success");
            } catch (\Throwable $th) {
                DB::rollback();

                return json_encode($th->getMessage());
            }

        }

    }


    public function load_file_tbl(Request $request){
        $page_permission = Auth::user()->access[$request->page]["access"];

        $files = DB::connection("intra_payroll")->table("tbl_file")->get();
        $files = json_decode(json_encode($files),true);
        $type = DB::connection("intra_payroll")->table("lib_file_type")->get();
        $type = json_decode(json_encode($type),true);
        //load all files
        $emp_id = $request->emp_id;
        if (is_array($emp_id)) {
            $tbl_file = DB::connection("intra_payroll")->table("tbl_file")
            ->select(DB::raw('GROUP_CONCAT(id SEPARATOR \';\') as ids'), 'id_type') // show delete in employee
            ->whereIn('emp_id', $emp_id)
            ->groupBy('id_type')
            ->get();
        }else{
            $tbl_file = DB::connection("intra_payroll")->table("tbl_file")
            ->select(DB::raw('GROUP_CONCAT(id SEPARATOR \';\') as ids'), 'id_type') // show delete in employee
            ->where('emp_id', $emp_id)
            ->groupBy('id_type')
            ->get();
        }

        $data = collect($tbl_file);

        return Datatables::of($data)
            ->addColumn('file', function($row) use($files){
                $ids = explode(";",$row->ids);
                $data = $this->search_multi_array($files, "id", $ids[0]);
                return $data["file_name"];

            })
            ->addColumn('type', function($row) use ($type) {
                $ids = explode(";",$row->id_type); // show delete in employee
                $data = $this->search_multi_array($type, "id", $ids[0]);
                return $data["name"];

            })
            ->addColumn('date_created', function($row) use ($files){
                $ids = explode(";",$row->ids);
                $data = $this->search_multi_array($files, "id", $ids[0]);
                return $data["date_created"];
            })



            ->addColumn('action', function($row) use ($page_permission, $request, $files){
                $btn = "";

                $ids = explode(";",$row->ids);

                foreach($ids as $file_id){
                    $data = $this->search_multi_array($files, "id", $file_id);

                    $btn .= '<a class="btn btn-success btn-sm mr-1" href="'.asset('public/'.$data["upload_path"]).'"  download><i class="fas fa-file-download"></i> Download</a>';
                }

                // show delete in employee
                $btn .= "<button 
                class='btn btn-sm btn-danger'
                onclick='delete_file(".'"'.$row->ids.'"'.")'
                >
                Delete
                </button>";
              
                //commented by Migz
                // if(preg_match("/U/i", $page_permission)){
                //     if(Auth::user()->access[$request->page]["user_type"] != "employee"){
                //     $btn .= "<button 
                //         class='btn btn-sm btn-danger'
                //         onclick='delete_file(".'"'.$row->ids.'"'.")'
                //         >
                //         Delete
                //         </button>";
                //     }
                // }

                // elseif(preg_match("/D/i", $page_permission)){
                //     if(Auth::user()->access[$request->page]["user_type"] != "employee"){
                //     $btn .= "<button 
                //         class='btn btn-sm btn-danger'
                //         onclick='delete_file(".'"'.$row->ids.'"'.")'
                //         >
                //         Delete
                //         </button>";
                //     }
                // }

                return $btn;
            })
            ->rawColumns(['action'])
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

    public function delete_emp_file(Request $request){
        $ids = explode(";",$request->id);
        $files = DB::connection("intra_payroll")->table("tbl_file")->get();
        $files = json_decode(json_encode($files),true);
        DB::beginTransaction();

        try {

            foreach($ids as $id){
                $data = $this->search_multi_array($files, "id", $id);
                if(isset($data["id"])){
                    
                   $del = Storage::disk('public')->delete($data["upload_path"]);;
                    // dd($del);
                    DB::connection("intra_payroll")->table("tbl_file")
                        ->where('id', $id)
                        ->delete();


                }
    
            }


            DB::commit();
            return json_encode("success");
        } catch (\Throwable $th) {
            DB::rollback();
            return json_encode($th->getMessage());
        }

        



    }


}
