<?php

namespace App\Http\Requests\Api\User;

use App\Http\Requests\BaseRequest;
use App\Rules\ValidUsernameRule;

class StoreUsernameRequest extends BaseRequest
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
            'username' => ['required','string', 'min:3', 'max:12', new ValidUsernameRule()],
        ];
    }
}
