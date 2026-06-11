<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'label' => ['required', 'string', 'max:255'],
            'penerima' => ['required', 'string', 'max:255'],
            'no_hp_penerima' => ['required', 'string', 'max:20'],
            'detail_alamat' => ['required', 'string'],
            'plus_code' => ['nullable', 'string', 'max:255'],
        ];
    }
}
