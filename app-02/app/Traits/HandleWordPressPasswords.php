<?php

namespace App\Traits;

use App\User;
use Hautelook\Phpass\PasswordHash;
use Illuminate\Support\Facades\Hash;
use App\Traits\CheckEmailOrUsername;

trait HandleWordPressPasswords
{
    use CheckEmailOrUsername;

    public function HandleWordPressPassword($credentials) {
        $login_field = $this->CheckEmailOrUsername($credentials['login']);

        $user = User::where($login_field, $credentials['login'])->first();

        if (!$user) {
            return response()->json(['data' => trans('auth.failed')], 401);
        }

        if (!empty($user->legacy_password)) {
            $passwordHasher = new PasswordHash(8,true);

            $check_password = $passwordHasher->CheckPassword(trim($credentials['password']), $user->legacy_password);

            if ($check_password) {
                $user->password = Hash::make(trim($credentials['password']));
                $user->legacy_password = '';
                $user->save();
            }
        }
    }
}
