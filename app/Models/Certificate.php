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
        'file',
        'issued_at',
    ];

    protected $appends = ['file_url'];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function getFileUrlAttribute(): ?string
    {
        if (!$this->file) {
            return null;
        }

        return asset('storage/' . $this->file);
    }
}
