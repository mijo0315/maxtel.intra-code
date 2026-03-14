<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ManagementSummaryExport;

class SummaryController extends Controller
{
    /**
     * Display Management Overview with employee selector
     */
    public function index()
    {
        return view('Summary.index');
    }

    /**
     * Get all employees for dropdown
     */
    public function getAllEmployees()
    {
        try {
            $role_id = Auth::user()->role_id;
            $query = DB::connection('intra_payroll')
                ->table('tbl_employee')
                ->select(
                    'id',
                    DB::raw("CONCAT(first_name, ' ', IFNULL(middle_name, ''), ' ', last_name) as name"),
                    'emp_code'
                )
                ->where('is_active', 1);
            
            // Filter employees based on role-based group management
            if ($role_id === 1) {
                // Admin sees all employees
                $employees = $query->orderBy('first_name')->get();
            } elseif ($role_id === 4) { // HR Group D
                $employees = $query->where(function ($q) {
                    $q->where("hr_group", "group_d")
                    ->orWhere("user_id", Auth::user()->id);
                })->orderBy('first_name')->get();
            } elseif ($role_id === 5) { // HR Group B,C,E
                $employees = $query->where(function ($q) {
                    $q->whereIn("hr_group", ["group_b","group_c","group_e"])
                    ->orWhere("user_id", Auth::user()->id);
                })->orderBy('first_name')->get();
            } elseif ($role_id === 14) { // HR Group B,C
                $employees = $query->where(function ($q) {
                    $q->whereIn("hr_group", ["group_b","group_c"])
                    ->orWhere("user_id", Auth::user()->id);
                })->orderBy('first_name')->get();
            } elseif ($role_id === 15) { // HR Group C,E
                $employees = $query->where(function ($q) {
                    $q->whereIn("hr_group", ["group_c","group_e"])
                    ->orWhere("user_id", Auth::user()->id);
                })->orderBy('first_name')->get();
            } else {
                // For other roles, only show their own record
                $employees = $query->where("user_id", Auth::user()->id)->orderBy('first_name')->get();
            }

            return response()->json($employees);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get employee summary data
     */
    public function getEmployeeSummary(Request $request)
    {
        try {
            $employeeId = $request->input('employee_id');

            if (!$employeeId) {
                return response()->json(['error' => 'Employee ID is required'], 400);
            }

            // Get NTE Count for employee
            $nte_count = DB::table('nte_notes')
                ->whereNull('parent_id')
                ->where('employee_id', $employeeId)
                ->count();

            $nte_pending = DB::table('nte_notes')
                ->whereNull('parent_id')
                ->where('employee_id', $employeeId)
                ->where('status', '!=', 'resolved')
                ->count();

            $nte_resolved = DB::table('nte_notes')
                ->whereNull('parent_id')
                ->where('employee_id', $employeeId)
                ->where('status', 'resolved')
                ->count();

            // Get Performance Improvement Plan Count for employee
            $performance_count = DB::table('performance_improvement_notes')
                ->whereNull('parent_id')
                ->where('employee_id', $employeeId)
                ->count();

            $performance_pending = DB::table('performance_improvement_notes')
                ->whereNull('parent_id')
                ->where('employee_id', $employeeId)
                ->where('status', '!=', 'resolved')
                ->count();

            $performance_resolved = DB::table('performance_improvement_notes')
                ->whereNull('parent_id')
                ->where('employee_id', $employeeId)
                ->where('status', 'resolved')
                ->count();

            // Get Disciplinary Count for employee
            $disciplinary_count = DB::table('disciplinary_notes')
                ->whereNull('parent_id')
                ->where('employee_id', $employeeId)
                ->count();

            $disciplinary_pending = DB::table('disciplinary_notes')
                ->whereNull('parent_id')
                ->where('employee_id', $employeeId)
                ->where('status', '!=', 'resolved')
                ->count();

            $disciplinary_resolved = DB::table('disciplinary_notes')
                ->whereNull('parent_id')
                ->where('employee_id', $employeeId)
                ->where('status', 'resolved')
                ->count();

            // Get Personnel Action Count for employee
            $personnel_action_count = DB::connection('intra_payroll')
                ->table('tbl_personnel_action')
                ->where('emp_id', $employeeId)
                ->count();

            $personnel_action_pending = DB::connection('intra_payroll')
                ->table('tbl_personnel_action')
                ->where('emp_id', $employeeId)
                ->where('status', '!=', 'approved')
                ->count();

            $personnel_action_approved = DB::connection('intra_payroll')
                ->table('tbl_personnel_action')
                ->where('emp_id', $employeeId)
                ->where('status', 'approved')
                ->count();

            // Prepare summary data
            $summaryData = [
                'nte' => [
                    'total' => $nte_count,
                    'pending' => $nte_pending,
                    'resolved' => $nte_resolved,
                ],
                'performance_improvement' => [
                    'total' => $performance_count,
                    'pending' => $performance_pending,
                    'resolved' => $performance_resolved,
                ],
                'disciplinary' => [
                    'total' => $disciplinary_count,
                    'pending' => $disciplinary_pending,
                    'resolved' => $disciplinary_resolved,
                ],
                'personnel_action' => [
                    'total' => $personnel_action_count,
                    'pending' => $personnel_action_pending,
                    'approved' => $personnel_action_approved,
                ],
            ];

            return response()->json($summaryData);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get actual employee records (NTE, Performance Improvement, Disciplinary)
     */
    public function getEmployeeRecords(Request $request)
    {
        try {
            $employeeId = $request->input('employee_id');

            if (!$employeeId) {
                return response()->json(['error' => 'Employee ID is required'], 400);
            }

            $role_id = Auth::user()->role_id;
            
            // Check if user has access to this employee based on role-based groups
            if ($role_id !== 1) { // Not admin
                $employee = DB::connection('intra_payroll')
                    ->table('tbl_employee')
                    ->where('id', $employeeId)
                    ->first();
                
                if (!$employee) {
                    return response()->json(['error' => 'Employee not found'], 404);
                }
                
                // Check if user has access to this employee
                $hasAccess = false;
                if ($employee->user_id === Auth::user()->id) {
                    $hasAccess = true;
                } else {
                    $allowedGroups = [];
                    if ($role_id === 4) {
                        $allowedGroups = ['group_d'];
                    } elseif ($role_id === 5) {
                        $allowedGroups = ['group_b', 'group_c', 'group_e'];
                    } elseif ($role_id === 14) {
                        $allowedGroups = ['group_b', 'group_c'];
                    } elseif ($role_id === 15) {
                        $allowedGroups = ['group_c', 'group_e'];
                    }
                    
                    if (in_array($employee->hr_group, $allowedGroups)) {
                        $hasAccess = true;
                    }
                }
                
                if (!$hasAccess) {
                    return response()->json(['error' => 'You do not have access to this employee\'s records'], 403);
                }
            }

            // Get NTE records for employee
            $nte_records = DB::table('nte_notes')
                ->select('id', 'date_served', 'case_details', 'remarks')
                ->where('employee_id', $employeeId)
                ->whereNull('parent_id')
                ->orderBy('date_served', 'desc')
                ->get();

            // Get Performance Improvement records for employee
            $performance_records = DB::table('performance_improvement_notes')
                ->select('id', 'date_served', 'case_details', 'remarks')
                ->where('employee_id', $employeeId)
                ->whereNull('parent_id')
                ->orderBy('date_served', 'desc')
                ->get();

            // Get Disciplinary records for employee
            $disciplinary_records = DB::table('disciplinary_notes')
                ->select('id', 'date_served', 'case_details', 'remarks')
                ->where('employee_id', $employeeId)
                ->whereNull('parent_id')
                ->orderBy('date_served', 'desc')
                ->get();

            // Prepare response data
            $recordsData = [
                'nte' => $nte_records,
                'performance' => $performance_records,
                'disciplinary' => $disciplinary_records,
            ];

            return response()->json($recordsData);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Export employee summary to Excel
     */
    public function exportEmployeeSummary(Request $request)
    {
        try {
            $employeeId = $request->input('employee_id');

            if (!$employeeId) {
                return back()->with('error', 'Employee ID is required');
            }

            // Get employee details
            $employee = DB::connection('intra_payroll')
                ->table('tbl_employee')
                ->select(
                    'id',
                    DB::raw("CONCAT(first_name, ' ', IFNULL(middle_name, ''), ' ', last_name) as name"),
                    'emp_code'
                )
                ->where('id', $employeeId)
                ->first();

            if (!$employee) {
                return back()->with('error', 'Employee not found');
            }

            // Get NTE records
            $nte_records = DB::table('nte_notes')
                ->select('id', 'date_served', 'case_details', 'remarks')
                ->where('employee_id', $employeeId)
                ->whereNull('parent_id')
                ->orderBy('date_served', 'desc')
                ->get();

            // Get Performance Improvement records
            $performance_records = DB::table('performance_improvement_notes')
                ->select('id', 'date_served', 'case_details', 'remarks')
                ->where('employee_id', $employeeId)
                ->whereNull('parent_id')
                ->orderBy('date_served', 'desc')
                ->get();

            // Get Disciplinary records
            $disciplinary_records = DB::table('disciplinary_notes')
                ->select('id', 'date_served', 'case_details', 'remarks')
                ->where('employee_id', $employeeId)
                ->whereNull('parent_id')
                ->orderBy('date_served', 'desc')
                ->get();

            $filename = 'Management_Summary_' . str_replace(' ', '_', $employee->name) . '_' . date('Y-m-d_H-i-s') . '.xlsx';

            return Excel::download(
                new ManagementSummaryExport($employee, $nte_records, $performance_records, $disciplinary_records),
                $filename
            );
        } catch (\Exception $e) {
            return back()->with('error', 'Error exporting data: ' . $e->getMessage());
        }
    }

    /**
     * Get record details for viewing in modal
     */
    public function getRecordDetails(Request $request)
    {
        try {
            $type = $request->input('type');
            $id = $request->input('id');

            if (!$type || !$id) {
                return response()->json(['error' => 'Type and ID are required'], 400);
            }

            $table = '';
            if ($type === 'nte') {
                $table = 'nte_notes';
            } elseif ($type === 'performance') {
                $table = 'performance_improvement_notes';
            } elseif ($type === 'disciplinary') {
                $table = 'disciplinary_notes';
            } else {
                return response()->json(['error' => 'Invalid record type'], 400);
            }

            $record = DB::table($table)
                ->select('id', 'date_served', 'case_details', 'remarks', 'attachment_path')
                ->where('id', $id)
                ->first();

            if (!$record) {
                return response()->json(['error' => 'Record not found'], 404);
            }

            return response()->json($record);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
