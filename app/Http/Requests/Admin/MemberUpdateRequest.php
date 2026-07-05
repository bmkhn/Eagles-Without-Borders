<?php

namespace App\Http\Requests\Admin;

use App\Models\Member;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MemberUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var Member $member */
        $member = $this->route('member');

        return [
            'club_id' => ['required', 'integer', 'exists:clubs,id'],
            'position_id' => ['required', 'integer', 'exists:positions,id'],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique(Member::class)->where(function ($query) {
                    return $query->where('club_id', $this->club_id)
                        ->where('position_id', $this->position_id);
                })->ignore($member?->id),
            ],
            'contact_number' => ['required', 'string', 'max:255'],
            'profile_picture' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
            'remove_photo' => ['nullable', 'boolean'],
        ];
    }
}
