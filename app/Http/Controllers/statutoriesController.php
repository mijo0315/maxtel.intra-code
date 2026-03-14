<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use DB;
use Storage;
use Yajra\DataTables\DataTables;
class statutoriesController extends Controller
{
    public function statutory_management(){

        return view("statutories.index");
    }
    public function update_gsis_rate(Request $request){
        DB::beginTransaction();

        try {
            DB::connection("intra_payroll")->table("tbl_site_config")
                ->update([
                    "gsis_contribution" =>$request->emp_rate,
                    "gsis_company" =>$request->com_rate,
                    "user_id" => Auth::user()->id
                ]);

            DB::commit();
            return json_encode("Success");
        } catch (\Throwable $th) {
            DB::rollback();
            return json_encode($th->getMessage());
        }

    }
    
    
    public function tax_tbl(Request $request){
        $page_permission = Auth::user()->access[$request->page]["access"];
        $data = DB::connection("intra_payroll")->table("lib_tax_table")
            ->orderBy("type")
            ->orderBy("salary_from")
            ->orderBy("salary_to")
            
            ->get();
       
        $data = collect($data);

        return Datatables::of($data)
        ->addColumn('type', function($row){
            return $row->type;
        })
        ->addColumn('salary_from', function($row){
            return number_format($row->salary_from,2);
        })
        ->addColumn('salary_to', function($row){
            return number_format($row->salary_to,2);
        })

        ->addColumn('fix_tax', function($row){
            return number_format($row->fix_amount,2);
        })
        ->addColumn('tax_over', function($row){
            return number_format($row->rate_over,2);
        })
        ->addColumn('tax_rate', function($row){
            return number_format($row->rate,2);
        })
        ->addColumn('year', function($row){
            return $row->effective_year;
        })
        ->addColumn('action', function($row) use ($page_permission){
            $btn = "";
            if(preg_match("/U/i", $page_permission)){
                $btn .= "<a 
                class='btn btn-sm btn-success'
                data-id='".$row->id."' 
                data-type='".$row->type."' 
                data-salary_from='".$row->salary_from."' 
                data-salary_to='".$row->salary_to."' 
                data-fix_amount='".$row->fix_amount."' 
                data-rate_over='".$row->rate_over."' 
                data-rate='".$row->rate."' 
                data-year_effect='".$row->effective_year."' 
                data-toggle='modal' 
                data-target='#tax_modal'
    
                >
                Edit
                </a>";
            }
          

            return $btn;
        })
        ->rawColumns(['action'])
        ->make(true);
    }

    public function hdmf_tbl(Request $request){
        $page_permission = Auth::user()->access[$request->page]["access"];
        $data = DB::connection("intra_payroll")->table("lib_hdmf")
            ->orderBy("salary_from")
            ->orderBy("salary_to")
            
            ->get();

        $data = collect($data);

        return Datatables::of($data)
        ->addColumn('salary_from', function($row){
            return number_format($row->salary_from,2);
        })
        ->addColumn('salary_to', function($row){
            return number_format($row->salary_to,2);
        })

        ->addColumn('com_share', function($row){
            return number_format($row->rate_employer,5);
        })
        ->addColumn('emp_share', function($row){
            return number_format($row->rate_employee,5);
        })
        ->addColumn('year', function($row){
            return $row->year_effect;
        })
        ->addColumn('action', function($row) use ($page_permission){
            $btn = "";
            if(preg_match("/U/i", $page_permission)){
                $btn .= "<a 
                class='btn btn-sm btn-success'
                data-id='".$row->id."' 
                data-salary_from='".$row->salary_from."' 
                data-salary_to='".$row->salary_to."' 
                data-rate_employer='".$row->rate_employer."' 
                data-rate_employee='".$row->rate_employee."' 
                data-year_effect='".$row->year_effect."' 
                data-toggle='modal' 
                data-target='#hdmf_modal'
    
                >
                Edit
                </a>";
            }
          

            return $btn;
        })
        ->rawColumns(['action'])
        ->make(true);
    }

    public function ph_tbl(Request $request){
        $page_permission = Auth::user()->access[$request->page]["access"];
        $data = DB::connection("intra_payroll")->table("lib_philhealth")
            ->orderBy("salary_from")
            ->orderBy("salary_to")
            
            ->get();

        $data = collect($data);

        return Datatables::of($data)
        ->addColumn('salary_from', function($row){
            return number_format($row->salary_from,2);
        })
        ->addColumn('salary_to', function($row){
            return number_format($row->salary_to,2);
        })

        ->addColumn('com_share', function($row){
            return number_format($row->rate_employer,5);
        })
        ->addColumn('emp_share', function($row){
            return number_format($row->rate_employee,5);
        })
        ->addColumn('year', function($row){
            return $row->year_effect;
        })
        ->addColumn('action', function($row) use ($page_permission){
            $btn = "";
            if(preg_match("/U/i", $page_permission)){
                $btn .= "<a 
                class='btn btn-sm btn-success'
                data-id='".$row->id."' 
                data-salary_from='".$row->salary_from."' 
                data-salary_to='".$row->salary_to."' 
                data-rate_employer='".$row->rate_employer."' 
                data-rate_employee='".$row->rate_employee."' 
                data-year_effect='".$row->year_effect."' 
                data-toggle='modal' 
                data-target='#ph_modal'
    
                >
                Edit
                </a>";
            }
          

            return $btn;
        })
        ->rawColumns(['action'])
        ->make(true);
    }

