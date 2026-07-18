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
            'contact_number' => ['required', 'string', 'max:255'],
            'profile_picture' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],

            // Certificates
            'certificates' => ['nullable', 'array'],
            'certificates.*.name' => ['required_with:certificates.*.file', 'string', 'max:255'],
            'certificates.*.file' => ['nullable', 'file', 'mimes:pdf,jpeg,png,jpg,gif,webp', 'max:5120'],
            'certificates.*.issued_at' => ['nullable', 'date'],

            // Payments (optional, recorded at creation)
            'payments' => ['nullable', 'array'],
            'payments.*.year_paid' => ['required', 'integer', 'min:2000', 'max:2099'],
            'payments.*.date_paid' => ['nullable', 'date'],
        ];
    }

    public function attributes(): array
    {
        return [
            'certificates.*.name' => 'certificate name',
            'certificates.*.file' => 'certificate file',
            'certificates.*.issued_at' => 'certificate issue date',
            'payments.*.year_paid' => 'payment year',
            'payments.*.date_paid' => 'payment date',
        ];
    }
}
