<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersonnelAction extends Model
{
    use HasFactory;

    protected $table = 'tbl_personnel_action';
    protected $connection = 'intra_payroll';
    protected $guarded = [];
    public $timestamps = false;

    protected $fillable = [
        'employee_id',
        'action_type',
        'position_id',
        'department_id',
        'date_hired',
        'sss_number',
        'hdmf_number',
        'tin_number',
        'effective_date',
        'remarks',
        'date_created',
        'user_id_added',
        'endorsement_name',
        'endorsement_position',
        'endorsement_date',
        'approved_name',
        'approved_position',
        'approved_date',
        'ack_employee_name',
        'ack_date',
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
        'total_comp_to',
        'user_id_updated',
        'date_updated'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }
}
