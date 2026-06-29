<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class AccountabilitiesController extends Controller
{
    /**
     * Display the List of Accountabilities page
     */
    public function index()
    {
        // Ensure permission exists for list_of_accountabilities (following NTE pattern)
        $this->ensureListOfAccountabilitiesPermission();

        // Get user permission for this page
        $routeName = 'list_of_accountabilities';
        $userAccess = Auth::user()->access[$routeName] ?? null;
        $userPermission = $userAccess['access'] ?? null;
        
        // Check if user has read-only permission (value 3 = "R" only)
        $isReadOnly = $userPermission === '3' || (preg_match("/R/i", $userPermission ?? '') && !preg_match("/C|U/i", $userPermission ?? ''));

        // Check if user has access to this page - allow if user is staff or has permission
        $hasAccess = isset(Auth::user()->access[$routeName]) || Auth::user()->role_id == 2;
        
        if (!$hasAccess) {
            return redirect()->route('dashboard')->with('error', 'You do not have access to this page.');
        }

        // Check if user can create accountabilities (role_id 2 cannot create)
        $canCreateAccountability = auth()->user()->role_id != 2;
        
        // For staff/employees (role_id == 2) or read-only users, redirect to their own accountabilities view
        if (!$canCreateAccountability || $isReadOnly) {
            return redirect()->route('my_accountabilities');
        }

        // For admins with create/update access, get all accountabilities
        $accountabilities = collect();
        try {
            $accountabilities = DB::connection("intra_payroll")
                ->table('tbl_accountabilities as a')
                ->leftJoin('tbl_employee as e', 'a.employee_id', '=', 'e.id')
                ->select([
                    'a.*',
                    'e.first_name',
                    'e.last_name'
                ])
                ->orderBy('a.date_assigned', 'desc')
                ->get();
        } catch (\Exception $e) {
            Log::error('Error loading accountabilities: ' . $e->getMessage());
        }

        return view('list_of_accountabilities.index', compact('accountabilities', 'canCreateAccountability'));
    }

    /**
     * Display staff view - shows only accountabilities assigned to the logged-in employee
     */
    public function staffView()
    {
        Log::info('staffView called for user: ' . Auth::user()->id);
        
        // Ensure the permission exists for staff role first
        $this->ensureStaffPermission();

        // After ensuring permission, reload the access to get updated permissions
        $access = Auth::user()->access;
        Log::info('User access after ensureStaffPermission:', $access);

        // Check if user has access to this page or is staff (role_id = 2)
        $hasAccess = Auth::user()->role_id == 2 || isset($access['list_of_accountabilities']);
        
        if (!$hasAccess) {
            Log::warning('User ' . Auth::user()->id . ' does not have access to staffView. Role: ' . Auth::user()->role_id);
            return redirect()->route('dashboard')->with('error', 'You do not have access to this page.');
        }

        try {
            // Get the employee linked to the current user
            $employee = DB::connection("intra_payroll")
                ->table('tbl_employee')
                ->where('user_id', Auth::user()->id)
                ->first();

            // Check if table exists first
            $tableExists = DB::connection("intra_payroll")
                ->select("SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'tbl_accountabilities'");
            
            $accountabilities = collect();
            
            if ($tableExists[0]->count > 0 && $employee) {
                // Get accountabilities assigned to the current employee
                $rawAccountabilities = DB::connection("intra_payroll")
                    ->table('tbl_accountabilities as a')
                    ->leftJoin('tbl_employee as e', 'a.employee_id', '=', 'e.id')
                    ->select([
                        'a.id',
                        'a.employee_id',
                        'a.item_name',
                        'a.item_description',
                        'a.item_value',
                        'a.serial_number',
                        'a.property_number',
                        'a.date_assigned',
                        'a.status',
                        'a.condition_assigned',
                        'a.remarks',
                        'e.first_name',
                        'e.last_name'
                    ])
                    ->where('a.employee_id', $employee->id)
                    ->orderBy('a.date_assigned', 'desc')
                    ->get();

                // Add badge class to each accountability
                $accountabilities = $rawAccountabilities->map(function($accountability) {
                    $accountability->badge_class = $this->getStatusBadgeClass($accountability->status);
                    return $accountability;
                });
            }

            return view('list_of_accountabilities.staff', compact('accountabilities'));

        } catch (\Exception $e) {
            Log::error('Staff accountabilities view error: ' . $e->getMessage());
            return view('list_of_accountabilities.staff', ['accountabilities' => collect()]);
        }
    }

    /**
     * Ensure the staff permission exists in the database
     * Following the NTE pattern with proper route field
     */
    private function ensureStaffPermission()
    {
        try {
            // Check if permission already exists - need to match by route
            $existingPermission = DB::connection("intra_payroll")
                ->table('lib_permission')
                ->where('route', 'list_of_accountabilities')
                ->first();

            // Get or create the permission
            if (!$existingPermission) {
                // Add the list_of_accountabilities permission with route (like NTE does)
                $permissionId = DB::connection("intra_payroll")
                    ->table('lib_permission')
                    ->insertGetId([
                        'name' => 'List of Accountabilities',
                        'icon' => 'public/assets/img/accountability.svg',
                        'route' => 'list_of_accountabilities',
                        'date_created' => now(),
                        'date_updated' => now()
                    ]);

                Log::info('Created list_of_accountabilities permission with ID: ' . $permissionId);
            } else {
                $permissionId = $existingPermission->id;
                Log::info('Found existing list_of_accountabilities permission with ID: ' . $permissionId);
            }

            // Always ensure staff role has this permission
            $staffRoleAccess = DB::connection("intra_payroll")
                ->table('tbl_role_access')
                ->where('id', 2)
                ->first();

            if ($staffRoleAccess) {
                // Get current permission string
                $currentPermissions = $staffRoleAccess->permission;
                $permissions = explode(';', $currentPermissions);
                
                // Check if permission is already in the list
                $permissionExists = false;
                foreach ($permissions as $perm) {
                    if (strpos($perm, $permissionId . '|') === 0) {
                        $permissionExists = true;
                        break;
                    }
                }
                
                // Add permission if it doesn't exist
                if (!$permissionExists) {
                    // Add read access for the new permission (format: permissionId|accessLevel;)
                    // Access level: 1=CRUD, 2=U, 3=R, 0=No Access
                    $permissions[] = $permissionId . '|3'; // 3 = Read only
                    
                    $newPermissionString = implode(';', $permissions);

                    DB::connection("intra_payroll")
                        ->table('tbl_role_access')
                        ->where('id', 2)
                        ->update([
                            'permission' => $newPermissionString,
                            'date_updated' => now()
                        ]);

                    Log::info('Updated staff role permissions to include list_of_accountabilities');
                } else {
                    Log::info('Staff role already has list_of_accountabilities permission');
                }
            }
        } catch (\Exception $e) {
            Log::error('Error ensuring staff permission: ' . $e->getMessage());
        }
    }

    /**
     * Ensure list_of_accountabilities permission exists in database
     * This gets called on every access to ensure permission is properly cached
     */
    private function ensureListOfAccountabilitiesPermission()
    {
        try {
            // Check if permission already exists by route
            $existingPermission = DB::connection("intra_payroll")
                ->table('lib_permission')
                ->where('route', 'list_of_accountabilities')
                ->first();

            // If it doesn't exist, create it
            if (!$existingPermission) {
                $inserted = DB::connection("intra_payroll")
                    ->table('lib_permission')
                    ->insert([
                        'name' => 'List of Accountabilities',
                        'icon' => 'public/assets/img/accountability.svg',
                        'route' => 'list_of_accountabilities',
                        'date_created' => now(),
                        'date_updated' => now()
                    ]);

                Log::info('Created list_of_accountabilities permission in lib_permission. Insert result: ' . ($inserted ? 'success' : 'failed'));
            } else {
                Log::info('Permission list_of_accountabilities already exists with ID: ' . $existingPermission->id);
            }
        } catch (\Exception $e) {
            Log::error('Error ensuring list_of_accountabilities permission: ' . $e->getMessage());
        }
    }

    /**
     * Get accountabilities data for DataTables
     */
    public function list(Request $request)
    {
        try {
            Log::info('Accountabilities list called', ['request' => $request->all()]);
            
            // Check if this is a staff view request
            if ($request->has('staff_view') && $request->staff_view) {
                return $this->getStaffAccountabilities($request);
            }
            
            // Check if table exists first
            $tableExists = DB::connection("intra_payroll")
                ->select("SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'tbl_accountabilities'");
            
            if ($tableExists[0]->count == 0) {
                return response()->json([
                    'draw' => intval($request->input('draw', 1)),
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => [],
                    'debug' => 'Table does not exist'
                ]);
            }

            // Get accountabilities with employee names using correct column names
            $role_id = auth()->user()->role_id;
            
            $query = DB::connection("intra_payroll")
                ->table('tbl_accountabilities as a')
                ->leftJoin('tbl_employee as e', 'a.employee_id', '=', 'e.id')
                ->select([
                    'a.id',
                    'a.employee_id',
                    'a.item_name',
                    'a.item_description',
                    'a.date_assigned',
                    'a.status',
                    'e.first_name',
                    'e.last_name'
                ]);

            // Filter by role-based group management
            if ($role_id === 1 || $role_id === 27) {
                // Admin sees all accountabilities
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
                // For other roles, only show their own accountabilities
                $query->where("e.user_id", Auth::user()->id);
            }

            // Apply status filter if provided
            if ($request->has('status_filter') && !empty($request->status_filter)) {
                $query->where('a.status', $request->status_filter);
            }

            // Apply search filter if provided
            if ($request->has('search') && !empty($request->input('search.value'))) {
                $searchTerm = $request->input('search.value');
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('a.item_name', 'LIKE', '%' . $searchTerm . '%')
                      ->orWhere('a.item_description', 'LIKE', '%' . $searchTerm . '%')
                      ->orWhere('a.serial_number', 'LIKE', '%' . $searchTerm . '%')
                      ->orWhere('a.property_number', 'LIKE', '%' . $searchTerm . '%')
                      ->orWhere('e.first_name', 'LIKE', '%' . $searchTerm . '%')
                      ->orWhere('e.last_name', 'LIKE', '%' . $searchTerm . '%');
                });
            }

            // Get total count before filtering (for recordsTotal)
            $totalQuery = DB::connection("intra_payroll")
                ->table('tbl_accountabilities as a')
                ->leftJoin('tbl_employee as e', 'a.employee_id', '=', 'e.id');
            
            // Apply role-based filtering to total count
            if ($role_id === 1 || $role_id === 27) {
                // Admin sees all accountabilities
            } elseif ($role_id === 4) { // HR Group D
                $totalQuery->where(function ($q) {
                    $q->where("e.hr_group", "group_d")
                    ->orWhere("e.user_id", Auth::user()->id);
                });
            } elseif ($role_id === 5) { // HR Group B,C,E
                $totalQuery->where(function ($q) {
                    $q->whereIn("e.hr_group", ["group_b","group_c","group_e"])
                    ->orWhere("e.user_id", Auth::user()->id);
                });
            } elseif ($role_id === 14) { // HR Group B,C
                $totalQuery->where(function ($q) {
                    $q->whereIn("e.hr_group", ["group_b","group_c"])
                    ->orWhere("e.user_id", Auth::user()->id);
                });
            } elseif ($role_id === 15) { // HR Group C,E
                $totalQuery->where(function ($q) {
                    $q->whereIn("e.hr_group", ["group_c","group_e"])
                    ->orWhere("e.user_id", Auth::user()->id);
                });
            } else {
                // For other roles, only show their own accountabilities
                $totalQuery->where("e.user_id", Auth::user()->id);
            }
            
            $recordsTotal = $totalQuery->count();
            
            $accountabilities = $query->orderBy('a.id', 'desc')->get();
            $recordsFiltered = count($accountabilities);

            // Format data for DataTables
            $formattedData = [];
            foreach ($accountabilities as $row) {
                $employeeName = trim($row->first_name . ' ' . $row->last_name);
                if (empty(trim($employeeName))) {
                    $employeeName = 'Unknown Employee';
                }
                
                $actions = '<button class="btn btn-sm btn-info mr-1 edit-accountability-btn" data-id="' . $row->id . '"><i class="fa fa-edit"></i> Edit</button>';
                $actions .= '<button class="btn btn-sm btn-danger delete-accountability-btn" data-id="' . $row->id . '"><i class="fa fa-trash"></i> Delete</button>';

                $statusBadge = '<span class="badge badge-' . $this->getStatusBadgeClass($row->status) . '">' . ucfirst($row->status) . '</span>';

                $formattedData[] = [
                    'id' => $row->id,
                    'employee' => $employeeName,
                    'item' => $row->item_name,
                    'description' => $row->item_description ?? '-',
                    'date_assigned' => date('M d, Y', strtotime($row->date_assigned)),
                    'status' => $statusBadge,
                    'action' => $actions
                ];
            }

            return response()->json([
                'draw' => intval($request->input('draw', 1)),
                'recordsTotal' => $recordsTotal,
                'recordsFiltered' => $recordsFiltered,
                'data' => $formattedData,
                'debug' => 'Data loaded successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Accountabilities list error: ' . $e->getMessage());
            return response()->json([
                'draw' => intval($request->input('draw', 1)),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get accountabilities for staff view (only their own)
     */
    private function getStaffAccountabilities(Request $request)
    {
        try {
            $currentUserId = Auth::user()->id;
            Log::info('Staff accountabilities - Current User ID: ' . $currentUserId);
            
            // Check if table exists first
            $tableExists = DB::connection("intra_payroll")
                ->select("SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'tbl_accountabilities'");
            
            if ($tableExists[0]->count == 0) {
                return response()->json([
                    'success' => true,
                    'data' => []
                ]);
            }

            // First, let's check all accountabilities to see what's in the table
            $allAccountabilities = DB::connection("intra_payroll")
                ->table('tbl_accountabilities')
                ->get();
            Log::info('All accountabilities in table:', $allAccountabilities->toArray());
            
            // Get accountabilities assigned to the current user
            $accountabilities = DB::connection("intra_payroll")
                ->table('tbl_accountabilities as a')
                ->leftJoin('tbl_employee as e', 'a.employee_id', '=', 'e.id')
                ->select([
                    'a.id',
                    'a.employee_id',
                    'a.item_name',
                    'a.item_description',
                    'a.item_value',
                    'a.serial_number',
                    'a.property_number',
                    'a.date_assigned',
                    'a.status',
                    'a.condition_assigned',
                    'a.remarks',
                    'e.first_name',
                    'e.last_name'
                ])
                ->where('a.employee_id', $currentUserId)
                ->orderBy('a.date_assigned', 'desc')
                ->get();
                
            Log::info('Filtered accountabilities for user ' . $currentUserId . ':', $accountabilities->toArray());

            // Format data for display
            $formattedData = [];
            foreach ($accountabilities as $row) {
                $formattedData[] = [
                    'id' => $row->id,
                    'item' => $row->item_name,
                    'description' => $row->item_description ?? 'No description provided',
                    'date_assigned' => date('M d, Y', strtotime($row->date_assigned)),
                    'status' => $row->status,
                    'item_value' => $row->item_value,
                    'serial_number' => $row->serial_number,
                    'property_number' => $row->property_number,
                    'condition_assigned' => $row->condition_assigned,
                    'remarks' => $row->remarks
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $formattedData
            ]);

        } catch (\Exception $e) {
            Log::error('Staff accountabilities error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'data' => [],
                'error' => 'Error loading accountabilities'
            ]);
        }
    }

    /**
     * Get status badge class for styling
     */
    private function getStatusBadgeClass($status)
    {
        switch ($status) {
            case 'assigned':
                return 'primary';
            case 'returned':
                return 'success';
            case 'lost':
                return 'danger';
            case 'damaged':
                return 'warning';
            default:
                return 'secondary';
        }
    }

    /**
     * Get employees for dropdown (filtered by role-based hr_group management)
     */
    public function getEmployees()
    {
        try {
            Log::info('Getting employees for dropdown');
            
            $role_id = auth()->user()->role_id;
            $query = DB::connection("intra_payroll")
                ->table('tbl_employee')
                ->select(['id', 'first_name', 'last_name', 'hr_group']);
            
            // Filter employees based on role_id and the groups this role manages
            if ($role_id === 1 || $role_id === 27) {
                // Admin sees all employees
                $employees = $query->orderBy('first_name')->get();
            } elseif ($role_id === 4) { // HR Group D
                $employees = $query->where(function ($q) {
                    $q->where("hr_group", "group_d")
                    ->orWhere("user_id", Auth::user()->id);
                })->where('is_active', 1)->orderBy('first_name')->get();
            } elseif ($role_id === 5) { // HR Group B,C,E
                $employees = $query->where(function ($q) {
                    $q->whereIn("hr_group", ["group_b","group_c","group_e"])
                    ->orWhere("user_id", Auth::user()->id);
                })->where('is_active', 1)->orderBy('first_name')->get();
            } elseif ($role_id === 14) { // HR Group B,C
                $employees = $query->where(function ($q) {
                    $q->whereIn("hr_group", ["group_b","group_c"])
                    ->orWhere("user_id", Auth::user()->id);
                })->where('is_active', 1)->orderBy('first_name')->get();
            } elseif ($role_id === 15) { // HR Group C,E
                $employees = $query->where(function ($q) {
                    $q->whereIn("hr_group", ["group_c","group_e"])
                    ->orWhere("user_id", Auth::user()->id);
                })->where('is_active', 1)->orderBy('first_name')->get();
            } else {
                // For other roles, only show their own record
                $employees = $query->where("user_id", Auth::user()->id)->get();
            }

            // Format the employee names
            $formattedEmployees = [];
            foreach ($employees as $emp) {
                $formattedEmployees[] = [
                    'id' => $emp->id,
                    'name' => trim($emp->first_name . ' ' . $emp->last_name)
                ];
            }

            Log::info('Found employees', ['count' => count($formattedEmployees)]);

            return response()->json([
                'success' => true,
                'employees' => $formattedEmployees
            ]);

        } catch (\Exception $e) {
            Log::error('Get employees error: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to load employees: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a new accountability record
     */
    public function store(Request $request)
    {
        try {
            // Restrict role_id 2 from creating accountabilities
            if (auth()->user()->role_id == 2) {
                return response()->json([
                    'success' => false,
                    'error' => 'You do not have permission to create accountability records.'
                ], 403);
            }
            
            // Validate the request
            $request->validate([
                'employee_id' => 'required|integer',
                'item_name' => 'required|string|max:255',
                'item_description' => 'nullable|string',
                'item_value' => 'nullable|numeric|min:0',
                'serial_number' => 'nullable|string|max:100',
                'property_number' => 'nullable|string|max:100',
                'date_assigned' => 'required|date',
                'status' => 'required|in:assigned,returned,lost,damaged',
                'condition_assigned' => 'nullable|string',
                'remarks' => 'nullable|string'
            ]);

            // Insert the accountability record
            $id = DB::connection("intra_payroll")->table("tbl_accountabilities")->insertGetId([
                'employee_id' => $request->employee_id,
                'item_name' => $request->item_name,
                'item_description' => $request->item_description,
                'item_value' => $request->item_value,
                'serial_number' => $request->serial_number,
                'property_number' => $request->property_number,
                'date_assigned' => $request->date_assigned,
                'status' => $request->status,
                'condition_assigned' => $request->condition_assigned,
                'remarks' => $request->remarks,
                'assigned_by' => auth()->id(),
                'date_created' => now(),
                'date_updated' => now()
            ]);

            Log::info('Successfully created accountability record', [
                'accountability_id' => $id,
                'employee_id' => $request->employee_id,
                'item_name' => $request->item_name,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'accountability_id' => $id,
                'message' => 'Accountability record has been successfully created.'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Validation failed: ' . implode(', ', $e->validator->errors()->all())
            ], 422);

        } catch (\Exception $e) {
            Log::error('Error creating accountability record: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
                'request_data' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to create accountability record: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific accountability record for editing
     */
    public function show($id)
    {
        try {
            $accountability = DB::connection("intra_payroll")
                ->table('tbl_accountabilities as a')
                ->leftJoin('tbl_employee as e', 'a.employee_id', '=', 'e.id')
                ->select([
                    'a.*',
                    'e.first_name',
                    'e.last_name'
                ])
                ->where('a.id', $id)
                ->first();

            if (!$accountability) {
                return response()->json([
                    'success' => false,
                    'error' => 'Accountability record not found'
                ], 404);
            }

            Log::info('Retrieved accountability record for editing', [
                'accountability_id' => $id,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'accountability' => $accountability
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving accountability record: ' . $e->getMessage(), [
                'accountability_id' => $id,
                'user_id' => auth()->id()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to retrieve accountability record: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an existing accountability record
     */
    public function update(Request $request, $id)
    {
        try {
            // Validate the request
            $request->validate([
                'employee_id' => 'required|integer',
                'item_name' => 'required|string|max:255',
                'item_description' => 'nullable|string',
                'item_value' => 'nullable|numeric|min:0',
                'serial_number' => 'nullable|string|max:100',
                'property_number' => 'nullable|string|max:100',
                'date_assigned' => 'required|date',
                'status' => 'required|in:assigned,returned,lost,damaged',
                'condition_assigned' => 'nullable|string',
                'remarks' => 'nullable|string'
            ]);

            // Check if record exists
            $existingRecord = DB::connection("intra_payroll")
                ->table("tbl_accountabilities")
                ->where('id', $id)
                ->first();

            if (!$existingRecord) {
                return response()->json([
                    'success' => false,
                    'error' => 'Accountability record not found'
                ], 404);
            }

            // Update the accountability record
            DB::connection("intra_payroll")->table("tbl_accountabilities")
                ->where('id', $id)
                ->update([
                    'employee_id' => $request->employee_id,
                    'item_name' => $request->item_name,
                    'item_description' => $request->item_description,
                    'item_value' => $request->item_value,
                    'serial_number' => $request->serial_number,
                    'property_number' => $request->property_number,
                    'date_assigned' => $request->date_assigned,
                    'status' => $request->status,
                    'condition_assigned' => $request->condition_assigned,
                    'remarks' => $request->remarks,
                    'date_updated' => now()
                ]);

            Log::info('Successfully updated accountability record', [
                'accountability_id' => $id,
                'employee_id' => $request->employee_id,
                'item_name' => $request->item_name,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Accountability record has been successfully updated.'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Validation failed: ' . implode(', ', $e->validator->errors()->all())
            ], 422);

        } catch (\Exception $e) {
            Log::error('Error updating accountability record: ' . $e->getMessage(), [
                'accountability_id' => $id,
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
                'request_data' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to update accountability record: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete an accountability record
     */
    public function destroy($id)
    {
        try {
            Log::info('Delete accountability record initiated', ['id' => $id]);
            
            // Verify database connection
            try {
                $dbTest = DB::connection("intra_payroll")->select('SELECT 1');
                Log::info('Database connection verified');
            } catch (\Exception $dbError) {
                Log::error('Database connection failed', ['error' => $dbError->getMessage()]);
                return response()->json([
                    'success' => false,
                    'error' => 'Database connection error'
                ], 500);
            }
            
            // Check if table exists
            $tableExists = DB::connection("intra_payroll")
                ->select("SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'tbl_accountabilities'");
            
            if ($tableExists[0]->count == 0) {
                Log::error('Table tbl_accountabilities does not exist');
                return response()->json([
                    'success' => false,
                    'error' => 'Table does not exist'
                ], 500);
            }
            
            // Check if record exists
            $existingRecord = DB::connection("intra_payroll")
                ->table("tbl_accountabilities")
                ->where('id', $id)
                ->first();

            if (!$existingRecord) {
                Log::warning('Accountability record not found', ['id' => $id]);
                return response()->json([
                    'success' => false,
                    'error' => 'Accountability record not found'
                ], 404);
            }

            Log::info('Found record to delete', [
                'accountability_id' => $id,
                'item_name' => $existingRecord->item_name ?? 'N/A',
                'employee_id' => $existingRecord->employee_id ?? 'N/A'
            ]);

            // Delete the accountability record
            $deleteResult = DB::connection("intra_payroll")
                ->table("tbl_accountabilities")
                ->where('id', $id)
                ->delete();

            Log::info('Delete query executed', [
                'accountability_id' => $id,
                'delete_result' => $deleteResult
            ]);

            Log::info('Successfully deleted accountability record', [
                'accountability_id' => $id,
                'item_name' => $existingRecord->item_name ?? 'N/A',
                'employee_id' => $existingRecord->employee_id ?? 'N/A',
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Accountability record has been successfully deleted.'
            ]);

        } catch (\Illuminate\Database\QueryException $qe) {
            Log::error('Database error deleting accountability record', [
                'accountability_id' => $id,
                'error_code' => $qe->getCode(),
                'message' => $qe->getMessage(),
                'trace' => $qe->getTraceAsString(),
                'user_id' => auth()->id()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Database error: ' . $qe->getMessage()
            ], 500);
            
        } catch (\Exception $e) {
            Log::error('Error deleting accountability record: ' . $e->getMessage(), [
                'accountability_id' => $id,
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to delete accountability record: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create the tbl_accountabilities table
     */
    public function createTable(Request $request)
    {
        try {
            // Check if table already exists
            $tableExists = DB::connection("intra_payroll")
                ->select("SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'tbl_accountabilities'");
            
            if ($tableExists[0]->count > 0) {
                return response()->json([
                    'success' => false,
                    'error' => 'Table tbl_accountabilities already exists in the database.'
                ], 400);
            }

            // Create the table
            $sql = "
                CREATE TABLE `tbl_accountabilities` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `employee_id` int(11) NOT NULL,
                    `item_name` varchar(255) NOT NULL,
                    `item_description` text DEFAULT NULL,
                    `item_value` decimal(10,2) DEFAULT NULL,
                    `serial_number` varchar(100) DEFAULT NULL,
                    `property_number` varchar(100) DEFAULT NULL,
                    `date_assigned` date NOT NULL,
                    `date_returned` date DEFAULT NULL,
                    `status` enum('assigned','returned','lost','damaged') NOT NULL DEFAULT 'assigned',
                    `condition_assigned` text DEFAULT NULL,
                    `condition_returned` text DEFAULT NULL,
                    `remarks` text DEFAULT NULL,
                    `assigned_by` int(11) NOT NULL,
                    `received_by` int(11) DEFAULT NULL,
                    `date_created` datetime NOT NULL DEFAULT current_timestamp(),
                    `date_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                    PRIMARY KEY (`id`),
                    KEY `idx_employee_id` (`employee_id`),
                    KEY `idx_status` (`status`),
                    KEY `idx_date_assigned` (`date_assigned`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ";

            DB::connection("intra_payroll")->statement($sql);

            Log::info('Successfully created tbl_accountabilities table', [
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Table tbl_accountabilities has been successfully created with the following columns:
                - id (Auto increment primary key)
                - employee_id (Link to employee)
                - item_name (Name of the item)
                - item_description (Description of the item)
                - item_value (Monetary value)
                - serial_number (Serial/Asset number)
                - property_number (Property tag number)
                - date_assigned (Date when item was assigned)
                - date_returned (Date when item was returned)
                - status (assigned, returned, lost, damaged)
                - condition_assigned (Condition when assigned)
                - condition_returned (Condition when returned)
                - remarks (Additional notes)
                - assigned_by (User who assigned the item)
                - received_by (User who received the item back)
                - date_created, date_updated (Timestamps)'
            ]);

        } catch (\Exception $e) {
            Log::error('Error creating tbl_accountabilities table: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to create table: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export tbl_accountabilities table data as CSV
     */
    public function exportTable(Request $request)
    {
        try {
            // Check if table exists first
            $tableExists = DB::connection("intra_payroll")
                ->select("SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'tbl_accountabilities'");

            if ($tableExists[0]->count == 0) {
                return response()->json([
                    'success' => false,
                    'error' => 'Table tbl_accountabilities does not exist'
                ]);
            }

            // Get table structure
            $tableStructure = DB::connection('intra_payroll')->select("SHOW CREATE TABLE tbl_accountabilities");
            
            // Get all data from the table
            $accountabilities = DB::connection('intra_payroll')
                ->table('tbl_accountabilities')
                ->get();

            // Create SQL dump content
            $sql_dump = "-- SQL Dump for tbl_accountabilities\n";
            $sql_dump .= "-- Generated on: " . date('Y-m-d H:i:s') . "\n";
            $sql_dump .= "-- Database: intra_payroll\n";
            $sql_dump .= "-- Total Records: " . $accountabilities->count() . "\n\n";
            
            // Add table structure
            $sql_dump .= "-- Table structure for table `tbl_accountabilities`\n";
            $sql_dump .= "DROP TABLE IF EXISTS `tbl_accountabilities`;\n";
            $sql_dump .= $tableStructure[0]->{'Create Table'} . ";\n\n";
            
            // Add data if exists
            if ($accountabilities->count() > 0) {
                $sql_dump .= "-- Dumping data for table `tbl_accountabilities`\n";
                $sql_dump .= "-- Records: " . $accountabilities->count() . "\n\n";
                
                // Get column names dynamically
                $firstRecord = $accountabilities->first();
                $columns = array_keys((array)$firstRecord);
                $columnList = '`' . implode('`, `', $columns) . '`';
                
                $sql_dump .= "INSERT INTO `tbl_accountabilities` ($columnList) VALUES\n";
                
                $values = [];
                foreach ($accountabilities as $index => $accountability) {
                    $valueArray = [];
                    foreach ($columns as $column) {
                        $value = $accountability->$column;
                        if ($value === null) {
                            $valueArray[] = 'NULL';
                        } elseif (is_numeric($value)) {
                            $valueArray[] = $value;
                        } else {
                            $valueArray[] = "'" . addslashes($value) . "'";
                        }
                    }
                    $values[] = "(" . implode(', ', $valueArray) . ")";
                }
                
                $sql_dump .= implode(",\n", $values) . ";\n\n";
            } else {
                $sql_dump .= "-- No data found in table `tbl_accountabilities`\n\n";
            }
            
            $sql_dump .= "-- End of SQL dump\n";

            Log::info('Exported tbl_accountabilities table as SQL with ' . $accountabilities->count() . ' records', [
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'sql_data' => $sql_dump,
                'record_count' => $accountabilities->count(),
                'message' => 'Successfully exported SQL dump with ' . $accountabilities->count() . ' accountability records'
            ]);

        } catch (\Exception $e) {
            Log::error('Error exporting tbl_accountabilities table as SQL: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to export SQL table: ' . $e->getMessage()
            ], 500);
        }
    }

    public function export()
    {
        try {
            $accountabilities = DB::connection("intra_payroll")
                ->table('tbl_accountabilities as a')
                ->leftJoin('tbl_employee as e', 'a.employee_id', '=', 'e.id')
                ->select(
                    'a.id',
                    DB::raw("CONCAT(e.first_name, ' ', e.last_name) as employee_name"),
                    'a.item_name',
                    'a.item_description',
                    'a.item_value',
                    'a.serial_number',
                    'a.property_number',
                    'a.date_assigned',
                    'a.status',
                    'a.condition_assigned',
                    'a.remarks'
                )
                ->get();

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Accountabilities');

            $headers = ['ID', 'Employee', 'Item Name', 'Description', 'Item Value', 'Serial Number', 'Property Number', 'Date Assigned', 'Status', 'Condition Assigned', 'Remarks'];
            $sheet->fromArray($headers, null, 'A1');

            $headerStyle = [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '2f47ba']],
                'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            ];
            foreach (range('A', 'K') as $column) {
                $sheet->getStyle($column . '1')->applyFromArray($headerStyle);
            }

            $row = 2;
            foreach ($accountabilities as $accountability) {
                $sheet->setCellValue('A' . $row, $accountability->id);
                $sheet->setCellValue('B' . $row, $accountability->employee_name);
                $sheet->setCellValue('C' . $row, $accountability->item_name);
                $sheet->setCellValue('D' . $row, $accountability->item_description ?? '');
                $sheet->setCellValue('E' . $row, $accountability->item_value ?? '');
                $sheet->setCellValue('F' . $row, "'" . ($accountability->serial_number ?? ''));
                $sheet->setCellValue('G' . $row, "'" . ($accountability->property_number ?? ''));
                $sheet->setCellValue('H' . $row, $accountability->date_assigned ?? '');
                $sheet->setCellValue('I' . $row, $accountability->status ?? '');
                $sheet->setCellValue('J' . $row, $accountability->condition_assigned ?? '');
                $sheet->setCellValue('K' . $row, $accountability->remarks ?? '');
                $row++;
            }

            foreach (range('A', 'K') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }

            $fileName = 'Accountabilities_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
            
            // Create temp directory if it doesn't exist
            $tempDir = storage_path('app/temp');
            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0755, true);
            }
            
            $tempPath = $tempDir . '/' . $fileName;
            $writer = new Xlsx($spreadsheet);
            $writer->save($tempPath);

            return response()->download($tempPath, $fileName, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"'
            ])->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            Log::error('Error exporting accountabilities: ' . $e->getMessage() . ' | ' . $e->getFile() . ':' . $e->getLine());
            return back()->with('error', 'Error exporting accountabilities: ' . $e->getMessage());
        }
    }
}
