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
        $active_users = User::all();
        return view('user.index', compact('active_users'));
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
