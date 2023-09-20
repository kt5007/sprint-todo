<?php

namespace App\Http\Controllers;

use App\Services\SprintService;
use App\Services\FreeService;
use Illuminate\Http\Request;
use DB;

class FreeController extends Controller
{
    public function __construct(
        SprintService $sprint_service,
        FreeService $free_service
    ) {
        $this->sprint_service = $sprint_service;
        $this->free_service = $free_service;
    }

    public function index(Request $request)
    {
        $current_sprint_id = $this->sprint_service->currentSprintId();
        //パラメータが渡されなかったとき、今日を含むスプリントの画面をデフォルト表示
        $sprint_id =
            $request->query('sprint', $current_sprint_id);
        $is_valid = $this->sprint_service->isSprintIdValid($sprint_id);

        //存在しないsprint_idがパラメータで渡されたとき、スプリント一覧画面にリダイレクト
        if (!$is_valid) {
            return redirect('/sprint');
        }

        // user_id, user_name のデータは $freetime_data に含まれている。
        // そのスプリント時点で存在しないユーザーを表示させないようにするため、
        // すべてのユーザーを取得する $user_service->getUserIds() メソッドは用いない
        $freetime_data = $this->free_service->freetimeTableData($sprint_id);
        $weekday_array = $this->free_service->weekdayArray($freetime_data);

        $previous_next_sprint_ids = $this->sprint_service->previousAndNextSprintIds($sprint_id);
        $start_and_end = $this->sprint_service->sprintStartAndEnd($sprint_id);
        $is_current_sprint = $sprint_id == $current_sprint_id;

        return view('free.index', compact(
            'sprint_id',
            'freetime_data',
            'weekday_array',
            'previous_next_sprint_ids',
            'start_and_end',
            'is_current_sprint',
        ));
    }

    public function edit(Request $request)
    {
        $sprint_id =
            $request->query('sprint', SprintService::INVALID_SPRINT_ID);
        $is_valid = $this->sprint_service->isSprintIdValid($sprint_id);

        //存在しないsprint_idがパラメータで渡されたとき、スプリント一覧画面にリダイレクト
        if (!$is_valid) {
            return redirect('/sprint');
        }

        $freetime_data = $this->free_service->freetimeTableData($sprint_id);
        $weekday_array = $this->free_service->weekdayArray($freetime_data);

        return view('free.edit', compact(
            'sprint_id',
            'freetime_data',
            'weekday_array',
        ));
    }

    public function update(Request $request)
    {
        $request = $request->all();
        $sprint_id = (int)$request['sprint'];
        $freetime_data = $this->free_service->freetimeTableData($sprint_id);

        $this->free_service->updateFreetime($request, $freetime_data);

        return redirect('/free?sprint=' . $sprint_id);
    }
}
