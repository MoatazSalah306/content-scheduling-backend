<?php

namespace App\Http\Requests;

use App\Models\Platform;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

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
            'image'          => 'sometimes|image|mimes:jpg,jpeg,png|max:2048',
            'scheduled_time' => 'sometimes|required|date|after_or_equal:now',
            'status'         => 'sometimes|required|in:draft,scheduled,published',
            'platforms'      => 'sometimes|required|array|min:1',
            'platforms.*'    => 'sometimes|exists:platforms,id',
        ];
    }

        public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $data = $this->validated();
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
            
            // Add this to ensure proper error response format
            if ($validator->errors()->any()) {
                throw new HttpResponseException(response()->json([
                    'success' => false,
                    'message' => 'Validation Error',
                    'errors' => $validator->errors()
                ], 422));
            }
        });
    }
}
