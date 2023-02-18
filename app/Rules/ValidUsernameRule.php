<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ValidUsernameRule implements Rule
{
    private $message = "Username format is invalid";
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        // Usernames can only begin with letters (a-z).
        if(!preg_match('/^[a-z]/', $value))
        {
            $this->message = "Username can only begin with letters (a-z)";
            return false;
        }
        // Usernames can contain letters (a-z), numbers (0-9), periods (.) and underscore (_).
        if(!preg_match('/^[0-9a-z_.]+$/', $value))
        {
            $this->message = "Username can contain letters (a-z), numbers (0-9), periods (.) and underscore (_)";
            return false;
        }
        // Usernames can contain only one period (.)
        if(substr_count($value, ".") > 1)
        {
            $this->message = "Username can contain only one period (.)";
            return false;
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->message;
    }
}
