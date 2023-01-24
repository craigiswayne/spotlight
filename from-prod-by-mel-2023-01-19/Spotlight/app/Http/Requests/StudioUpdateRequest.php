<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StudioUpdateRequest extends FormRequest
{
	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize() {
		return true;
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules() {
         //dd(request()->all());
		return [
			'image' => ['sometimes', 'required', 'mimes:jpg,jpeg,png,svg'],
			'video' => ['sometimes', 'required', 'mimes:mp4'],
			'studio_name' => ['sometimes', 'required', 'string', 'min:1', 'max:255']
		];
	}

	/**
	 * Get the error messages for the defined validation rules.
	 *
	 * @return array
	 */
	public function messages() {
		return [
			'image.required' => 'An image is required',
			'image.mimes' => 'The image must be a JPG, JPEG, PNG, or SVG',
			'video.required' => 'A video is required',
			'video.mimes' => 'The video must be in MP4 format',
			'studio_name.required' => 'A name is required',
			'studio_name.min' => 'The name must contain at least one character that is not a space',
			'studio_name.max' => 'The name cannot exceed 255 characters'
		];
	}
}