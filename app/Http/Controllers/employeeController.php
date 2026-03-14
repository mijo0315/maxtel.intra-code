<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use DB;
use Storage;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Hash;
use App\Exports\EmployeesExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;
use DateTime;
use PhpOffice\PhpSpreadsheet\Shared\Date as PhpOfficeDate;

class employeeController extends Controller
{

    public function view_employee(Request $request){
      $emp_data = DB::connection("intra_payroll")->table("tbl_employee")
            ->where("id", $request->id)
            ->first();
        
        return json_encode($emp_data);
    }

    public function view_user_data(Request $request){
        $emp_data = DB::connection("intra_payroll")->table("users")
            ->where("id", $request->id)
            ->first();
          
        if($emp_data != null){

            return json_encode($emp_data);

        }else{
        return json_encode(false);

        }
       
    

    }

    public function employees_management(){
        $role_id = Auth::user()->role_id;
        $employee_count = DB::connection("intra_payroll")->table("tbl_employee");

        if ($role_id === 4) { // HR Group D
            $employee_count = $employee_count->where("hr_group", "group_d");
        } elseif ($role_id === 5) { // HR Group B,C,E
            $employee_count = $employee_count->whereIn("hr_group", ["group_b","group_c","group_e"]);
        } elseif ($role_id === 14) { // HR Group B,C
            $employee_count = $employee_count->whereIn("hr_group", ["group_b","group_c"]);
        } elseif ($role_id === 15) { // HR Group C,E
            $employee_count = $employee_count->whereIn("hr_group", ["group_c","group_e"]);
        } 
        // elseif ($role_id === 22) { // HR Group E
        //     $employee_count = $employee_count->where("hr_group", "group_e");
        // }
        $employee_count = $employee_count->where("is_active", 1)->count();

        $position = json_decode(json_encode(DB::connection("intra_payroll")->table("lib_position")->where("is_active", 1)->get()),true);
        
        $division = json_decode(json_encode(DB::connection("intra_payroll")->table("tbl_division")->where("is_active", 1)->get()),true);
        $department = json_decode(json_encode(DB::connection("intra_payroll")->table("tbl_department")->where("is_active", 1)->get()),true);
        $branch = json_decode(json_encode(DB::connection("intra_payroll")->table("tbl_branch")->where("is_active", 1)->get()),true);
        $designation = json_decode(json_encode(DB::connection("intra_payroll")->table("lib_designation")->where("is_active", 1)->get()),true);

        $role = json_decode(json_encode(DB::connection("intra_payroll")->table("tbl_role_access")->where("is_active", 1)->get()),true);
        $lib_week_schedule = json_decode(json_encode(DB::connection("intra_payroll")->table("lib_week_schedule")->where("is_active", 1)->get()),true);



        // dd($position);

        return view("employee.index")
            ->with("employee_count", $employee_count)
            ->with('position', $position)
            ->with('division', $division)
            ->with('department', $department)
            ->with('branch', $branch)
            ->with('designation', $designation)
            ->with('role', $role)
            ->with('lib_week_schedule', $lib_week_schedule)
            
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
    
    public function create_employee(Request $request){
        //CHECK EMPCODE
        $check = DB::connection("intra_payroll")->table("tbl_employee")
            ->where("emp_code", $request->emp_code)
            ->first();
        
        if($check != null){
            return json_encode("duplicate");

        }

        $insert_array = array( 
            "emp_code" => $request->emp_code,
            "last_name" => $request->last_name,
            "first_name" => $request->first_name,
            "middle_name" => $request->middle_name,
            "ext_name" => $request->ext_name,
            "contact_no" => $request->contact_no,
            "sss_number" => $request->sss_no,
            "philhealth_number" => $request->philhealth_no,
            "hdmf_number" => $request->hdmf_no,
            "tin_number" => $request->tin_no,
            "bio_id" => $request->bio_id,
            "position_id" => $request->position,
            "department" => $request->department,
            "branch_id" => $request->branch,
            "designation" => $request->designation,
            "is_direct" => $request->hiring_type,
            "agency_name" => $request->agency_name,
            "salary_type" => $request->salary_type,
            "salary_rate" => $request->salary_rate,
            "yearly_divisor" => $request->yearly_divisor,
            "allowance" => $request->allowance,
            "is_mwe" => $request->is_mwe,
            "fix_divisor" => $request->fix_divisor,
            "fix_hdmf" => $request->fix_hdmf,
            "fix_sss" => $request->fix_sss,
            "fix_philhealth" => $request->fix_philhealth,
            "fix_tax_rate" => $request->fix_rate,
            "hr_group" => $request->hr_group,
            "is_active" => $request->is_active,
            "employee_status" => $request->employee_status,
            "user_id_added" => Auth::user()->id,
            "address" => $request->address,
            "start_date" => $request->start_date,
            "date_of_birth" => $request->date_of_birth,
            "date_created" => date("Y-m-d H:i:s")
            );

        $file = $request->file('emp_img');

        if($file != null){
            if (env('APP_ENV') === 'local') {
                $destinationPath = 'images/';
                $pathPrefix = 'images/';
            } else {
                $destinationPath = 'public/images/';
                $pathPrefix = 'public/images/';
            }
            $timestamp = time();
            //Move Uploaded File
            $fileName = $request->emp_code . '-' . $timestamp . '.' . $file->getClientOriginalExtension();
            $file->move($destinationPath, $fileName);

            $path = $pathPrefix . $fileName;
            $insert_array["profile_picture"] = $path;

        }
       
      
        DB::beginTransaction();
        try {
            $user_id = 0;
            if(!empty($request->user_name) && !empty($request->password)){
                //CREATE
                $check = DB::connection("intra_payroll")->table("users")    
                ->where("username", $request->user_name)
                ->first();

                if($check != null){
                    return json_encode("username already used");
                }else{
                    $name = $request->first_name." ".$request->last_name;
                    $email = $request->user_name;

                    // Check if username is already an email
                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $email = $email . '@mail.com';
                    }

                    $user_id =  DB::connection("intra_payroll")->table("users")
                    ->insertGetId([
                        "name" => $name,
                        "firstName" => $request->first_name,
                        "middleName" => $request->middle_name,
                        "lastName" => $request->last_name,
                        "extName" => $request->ext_name,
                        "position" => $request->position_name,
                        "role_id" => $request->role,
                        "email" => $email,
                        "username" => $request->user_name,
                        "password" => Hash::make($request->password)
                    ]);
                }
               
            }

               
            $insert_array["user_id"] = $user_id;
               



            //UPDATE EMPLOYEE
            DB::connection("intra_payroll")->table("tbl_employee")
            ->insert($insert_array);

            DB::commit();

            return json_encode("true");
        } catch (\Throwable $th) {
            DB::rollback();
            return json_encode($th->getMessage());
        }
    }


