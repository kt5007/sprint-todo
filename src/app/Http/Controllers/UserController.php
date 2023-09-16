<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        return view('user.index');
    }

    // ユーザー登録フォームを処理するアクション
    public function register(Request $request)
    {
        // フォームから送信されたデータを取得
        $username = $request->input('username');

        // データを処理するロジックをここに追加
        dd($username);

        // ユーザー登録が成功した場合のリダイレクト先を設定
        return redirect()->route('home')->with('success', 'ユーザーが登録されました。');
    }
}
