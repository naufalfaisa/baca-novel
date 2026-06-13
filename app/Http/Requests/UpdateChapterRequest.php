<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateChapterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $novelId   = $this->route('novel')?->id;
        $chapterId = $this->route('chapter')?->id;

        return [
            'chapter_number' => [
                'required',
                'integer',
                Rule::unique('chapters')
                    ->where(fn($q) => $q->where('novel_id', $novelId))
                    ->ignore($chapterId),
            ],
            'title'   => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
        ];
    }
}
