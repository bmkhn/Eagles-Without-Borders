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
            'contact_number' => ['required', 'string', 'max:255'],
            'profile_picture' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
            'remove_photo' => ['nullable', 'boolean'],

            // Certificates
            'certificates' => ['nullable', 'array'],
            'certificates.*.name' => ['required_with:certificates.*.file', 'string', 'max:255'],
            'certificates.*.file' => ['nullable', 'file', 'mimes:pdf,jpeg,png,jpg,gif,webp', 'max:5120'],
            'certificates.*.issued_at' => ['nullable', 'date'],
        ];
    }

    public function attributes(): array
    {
        return [
            'certificates.*.name' => 'certificate name',
            'certificates.*.file' => 'certificate file',
            'certificates.*.issued_at' => 'certificate issue date',
        ];
    }
}
