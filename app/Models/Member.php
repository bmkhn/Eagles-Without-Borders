<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Member extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'club_id',
        'position_id',
        'first_name',
        'middle_initial',
        'last_name',
        'suffix',
        'status',
        'slug',
        'contact_number',
        'profile_picture',
    ];

    protected $appends = ['profile_picture_url', 'name'];

    public function getNameAttribute(): string
    {
        $parts = [$this->first_name];

        if ($this->middle_initial) {
            $parts[] = $this->middle_initial . '.';
        }

        $parts[] = $this->last_name;

        if ($this->suffix) {
            $parts[] = $this->suffix;
        }

        return implode(' ', $parts);
    }

    public function getProfilePictureUrlAttribute(): ?string
    {
        if (!$this->profile_picture) {
            return null;
        }

        return asset('storage/' . $this->profile_picture);
    }

    protected static function slugify(string $value): string
    {
        $value = mb_strtolower(trim($value));

        // Replace any non alphanumeric sequences with hyphen.
        $value = preg_replace('/[^a-z0-9]+/i', '-', $value) ?? '';

        // Collapse consecutive hyphens and trim.
        $value = preg_replace('/-+/u', '-', $value) ?? '';
        $value = trim($value, '-');

        return $value;
    }

    /**
     * Generate unique slug with collision handling.
     * Soft-deleted members are ignored for collision checks (recommended).
     */
    public static function generateUniqueSlug(string $firstName, string $lastName, int $clubId, ?int $ignoreMemberId = null): string
    {
        $base = static::slugify($firstName . ' ' . $lastName);

        if ($base === '') {
            $base = 'member';
        }

        $query = static::query()
            ->where('club_id', $clubId)
            ->where('slug', $base);

        if ($ignoreMemberId) {
            $query->where('id', '!=', $ignoreMemberId);
        }

        // members table uses SoftDeletes trait; default Eloquent query excludes soft-deleted rows.
        if (!$query->exists()) {
            return $base;
        }

        for ($i = 2; $i < 10000; $i++) {
            $candidate = "{$base}-{$i}";

            $candidateQuery = static::query()
                ->where('club_id', $clubId)
                ->where('slug', $candidate);

            if ($ignoreMemberId) {
                $candidateQuery->where('id', '!=', $ignoreMemberId);
            }

            if (!$candidateQuery->exists()) {
                return $candidate;
            }
        }

        // Fallback (extremely unlikely)
        return "{$base}-".time();
    }

    /**
     * Instance helper to populate slug from name parts.
     */
    public function applySlugFromName(): void
    {
        $this->slug = static::generateUniqueSlug($this->first_name, $this->last_name, (int) $this->club_id, $this->id);
    }

    /**
     * Detect potential duplicate members for a given club.
     *
     * Duplicate rules:
     * - exact contact_number match
     * - exact/similar first_name + last_name after normalization
     *
     * Soft-deleted members are ignored (default Eloquent behavior).
     */
    public static function findPotentialDuplicates(
        int $clubId,
        string $firstName,
        string $lastName,
        ?string $contactNumber,
        ?int $ignoreMemberId = null
    ) {
        $q = static::query()->where('club_id', $clubId);

        if ($ignoreMemberId) {
            $q->where('id', '!=', $ignoreMemberId);
        }

        // Exact contact number match (if provided)
        if ($contactNumber !== null && trim($contactNumber) !== '') {
            $q->where(function ($sub) use ($contactNumber, $firstName, $lastName) {
                $sub->where('contact_number', $contactNumber)
                    ->orWhere(function ($nameQ) use ($firstName, $lastName) {
                        $nameQ->whereRaw('LOWER(TRIM(first_name)) = ?', [mb_strtolower(trim($firstName))])
                              ->whereRaw('LOWER(TRIM(last_name)) = ?', [mb_strtolower(trim($lastName))]);
                    });
            });
        } else {
            $q->whereRaw('LOWER(TRIM(first_name)) = ?', [mb_strtolower(trim($firstName))])
              ->whereRaw('LOWER(TRIM(last_name)) = ?', [mb_strtolower(trim($lastName))]);
        }

        return $q->get();
    }

    /**
     * Model-only payload intended for a \"warning modal\".
     */
    public static function duplicateWarningPayload(
        int $clubId,
        string $firstName,
        string $lastName,
        ?string $contactNumber,
        ?int $ignoreMemberId = null
    ): array {
        $duplicates = static::findPotentialDuplicates($clubId, $firstName, $lastName, $contactNumber, $ignoreMemberId);

        return [
            'message' => 'Potential duplicate members found',
            'duplicates' => $duplicates->map(fn (self $m) => [
                'id' => $m->id,
                'name' => $m->name,
                'slug' => $m->slug,
                'contact_number' => $m->contact_number,
                'position_id' => $m->position_id,
            ])->values()->all(),
            'canContinue' => true,
            'canCancel' => true,
        ];
    }

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }
}
