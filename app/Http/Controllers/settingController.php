<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Auth;
use Storage;

class settingController extends Controller
{
    public function system_management(){

        $schedule_list = DB::connection("intra_payroll")->table("lib_week_schedule")
            ->where("is_active", 1)
            ->get();
   
        return view("settings.index")
            ->with("schedule_list", $schedule_list)
            ;

    }


    public function update_post(Request $request){

            DB::beginTransaction();

            try {
                $update_array = array();

                if($request->main_logo != null){
                    $base64_str = $request->main_logo;
                    $t=time()."main";
                    $image = base64_decode($base64_str);
                    $safeName = 'upload_images/logo/'.$t.'.'.'png';
                    // $safeName = $t.'.'.'png';
                    // dd("aaa");
                    $upload_name = Storage::disk('public')->put($safeName, $image);
                    // dd($upload_name);
                    $update_array["logo_main"] =  "public/".$safeName;
                }
        
                if($request->sub_logo != null){
                    $base64_str = $request->sub_logo;
                    $t=time()."sub";
                    $image = base64_decode($base64_str);
                    $safeName = 'upload_images/logo/'.$t.'.'.'png';
                    $upload_name = Storage::disk('public')->put($safeName, $image);
                    $update_array["logo_sub"] =  "public/".$safeName;
                }
                
                    $update_array["company_name"] = $request->company_name;
                    $update_array["url"] = $request->url;
                    $update_array["address"] = $request->address;
                    $update_array["default_work_settings"] = $request->default_work_settings;
                    $update_array["divisor"] = $request->divisor;
                    $update_array["daily_divisor"] = $request->daily_divisor;
                    $update_array["is_government"] = $request->is_gov;
                    
                DB::connection("intra_payroll")->table("tbl_site_config")
                    ->update($update_array);


            DB::commit();
            return json_encode(true);
            } catch (\Throwable $th) {
                //throw $th;
                DB::rollBack();
                // dd();
                // return json_encode($th->getMessage());
                return json_encode(false);
               
            }





    }



}
