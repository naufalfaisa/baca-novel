<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreChapterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $novelId = $this->route('novel')?->id;

        return [
            'chapter_number' => [
                'required',
                'integer',
                Rule::unique('chapters')->where(fn($q) => $q->where('novel_id', $novelId)),
            ],
            'title'   => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
        ];
    }
}
