<?php

namespace App\Http\Requests\Admin;

use App\Models\Member;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MemberStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'club_id' => ['required', 'integer', 'exists:clubs,id'],
            'position_id' => ['required', 'integer', 'exists:positions,id'],
            'first_name' => [
                'required',
                'string',
                'max:255',
            ],
            'middle_initial' => ['nullable', 'string', 'max:10'],
            'last_name' => [
                'required',
                'string',
                'max:255',
            ],
            'suffix' => ['nullable', 'string', 'max:50'],
            'status' => ['sometimes', 'string', 'in:active,inactive'],
            'contact_number' => ['required', 'string', 'max:255'],
            'profile_picture' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
        ];
    }
}
