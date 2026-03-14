<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;
use Auth;
class employee_logged extends Model
{
    public function getLinkedEmployee(){
        
        $tbl_employee = DB::connection("intra_payroll")->table("tbl_employee")
            ->where("user_id", Auth::user()->id)
            ->first();
            $linked = array();
            if($tbl_employee != null){
                $linked["profile_picture"] = $tbl_employee->profile_picture;
                $linked["emp_code"] = $tbl_employee->emp_code;
                $linked["id"] = $tbl_employee->id;
               
            }else{
                $linked["profile_picture"] = "";
                $linked["emp_code"] = "";
                $linked["id"] = "0";
            }
           
    return $linked;
    }
}
