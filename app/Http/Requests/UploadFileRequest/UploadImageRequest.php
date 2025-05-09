<?php

namespace App\Http\Requests\UploadFileRequest;

use Illuminate\Foundation\Http\FormRequest;

class UploadImageRequest extends FormRequest
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
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'folder' => 'nullable|string',
            'old_path' => 'nullable|string'
        ];
    }

    public function messages(): array
    {
        return [
            // Messages cho trường 'image'
            'image.required' => 'Vui lòng chọn một file ảnh để upload.',
            'image.image' => 'File được chọn phải là một ảnh hợp lệ.',
            'image.mimes' => 'Ảnh chỉ được phép có định dạng: jpeg, png, jpg, hoặc gif.',
            'image.max' => 'Kích thước ảnh không được vượt quá 2MB.',

            // Messages cho trường 'old_avatar'
            'old_path.string' => 'URL avatar cũ phải là một chuỗi ký tự hợp lệ.'
        ];
    }
}
