<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NteNote extends Model
{
    use HasFactory;

    protected $table = 'nte_notes';

    protected $fillable = [
        'employee_id',
        'case_details',
        'remarks',
        'date_served',
        'attachment_path',
        'parent_id',
    ];

    protected $dates = [
        'date_served',
        'created_at',
        'updated_at',
    ];

    /**
     * Get the employee that this NTE note belongs to
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    /**
     * Get the replies to this NTE note
     */
    public function replies()
    {
        return $this->hasMany(NteNote::class, 'parent_id', 'id');
    }

    /**
     * Get the parent NTE note (if this is a reply)
     */
    public function parent()
    {
        return $this->belongsTo(NteNote::class, 'parent_id');
    }
}
