<?php

namespace App\Http\Controllers;

use App\Models\PersonnelAction;
use App\Models\PersonnelActionDetail;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class PersonnelActionController extends Controller
{
    public function index()
    {
        // Get user permission for this page
        $routeName = 'personnel_action_form';
        $userAccess = Auth::user()->access[$routeName] ?? null;
        $userPermission = $userAccess['access'] ?? null;
        
        // Check if user has read-only permission (value 3 = "R" only, without C or U)
        $isReadOnly = $userPermission === '3' || (preg_match("/R/i", $userPermission ?? '') && !preg_match("/C|U/i", $userPermission ?? ''));
        
        // Query employees only for non-read-only users (admin/managers with create/update access)
        $employees = [];
        $role_id = Auth::user()->role_id;
        
        if (!$isReadOnly) {
            $query = DB::connection('intra_payroll')
                ->table('tbl_employee')
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
        }

        // Fetch departments for dropdown
        $departments = DB::connection('intra_payroll')
            ->table('tbl_department')
            ->where('is_active', 1)
            ->orderBy('department')
            ->get();

        // Fetch positions for dropdown
        $positions = DB::connection('intra_payroll')
            ->table('lib_position')
            ->where('is_active', 1)
            ->orderBy('name')
            ->get();
        
        // Fetch schedules for dropdown (lib_week_schedule - full weekly schedules)
        $schedules = DB::connection('intra_payroll')
            ->table('lib_week_schedule')
            ->where('is_active', 1)
            ->orderBy('code')
            ->get();
        
        // Fetch daily schedules for dropdown (lib_schedule - individual shifts)
        $daily_schedules = DB::connection('intra_payroll')
            ->table('lib_schedule')
            ->where('is_active', 1)
            ->orderBy('name')
            ->get();
        
        // Fetch branches for dropdown
        $branches = DB::connection('intra_payroll')
            ->table('tbl_branch')
            ->where('is_active', 1)
            ->orderBy('branch')
            ->get();
        
        // Fetch saved personnel actions with related data
        $personnelActions = [];
        try {
            $query = DB::connection('intra_payroll')
                ->table('tbl_personnel_action as pa')
                ->leftJoin('tbl_employee as e', 'pa.employee_id', '=', 'e.id')
                ->leftJoin('lib_position as p', 'e.position_id', '=', 'p.id')
                ->leftJoin('tbl_department as d', 'e.department', '=', 'd.id')
                ->select(
                    'pa.id',
                    'pa.employee_id',
                    'pa.action_type',
                    'pa.effective_date',
                    'pa.remarks',
                    'pa.date_created',
                    'e.emp_code',
                    'e.first_name',
                    'e.last_name',
                    'p.name as position_name',
                    'd.department as department_name'
                );
            
            // Filter by role-based group management
            if ($isReadOnly) {
                // If read-only, show only the logged-in user's employee data
                $loggedInEmployee = DB::connection('intra_payroll')
                    ->table('tbl_employee')
                    ->where('user_id', Auth::user()->id)
                    ->first();
                
                if ($loggedInEmployee) {
                    $query->where('pa.employee_id', $loggedInEmployee->id);
                }
            } else {
                // Filter personnel actions based on role-based groups
                if ($role_id === 1) {
                    // Admin sees all personnel actions
                } elseif ($role_id === 4) { // HR Group D
                    $query->where(function ($q) {
                        $q->where("e.hr_group", "group_d")
                        ->orWhere("e.user_id", Auth::user()->id);
                    });
                } elseif ($role_id === 5) { // HR Group B,C,E
                    $query->where(function ($q) {
                        $q->whereIn("e.hr_group", ["group_b","group_c","group_e"])
                        ->orWhere("e.user_id", Auth::user()->id);
                    });
                } elseif ($role_id === 14) { // HR Group B,C
                    $query->where(function ($q) {
                        $q->whereIn("e.hr_group", ["group_b","group_c"])
                        ->orWhere("e.user_id", Auth::user()->id);
                    });
                } elseif ($role_id === 15) { // HR Group C,E
                    $query->where(function ($q) {
                        $q->whereIn("e.hr_group", ["group_c","group_e"])
                        ->orWhere("e.user_id", Auth::user()->id);
                    });
                } else {
                    // For other roles, only show their own personnel actions
                    $query->where("e.user_id", Auth::user()->id);
                }
            }
            
            $personnelActions = $query->orderBy('pa.date_created', 'desc')->get();
            
            Log::info('Personnel actions fetched: ' . count($personnelActions));
            
        } catch (\Exception $e) {
            Log::warning('Could not fetch personnel actions: ' . $e->getMessage());
            $personnelActions = [];
        }
        
        return view('personnel_action_form.index', compact('employees', 'personnelActions', 'isReadOnly', 'departments', 'positions', 'schedules', 'daily_schedules', 'branches'));
    }

    public function getEmployeeData(Request $request)
    {
        try {
            $employee_id = $request->input('employee_id');
            
            if (!$employee_id) {
                return response()->json(['error' => 'Employee ID is required'], 400);
            }
            
            // Query employee data directly from database
            $employee = DB::connection('intra_payroll')
                ->table('tbl_employee')
                ->where('id', $employee_id)
                ->first();
            
            if (!$employee) {
                return response()->json(['error' => 'Employee not found with ID: ' . $employee_id], 404);
            }

            // Get position name from lib_position
            $position_name = '';
            if (!empty($employee->position_id)) {
                $position = DB::connection('intra_payroll')
                    ->table('lib_position')
                    ->where('id', $employee->position_id)
                    ->value('name');
                $position_name = $position ?? '';
            }

            // Get department name from tbl_department (not lib_department)
            $department_name = '';
            if (!empty($employee->department)) {
                $department = DB::connection('intra_payroll')
                    ->table('tbl_department')
                    ->where('id', $employee->department)
                    ->value('department');
                $department_name = $department ?? '';
            }

            // Get schedule information
            $schedule = DB::connection('intra_payroll')->table('lib_schedule')
                ->where('id', $employee->schedule_id ?? 0)
                ->first();

            $schedule_data = [
                'am_in' => $schedule ? $schedule->am_in : '00:00:00',
                'am_out' => $schedule ? $schedule->am_out : '00:00:00',
                'pm_in' => $schedule ? $schedule->pm_in : '00:00:00',
                'pm_out' => $schedule ? $schedule->pm_out : '00:00:00',
                'ot_in' => $schedule ? $schedule->ot_in : '00:00:00',
                'ot_out' => $schedule ? $schedule->ot_out : '00:00:00'
            ];

            // Get branch name from tbl_branch
            $branch_name = '';
            if (!empty($employee->branch_id)) {
                $branch = DB::connection('intra_payroll')
                    ->table('tbl_branch')
                    ->where('id', $employee->branch_id)
                    ->value('branch');
                $branch_name = $branch ?? '';
            }

            $response_data = [
                'id' => $employee->id,
                'emp_code' => $employee->emp_code ?? '',
                'first_name' => $employee->first_name ?? '',
                'last_name' => $employee->last_name ?? '',
                'position_id' => $employee->position_id ?? '',
                'position_name' => $position_name,
                'date_hired' => !empty($employee->date_created) ? date('Y-m-d', strtotime($employee->date_created)) : '',
                'department' => $employee->department ?? '',
                'department_name' => $department_name,
                'branch_name' => $branch_name,
                'sss_number' => $employee->sss_number ?? '',
                'hdmf_number' => $employee->hdmf_number ?? '',
                'philhealth_number' => $employee->philhealth_number ?? '',
                'tin_number' => $employee->tin_number ?? '',
                'salary_type' => $employee->salary_type ?? '',
                'salary_rate' => $employee->salary_rate ?? 0,
                'allowance' => $employee->allowance ?? '',
                'schedule_id' => $employee->schedule_id ?? 0,
                'is_mwe' => $employee->is_mwe ?? 0,
                'employee_status' => $employee->employee_status ?? '',
                'schedule' => $schedule_data
            ];

            return response()->json($response_data);
        } catch (\Exception $e) {
            Log::error('Employee Data Error: ' . $e->getMessage() . ' - Trace: ' . $e->getTraceAsString());
            return response()->json(['error' => 'Server Error: ' . $e->getMessage()], 500);
        }
    }

    private function getPositionName($position_id)
    {
        // Query from lib_position table
        $position = DB::connection('intra_payroll')->table('lib_position')
            ->where('id', $position_id)
            ->first();
        
        return $position ? $position->name : '';
    }

    private function getDepartmentName($department_id)
    {
        // Query from lib_department table
        $department = DB::connection('intra_payroll')->table('lib_department')
            ->where('id', $department_id)
            ->first();
        
        return $department ? $department->name : '';
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'employee_id' => 'required|exists:intra_payroll.tbl_employee,id',
                'action_type' => 'required|string',
                'effective_date' => 'required|date',
                'remarks' => 'nullable|string'
            ]);

            $validated['date_created'] = now();
            $validated['user_id_added'] = auth()->user()->id;

            $personnelAction = PersonnelAction::create($validated);

            // Store table details if provided
            if ($request->has('details')) {
                $details = $request->input('details');
                foreach ($details as $detail) {
                    // Filter out empty details
                    $filteredDetail = array_filter($detail, function($value) {
                        return $value !== null && $value !== '';
                    });
                    
                    if (!empty($filteredDetail)) {
                        // Update the personnel action with detail fields
                        DB::connection('intra_payroll')->table('tbl_personnel_action')->where('id', $personnelAction->id)->update([
                            'department_from' => $detail['department_from'] ?? null,
                            'department_to' => $detail['department_to'] ?? null,
                            'branch_from' => $detail['branch_from'] ?? null,
                            'branch_to' => $detail['branch_to'] ?? null,
                            'emp_status_from' => $detail['emp_status_from'] ?? null,
                            'emp_status_to' => $detail['emp_status_to'] ?? null,
                            'position_from' => $detail['position_from'] ?? null,
                            'position_to' => $detail['position_to'] ?? null,
                            'schedule_from' => $detail['schedule_from'] ?? null,
                            'schedule_to' => $detail['schedule_to'] ?? null,
                            'supervisor_from' => $detail['supervisor_from'] ?? null,
                            'supervisor_to' => $detail['supervisor_to'] ?? null,
                            'supervisor_position_from' => $detail['supervisor_position_from'] ?? null,
                            'supervisor_position_to' => $detail['supervisor_position_to'] ?? null,
                            'salary_from' => $detail['salary_from'] ?? null,
                            'salary_to' => $detail['salary_to'] ?? null,
                            'allowance_from' => $detail['allowance_from'] ?? null,
                            'allowance_to' => $detail['allowance_to'] ?? null,
                            'others_from' => $detail['others_from'] ?? null,
                            'others_to' => $detail['others_to'] ?? null,
                            'total_comp_from' => $detail['total_comp_from'] ?? null,
                            'total_comp_to' => $detail['total_comp_to'] ?? null,
                        ]);
                    }
                }
            }

            return response()->json(['message' => 'Personnel action created successfully'], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getActionDetails(Request $request)
    {
        try {
            $action_id = $request->input('action_id');
            
            if (!$action_id) {
                return response()->json(['error' => 'Action ID is required'], 400);
            }

            // Fetch the personnel action with employee information
            $action = DB::connection('intra_payroll')
                ->table('tbl_personnel_action as pa')
                ->leftJoin('tbl_employee as e', 'pa.employee_id', '=', 'e.id')
                ->select(
                    'pa.id',
                    'pa.employee_id',
                    'pa.action_type',
                    'pa.effective_date',
                    'pa.remarks',
                    'e.emp_code',
                    'e.first_name',
                    'e.last_name'
                )
                ->where('pa.id', $action_id)
                ->first();

            if (!$action) {
                return response()->json(['error' => 'Personnel action not found'], 404);
            }

            // Build employee name
            $employee_name = ($action->emp_code ? $action->emp_code . ' - ' : '') . ($action->first_name ?? '') . ' ' . ($action->last_name ?? '');
            $employee_name = trim($employee_name);

            // Fetch the detail rows from main table
            $details = DB::connection('intra_payroll')
                ->table('tbl_personnel_action')
                ->where('id', $action_id)
                ->select(
                    'department_from',
                    'department_to',
                    'branch_from',
                    'branch_to',
                    'emp_status_from',
                    'emp_status_to',
                    'position_from',
                    'position_to',
                    'schedule_from',
                    'schedule_to',
                    'supervisor_from',
                    'supervisor_to',
                    'supervisor_position_from',
                    'supervisor_position_to',
                    'salary_from',
                    'salary_to',
                    'allowance_from',
                    'allowance_to',
                    'others_from',
                    'others_to',
                    'total_comp_from',
                    'total_comp_to'
                )
                ->get();

            return response()->json([
                'id' => $action->id,
                'employee_id' => $action->employee_id,
                'employee_name' => $employee_name,
                'action_type' => $action->action_type,
                'effective_date' => $action->effective_date,
                'remarks' => $action->remarks,
                'details' => $details
            ], 200);
        } catch (\Exception $e) {
            Log::error('Action Details Error: ' . $e->getMessage() . ' - Trace: ' . $e->getTraceAsString());
            return response()->json(['error' => 'Server Error: ' . $e->getMessage()], 500);
        }
    }

    public function deleteAction(Request $request)
    {
        try {
            $action_id = $request->input('action_id');
            
            if (!$action_id) {
                return response()->json(['error' => 'Action ID is required'], 400);
            }

            // Delete the personnel action (details are in same row)
            $deleted = DB::connection('intra_payroll')
                ->table('tbl_personnel_action')
                ->where('id', $action_id)
                ->delete();

            if ($deleted) {
                return response()->json(['message' => 'Personnel action deleted successfully'], 200);
            } else {
                return response()->json(['error' => 'Personnel action not found'], 404);
            }
        } catch (\Exception $e) {
            Log::error('Delete Action Error: ' . $e->getMessage() . ' - Trace: ' . $e->getTraceAsString());
            return response()->json(['error' => 'Server Error: ' . $e->getMessage()], 500);
        }
    }

    public function updateAction(Request $request)
    {
        try {
            $action_id = $request->input('action_id');
            
            if (!$action_id) {
                return response()->json(['error' => 'Action ID is required'], 400);
            }

            $validated = $request->validate([
                'employee_id' => 'required|exists:intra_payroll.tbl_employee,id',
                'action_type' => 'required|string',
                'effective_date' => 'required|date',
                'remarks' => 'nullable|string'
            ]);

            $validated['user_id_updated'] = auth()->user()->id;
            $validated['date_updated'] = now();

            // Update the personnel action
            $updated = DB::connection('intra_payroll')
                ->table('tbl_personnel_action')
                ->where('id', $action_id)
                ->update($validated);

            // Update details if provided
            if ($request->has('details')) {
                $details = $request->input('details');
                foreach ($details as $detail) {
                    // Filter out empty details
                    $filteredDetail = array_filter($detail, function($value) {
                        return $value !== null && $value !== '';
                    });
                    
                    if (!empty($filteredDetail)) {
                        // Update the personnel action with detail fields
                        DB::connection('intra_payroll')
                            ->table('tbl_personnel_action')
                            ->where('id', $action_id)
                            ->update([
                                'department_from' => $detail['department_from'] ?? null,
                                'department_to' => $detail['department_to'] ?? null,
                                'branch_from' => $detail['branch_from'] ?? null,
                                'branch_to' => $detail['branch_to'] ?? null,
                                'emp_status_from' => $detail['emp_status_from'] ?? null,
                                'emp_status_to' => $detail['emp_status_to'] ?? null,
                                'position_from' => $detail['position_from'] ?? null,
                                'position_to' => $detail['position_to'] ?? null,
                                'schedule_from' => $detail['schedule_from'] ?? null,
                                'schedule_to' => $detail['schedule_to'] ?? null,
                                'supervisor_from' => $detail['supervisor_from'] ?? null,
                                'supervisor_to' => $detail['supervisor_to'] ?? null,
                                'supervisor_position_from' => $detail['supervisor_position_from'] ?? null,
                                'supervisor_position_to' => $detail['supervisor_position_to'] ?? null,
                                'salary_from' => $detail['salary_from'] ?? null,
                                'salary_to' => $detail['salary_to'] ?? null,
                                'allowance_from' => $detail['allowance_from'] ?? null,
                                'allowance_to' => $detail['allowance_to'] ?? null,
                                'others_from' => $detail['others_from'] ?? null,
                                'others_to' => $detail['others_to'] ?? null,
                                'total_comp_from' => $detail['total_comp_from'] ?? null,
                                'total_comp_to' => $detail['total_comp_to'] ?? null,
                            ]);
                    }
                }
            }

            return response()->json(['message' => 'Personnel action updated successfully'], 200);
        } catch (\Exception $e) {
            Log::error('Update Action Error: ' . $e->getMessage() . ' - Trace: ' . $e->getTraceAsString());
            return response()->json(['error' => 'Server Error: ' . $e->getMessage()], 500);
        }
    }

    public function getEmployeeSchedule(Request $request)
    {
        try {
            $emp_id = $request->input('employee_id');
            $month_view = $request->input('month_view', now()->format('Y-m-01'));
            
            $date_from = date("Y-m-01", strtotime($month_view));
            $date_to = date("Y-m-t", strtotime($date_from));
            
            // Get employee info
            $employee = DB::connection('intra_payroll')
                ->table('tbl_employee')
                ->where('id', $emp_id)
                ->first();
            
            if (!$employee) {
                return response()->json(['error' => 'Employee not found'], 404);
            }
            
            // Get daily schedules
            $daily_schedules = DB::connection('intra_payroll')
                ->table('tbl_daily_schedule')
                ->where('emp_id', $emp_id)
                ->whereBetween('schedule_date', [$date_from, $date_to])
                ->get();
            
            // Get library schedules
            $lib_schedules = DB::connection('intra_payroll')
                ->table('lib_schedule')
                ->where('is_active', 1)
                ->get();
            
            // Get weekly schedules
            $lib_week_schedules = DB::connection('intra_payroll')
                ->table('lib_week_schedule')
                ->where('is_active', 1)
                ->get();
            
            // Get holidays
            $holidays = DB::connection('intra_payroll')
                ->table('tbl_holiday')
                ->whereBetween('holiday_date', [$date_from, $date_to])
                ->get();
            
            // Get leaves
            $leaves = DB::connection('intra_payroll')
                ->table('tbl_leave_used')
                ->where('emp_id', $emp_id)
                ->where('leave_status', 'APPROVED')
                ->where('leave_year', date('Y', strtotime($date_from)))
                ->get();
            
            $calendar_data = [];
            $cur_day = $date_from;
            
            while (strtotime($cur_day) <= strtotime($date_to)) {
                $schedule_info = null;
                
                // Check for daily schedule override
                $daily_sched = $daily_schedules->firstWhere('schedule_date', $cur_day);
                
                if ($daily_sched) {
                    $lib_sched = $lib_schedules->firstWhere('id', $daily_sched->schedule_id);
                    if ($lib_sched) {
                        if ($lib_sched->is_flexi == 1) {
                            $schedule_info = "Flexible " . $lib_sched->required_hours . " hrs";
                        } else {
                            $schedule_info = date('g:i A', strtotime($lib_sched->am_in)) . " - " . date('g:i A', strtotime($lib_sched->pm_out));
                        }
                    } else {
                        $schedule_info = "NO SCHEDULE";
                    }
                } else {
                    // Use employee's default schedule
                    if ($employee->schedule_id != 0) {
                        $week_sched = $lib_week_schedules->firstWhere('id', $employee->schedule_id);
                        if ($week_sched) {
                            $day_name = strtolower(date('l', strtotime($cur_day)));
                            $sched_id = $week_sched->$day_name ?? 0;
                            if ($sched_id != 0) {
                                $lib_sched = $lib_schedules->firstWhere('id', $sched_id);
                                if ($lib_sched) {
                                    if ($lib_sched->is_flexi == 1) {
                                        $schedule_info = "Flexible " . $lib_sched->required_hours . " hrs";
                                    } else {
                                        $schedule_info = date('g:i A', strtotime($lib_sched->am_in)) . " - " . date('g:i A', strtotime($lib_sched->pm_out));
                                    }
                                } else {
                                    $schedule_info = "NO SCHEDULE";
                                }
                            } else {
                                $schedule_info = "NO SCHEDULE";
                            }
                        }
                    }
                }
                
                // Check for holidays
                $is_holiday = $holidays->firstWhere('holiday_date', $cur_day) ? true : false;
                
                // Check for leaves
                $is_leave = false;
                foreach ($leaves as $leave) {
                    if (strtotime($cur_day) >= strtotime($leave->leave_date_from) && strtotime($cur_day) <= strtotime($leave->leave_date_to)) {
                        $is_leave = true;
                        break;
                    }
                }
                
                $calendar_data[] = [
                    'date' => $cur_day,
                    'day_of_week' => date('l', strtotime($cur_day)),
                    'schedule' => $schedule_info,
                    'is_holiday' => $is_holiday,
                    'is_leave' => $is_leave
                ];
                
                $cur_day = date('Y-m-d', strtotime($cur_day . ' +1 day'));
            }
            
            return response()->json([
                'success' => true,
                'employee_name' => $employee->first_name . ' ' . $employee->last_name,
                'month' => date('F Y', strtotime($date_from)),
                'schedule_data' => $calendar_data
            ]);
        } catch (\Exception $e) {
            Log::error('Get Schedule Error: ' . $e->getMessage() . ' - Trace: ' . $e->getTraceAsString());
            return response()->json(['error' => 'Server Error: ' . $e->getMessage()], 500);
        }
    }

    public function getScheduleList(Request $request)
    {
        try {
            $employee_id = $request->input('employee_id');
            
            if (!$employee_id) {
                return response()->json(['error' => 'Employee ID is required'], 400);
            }

            // Get employee's schedule_id
            $employee = DB::connection('intra_payroll')
                ->table('tbl_employee')
                ->where('id', $employee_id)
                ->select('schedule_id')
                ->first();

            if (!$employee) {
                return response()->json(['error' => 'Employee not found'], 404);
            }

            // Get the week schedule details
            $schedules = [];
            if ($employee->schedule_id && $employee->schedule_id != 0) {
                $week_schedule = DB::connection('intra_payroll')
                    ->table('lib_week_schedule')
                    ->where('id', $employee->schedule_id)
                    ->first();

                if ($week_schedule) {
                    // Get all schedule IDs from the week schedule
                    $schedule_ids = [];
                    $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                    
                    foreach ($days as $day) {
                        if ($week_schedule->$day != 0) {
                            $schedule_ids[] = $week_schedule->$day;
                        }
                    }

                    // Get unique schedule details
                    if (!empty($schedule_ids)) {
                        $schedules = DB::connection('intra_payroll')
                            ->table('lib_schedule')
                            ->whereIn('id', array_unique($schedule_ids))
                            ->select('id', 'name', 'am_in', 'am_out', 'pm_in', 'pm_out', 'is_flexi', 'required_hours')
                            ->orderBy('name')
                            ->get();
                    }
                }
            }

            // Get ALL available schedules for the "To" field
            $all_schedules = DB::connection('intra_payroll')
                ->table('lib_schedule')
                ->where('is_active', 1)
                ->select('id', 'name', 'am_in', 'am_out', 'pm_in', 'pm_out', 'is_flexi', 'required_hours')
                ->orderBy('name')
                ->get();

            return response()->json([
                'schedule_id' => $employee->schedule_id,
                'schedules' => $schedules,
                'all_schedules' => $all_schedules
            ], 200);
        } catch (\Exception $e) {
            Log::error('Get Schedule List Error: ' . $e->getMessage() . ' - Trace: ' . $e->getTraceAsString());
            return response()->json(['error' => 'Server Error: ' . $e->getMessage()], 500);
        }
    }
}


