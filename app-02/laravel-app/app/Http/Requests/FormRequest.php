<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http;

class FormRequest extends Http\FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [];
    }
}
