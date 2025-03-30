<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Position extends Model
{
    use HasFactory;

    protected $table = 'positions';
    protected $primaryKey = 'id';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'admin_created_id',
        'admin_updated_id'
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_created_id');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_updated_id');
    }

    public function admins(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}
