<?php

namespace App\Http\Requests\Api\User;

use App\Http\Requests\BaseRequest;
use App\Rules\ValidUsernameRule;

class StoreGeneralDataRequest extends BaseRequest
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
            'age' => 'nullable',
            'gender' => 'nullable',
            'about_you' => 'nullable',
            'facebook_url' => 'nullable|url|regex:/http(?:s):\/\/(?:www\.)facebook\.com\/.+/i',
            'instagram_url' => 'nullable|url|regex:/http(?:s):\/\/(?:www\.)instagram\.com\/.+/i',
            'twitter_url' => 'nullable|url|regex:/http(?:s):\/\/(?:www\.)twitter\.com\/.+/i',
            'username' => ['nullable','string', 'min:3', 'max:12', new ValidUsernameRule()],
        ];
    }
}
