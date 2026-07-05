<?php

namespace App\Http\Requests\Admin;

use App\Models\Position;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PositionUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var Position $position */
        $position = $this->route('position');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique(Position::class, 'name')->ignore($position?->id),
            ],
        ];
    }
}
