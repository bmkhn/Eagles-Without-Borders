<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Club extends Model
{
    use HasFactory;

    protected $fillable = [
        'region_id',
        'name',
    ];

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(Member::class);
    }

    public function clubPresident(): HasOne
    {
        return $this->hasOne(User::class, 'club_id')
            ->whereHas('roles', function ($q) {
                $q->where('name', 'club-admin');
            });
    }

    /**
     * All admin accounts connected to this club.
     */
    public function adminUsers(): HasMany
    {
        return $this->hasMany(User::class, 'club_id');
    }
}
