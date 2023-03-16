<?php

namespace App\Traits;

trait CheckEmailOrUsername
{
    public function checkEmailOrUsername($data) {
        return filter_var($data, FILTER_VALIDATE_EMAIL) ? 'email' : 'user_login';
    }
}