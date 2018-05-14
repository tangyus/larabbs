<?php

namespace App\Http\Requests\Api;

class ImageRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'type' => 'required|string|in:avatar,topic'
        ];

        if ($this->type = 'avatar') {
        	$rules['image'] = 'mimes:jpeg,bmp,png,gif|dimensions:min_width:200,min_height:200';
		} else {
        	$rules['image'] = 'mimes:jpeg,bmp,png,gif';
		}

		return $rules;
    }

    public function messages()
	{
		return [
			'image:dimensions' => '图片的清晰度不够，宽和高需要 200px 以上',
			'image:mimes' => '图片格式不正确',
		];
	}
}