<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use DB;
use Yajra\DataTables\DataTables;

class scheduleController extends Controller
{
    public function schedule_management(){
        $lib_schedule = DB::connection("intra_payroll")->table("lib_schedule")
                    ->where("is_active", 1)
                    ->get();

        return view("schedule.index")
            ->with("schedule_library",$lib_schedule)
        
        ;
    }

    public function sched_list(Request $request){
        

        $page_permission = Auth::user()->access[$request->page]["access"];


        $data = DB::connection("intra_payroll")->table("lib_week_schedule")
            ->orderBy("date_updated", "DESC")
            ->get();

        $data = collect($data);


        return Datatables::of($data)
            ->addColumn('monday', function($row){
                $info = DB::connection("intra_payroll")->table("lib_schedule")
                        ->where("id", $row->monday)
                        ->where("is_active", 1)

                        ->first();

                        if($info != null){
                            $btn = "<a class='btn btn-sm btn-info' data-toggle='modal' data-target='#sched_info'  data-code='".$info->code."' data-name='".$info->name."' data-am_in='".$info->am_in."' data-am_out='".$info->am_out."' data-pm_in='".$info->pm_in."' data-pm_out='".$info->pm_out."' data-ot_in='".$info->ot_in."' data-ot_out='".$info->ot_out."' data-grace_period='".$info->grace_period."'>".$info->code."</a>";
                        }else{
                            $btn = "<a class='btn btn-sm btn-dark' > Rest day </a>";
                        }
              
                 return $btn;

            })

            ->addColumn('tuesday', function($row){
                $info = DB::connection("intra_payroll")->table("lib_schedule")
                        ->where("id", $row->tuesday)
                        ->where("is_active", 1)

                        ->first();

                        if($info != null){
                            $btn = "<a class='btn btn-sm btn-info' data-toggle='modal' data-target='#sched_info'  data-code='".$info->code."' data-name='".$info->name."' data-am_in='".$info->am_in."' data-am_out='".$info->am_out."' data-pm_in='".$info->pm_in."' data-pm_out='".$info->pm_out."' data-ot_in='".$info->ot_in."' data-ot_out='".$info->ot_out."' data-grace_period='".$info->grace_period."'>".$info->code."</a>";
                        }else{
                            $btn = "<a class='btn btn-sm btn-dark' > Rest day </a>";
                        }
              
                 return $btn;

            })

            ->addColumn('wednesday', function($row){
                $info = DB::connection("intra_payroll")->table("lib_schedule")
                        ->where("id", $row->wednesday)
                        ->where("is_active", 1)

                        ->first();

                        if($info != null){
                            $btn = "<a class='btn btn-sm btn-info' data-toggle='modal' data-target='#sched_info'  data-code='".$info->code."' data-name='".$info->name."' data-am_in='".$info->am_in."' data-am_out='".$info->am_out."' data-pm_in='".$info->pm_in."' data-pm_out='".$info->pm_out."' data-ot_in='".$info->ot_in."' data-ot_out='".$info->ot_out."' data-grace_period='".$info->grace_period."'>".$info->code."</a>";
                        }else{
                            $btn = "<a class='btn btn-sm btn-dark' > Rest day </a>";
                        }
                 return $btn;
            })
            ->addColumn('thursday', function($row){
                $info = DB::connection("intra_payroll")->table("lib_schedule")
                        ->where("id", $row->thursday)
                        ->where("is_active", 1)

                        ->first();

                        if($info != null){
                            $btn = "<a class='btn btn-sm btn-info' data-toggle='modal' data-target='#sched_info'  data-code='".$info->code."' data-name='".$info->name."' data-am_in='".$info->am_in."' data-am_out='".$info->am_out."' data-pm_in='".$info->pm_in."' data-pm_out='".$info->pm_out."' data-ot_in='".$info->ot_in."' data-ot_out='".$info->ot_out."' data-grace_period='".$info->grace_period."'>".$info->code."</a>";
                        }else{
                            $btn = "<a class='btn btn-sm btn-dark' > Rest day </a>";
                        }
                 return $btn;
            })

            ->addColumn('friday', function($row){
                $info = DB::connection("intra_payroll")->table("lib_schedule")
                        ->where("id", $row->friday)
                        ->where("is_active", 1)

                        ->first();
                        if($info != null){
                            $btn = "<a class='btn btn-sm btn-info' data-toggle='modal' data-target='#sched_info'  data-code='".$info->code."' data-name='".$info->name."' data-am_in='".$info->am_in."' data-am_out='".$info->am_out."' data-pm_in='".$info->pm_in."' data-pm_out='".$info->pm_out."' data-ot_in='".$info->ot_in."' data-ot_out='".$info->ot_out."' data-grace_period='".$info->grace_period."'>".$info->code."</a>";
                        }else{
                            $btn = "<a class='btn btn-sm btn-dark' > Rest day </a>";
                        }
                 return $btn;
            })
            ->addColumn('saturday', function($row){
                $info = DB::connection("intra_payroll")->table("lib_schedule")
                        ->where("id", $row->saturday)
                        ->where("is_active", 1)

                        ->first();

                        if($info != null){
                            $btn = "<a class='btn btn-sm btn-info' data-toggle='modal' data-target='#sched_info'  data-code='".$info->code."' data-name='".$info->name."' data-am_in='".$info->am_in."' data-am_out='".$info->am_out."' data-pm_in='".$info->pm_in."' data-pm_out='".$info->pm_out."' data-ot_in='".$info->ot_in."' data-ot_out='".$info->ot_out."' data-grace_period='".$info->grace_period."'>".$info->code."</a>";
                        }else{
                            $btn = "<a class='btn btn-sm btn-dark' > Rest day </a>";
                        }
                 return $btn;
            })
            ->addColumn('sunday', function($row){
                $info = DB::connection("intra_payroll")->table("lib_schedule")
                        ->where("id", $row->sunday)
                        ->where("is_active", 1)
                        ->first();

                if($info != null){
                    $btn = "<a class='btn btn-sm btn-info' data-toggle='modal' data-target='#sched_info'  data-code='".$info->code."' data-name='".$info->name."' data-am_in='".$info->am_in."' data-am_out='".$info->am_out."' data-pm_in='".$info->pm_in."' data-pm_out='".$info->pm_out."' data-ot_in='".$info->ot_in."' data-ot_out='".$info->ot_out."' data-grace_period='".$info->grace_period."'>".$info->code."</a>";
                }else{
                    $btn = "<a class='btn btn-sm btn-dark' > Rest day </a>";
                }
                 return $btn;
            })

            ->addColumn('is_active', function($row) use ($page_permission){
                if(preg_match("/U/i", $page_permission)){
                    if($row->is_active){
                        $btn = "<a class='btn btn-sm btn-success' onclick='set_status(".$row->id.")' > Active </a>";
                    }else{
                        $btn = "<a class='btn btn-sm btn-danger' onclick='set_status(".$row->id.")' > In Active </a>";
                    }
                }else{
                    if($row->is_active){
                        $btn = "<a class='btn btn-sm btn-success'> Active </a>";
                    }else{
                        $btn = "<a class='btn btn-sm btn-danger'> In Active </a>";
                    }
                }


              
                 return $btn;
            })
       
            ->addColumn('action', function($row) use ($page_permission){

                if(preg_match("/U/i", $page_permission)){
                    $type = "sched_list";
                    $btn = "<a class='btn btn-sm btn-info' data-target='#sched_add_edit' data-toggle='modal' data-sched_id = '".$row->id."' > EDIT </a>";
                    // add delete in sched
                    $btn .= " <button 
                    class='btn btn-sm btn-danger'
                    onclick='delete_sched(" . $row->id . ", \"" . $type . "\")'
                    >
                    Delete
                    </button>";
                }else{
                    $btn = "";
                }
                return $btn;

                })

                ->rawColumns(['monday','tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday', 'is_active','action'])
            ->make(true);

    }

    
    public function sched_library(Request $request){

        $page_permission = Auth::user()->access[$request->page]["access"];

        $data = DB::connection("intra_payroll")->table("lib_schedule")
            ->orderBy("date_updated", "DESC")
            ->get();

        $data = collect($data);


        return Datatables::of($data)
         


            ->addColumn('is_active', function($row) use ($page_permission){
                if(preg_match("/U/i", $page_permission)){
                    if($row->is_active){
                        $btn = "<a class='btn btn-sm btn-success' onclick='set_status_lib(".$row->id.")' > Active </a>";
                    }else{
                        $btn = "<a class='btn btn-sm btn-danger' onclick='set_status_lib(".$row->id.")' > In Active </a>";
                    }


                }else{
                    if($row->is_active){
                        $btn = "<a class='btn btn-sm btn-success' > Active </a>";
                    }else{
                        $btn = "<a class='btn btn-sm btn-danger' > In Active </a>";
                    }
                }


                
                 return $btn;
            })
       
            ->addColumn('am_in', function($row) use ($page_permission){
                    if($row->is_flexi == 1){
                       return "";
                    }else{
                        return $row->am_in;
                    }
                })
                ->addColumn('am_out', function($row) use ($page_permission){
                    if($row->is_flexi == 1){
                        return "REQUIRED";
                    }else{
                        return $row->am_out;
                    }
                })
                ->addColumn('pm_in', function($row) use ($page_permission){
                    if($row->is_flexi == 1){
                        return "HOURS";
                        
                    }else{
                        return $row->pm_in;
                    }
                })
                ->addColumn('pm_out', function($row) use ($page_permission){
                    if($row->is_flexi == 1){
                        return $row->required_hours ." hours";
                    }else{
                        return $row->pm_out;
                    }
                })
                ->addColumn('ot_in', function($row) use ($page_permission){
                    if($row->is_flexi == 1){
                        return "";
                    }else{
                        return $row->ot_in;
                    }
                })
                ->addColumn('ot_out', function($row) use ($page_permission){
                    if($row->is_flexi == 1){
                        return "";
                    }else{
                        return $row->ot_out;
                    }
                })

            ->addColumn('action', function($row) use ($page_permission){
                if(preg_match("/U/i", $page_permission)){
                    $type = "sched_lib";
                    $btn = "<a class='btn btn-sm btn-info' data-target='#library_add_edit' data-toggle='modal' data-sched_id = '".$row->id."' > EDIT </a>";
                    // add delete in sched
                    $btn .= " <button 
                    class='btn btn-sm btn-danger'
                    onclick='delete_sched(" . $row->id . ", \"" . $type . "\")'
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

    public function get_sched_lib_info(Request $request){
        $data = DB::connection("intra_payroll")->table("lib_schedule")
            ->where("id", $request->id)
            ->first();

            if($data != null){
                // dd($data);
                return json_encode($data);
            }else{
                return json_encode("No Schedule Information");
            }
    }

    public function update_schedule_library(Request $request){
       
        DB::beginTransaction();

        try {

            if($request->id != "new"){
                $data = DB::connection("intra_payroll")->table("lib_schedule")
                ->where("id", $request->id)
                ->first();

                if($data != null){
                    $check = DB::connection("intra_payroll")->table("lib_schedule")
                        ->where("code", $request->code)
                        ->where("id", "!=", $request->id)
                        ->first();
                    if($check != null){
                        return json_encode("Code Already Used");
                    }


                    DB::connection("intra_payroll")->table("lib_schedule")
                    ->where("id", $request->id)
                    ->update([
                        "code" => $request->code,
                        "name" => $request->name,
                        "required_hours" => $request->required_hours,
                        "is_flexi" => $request->is_flexi,
                        "grace_period" => $request->grace_period,
                        "am_in" => $request->am_in,
                        "am_out" => $request->am_out,
                        "pm_in" => $request->pm_in,
                        "pm_out" => $request->pm_out,
                        "ot_in" => $request->ot_in,
                        "ot_out" => $request->ot_out,
                        "user_updated" => Auth::user()->id
                    ]);
                }else{
                    return json_encode(false);
                }


            }else{

                $check = DB::connection("intra_payroll")->table("lib_schedule")
                ->where("code", $request->code)
                ->first();
                    if($check != null){
                        return json_encode("Code Already Used");
                    }


                DB::connection("intra_payroll")->table("lib_schedule")
                ->insert([
                    "code" => $request->code,
                    "name" => $request->name,
                    "required_hours" => $request->required_hours,
                    "is_flexi" => $request->is_flexi,
                    "grace_period" => $request->grace_period,
                    "am_in" => $request->am_in,
                    "am_out" => $request->am_out,
                    "pm_in" => $request->pm_in,
                    "pm_out" => $request->pm_out,
                    "ot_in" => $request->ot_in,
                    "ot_out" => $request->ot_out,
                    "date_created" => date("Y-m-d H:i:s"),
                    "user_updated" => Auth::user()->id
                ]);
            }

            

            DB::commit();
            return json_encode("success");
        } catch (\Throwable $th) {
            DB::rollback();

            return json_encode($th->getMessage());
        }


}



    public function get_sched_info(Request $request){
        $data = DB::connection("intra_payroll")->table("lib_week_schedule")
            ->where("id", $request->id)
            ->first();

            if($data != null){
                // dd($data);
                return json_encode($data);
            }else{
                return json_encode("No Schedule Information");
            }



    }

    public function update_schedule(Request $request){
       
            DB::beginTransaction();

            try {

                if($request->id != "new"){
                    $data = DB::connection("intra_payroll")->table("lib_week_schedule")
                    ->where("id", $request->id)
                    ->first();

                    if($data != null){
                        $check = DB::connection("intra_payroll")->table("lib_week_schedule")
                            ->where("code", $request->code)
                            ->where("id", "!=", $request->id)
                            ->first();
                        if($check != null){
                            return json_encode("Code Already Used");
                        }


                        DB::connection("intra_payroll")->table("lib_week_schedule")
                        ->where("id", $request->id)
                        ->update([
                            "code" => $request->code,
                            "name" => $request->name,
                            "monday" => $request->monday,
                            "tuesday" => $request->tuesday,
                            "wednesday" => $request->wednesday,
                            "thursday" => $request->thursday,
                            "friday" => $request->friday,
                            "saturday" => $request->saturday,
                            "sunday" => $request->sunday,
                            "user_id" => Auth::user()->id
                        ]);
                    }else{
                        return json_encode(false);
                    }


                }else{

                    $check = DB::connection("intra_payroll")->table("lib_week_schedule")
                    ->where("code", $request->code)
                    ->first();
                        if($check != null){
                            return json_encode("Code Already Used");
                        }


                    DB::connection("intra_payroll")->table("lib_week_schedule")
                    ->insert([
                        "code" => $request->code,
                        "name" => $request->name,
                        "monday" => $request->monday,
                        "tuesday" => $request->tuesday,
                        "wednesday" => $request->wednesday,
                        "thursday" => $request->thursday,
                        "friday" => $request->friday,
                        "saturday" => $request->saturday,
                        "sunday" => $request->sunday,
                        "date_created" => date("Y-m-d H:i:s"),
                        "user_id" => Auth::user()->id
                    ]);
                }

                

                DB::commit();
                return json_encode("success");
            } catch (\Throwable $th) {
                DB::rollback();

                return json_encode($th->getMessage());
            }


    }




    public function update_status(Request $request){
        

        DB::beginTransaction();
        try {
            //code...
            $data = DB::connection("intra_payroll")->table("lib_week_schedule")
                ->where("id", $request->id)
                ->first();
            if($data != null){
                if($data->is_active){
                    $update =0;
                }else{
                    $update = 1;
                }
                DB::connection("intra_payroll")->table("lib_week_schedule")
                ->where("id", $request->id)
                ->update(
                  [  "is_active" => $update]
                );

            }


            DB::commit();
            return json_encode(true);
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollback();
            return json_encode(false);
        }


    }

    
    public function update_status_lib(Request $request){
        

        DB::beginTransaction();
        try {
            //code...
            $data = DB::connection("intra_payroll")->table("lib_schedule")
                ->where("id", $request->id)
                ->first();
            if($data != null){
                if($data->is_active){
                    $update =0;
                }else{
                    $update = 1;
                }
                DB::connection("intra_payroll")->table("lib_schedule")
                ->where("id", $request->id)
                ->update(
                  [  "is_active" => $update]
                );

            }


            DB::commit();
            return json_encode(true);
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollback();
            return json_encode(false);
        }


    }
    // add delete in sched
    public function delete_sched(Request $request){
        $type = $request->type;
        $tbl = "";
        if($type == "sched_list"){
            $tbl = "lib_week_schedule";
        }else{
            $tbl = "lib_schedule";
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

    public function getEmployeeSchedule(Request $request)
    {
        try {
            $employee_id = $request->input('employee_id');
            
            if (!$employee_id) {
                return response()->json(['error' => 'Employee ID is required'], 400);
            }

            // Get employee's schedule_id from tbl_employee
            $employee = DB::connection('intra_payroll')
                ->table('tbl_employee')
                ->where('id', $employee_id)
                ->select('schedule_id', 'emp_code', 'first_name', 'last_name')
                ->first();

            if (!$employee) {
                return response()->json(['error' => 'Employee not found'], 404);
            }

            // If employee has a schedule_id, fetch it from lib_schedule
            if ($employee->schedule_id) {
                $schedule = DB::connection('intra_payroll')
                    ->table('lib_schedule')
                    ->where('id', $employee->schedule_id)
                    ->where('is_active', 1)
                    ->first();

                if ($schedule) {
                    return response()->json([
                        'success' => true,
                        'employee_name' => $employee->emp_code . ' - ' . $employee->first_name . ' ' . $employee->last_name,
                        'schedule' => $schedule,
                        'am_in' => $schedule->am_in,
                        'am_out' => $schedule->am_out,
                        'pm_in' => $schedule->pm_in,
                        'pm_out' => $schedule->pm_out,
                        'ot_in' => $schedule->ot_in,
                        'ot_out' => $schedule->ot_out,
                        'grace_period' => $schedule->grace_period,
                        'schedule_code' => $schedule->code,
                        'schedule_name' => $schedule->name
                    ], 200);
                }
            }

            return response()->json([
                'success' => false,
                'employee_name' => $employee->emp_code . ' - ' . $employee->first_name . ' ' . $employee->last_name,
                'message' => 'No schedule assigned to this employee'
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Server Error: ' . $e->getMessage()], 500);
        }
    }
}

