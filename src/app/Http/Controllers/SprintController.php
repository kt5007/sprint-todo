<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\UserService;
use App\Services\SprintService;
use App\Services\FreeTemplateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SprintController extends Controller
{
    const NUM_PAGINATION = 10;

    public function __construct(
        UserService $user_service,
        SprintService $sprint_service,
        FreeTemplateService $free_template_service
    ) {
        $this->user_service = $user_service;
        $this->sprint_service = $sprint_service;
        $this->free_template_service = $free_template_service;
    }

    public function index()
    {
        // sprints dbからid,stat_sprint_date,end_sprint_date,memoをcollection型で取得
        $sprints = $this->sprint_service->getSprints(self::NUM_PAGINATION);
        $active_users = User::all();

        return view('sprint.index', compact('sprints', 'active_users'));
    }

    public function store(Request $request)
    {
        // リクエストデータを取得
        $data = $request->all();
        // Sprint DB に日付を最終行に挿入する
        $this->sprint_service->storeSprint($data);
        // Free DBに作成した期間分初期空き時間作成
        // ユーザー分✕期間
        $latest_sprint_id = $this->sprint_service->getLatestSprintid();
        $active_user_ids = User::pluck('id');

        if (!isset($data['apply_template'])) {
            $this->free_template_service->applyFreetimeTemplate($latest_sprint_id);
        } else {
            $this->free_template_service->createZeroFreetimeData($latest_sprint_id, $active_user_ids);
        }

        return redirect()->to('/free?sprint=' . $latest_sprint_id);
    }

    public function edit_template()
    {
        $template_data = $this->free_template_service->templateTableData();
        $weekday_array = ['日', '月', '火', '水', '木', '金', '土'];

        return view('sprint.template', compact('template_data', 'weekday_array'));
    }

    public function update_template(Request $request)
    {
        $request = $request->all();
        $template_data = $this->free_template_service->templateTableData();
        $this->free_template_service->updateTemplate($request, $template_data);

        return redirect('/sprint');
    }
}
