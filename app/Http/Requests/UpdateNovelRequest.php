<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNovelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'        => ['required', 'string', 'max:255'],
            'author_name'  => ['required', 'string', 'max:255'],
            'synopsis'     => ['nullable', 'string'],
            'cover_image'  => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2560'],
            'remove_cover' => ['nullable', 'boolean'],
            'genres'       => ['nullable', 'array'],
            'genres.*'     => ['exists:genres,id'],
        ];
    }
}