    public function update_employee(Request $request){
        //CHECK EMPCODE
        $check = DB::connection("intra_payroll")->table("tbl_employee")
            ->where("emp_code", $request->emp_code)
            ->where("id", "!=",$request->save_btn)
            ->first();
        
        if($check != null){
            return json_encode("duplicate");

        }

        $update_array = array( 
            "emp_code" => $request->emp_code,
            "bio_id" => $request->bio_id,
            "last_name" => $request->last_name,
            "first_name" => $request->first_name,
            "middle_name" => $request->middle_name,
            "ext_name" => $request->ext_name,
            "contact_no" => $request->contact_no,
            "sss_number" => $request->sss_no,
            "philhealth_number" => $request->philhealth_no,
            "hdmf_number" => $request->hdmf_no,
            "tin_number" => $request->tin_no,
            "position_id" => $request->position,
            "department" => $request->department,
            "branch_id" => $request->branch,
            "designation" => $request->designation,
            "is_direct" => $request->hiring_type,
            "agency_name" => $request->agency_name,
            "salary_type" => $request->salary_type,
            "salary_rate" => $request->salary_rate,
            "yearly_divisor" => $request->yearly_divisor,
            "allowance" => $request->allowance,
            "is_mwe" => $request->is_mwe,
            "fix_divisor" => $request->fix_divisor,
            "fix_hdmf" => $request->fix_hdmf,
            "fix_sss" => $request->fix_sss,
            "fix_philhealth" => $request->fix_philhealth,
            "fix_tax_rate" => $request->fix_rate,
            "hr_group" => $request->hr_group,
            "is_active" => $request->is_active,
            "employee_status" => $request->employee_status,
            "user_id_added" => Auth::user()->id,
            "address" => $request->address,
            "start_date" => $request->start_date,
            "date_of_birth" => $request->date_of_birth
            );

        $file = $request->file('emp_img');

        if($file != null){
            if (env('APP_ENV') === 'local') {
                $destinationPath = 'images/';
                $pathPrefix = 'images/';
            } else {
                $destinationPath = 'public/images/';
                $pathPrefix = 'public/images/';
            }
            $old_profile = DB::connection("intra_payroll")->table("tbl_employee")
                ->where("id", $request->save_btn)
                ->value("profile_picture");

            if ($old_profile && file_exists($old_profile)) {
                unlink($old_profile);
            }
            $timestamp = time();
            //Move Uploaded File
            $fileName = $request->emp_code . '-' . $timestamp . '.' . $file->getClientOriginalExtension();
            $file->move($destinationPath, $fileName);

            $path = $pathPrefix . $fileName;
            $update_array["profile_picture"] = $path;

        }
       
      
        DB::beginTransaction();
        try {
            //UPDATE EMPLOYEE
            DB::connection("intra_payroll")->table("tbl_employee")
            ->where("id", $request->save_btn)
            ->update($update_array);

            DB::commit();

            return json_encode("true");
        } catch (\Throwable $th) {
            DB::rollback();
            return json_encode($th->getMessage());
        }
    }