    public function sss_tbl(Request $request){
        $page_permission = Auth::user()->access[$request->page]["access"];
        $data = DB::connection("intra_payroll")->table("lib_sss")
            ->orderBy("salary_from")
            ->orderBy("salary_to")
            
            ->get();

        $data = collect($data);

        return Datatables::of($data)
        ->addColumn('salary_from', function($row){
            return number_format($row->salary_from,2);
        })
        ->addColumn('salary_to', function($row){
            return number_format($row->salary_to,2);
        })
        ->addColumn('credit', function($row){
            return number_format($row->credit_ec,2);
        })
        ->addColumn('com_share', function($row){
            return number_format($row->regular_er,2);
        })
        ->addColumn('emp_share', function($row){
            return number_format($row->regular_ee,2);
        })
        ->addColumn('ec', function($row){
            return number_format($row->ec,2);
        })
        ->addColumn('action', function($row) use ($page_permission){
            $btn = "";
            if(preg_match("/U/i", $page_permission)){
                $btn .= "<a 
                class='btn btn-sm btn-success'
                data-id='".$row->id."' 
                data-salary_from='".$row->salary_from."' 
                data-salary_to='".$row->salary_to."' 
                data-credit_ec='".$row->credit_ec."' 
                data-regular_er='".$row->regular_er."' 
                data-regular_ee='".$row->regular_ee."' 
                data-ec='".$row->ec."' 
                data-toggle='modal' 
                data-target='#sss_modal'
    
                >
                Edit
                </a>";
            }
          

            return $btn;
        })
        ->rawColumns(['action'])
        ->make(true);
    }

    public function update_sss_rate(Request $request){
        DB::beginTransaction();

        try {
            DB::connection("intra_payroll")->table("lib_sss")
                ->where("id", $request->id)
                ->update([
                    "salary_from" => $request->sss_salary_from,
                    "salary_to" => $request->sss_salary_to,
                    "credit_ec" => $request->sss_credit,
                    "regular_er" => $request->sss_com_share,
                    "regular_ee" => $request->sss_emp_share,
                    "ec" => $request->sss_ec,
                    "user_id" => Auth::user()->id
                ]);

            DB::commit();
            return json_encode("Success");
        } catch (\Throwable $th) {
            DB::rollback();
            return json_encode($th->getMessage());
        }



    }
    public function update_ph_rate(Request $request){
        DB::beginTransaction();
        // dd($request->all());
        try {
            DB::connection("intra_payroll")->table("lib_philhealth")
                ->where("id", $request->id)
                ->update([
                    "salary_from" => $request->ph_salary_from,
                    "salary_to" => $request->ph_salary_to,
                    "rate_employer" => $request->ph_com_share,
                    "rate_employee" => $request->ph_emp_share,
                    "year_effect" => $request->ph_year,
                    "user_id" => Auth::user()->id
                ]);

            DB::commit();
            return json_encode("Success");
        } catch (\Throwable $th) {
            DB::rollback();
            return json_encode($th->getMessage());
        }

    }

    
    public function update_tax_rate(Request $request){
        DB::beginTransaction();
    
        try {
            DB::connection("intra_payroll")->table("lib_tax_table")
                ->where("id", $request->id)
                ->update([
                    "type" => $request->tax_type,
                    "salary_from" => $request->tax_salary_from,
                    "salary_to" => $request->tax_salary_to,
                    "fix_amount" => $request->tax_amount,
                    "rate_over" => $request->tax_rate_over,
                    "rate" => $request->tax_rate,
                    "effective_year" => $request->tax_year,
                    "user_id" => Auth::user()->id
                ]);

            DB::commit();
            return json_encode("Success");
        } catch (\Throwable $th) {
            DB::rollback();
            return json_encode($th->getMessage());
        }

    }


    public function update_hdmf_rate(Request $request){
        DB::beginTransaction();
        // dd($request->all());
        try {
            DB::connection("intra_payroll")->table("lib_hdmf")
                ->where("id", $request->id)
                ->update([
                    "salary_from" => $request->hdmf_salary_from,
                    "salary_to" => $request->hdmf_salary_to,
                    "rate_employer" => $request->hdmf_com_share,
                    "rate_employee" => $request->hdmf_emp_share,
                    "year_effect" => $request->hdmf_year,
                    "user_id" => Auth::user()->id
                ]);

            DB::commit();
            return json_encode("Success");
        } catch (\Throwable $th) {
            DB::rollback();
            return json_encode($th->getMessage());
        }

    }
    

}
