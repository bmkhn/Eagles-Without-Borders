<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Certificate extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'name',
        'issued_at',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }
}
