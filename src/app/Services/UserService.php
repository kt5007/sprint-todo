<?php

namespace App\Services;

use App\Models\User;
use App\Services;
use Illuminate\Support\Collection;
use DB;
use Illuminate\Http\Request;

class UserService
{
    // ユーザーモデルを使用してユーザーを登録
    public function getActiveUsers()
    {
        $activeUsers = User::whereNull('deleted_at')->get();
        return $activeUsers;
    }

    // ユーザーモデルを使用してユーザーを登録
    public function registerUser($userData)
    {
        $user = new User();
        $user->fill($userData);
        $user->save();

        return $user;
    }
}
