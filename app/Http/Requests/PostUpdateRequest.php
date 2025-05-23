<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PostUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
{
    return [
        'title'          => 'sometimes|required|string|max:255',
        'content'        => 'sometimes|required|string',
        'image_url'      => 'sometimes|nullable|url',
        'scheduled_time' => 'sometimes|required|date|after_or_equal:now',
        'status'         => 'sometimes|required|in:draft,scheduled,published',
        'platforms'      => 'sometimes|required|array|min:1',
        'platforms.*'    => 'sometimes|exists:platforms,id',
    ];
}
}
