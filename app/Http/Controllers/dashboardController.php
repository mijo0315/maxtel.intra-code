<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Auth;
class dashboardController extends Controller
{
        public function dashboard(){
            $role_id = Auth::user()->role_id;
            $tbl_employee = DB::connection("intra_payroll")->table("tbl_employee");

            if ($role_id === 4) { // HR Group B
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

            $tbl_employee = $tbl_employee->where("is_active", 1)->get();

            $tbl_employee = count($tbl_employee);
            // $department = count(DB::connection("intra_payroll")->table("tbl_employee")->select("department")->where("is_active",1)->groupBy('department')->get());
            $department = DB::connection("intra_payroll")->table("tbl_department")->where("is_active",1)->count();
            $files = count(DB::connection("intra_payroll")->table("tbl_file")->get());
            $loans = count(DB::connection("intra_payroll")->table("tbl_loan_file")->where("is_done",0)->get());
            // $branches = count(DB::connection("intra_payroll")->table("tbl_employee")->select("branch_id")->where("is_active",1)->groupBy('branch_id')->get());
            $branches = DB::connection("intra_payroll")->table("tbl_branch")->where("is_active",1)->count();
            $leave_total = 0;
            $payroll_processing = 0;
            $payroll_done = 0;

            $logs = 0;


            if(Auth::user()->access["dashboard"]['user_type'] == "employee" )
            {
                $leave_count = DB::connection("intra_payroll")->table("tbl_leave_used")->where("emp_id",Auth::user()->company["linked_employee"]["id"])->where("leave_status", "APPROVED")->where("leave_year", date("Y"))->sum("leave_count");
                $leave_total = DB::connection("intra_payroll")->table("tbl_leave_credits")->where("emp_id",Auth::user()->company["linked_employee"]["id"])->where("year_given", date("Y"))->sum("leave_count");
                // dd($leave_total);
                $payroll_processing = count(DB::connection("intra_payroll")->table("tbl_payroll")->where("employee", "LIKE", "%|".Auth::user()->company["linked_employee"]["id"]."|%")->where("payroll_status", "!=", "CLOSED")->get());
                $payroll_done = count(DB::connection("intra_payroll")->table("tbl_payroll")->where("employee", "LIKE", "%|".Auth::user()->company["linked_employee"]["id"]."|%")->where("payroll_status", "CLOSED")->get());
                
                $logs = count(DB::connection("intra_payroll")->table("tbl_raw_logs")->where("biometric_id", Auth::user()->company["linked_employee"]["bio_id"])->where("logs", "LIKE", date("Y-m-d")."%")->get());
            }
            else{
                $leave_count = count(DB::connection("intra_payroll")->table("tbl_leave_used")->whereRaw("'".date("Y-m-d")."' BETWEEN leave_date_from and leave_date_to and leave_status = 'APPROVED'")->get() );
            }

            $user_type = Auth::user()->access["dashboard"]["user_type"];
            $is_admin = (strtolower($user_type) === "admin");
            $can_view_graphs = $is_admin;
            $can_view_employee_status = (strtolower($user_type) !== "employee");

            return view("dashboard.index")
                ->with("tbl_employee", $tbl_employee)
                ->with("department", $department)
                ->with("leave_count", $leave_count)
                ->with("leave_total", $leave_total)
                ->with("payroll_done", $payroll_done)
                ->with("payroll_processing", $payroll_processing)
                ->with("logs", $logs)
                ->with("files", $files)
                ->with("loans", $loans)
                ->with("branches", $branches)
                ->with("role_id", $role_id)
                ->with("user_type", $user_type)
                ->with("can_view_graphs", $can_view_graphs)
                ->with("can_view_employee_status", $can_view_employee_status)
            ;

        }

        public function branch_per_emp(){
            $tbl_employee = DB::connection("intra_payroll")->table("tbl_employee")
            ->select("tbl_branch.branch as name", DB::raw("COUNT(tbl_employee.id) as y"))
            ->join("tbl_branch", "tbl_branch.id","=","branch_id")
            ->where("tbl_employee.is_active",1)
            ->groupBy("branch_id")
            ->get();

            return json_encode($tbl_employee);
        }

        public function count_mwe(){
            $tbl_employee = DB::connection("intra_payroll")->table("tbl_employee")
            ->select(DB::raw("IF(is_mwe = 1, 'MWE', 'NON-MWE') as name"), DB::raw("COUNT(tbl_employee.id) as y"))
            ->where("tbl_employee.is_active",1)
            ->groupBy("is_mwe")
            ->get();

            return json_encode($tbl_employee);
        }

        public function getEmployeeManagementRecordsCount(Request $request){
            $employeeId = $request->input('employee_id');

            if (!$employeeId) {
                return response()->json(['error' => 'Employee ID is required'], 400);
            }

            // Get NTE count
            $nte_count = DB::table('nte_notes')
                ->where('employee_id', $employeeId)
                ->whereNull('parent_id')
                ->count();

            // Get Performance Improvement count
            $performance_count = DB::table('performance_improvement_notes')
                ->where('employee_id', $employeeId)
                ->whereNull('parent_id')
                ->count();

            // Get Disciplinary count
            $disciplinary_count = DB::table('disciplinary_notes')
                ->where('employee_id', $employeeId)
                ->whereNull('parent_id')
                ->count();

            return response()->json([
                'nte' => $nte_count,
                'performance' => $performance_count,
                'disciplinary' => $disciplinary_count
            ]);
        }

        public function getApprovalsCount(Request $request){
            // Get pending OT approvals
            $ot_approvals = DB::connection('intra_payroll')
                ->table('tbl_overtime')
                ->where('ot_status', 'PENDING')
                ->orWhere('ot_status', 'For Approval')
                ->count();

            // Get pending filed leave approvals
            $filed_leaves = DB::connection('intra_payroll')
                ->table('tbl_leave_used')
                ->where('leave_status', 'PENDING')
                ->orWhere('leave_status', 'For Approval')
                ->count();

            return response()->json([
                'ot_approvals' => $ot_approvals,
                'filed_leaves' => $filed_leaves
            ]);
        }

        public function getEmployeesByStatus(Request $request){
            $status = $request->input('status');
            $role_id = Auth::user()->role_id;

            try {
                $query = DB::connection('intra_payroll')
                    ->table('tbl_employee')
                    ->where('employee_status', $status)
                    ->where('is_active', 1);

                // Apply HR group filtering based on role_id
                if ($role_id === 4) { // HR Group B
                    $query = $query->where("hr_group", "group_d");
                } elseif ($role_id === 5) { // HR Group B,C,E
                    $query = $query->whereIn("hr_group", ["group_b","group_c","group_e"]);
                } elseif ($role_id === 14) { // HR Group B,C
                    $query = $query->whereIn("hr_group", ["group_b","group_c"]);
                } elseif ($role_id === 15) { // HR Group C,E
                    $query = $query->whereIn("hr_group", ["group_c","group_e"]);
                }

                $employees = $query->select(
                    'id',
                    DB::raw("CONCAT(first_name, ' ', COALESCE(middle_name, ''), ' ', last_name) as name")
                )
                ->get();

                return response()->json([
                    'data' => $employees
                ]);
            } catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
        }

}
