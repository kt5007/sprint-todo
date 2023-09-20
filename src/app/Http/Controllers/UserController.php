<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\UserService;
use App\Services\FreeTemplateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function __construct(
        UserService $user_service,
        FreeTemplateService $template_service
    ) {
        $this->user_service = $user_service;
        $this->template_service = $template_service;
    }

    public function index()
    {
        $activeUsers = $this->user_service->getActiveUsers();
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
        $user = $this->user_service->registerUser($userData);

        $coming_user_id =
        DB::table('users')
        ->select('id')
        ->orderBy('id')
        ->get()
        ->last()
        ->id;

        $this->template_service->insertNewUserToTemplate($coming_user_id);

        // ユーザー登録が成功した場合のリダイレクト先を設定
        return redirect()->route('user.index')->with('success', 'ユーザーが登録されました。');
    }
    public function user_create(Request $request)
    {
        $data = $request->all();
        $this->user_service->createUser($data['user_name']);

        $coming_user_id =
            DB::table('users')
            ->select('id')
            ->orderBy('id')
            ->get()
            ->last()
            ->id;

        $this->template_service->insertNewUserToTemplate($coming_user_id);

        return back();
    }

    public function delete($user_id)
    {
        User::find($user_id)->delete();
        return back();
    }
}
