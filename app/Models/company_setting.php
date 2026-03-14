<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;
use Auth;
class company_setting extends Model
{

    function search_multi_array($array, $key, $value) {
        foreach ($array as $subarray) {
            if (isset($subarray[$key]) && $subarray[$key] == $value) {
                return $subarray;
            }
        }
        return null;
    }
   
    public function getCompanySettings(){
        $settings = DB::connection("intra_payroll")->table("tbl_site_config")
            ->first();

            $company = array(
                "company_name" => "",
                "logo_main" => "",
                "logo_sub" => "",
                "default_work_settings" => "",
                "address" => "",
                "divisor" => "26",
                "daily_divisor" => "8",
                "url" => "",
                "is_government" => "",
                "gsis_contribution" => 0,
                "gsis_company" => 0,
                "required_lunch_in_out" => 0,
                "version" => "1"
            );

            if($settings != null){
                $company = array(
                    "company_name" => $settings->company_name,
                    "logo_main" => $settings->logo_main,
                    "logo_sub" => $settings->logo_sub,
                    "default_work_settings" => $settings->default_work_settings,
                    "address" => $settings->address,
                    "divisor" =>$settings->divisor,
                    "daily_divisor" =>$settings->daily_divisor,
                    "url" => $settings->url,
                    "is_government" => $settings->is_government,
                    "gsis_contribution" => $settings->gsis_contribution,
                    "gsis_company" => $settings->gsis_company,
                    "required_lunch_in_out" => $settings->required_lunch_in_out,
                    "version" => $settings->version
                    
                );
            }
      

            $lib_position  = json_decode(json_encode(DB::connection("intra_payroll")->table("lib_position")->get()),true);
            $tbl_department  = json_decode(json_encode(DB::connection("intra_payroll")->table("tbl_department")->get()),true);
            $tbl_branch  = json_decode(json_encode(DB::connection("intra_payroll")->table("tbl_branch")->get()),true);
            $lib_designation  = json_decode(json_encode(DB::connection("intra_payroll")->table("lib_designation")->get()),true);
            


            $tbl_employee = DB::connection("intra_payroll")->table("tbl_employee")
            ->where("user_id", Auth::user()->id)
            ->first();
            if($tbl_employee != null){
                $data =  $this->search_multi_array($lib_position, "id", $tbl_employee->position_id);
                    $position = "N/A";  if(isset($data["name"])) $position = $data["name"];
                $data =  $this->search_multi_array($tbl_department, "id", $tbl_employee->department);
                    $department = "N/A";  if(isset($data["department"])) $department = $data["department"];
                $data =  $this->search_multi_array($tbl_branch, "id", $tbl_employee->branch_id);
                    $branch = "N/A";  if(isset($data["branch"])) $branch = $data["branch"];
                $data =  $this->search_multi_array($lib_designation, "id", $tbl_employee->designation);
                    $designation = "N/A";  if(isset($data["name"])) $designation = $data["name"];

                $company["linked_employee"] = array(
                    "profile_picture" => $tbl_employee->profile_picture,
                    "emp_code" => $tbl_employee->emp_code,
                    "name" => $tbl_employee->last_name.", ".$tbl_employee->first_name,
                    "position" => $position,
                    "department" => $department,
                    "branch" => $branch,
                    "designation" => $designation,
                    "bio_id" => $tbl_employee->bio_id,
                    "id" => $tbl_employee->id,
                    "branch_id" => $tbl_employee->branch_id,

                    

                );
                
            }else{
                $company["linked_employee"] = array(
                    "profile_picture" => "",
                    "emp_code" => "",
                    "name" => "",
                    "position" => "",
                    "id" => "0",
                    "position" => "",
                    "department" => "",
                    "branch" => "",
                    "designation" => "",
                    "bio_id" => "",
                    "branch_id" => "",
                );
            }



            return $company;
    }

}
