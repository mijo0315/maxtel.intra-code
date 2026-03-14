<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Auth;
use Illuminate\Support\Facades\Log;

class EmployeesExport implements FromCollection, WithHeadings, WithMapping, WithColumnFormatting
{
    protected $positions;
    protected $departments;
    protected $companies;
    protected $branches;
    protected $roleId;

    public function __construct($roleId = null)
    {
        // If roleId is not passed, try to get it from Auth
        if ($roleId === null) {
            $user = Auth::user();
            $this->roleId = $user ? $user->role_id : null;
        } else {
            $this->roleId = $roleId;
        }

        $this->positions = DB::connection("intra_payroll")
            ->table("lib_position")
            ->where("is_active", 1)
            ->get()
            ->keyBy("id");

        $this->departments = DB::connection("intra_payroll")
            ->table("tbl_department")
            ->where("is_active", 1)
            ->get()
            ->keyBy("id");

        $this->branches = DB::connection("intra_payroll")
            ->table("tbl_branch")
            ->where("is_active", 1)
            ->get()
            ->keyBy("id");
    }

    public function collection()
    {
        $role_id = $this->roleId;
        $hr_group = ['group_a', 'group_b'];
        
        Log::info('EmployeesExport Collection Started', [
            'role_id' => $role_id
        ]);
        
        // Check total employees in database
        $totalEmployees = DB::connection("intra_payroll")->table("tbl_employee")->count();
        Log::info('Total employees in database', ['count' => $totalEmployees]);
        
        // Check active employees
        $activeEmployees = DB::connection("intra_payroll")->table("tbl_employee")->where('is_active', 1)->count();
        Log::info('Active employees in database', ['count' => $activeEmployees]);
        
        // Check distinct hr_group values
        $hrGroups = DB::connection("intra_payroll")->table("tbl_employee")->distinct()->pluck('hr_group');
        Log::info('Distinct hr_group values in database', ['groups' => $hrGroups->toArray()]);
        
        if ($role_id === 4) { // HR Group D
            $hr_group = ['group_d'];
        } elseif ($role_id === 5) { // HR Group B, C,E
            $hr_group = ["group_b","group_c","group_e"];
        } elseif ($role_id === 14) { // HR Group B, C
            $hr_group = ["group_b","group_c"];
        } elseif ($role_id === 15) { // HR Group C,E
            $hr_group = ["group_c","group_e"];
        } elseif ($role_id === 1) { // HR Group A,B,C,D,E (Admin)
            $hr_group = ["group_a","group_b","group_c","group_d","group_e"];
        } else {
            // Fallback for any other role_id - export all active employees
            $hr_group = ["group_a","group_b","group_c","group_d","group_e"];
        }
        
        Log::info('EmployeesExport HR Groups', [
            'hr_group_filter' => $hr_group
        ]);

        // Build query for active employees
        $query = DB::connection("intra_payroll")->table("tbl_employee")
            ->where('is_active', 1);
        
        // For Admin (role_id 1), export ALL employees regardless of hr_group
        // For other roles, only apply hr_group filter if employees actually have hr_group values populated
        if ($role_id !== 1) {
            // Check if any employees have non-empty hr_group values
            $anyHrGroupExists = DB::connection("intra_payroll")->table("tbl_employee")
                ->whereNotNull('hr_group')
                ->where('hr_group', '!=', '')
                ->exists();
            
            if ($anyHrGroupExists) {
                $query->whereIn('hr_group', $hr_group);
            }
        }
        
        $employees = $query->select([
                'emp_code', 
                'last_name', 
                'first_name', 
                'middle_name', 
                'ext_name',
                'contact_no', 
                'sss_number', 
                'philhealth_number', 
                'hdmf_number',
                'tin_number', 
                'fix_sss',
                'fix_divisor',
                'fix_philhealth',
                'fix_hdmf',
                'fix_tax_rate',
                'position_id', 
                'department', 
                'start_date', 
                'date_of_birth',
                'address', 
                'salary_type', 
                'salary_rate', 
                'is_mwe', 
                'is_active',
                'branch_id',
                'yearly_divisor',
                'employee_status',
                'hr_group'
            ])
            ->get();
        
        Log::info('EmployeesExport Collection Result', [
            'total_employees_found' => $employees->count(),
            'database_connection' => 'intra_payroll',
            'filters_applied' => ['is_active' => 1, 'hr_group' => $hr_group],
            'role_id' => $role_id,
            'is_admin' => $role_id === 1
        ]);
        
        return $employees;
    }

    public function map($row): array
    {
        $hrGroupMap = [
            'group_a' => 'A',
            'group_b' => 'B',
            'group_c' => 'C',
            'group_d' => 'D',
            'group_e' => 'E',
        ];
        $hrGroup = $hrGroupMap[strtolower($row->hr_group)] ?? '';
        return [
            $row->emp_code,
            $row->last_name,
            $row->first_name,
            $row->middle_name,
            $row->ext_name,
            $row->contact_no,
            $row->sss_number,
            $row->philhealth_number,
            $row->hdmf_number,
            $row->tin_number,
            $row->fix_divisor ?? '',
            $row->fix_sss ?? '',
            $row->fix_philhealth ?? '',
            $row->fix_hdmf ?? '',
            $row->fix_tax_rate ?? '',
            $this->positions[$row->position_id]->name ?? '-',
            $this->departments[$row->department]->department ?? '-',
            $row->start_date ? date('Y-m-d', strtotime($row->start_date)) : '',
            $row->date_of_birth ? date('Y-m-d', strtotime($row->date_of_birth)) : '',
            $row->address,
            $row->salary_type,
            $row->salary_rate,
            $row->is_mwe == 1 ? 'Yes' : 'No',
            $row->is_active == 1 ? 'Active' : 'Inactive',
            $this->branches[$row->branch_id]->branch ?? '-',
            $row->yearly_divisor,
            $row->employee_status,
            $hrGroup
        ];
    }

    public function headings(): array
    {
        return [
            'Company ID Number', 
            'Last Name', 
            'First Name', 
            'Middle Name', 
            'Extension Name',
            'Contact Number', 
            'SSS No.', 
            'PhilHealth No.', 
            'HDMF No.', 
            'TIN No.',
            'Fix Divisor',
            'Fix SSS',
            'Fix Philhealth',
            'Fix HDMF',
            'Fix Tax Rate.',
            'Position', 
            'Department', 
            'Start Date', 
            'Date of Birth', 
            'Address',
            'Salary Type', 
            'Salary Rate', 
            'Minimum Wage Earner', 
            'Status',
            'Site',
            'Yearly Divisor',
            'Employee Status',
            'HR Group'
        ];
    }

    public function columnFormats(): array
    {
        return [
            'Q' => NumberFormat::FORMAT_DATE_YYYYMMDD, // Start Date
            'R' => NumberFormat::FORMAT_DATE_YYYYMMDD, // Date of Birth
        ];
    }
}


