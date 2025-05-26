<?php

namespace App\Http\Requests;

use App\Models\Platform;
use App\Models\Post;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Carbon as SupportCarbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

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
            'image'          => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'scheduled_time' => Rule::when(
                $this->input('status') === 'scheduled',
                ['required', 'date', 'after_or_equal:now'],
                ['nullable']
            ),
            'status'         => ['required', Rule::in(['draft', 'scheduled', 'published'])],
            'platforms'      => 'required|array|min:1',
            'platforms.*'    => 'exists:platforms,id',
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

             // ✅ Daily post limit validation
            if (($data['status'] ?? null) === 'scheduled' && isset($data['scheduled_time'])) {
                $scheduledDate = Carbon::parse($data['scheduled_time'])->toDateString();
               
                $user = Auth::user();

                $scheduledPostsCount = Post::where('user_id', $user->id)
                    ->whereDate('scheduled_time', $scheduledDate)
                    ->count();

                if ($scheduledPostsCount >= 10) {
                    $validator->errors()->add(
                        'scheduled_time',
                        'You have reached the daily limit of 10 scheduled posts.'
                    );
                }
                //  Log::info($scheduledDate);
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
