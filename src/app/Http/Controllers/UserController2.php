<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(
        UserService $userService
    ) {
        $this->userService = $userService;
    }

    public function index()
    {
        $activeUsers = $this->userService->getActiveUsers();
        return view('user.index', ['activeUsers' => $activeUsers]);
    }

    // ユーザー登録フォームを処理するアクション
    public function register(Request $request)
    {
        // フォームから送信されたデータを取得
        $user_name = $request->input('username');
        $userData = [
            'name' => $user_name
        ];

        // データを処理するロジック
        $user = $this->userService->registerUser($userData);

        // ユーザー登録が成功した場合のリダイレクト先を設定
        return redirect()->route('user.index')->with('success', 'ユーザーが登録されました。');
    }

}
