<?php

namespace App\Http\Requests;

use App\Models\Platform;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class PostStoreRequest extends FormRequest
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
            'title'          => 'required|string|max:255',
            'content'        => 'required|string',
            'image_url'      => 'nullable|url',
            'scheduled_time' => 'required|date|after_or_equal:now',
            'status'         => 'required|in:draft,scheduled,published',
            'platforms'      => 'required|array|min:1',
            'platforms.*'    => 'exists:platforms,id',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $data = $this->validated(); // safe & fresh data

            $platforms = Platform::whereIn('id', $data['platforms'] ?? [])->get();
            $contentLength = strlen($data['content'] ?? '');

            foreach ($platforms as $platform) {
                if ($contentLength > $platform->character_limit) {
                    $validator->errors()->add(
                        'content',
                        "Content exceeds limit ({$platform->character_limit} chars) for {$platform->name}."
                    );
                }

            }
        });
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422)
        );
    }
}
