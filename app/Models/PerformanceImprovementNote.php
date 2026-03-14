<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerformanceImprovementNote extends Model
{
    use HasFactory;

    protected $table = 'performance_improvement_notes';
    protected $guarded = [];

    // Cast date_served to Carbon instance
    protected $casts = [
        'date_served' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    public function replies()
    {
        return $this->hasMany(PerformanceImprovementNote::class, 'parent_id', 'id');
    }

    public function parent()
    {
        return $this->belongsTo(PerformanceImprovementNote::class, 'parent_id', 'id');
    }
}
