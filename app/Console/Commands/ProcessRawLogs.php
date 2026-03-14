<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessRawLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'time_card:process_raw_logs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process raw logs yesterday and update time cards, runs every 5:00 am daily.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        DB::beginTransaction();

        try {
            $yesterday_start_time = Carbon::yesterday();
            $yesterday_end_time = Carbon::yesterday()->endOfDay();

            Log::info("Processing raw logs: " . $yesterday_start_time->toDateTimeString() . " to " . $yesterday_end_time->toDateTimeString());

            $logs = DB::connection("intra_payroll")->table("tbl_raw_logs")
                ->join("tbl_employee", "bio_id", "=", "biometric_id")
                ->whereBetween("logs",[$yesterday_start_time, $yesterday_end_time])
                ->where("biometric_id", "!=", "")
                ->orderBy("tbl_raw_logs.logs", "ASC")
                ->orderBy("state")
                ->get();

            foreach($logs  as $log_data){
                $target_date = date("Y-m-d", strtotime($log_data->logs));

                $log_info = strtotime($log_data->logs);

                //CHECK TC
                $tc = DB::connection("intra_payroll")->table("tbl_timecard")
                    ->where("emp_id", $log_data->id)
                    ->where("target_date", $target_date)
                    ->first();

                if($tc != null){
                    $tc =json_decode(json_encode($tc), true);

                    if($log_data->state == "FLEX_IN" || $log_data->state == "FLEX_OUT" ){
                        //
                    }else{
                        $inserted_time = $tc[$log_data->state];
                    }
             
                    if($log_data->state == "AM_IN" || $log_data->state == "PM_IN" || $log_data->state == "OT_IN") {
                        if($inserted_time != "" || $inserted_time != null){
                            $current = strtotime($inserted_time);

                            if($log_info < $current){
                                DB::connection("intra_payroll")->table("tbl_timecard")
                                    ->where("emp_id", $tc["emp_id"])
                                    ->where("target_date", $target_date)
                                    ->where("is_manual", 0)
                                    ->update([
                                        $log_data->state => $log_data->logs,
                                        "user_id" => 1
                                    ]);
                            }
                        }else{

                            DB::connection("intra_payroll")->table("tbl_timecard")
                                ->where("emp_id", $tc["emp_id"])
                                ->where("target_date", $target_date)
                                ->where("is_manual", 0)
                                ->update([
                                    $log_data->state => $log_data->logs,
                                    "user_id" => 1
                                ]);
                        }
                    } elseif($log_data->state == "AM_OUT" || $log_data->state == "PM_OUT" || $log_data->state == "OT_OUT") {
                        if($inserted_time != "" || $inserted_time != null){
                            $current = strtotime($inserted_time);

                                if($log_info > $current){
                                    DB::connection("intra_payroll")->table("tbl_timecard")
                                        ->where("emp_id", $tc["emp_id"])
                                        ->where("target_date", $target_date)
                                        ->where("is_manual", 0)
                                        ->update([
                                            $log_data->state => $log_data->logs,
                                            "user_id" => 1
                                        ]);
                                }
                        }else{
                            DB::connection("intra_payroll")->table("tbl_timecard")
                                ->where("emp_id", $tc["emp_id"])
                                ->where("target_date", $target_date)
                                ->where("is_manual", 0)
                                ->update([
                                    $log_data->state => $log_data->logs,
                                    "user_id" => 1
                                ]);
                        }

                    }else{
                        if($log_data->state == "FLEX_OUT"){
                            $tc_flex = DB::connection("intra_payroll")->table("tbl_timecard")
                                ->where("emp_id", $tc["emp_id"])
                                ->where("target_date", $target_date)
                                ->first();

                            if($tc_flex != NULL){
                                // dd($tc_flex);"2023-08-23 23:16:36
                                $flex_in_log =strtotime($tc_flex->AM_IN);
                                $flex_out_log = strtotime($log_data->logs);

                                // dd($log_data->logs);"2023-08-23 23:20:25"

                                $time_consume = $flex_in_log - $flex_out_log;

                                $total_consume = round(abs($time_consume) / 60,2);

                                // dd($total_consume);

                                $hours = $total_consume / 60;

                                $total_hours = $tc_flex->flexi_hours + $hours;   

                                DB::connection("intra_payroll")->table("tbl_timecard")
                                ->where("emp_id", $tc["emp_id"])
                                ->where("target_date", $target_date)
                                ->where("is_manual", 0)
                                ->update([
                                    "AM_IN" => NULL,
                                    "user_id" => 1,
                                    "flexi_hours" => $total_hours
                                ]);
                            }

                        }elseif($log_data->state == "FLEX_IN"){
                            DB::connection("intra_payroll")->table("tbl_timecard")
                            ->where("emp_id", $tc["emp_id"])
                            ->where("target_date", $target_date)
                            ->where("is_manual", 0)
                            ->update([
                                "AM_IN" => $log_data->logs,
                                "user_id" => 1,
                            ]);
                        }
                    }
                }else{
                    if($log_data->state == "OT_IN" || $log_data->state == "OT_OUT"){
                        //CHECK IF HAS IN AND OUT
                        $tc2 = DB::connection("intra_payroll")->table("tbl_timecard")
                            ->where("emp_id", $log_data->id)
                            ->where("target_date", $target_date)
                            ->where("AM_IN", "!=", NULL)
                            ->orWhere("emp_id", $log_data->id)
                            ->where("target_date", $target_date)
                            ->where("AM_IN", "!=", "")
                            ->first();

                        if($tc2 != null){

                            $log2 = strtotime($tc2->logs);

                            $current_log = strtotime($log_data->logs);

                                if($log2 > $current_log){
                                    $target_date = date("Y-m-d", strtotime($target_date ." -1 day"));
                                }

                                $tc3 = DB::connection("intra_payroll")->table("tbl_timecard")
                                ->where("emp_id", $log_data->id)
                                ->where("target_date", $target_date)
                                ->first();

                                if($tc3 != null){

                                    DB::connection("intra_payroll")->table("tbl_timecard")
                                        ->where("emp_id", $tc["emp_id"])
                                        ->where("target_date", $target_date)
                                        ->where("is_manual", 0)
                                        ->update([
                                            $log_data->state => $log_data->logs,
                                            "user_id" => 1

                                        ]);

                                }else{

                                    DB::connection("intra_payroll")->table("tbl_timecard")
                                        ->insert([
                                            $log_data->state => $log_data->logs,
                                            "user_id" => 1,
                                            "target_date" => $target_date,
                                            "emp_id" => $log_data->id
                                        ]);
                                }
                        }else{

                            DB::connection("intra_payroll")->table("tbl_timecard")
                                        ->insert([
                                            $log_data->state => $log_data->logs,
                                            "user_id" => 1,
                                            "target_date" => $target_date,
                                            "emp_id" => $log_data->id
                                        ]);

                        }

                    }elseif($log_data->state == "AM_IN" || $log_data->state == "AM_OUT" || $log_data->state == "PM_IN" || $log_data->state == "PM_OUT"){

                        DB::connection("intra_payroll")->table("tbl_timecard")
                        ->insert([
                            $log_data->state => $log_data->logs,
                            "user_id" => 1,
                            "target_date" => $target_date,
                            "emp_id" => $log_data->id
                        ]);

                    }else{

                        if($log_data->state == "FLEX_IN"){

                            DB::connection("intra_payroll")->table("tbl_timecard")
                            ->insert([
                                "AM_IN" => $log_data->logs,
                                "user_id" => 1,
                                "target_date" => $target_date,
                                "emp_id" => $log_data->id
                            ]);
                        }
                    }

                }

            }

            DB::commit();

            Log::info("Processing raw logs success: " . $yesterday_start_time->toDateTimeString() . " to " . $yesterday_end_time->toDateTimeString());
        } catch (\Throwable $th) {

            DB::rollback();

            Log::info("Processing raw logs error: " . $yesterday_start_time->toDateTimeString() . " to " . $yesterday_end_time->toDateTimeString());
            Log::info($th->getMessage().'--'. $th->getLine());
        }
    }

}