    public function create_user_account(Request $request){
        DB::beginTransaction();
        try {

           $emp_id = DB::connection("intra_payroll")->table("tbl_employee")
                ->where("id", $request->id)
                ->value("user_id");

            
            $email = $request->user_name;

            // Check if username is already an email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $email = $email . '@mail.com';
            }

                if($emp_id == "0"){
                    //CREATE
                    $check = DB::connection("intra_payroll")->table("users")    
                    ->where("username", $request->user_name)
                    ->first();
    
                    if($check != null){
                        return json_encode("duplicate");
                    }

                    $user_id =  DB::connection("intra_payroll")->table("users")
                    ->insertGetId([
                        "name" => $request->name,
                        "firstName" => $request->first_name,
                        "middleName" => $request->middle_name,
                        "lastName" => $request->last_name,
                        "extName" => $request->ext_name,
                        "position" => $request->position,
                        "role_id" => $request->role_id,
                        "email" => $email,
                        "username" => $request->user_name,
                        "password" => Hash::make($request->password)
                    ]);

                    DB::connection("intra_payroll")->table("tbl_employee")
                        ->where("id", $request->id)
                        ->update([
                            "user_id" => $user_id 
                        ]);

                }else{

                    $check = DB::connection("intra_payroll")->table("users")    
                ->where("username", $request->user_name)
                ->first();

                  

                        if($check != null){

                            if($request->password == ""){
                                $array_update = array(
                                    "name" => $request->name,
                                    "firstName" => $request->first_name,
                                    "middleName" => $request->middle_name,
                                    "lastName" => $request->last_name,
                                    "extName" => $request->ext_name,
                                    "position" => $request->position,
                                    "role_id" => $request->role_id,
                                );
        
                            }else{
                                $array_update = array(
                                    "name" => $request->name,
                                    "firstName" => $request->first_name,
                                    "middleName" => $request->middle_name,
                                    "lastName" => $request->last_name,
                                    "extName" => $request->ext_name,
                                    "position" => $request->position,
                                    "role_id" => $request->role_id,
                                    "password" => Hash::make($request->password)
                                );
                            }

                            DB::connection("intra_payroll")->table("users")
                            ->where("id", $emp_id)
                            ->update($array_update);
                        }else{

                            if($request->password == ""){
                                $array_update = array(
                                    "name" => $request->name,
                                    "firstName" => $request->first_name,
                                    "middleName" => $request->middle_name,
                                    "lastName" => $request->last_name,
                                    "extName" => $request->ext_name,
                                    "position" => $request->position,
                                    "role_id" => $request->role_id,
                                    "email" => $email,
                                );

                            }else{
                                $array_update = array(
                                    "name" => $request->name,
                                    "firstName" => $request->first_name,
                                    "middleName" => $request->middle_name,
                                    "lastName" => $request->last_name,
                                    "extName" => $request->ext_name,
                                    "position" => $request->position,
                                    "role_id" => $request->role_id,
                                    "email" => $email,
                                    "username" => $request->user_name,
                                    "password" => Hash::make($request->password)
                                );
                            }


                            DB::connection("intra_payroll")->table("users")
                            ->where("id", $emp_id)
                            ->update($array_update);

                        }
                  


                }



           
            DB::commit();

            return json_encode("saved");


        } catch (\Throwable $th) {
            DB::rollback();
            return json_encode($th->getMessage());
        }
       

            



    }
    


    public function employee_list(Request $request){
        $page_permission = Auth::user()->access[$request->page]["access"];
        $role_id = Auth::user()->role_id;
        

        $position = json_decode(json_encode(DB::connection("intra_payroll")->table("lib_position")->where("is_active", 1)->get()),true);
        $department = json_decode(json_encode(DB::connection("intra_payroll")->table("tbl_department")->where("is_active", 1)->get()),true);
        $branch = json_decode(json_encode(DB::connection("intra_payroll")->table("tbl_branch")->where("is_active", 1)->get()),true);
        $designation = json_decode(json_encode(DB::connection("intra_payroll")->table("lib_designation")->where("is_active", 1)->get()),true);
                
        
        $data = DB::connection("intra_payroll")->table("tbl_employee");

        if ($role_id === 4) { // HR Group D
            $data = $data->where("hr_group", "group_d");
        } elseif ($role_id === 5) { // HR Group B,C,E
            $data = $data->whereIn("hr_group", ["group_b","group_c","group_e"]);
        } elseif ($role_id === 14) { // HR Group B,C
            $data = $data->whereIn("hr_group", ["group_b","group_c"]);
        } elseif ($role_id === 15) { // HR Group C,E
            $data = $data->whereIn("hr_group", ["group_c","group_e"]);
        } 
        // elseif ($role_id === 22) { // HR Group E
        //     $data = $data->where("hr_group", "group_e");
        // }
        $data = $data->orderBy("last_name")
            ->orderBy("first_name")
            ->orderBy("middle_name")
            ->orderBy("ext_name")
            ->get();

                $data = collect($data);

                return Datatables::of($data)
                ->addColumn('name', function($row){

                    $view = '<div class="table-img">';
                    $view .= '<a>';
                 
                    $view .= '<img src="'.asset_with_env(str_replace('public/', '', $row->profile_picture)).'" alt="profile" class="img-table" onerror="this.src='."'".asset_with_env('upload_images/emp_pic/avatar-user.jpg')."'".';" /><label>';
                    // $imgPath = str_replace('public/', '', $row->profile_picture);
                    // $cacheBuster = '?v=' . strtotime($row->date_updated ?? now()); // use updated_at or fallback to now()
                    // $view .= '<img src="'.asset_with_env($imgPath.$cacheBuster).'" alt="profile" class="img-table" onerror="this.src='."'".asset_with_env('upload_images/emp_pic/avatar-user.jpg')."'".';" /><label>';
                    $view .= $row->emp_code .'<br>';
                    $view .= $row->last_name.", ".$row->first_name." ".$row->middle_name." ".$row->ext_name;
                    
                    $view .= '</label>';
                    $view .= '</a></div>';
                    

                    return $view;
                })
                ->addColumn('position', function($row) use ($position){
                    $data = $this->search_multi_array($position, "id", $row->position_id);
                    if($data != null){
                        return $data["name"];
                    }else{
                        return "-";
                    }
                    
                 })
                ->addColumn('department', function($row) use ($department) {
                    $data = $this->search_multi_array($department, "id", $row->department);
                    if($data != null){
                        return $data["department"];
                    }else{
                        return "-";
                    }
                 })
                 ->addColumn('branch', function($row) use ($branch){
                    $data = $this->search_multi_array($branch, "id", $row->branch_id);
                    if($data != null){
                        return $data["branch"];
                    }else{
                        return "-";
                    }

                 })
                 ->addColumn('designation', function($row) use($designation){
                    $data = $this->search_multi_array($designation, "id", $row->designation);
                    if($data != null){
                        return $data["name"];
                    }else{
                        return "-";
                    }
                 })
                 ->addColumn('action', function($row) use ($page_permission){
                     // add delete
                     $btn = "";
                     if(preg_match("/U/i", $page_permission)){
                         $record_type = "employee";
                         $btn .= "<a class='btn btn-sm btn-info' onclick = 'emp_view(".$row->id.")' > EDIT </a>";
                         $btn .= " <button 
                         class='btn btn-sm btn-danger'
                         onclick='record_delete(" . $row->id . ", \"" . $record_type . "\")'
                         >
                         Delete
                         </button>";
                     }
     
                    
                         return $btn;
                 })

                 ->rawColumns(['name','action'])
                ->make(true);
    }

    public function division_list(Request $request){
        $page_permission = Auth::user()->access[$request->page]["access"];
 
        $data = DB::connection("intra_payroll")->table("tbl_division")
            ->orderBy("division")
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
                
                ->addColumn('date_updated', function($row){
                  return date("F j, Y", strtotime($row->date_updated));
                })
                
               
                 ->addColumn('action', function($row) use ($page_permission){
                    if(preg_match("/U/i", $page_permission)){
                        $btn = "<a class='btn btn-sm btn-info' 
                        data-toggle='modal' 
                        data-id='".$row->id."'
                        data-code='".$row->code."'
                        data-division='".$row->division."'
                        data-schedule_id='".$row->schedule_id."'
                        data-is_active='".$row->is_active."'
                        data-target='#division_modal'
                        
                        > EDIT </a>";

                        $record_type = "division";
                         $btn .= " <button 
                         class='btn btn-sm btn-danger'
                         onclick='record_delete(" . $row->id . ", \"" . $record_type . "\")'
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


    function save_division(Request $request){
        $save_division = $request->save_division;
        $div_code = $request->div_code;
        $div_name = $request->div_name;
        $div_sched = $request->div_sched;
        $is_active = $request->is_active;
        DB::beginTransaction();
        try {
            if($save_division == "new"){
                $check = DB::connection("intra_payroll")->table("tbl_division")
                    ->where("code", $div_code)
                    ->first();
                if($check != null){
                    return json_encode("duplicate");
                }
            
                $insert_array = array(
                    "code" => $div_code,
                    "division" => $div_name,
                    "schedule_id" => $div_sched,
                    "is_active" => $is_active,
                    "date_created" => date("Y-m-d H:i:s"),
                    "user_id" => Auth::user()->id
                );
                DB::connection("intra_payroll")->table("tbl_division")
                ->insert($insert_array);
            }else{
                $check = DB::connection("intra_payroll")->table("tbl_division")
                    ->where("code", $div_code)
                    ->where("id", "!=",$save_division)
                    ->first();
                if($check != null){
                    return json_encode("duplicate");
                }

                $update_array = array(
                    "code" => $div_code,
                    "division" => $div_name,
                    "schedule_id" => $div_sched,
                    "is_active" => $is_active,
                    "user_id" => Auth::user()->id
                );
                DB::connection("intra_payroll")->table("tbl_division")
                    ->where("id", $save_division)
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


    public function department_list(Request $request){
        $page_permission = Auth::user()->access[$request->page]["access"];
        $division = json_decode(json_encode(DB::connection("intra_payroll")->table("tbl_division")->where("is_active", 1)->get()),true);
 
        $data = DB::connection("intra_payroll")->table("tbl_department")
            ->orderBy("department")
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
                
                ->addColumn('date_updated', function($row){
                  return date("F j, Y", strtotime($row->date_updated));
                })
                
                ->addColumn('dept_div', function($row) use ($division){
                    $data = $this->search_multi_array($division, "id", $row->division_id);
                    if($data != null){
                        return $data["division"];
                    }else{
                        return "-";
                    }
                  })
               
                 ->addColumn('action', function($row) use ($page_permission){
                    // add delete
                    $btn = "";
                    if(preg_match("/U/i", $page_permission)){
                        $record_type = "department";
                        $btn .= "<a class='btn btn-sm btn-info' 
                        data-toggle='modal' 
                        data-id='".$row->id."'
                        data-code='".$row->code."'
                        data-dept_div='".$row->division_id."'
                        data-department='".$row->department."'
                        data-schedule_id='".$row->schedule_id."'
                        data-is_active='".$row->is_active."'
                        data-target='#department_modal'
                        
                        > EDIT </a>";

                        $btn .= " <button 
                        class='btn btn-sm btn-danger'
                        onclick='record_delete(" . $row->id . ", \"" . $record_type . "\")'
                        >
                        Delete
                        </button>";

                    }
    
                   
                        return $btn;
                 })

                 ->rawColumns(['is_active','action'])
                ->make(true);
    }



    function save_department(Request $request){
        $save_department = $request->save_department;
        $dept_code = $request->dept_code;
        // $dept_div = $request->dept_div;
        
        $dept_name = $request->dept_name;
        $dept_sched = $request->dept_sched;
        $is_active = $request->is_active;
        DB::beginTransaction();
        try {
            if($save_department == "new"){
                $check = DB::connection("intra_payroll")->table("tbl_department")
                    ->where("code", $dept_code)
                    ->first();
                if($check != null){
                    return json_encode("duplicate");
                }
            
                $insert_array = array(
                    "code" => $dept_code,
                    "department" => $dept_name,
                    // "division_id" => $dept_div,
                    "schedule_id" => $dept_sched,
                    "is_active" => $is_active,
                    "date_created" => date("Y-m-d H:i:s"),
                    "user_id" => Auth::user()->id
                );
                DB::connection("intra_payroll")->table("tbl_department")
                ->insert($insert_array);
            }else{
                $check = DB::connection("intra_payroll")->table("tbl_department")
                    ->where("code", $dept_code)
                    ->where("id", "!=",$save_department)
                    ->first();
                if($check != null){
                    return json_encode("duplicate");
                }

                $update_array = array(
                    "code" => $dept_code,
                    "department" => $dept_name,
                    // "division_id" => $dept_div,
                    "schedule_id" => $dept_sched,
                    "is_active" => $is_active,
                    "user_id" => Auth::user()->id
                );
                DB::connection("intra_payroll")->table("tbl_department")
                    ->where("id", $save_department)
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



    public function branch_list(Request $request){
        $page_permission = Auth::user()->access[$request->page]["access"];
 
        $data = DB::connection("intra_payroll")->table("tbl_branch")
            ->orderBy("branch")
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
                
                ->addColumn('date_updated', function($row){
                  return date("F j, Y", strtotime($row->date_updated));
                })
                
               
                 ->addColumn('action', function($row) use ($page_permission){
                   // add delete
                   $btn = "";
                   if(preg_match("/U/i", $page_permission)){
                       $record_type = "branch";
                       $btn .= "<a class='btn btn-sm btn-info' 
                       data-toggle='modal' 
                       data-id='".$row->id."'
                       data-code='".$row->code."'
                       data-branch='".$row->branch."'
                       data-schedule_id='".$row->schedule_id."'
                       data-is_active='".$row->is_active."'
                       data-target='#branch_modal'
                       
                       > EDIT </a>";
                       $btn .= " <button 
                       class='btn btn-sm btn-danger'
                       onclick='record_delete(" . $row->id . ", \"" . $record_type . "\")'
                       >
                       Delete
                       </button>";
                   }
   
                  
                       return $btn;
                 })

                 ->rawColumns(['is_active','action'])
                ->make(true);
    }


    function save_branch(Request $request){
        $save_branch = $request->save_branch;
        $branch_code = $request->branch_code;
        $branch_name = $request->branch_name;
        $branch_sched = $request->branch_sched;
        $is_active = $request->is_active;
        DB::beginTransaction();
        try {
            if($save_branch == "new"){
                $check = DB::connection("intra_payroll")->table("tbl_branch")
                    ->where("code", $branch_code)
                    ->first();
                if($check != null){
                    return json_encode("duplicate");
                }
            
                $insert_array = array(
                    "code" => $branch_code,
                    "branch" => $branch_name,
                    "schedule_id" => $branch_sched,
                    "is_active" => $is_active,
                    "date_created" => date("Y-m-d H:i:s"),
                    "user_id" => Auth::user()->id
                );
                DB::connection("intra_payroll")->table("tbl_branch")
                ->insert($insert_array);
            }else{
                $check = DB::connection("intra_payroll")->table("tbl_branch")
                    ->where("code", $branch_code)
                    ->where("id", "!=",$save_branch)
                    ->first();
                if($check != null){
                    return json_encode("duplicate");
                }

                $update_array = array(
                    "code" => $branch_code,
                    "branch" => $branch_name,
                    "schedule_id" => $branch_sched,
                    "is_active" => $is_active,
                    "user_id" => Auth::user()->id
                );
                DB::connection("intra_payroll")->table("tbl_branch")
                    ->where("id", $save_branch)
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

    public function position_list(Request $request){
        $page_permission = Auth::user()->access[$request->page]["access"];
 
        $data = DB::connection("intra_payroll")->table("lib_position")
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
                
                ->addColumn('date_updated', function($row){
                  return date("F j, Y", strtotime($row->date_updated));
                })

                ->addColumn('type', function($row){
                    $return_array = array("RF" => "Rank & File", 
                                          "EX" => "Executives",
                                          "SM" => "Supervisory / Managerial",
                                          "ST" => "Specialized / Technical Roles",
                                          "AD" => "Administrative",
                                          "FC" => "Freelance / Consulting");

                    return $return_array[$row->type];

                  })
                
               
                 ->addColumn('action', function($row) use ($page_permission){
                   // add delete
                   $btn = "";
                   if(preg_match("/U/i", $page_permission)){
                       $record_type = "position";
                       $btn .= "<a class='btn btn-sm btn-info' 
                       data-toggle='modal' 
                       data-id='".$row->id."'
                       data-code='".$row->code."'
                       data-name='".$row->name."'
                       data-rf='".$row->type."'
                       data-is_active='".$row->is_active."'
                       data-target='#position_modal'
                       
                       > EDIT </a>";
                       $btn .= " <button 
                       class='btn btn-sm btn-danger'
                       onclick='record_delete(" . $row->id . ", \"" . $record_type . "\")'
                       >
                       Delete
                       </button>";
                   }
   
                  
                       return $btn;
                 })

                 ->rawColumns(['is_active','action'])
                ->make(true);
    }


    function save_position(Request $request){
        $save_position = $request->save_position;
        $pos_code = $request->pos_code;
        $pos_name = $request->pos_name;
        $pos_type = $request->pos_type;
        $is_active = $request->is_active;
        DB::beginTransaction();
        try {
            if($save_position == "new"){
                $check = DB::connection("intra_payroll")->table("lib_position")
                    ->where("code", $pos_code)
                    ->first();
                if($check != null){
                    return json_encode("duplicate");
                }
            
                $insert_array = array(
                    "code" => $pos_code,
                    "name" => $pos_name,
                    "type" => $pos_type,
                    "is_active" => $is_active,
                    "date_created" => date("Y-m-d H:i:s"),
                    "user_id" => Auth::user()->id
                );
                DB::connection("intra_payroll")->table("lib_position")
                ->insert($insert_array);
            }else{
                $check = DB::connection("intra_payroll")->table("lib_position")
                    ->where("code", $pos_code)
                    ->where("id", "!=",$save_position)
                    ->first();
                if($check != null){
                    return json_encode("duplicate");
                }

                $update_array = array(
                    "code" => $pos_code,
                    "name" => $pos_name,
                    "type" => $pos_type,
                    "is_active" => $is_active,
                    "user_id" => Auth::user()->id
                );
                DB::connection("intra_payroll")->table("lib_position")
                    ->where("id", $save_position)
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





    public function designation_list(Request $request){
        $page_permission = Auth::user()->access[$request->page]["access"];
 
        $data = DB::connection("intra_payroll")->table("lib_designation")
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
                
                ->addColumn('date_updated', function($row){
                  return date("F j, Y", strtotime($row->date_updated));
                })
                
               
                 ->addColumn('action', function($row) use ($page_permission){
                    // add delete
                    $btn = "";
                    if(preg_match("/U/i", $page_permission)){
                        $record_type = "designation";
                        $btn .= "<a class='btn btn-sm btn-info' 
                        data-toggle='modal' 
                        data-id='".$row->id."'
                        data-code='".$row->code."'
                        data-name='".$row->name."'
                        data-is_active='".$row->is_active."'
                        data-target='#designation_modal'
                        
                        > EDIT </a>";
                        $btn .= " <button 
                        class='btn btn-sm btn-danger'
                        onclick='record_delete(" . $row->id . ", \"" . $record_type . "\")'
                        >
                        Delete
                        </button>";
                    }
    
                   
                        return $btn;
                 })

                 ->rawColumns(['is_active','action'])
                ->make(true);
    }


    function save_designation(Request $request){
        $save_designation = $request->save_designation;
        $des_code = $request->des_code;
        $des_name = $request->des_name;
        $is_active = $request->is_active;
        DB::beginTransaction();
        try {
            if($save_designation == "new"){
                $check = DB::connection("intra_payroll")->table("lib_designation")
                    ->where("code", $des_code)
                    ->first();
                if($check != null){
                    return json_encode("duplicate");
                }
            
                $insert_array = array(
                    "code" => $des_code,
                    "name" => $des_name,
                    "is_active" => $is_active,
                    "date_created" => date("Y-m-d H:i:s"),
                    "user_id" => Auth::user()->id
                );
                DB::connection("intra_payroll")->table("lib_designation")
                ->insert($insert_array);
            }else{
                $check = DB::connection("intra_payroll")->table("lib_designation")
                    ->where("code", $des_code)
                    ->where("id", "!=",$save_designation)
                    ->first();
                if($check != null){
                    return json_encode("duplicate");
                }

                $update_array = array(
                    "code" => $des_code,
                    "name" => $des_name,
                    "is_active" => $is_active,
                    "user_id" => Auth::user()->id
                );
                DB::connection("intra_payroll")->table("lib_designation")
                    ->where("id", $save_designation)
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
    // add delete
    public function delete_record(Request $request){
        $type = $request->record_type;
        $tbl = "";
        if($type == "employee"){
            $tbl = "tbl_employee";
        }elseif($type == "division"){ //add delete in division
            $tbl = "tbl_division";
        }elseif($type == "department"){
            $tbl = "tbl_department";
        }elseif($type == "branch"){
            $tbl = "tbl_branch";
        }elseif($type == "position"){
            $tbl = "lib_position";
        }else{
            $tbl = "lib_designation";
        }
        DB::beginTransaction();
            try {
                if($type == "employee"){
                    $employee = DB::table('tbl_employee')->where('id',$request->id)->first();

                    $check_loan =  DB::connection("intra_payroll")->table("tbl_loan_file")
                        ->where("emp_id", $request->id)
                        ->where("is_done", 1)
                        ->where("loan_status", 1)
                        ->get();

                    if($employee){
                        if(count($check_loan) == 0){
                            DB::connection("intra_payroll")->table("users")
                            ->where("id", $employee->user_id)
                            ->delete();
                        }  
                    }
                    if(count($check_loan)){
                        DB::rollback();
                        return json_encode("Cannot Delete due to employee has loan to deduct");
                    }


                    DB::connection("intra_payroll")->table($tbl)
                        ->where("id", $request->id)
                        ->update([
                            "is_active" => 0
                        ]);

                }

                
             


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

    public function export()
    {
        $user = Auth::user();
        $roleId = $user ? $user->role_id : null;
        
        Log::info('Employee Export Started', [
            'user_id' => $user->id ?? 'unknown',
            'user_name' => $user->name ?? 'unknown',
            'role_id' => $roleId
        ]);
        
        try {
            return Excel::download(new EmployeesExport($roleId), 'employees.xlsx');
        } catch (\Exception $e) {
            Log::error('Employee Export Failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

     //employee upload
    public function upload_excel_employee(Request $request)
    {
        $request->validate([
            'employee_excel_file' => 'required|mimes:xlsx,xls'
        ]);

        // Read Excel file
        $collection = Excel::toCollection($this, $request->file('employee_excel_file'));

        // Fetch active employees, departments, and positions from the database
        $tbl_employee = DB::connection("intra_payroll")->table("tbl_employee")
            ->where("is_active", 1)
            ->get()
            ->keyBy('emp_code')
            ->toArray();

        $tbl_position = DB::connection("intra_payroll")->table("lib_position")
            ->get()
            ->keyBy('name')
            ->toArray();

        $tbl_department = DB::connection("intra_payroll")->table("tbl_department")
            ->get()
            ->keyBy('department')
            ->toArray();

        $tbl_branch = DB::connection("intra_payroll")->table("tbl_branch")
            ->get()
            ->keyBy('branch')
            ->toArray();

        DB::beginTransaction();
        try {
            $processed_count = 0;
            $error_rows = [];
            
            foreach ($collection[0] as $index => $row) {
                // Skip header row only
                if ($index === 0) { 
                    continue;
                }

                // Convert Collection to array for processing
                $rowData = $row->toArray();
                
                // Extract data safely with null fallback - matching export format (47 columns)
                $emp_code = trim($rowData[0] ?? '');
                
                // Skip rows with no employee code or if row appears empty
                if (empty($emp_code) || count(array_filter($rowData)) < 3) {
                    continue; 
                }
                
                //personal info
                $last_name = trim($rowData[1] ?? '');
                $first_name = trim($rowData[2] ?? '');
                $middle_name = trim($rowData[3] ?? '');
                $ext_name = trim($rowData[4] ?? '');
                $contact_no = trim($rowData[5] ?? '');
                $address = trim($rowData[19] ?? '');
                
                Log::info("Processing employee row", [
                    'row_index' => $index,
                    'emp_code' => $emp_code,
                    'first_name' => $first_name,
                    'last_name' => $last_name
                ]);
                
                // Validate required fields
                if (empty($first_name) || empty($last_name)) {
                    Log::warning("Skipping row due to missing required fields", [
                        'row_index' => $index,
                        'emp_code' => $emp_code,
                        'first_name' => $first_name,
                        'last_name' => $last_name
                    ]);
                    continue;
                }

                // Govt benefits
                $sss = trim($rowData[6] ?? '');
                $philhealth = trim($rowData[7] ?? '');
                $hdmf = trim($rowData[8] ?? '');
                $tin = trim($rowData[9] ?? '');
                $fix_divisor = trim($rowData[10] ?? '');
                $fix_sss = trim($rowData[11] ?? '');
                $fix_philhealth = trim($rowData[12] ?? '');
                $fix_hdmf = trim($rowData[13] ?? '');
                $fix_tax_rate = trim($rowData[14] ?? '');

                // HR Info
                $position_name = trim($rowData[15] ?? '');
                $department_name = trim($rowData[16] ?? '');
                // Start Date
                $start_date = null;
                if (!empty($rowData[17])) {
                    try {
                        if (is_numeric($rowData[17])) {
                            $start_date = date('Y-m-d', PhpOfficeDate::excelToTimestamp($rowData[17]));
                        } else {
                            $date_formats = ['d/m/Y', 'm/d/Y', 'Y-m-d', 'd-m-Y'];
                            foreach ($date_formats as $format) {
                                $date = DateTime::createFromFormat($format, trim($rowData[17]));
                                if ($date) {
                                    $start_date = $date->format('Y-m-d');
                                    break;
                                }
                            }
                        }
                    } catch (\Exception $e) {
                        Log::warning("Invalid start date format", ['row' => $index, 'value' => $rowData[17]]);
                    }
                }
                // Date of Birth
                $birth_date = null;
                if (!empty($rowData[18])) {
                    try {
                        if (is_numeric($rowData[18])) {
                            $birth_date = date('Y-m-d', PhpOfficeDate::excelToTimestamp($rowData[18]));
                        } else {
                            $date_formats = ['d/m/Y', 'm/d/Y', 'Y-m-d', 'd-m-Y'];
                            foreach ($date_formats as $format) {
                                $date = DateTime::createFromFormat($format, trim($rowData[18]));
                                if ($date) {
                                    $birth_date = $date->format('Y-m-d');
                                    break;
                                }
                            }
                        }
                    } catch (\Exception $e) {
                        Log::warning("Invalid birth date format", ['row' => $index, 'value' => $rowData[18]]);
                    }
                }

                // Salary Info
                $salary_type = trim($rowData[20] ?? 'DAILY');
                $salary_rate = $rowData[21] ?? '';
                $is_mwe_text = trim($rowData[22] ?? '');
                $is_mwe = $is_mwe_text === 'Yes' ? 1 : 0;

                //Status
                $status_text = trim($rowData[23] ?? '');
                $is_active = $status_text === 'Active' ? 1 : 0;

                //Additional
                $branch_name = trim($rowData[24] ?? '');
                $yearly_divisor = trim($rowData[25] ?? '');
                $rawEmployeeStatus = trim($rowData[26] ?? '');
                // Leave NULL if empty
                if ($rawEmployeeStatus === '') {
                    $employee_status = ""; 
                } else {
                    $allowedStatuses = [
                        'PROBATIONARY'     => 'Probationary',
                        'TRAINEE'          => 'Trainee',
                        'PROJECT EMPLOYEE' => 'Project Employee',
                        'REGULAR'          => 'Regular',
                    ];

                    $key = strtoupper($rawEmployeeStatus);

                    $employee_status = $allowedStatuses[$key] ?? '';
                }
                $excelHrGroup = strtoupper(trim($rowData[27] ?? ''));

                $hrGroupMap = [
                    'A' => 'group_a',
                    'B' => 'group_b',
                    'C' => 'group_c',
                    'D' => 'group_d',
                    'E' => 'group_e',
                ];

                $hr_group = $hrGroupMap[$excelHrGroup] ?? '';

                // Check if position exists, if not insert it
                $position = DB::connection("intra_payroll")->table('lib_position')
                    ->where('name', $position_name)
                    ->first();

                if (!$position) {
                    $position_id = DB::connection("intra_payroll")->table('lib_position')->insertGetId([
                        'name' => $position_name,
                        'code' => $position_name,
                        'type' => 'RF',
                        'schedule_id' => 0,
                        'is_active' => 1,
                        'date_created' => date("Y-m-d H:i:s"),
                        'user_id' => Auth::user()->id ?? 1
                    ]);
                    $tbl_position[$position_name] = (object) ['id' => $position_id];
                } else {
                    $position_id = $position->id;
                }

                // Check if department exists, if not insert it
                $department = DB::connection("intra_payroll")->table('tbl_department')
                    ->where('department', $department_name)
                    ->first();

                if (!$department) {
                    $department_id = DB::connection("intra_payroll")->table('tbl_department')->insertGetId([
                        'department' => $department_name,
                        'division_id' => 0,
                        'code' => $department_name,
                        'schedule_id' => 0,
                        'is_active' => 1,
                        'date_created' => date("Y-m-d H:i:s"),
                        'user_id' => Auth::user()->id ?? 1
                    ]);
                    $tbl_department[$department_name] = (object) ['id' => $department_id];
                } else {
                    $department_id = $department->id;
                }

                // Check if branch exists, if not insert it
                $branch = DB::connection("intra_payroll")->table('tbl_branch')
                    ->where('branch', $branch_name)
                    ->first();

                if (!$branch) {
                    $branch_id = DB::connection("intra_payroll")->table('tbl_branch')->insertGetId([
                        'branch' => $branch_name,
                        'code' => $branch_name,
                        'schedule_id' => 0,
                        'is_active' => 1,
                        'date_created' => date("Y-m-d H:i:s"),
                        'user_id' => Auth::user()->id ?? 1
                    ]);
                    $tbl_branch[$branch_name] = (object) ['id' => $branch_id];
                } else {
                    $branch_id = $branch->id;
                }

                // Check if employee exists by employee code
                $existing_employee = DB::connection("intra_payroll")->table("tbl_employee")
                    ->where("emp_code", $emp_code)
                    ->first();

                if ($existing_employee) {
                    
                    // Update existing employee - ALL FIELDS
                    DB::connection("intra_payroll")->table("tbl_employee")
                        ->where("emp_code", $emp_code)
                        ->update([
                            "last_name" => $last_name,
                            "first_name" => $first_name,
                            "middle_name" => $middle_name,
                            "ext_name" => $ext_name,
                            "contact_no" => $contact_no,
                            "address" => $address,
                            "date_of_birth" => $birth_date,
                            "start_date" => $start_date,
                            "department" => $department_id,
                            "position_id" => $position_id,
                            "branch_id" => $branch_id,
                            "employee_status" => $employee_status,
                            "salary_type" => strtoupper($salary_type),
                            "salary_rate" => $salary_rate ?: 0,
                            "sss_number" => $sss,
                            "philhealth_number" => $philhealth,
                            "hdmf_number" => $hdmf,
                            "tin_number" => $tin,
                            "is_mwe" => $is_mwe,
                            "fix_divisor" => $fix_divisor,
                            "fix_sss" => $fix_sss,
                            "fix_hdmf" => $fix_hdmf,
                            "fix_philhealth" => $fix_philhealth,
                            "fix_tax_rate" => $fix_tax_rate,
                            "is_active" => $is_active,
                            "yearly_divisor" => $yearly_divisor,
                            "hr_group" => $hr_group
                        ]);
                    
                    Log::info("Updated existing employee", ['emp_code' => $emp_code]);
                } else {
                    // Insert new employee - ALL FIELDS
                    $employee_id = DB::connection("intra_payroll")->table("tbl_employee")->insertGetId([
                        "emp_code" => $emp_code,
                        "bio_id" => $emp_code,
                        "last_name" => $last_name,
                        "first_name" => $first_name,
                        "middle_name" => $middle_name,
                        "ext_name" => $ext_name,
                        "contact_no" => $contact_no,
                        "address" => $address,
                        "date_of_birth" => $birth_date,
                        "start_date" => $start_date,
                        "department" => $department_id,
                        "position_id" => $position_id,
                        "branch_id" => $branch_id,
                        "employee_status" => $employee_status,
                        "salary_type" => strtoupper($salary_type),
                        "salary_rate" => $salary_rate ?: 0,
                        "sss_number" => $sss,
                        "philhealth_number" => $philhealth,
                        "hdmf_number" => $hdmf,
                        "tin_number" => $tin,
                        "is_mwe" => $is_mwe,
                        "fix_divisor" => $fix_divisor,
                        "fix_sss" => $fix_sss,
                        "fix_hdmf" => $fix_hdmf,
                        "fix_philhealth" => $fix_philhealth,
                        "fix_tax_rate" => $fix_tax_rate,
                        "is_active" => $is_active,
                        "yearly_divisor" => $yearly_divisor,
                        "hr_group" => $hr_group
                    ]);
                    
                    Log::info("Created new employee", ['emp_code' => $emp_code]);
                }
                
                $processed_count++;
            }
            
            DB::commit();
            
            Log::info("Employee Excel upload completed", [
                'processed_count' => $processed_count,
                'user_id' => Auth::user()->id ?? 1
            ]);
            // Get updated employee count
            $employee_count = DB::connection("intra_payroll")->table("tbl_employee")->where("is_active", 1)->count();

            return response()->json([
                'message' => 'Uploaded successfully',
                'employee_count' => $employee_count,
                'processed_count' => $processed_count,
                'success' => true
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Log the error for debugging
            Log::error("Employee Excel upload failed", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::user()->id ?? 'unknown'
            ]);

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


}
