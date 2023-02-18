<?php

namespace App\Http\Requests\Api\Home;

use App\Http\Requests\BaseRequest;

class FollowingRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'follow_id' => 'required|exists:users,id',
            'is_follow' => 'required|boolean',
        ];
    }
}
