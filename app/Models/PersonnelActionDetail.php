<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersonnelActionDetail extends Model
{
    use HasFactory;

    protected $table = 'tbl_personnel_action';
    protected $connection = 'intra_payroll';
    protected $guarded = [];
    public $timestamps = false;

    protected $fillable = [
        'personnel_action_id',
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
    ];

    public function personnelAction()
    {
        return $this->belongsTo(PersonnelAction::class, 'personnel_action_id', 'id');
    }
}
