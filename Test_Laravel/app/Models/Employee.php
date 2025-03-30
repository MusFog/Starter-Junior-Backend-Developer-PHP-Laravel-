<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Employee extends Model
{
    use HasFactory;

    protected $table = 'employees';
    protected $primaryKey = 'id';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'user_id',
        'employee_name',
        'email',
        'phone',
        'level',
        'position_name',
        'position_id',
        'salary',
        'supervisor_name',
        'supervisor_id',
        'employment_date',
        'admin_created_id',
        'admin_updated_id',
        'image_path'
    ];

    protected $casts = [
        'employment_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function subordinates()
    {
        return $this->hasMany(Employee::class, 'supervisor_id');
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'supervisor_id');
    }

    public function scopeEligibleSupervisor(Builder $query, Employee $employee): void
    {
        $query->where('id', '!=', $employee->id)
            ->where('level', '>=', $employee->level);
    }

    public function reassignSubordinates(): void
    {
        $newSupervisor = self::eligibleSupervisor($this)->first();

        $this->subordinates()->update([
            'supervisor_id'   => $newSupervisor ? $newSupervisor->id : null,
            'supervisor_name' => $newSupervisor ? $newSupervisor->employee_name : null,
        ]);
    }



}
