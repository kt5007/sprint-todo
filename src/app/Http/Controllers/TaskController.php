<?php

namespace App\Http\Controllers;

use App\Models\Allocation;
use App\Models\User;
use Illuminate\Http\Request;
use App\Services\UserService;
use App\Services\FreeService;
use App\Services\TaskService;
use App\Services\SprintService;
use Carbon\Carbon;
use DB;

use function PHPUnit\Framework\isNull;

class TaskController extends Controller
{
    public function __construct(
        FreeService $free_service,
        UserService $user_service,
        TaskService $task_service,
        SprintService $sprint_service
    ) {
        $this->user_service = $user_service;
        $this->free_service = $free_service;
        $this->task_service = $task_service;
        $this->sprint_service = $sprint_service;
    }

    public function index(Request $request)
    {
        //パラメータが渡されなかったとき、今日を含むスプリントの画面をデフォルト表示
        $sprint_id =
            $request->query('sprint', $this->sprint_service->currentSprintId());
        $is_valid = $this->sprint_service->isSprintIdValid($sprint_id);
        //存在しないsprint_idがパラメータで渡されたとき、スプリント一覧画面にリダイレクト
        if (!$is_valid) {
            return redirect('/sprint');
        }

        $tasks = $this->task_service->getTaskData($sprint_id);
        $previous_next_sprint_ids = $this->sprint_service->previousAndNextSprintIds($sprint_id);
        $start_and_end = $this->sprint_service->sprintStartAndEnd($sprint_id);
        $is_current_sprint = $sprint_id == $this->sprint_service->currentSprintId();

        $user_ids = User::pluck('id');
        if(empty($user_ids)){
            return view('task.index', compact('users'));
        }
        $users = User::all();

        $target_date = Carbon::today();
        $today = $target_date->toDateString();
        $team_total_free_time = $this->free_service->getTeamTotalFreetimeWhenAccessed($user_ids, $sprint_id, $target_date);
        $user_total_free_times = $this->free_service->getUserTotalFreetimeWhenAccessed($user_ids, $sprint_id, $target_date);
        $team_total_task_time = $this->task_service->getTeamTotalTaskTimeWhenAccessed($user_ids, $sprint_id);
        $user_total_task_times = $this->task_service->getUserTotalTaskTimeWhenAccessed($user_ids, $sprint_id);

        return view('task.index', compact(
            'tasks',
            'sprint_id',
            'previous_next_sprint_ids',
            'start_and_end',
            'is_current_sprint',
            'team_total_free_time',
            'user_total_free_times',
            'team_total_task_time',
            'user_total_task_times',
            'users',
            'today'
        ));
    }

    public function create(Request $request)
    {
        $sprint_id =
            $request->query('sprint', SprintService::INVALID_SPRINT_ID);
        $is_valid = $this->sprint_service->isSprintIdValid($sprint_id);
        //存在しないsprint_idがパラメータで渡されたとき、スプリント一覧画面にリダイレクト
        if (!$is_valid) {
            return redirect('/sprint');
        }

        $users = User::all();
        $assigned_time_array = $this->task_service->usersAssignedTotalTimeArray($sprint_id);
        foreach ($users as $key => $_) {
            if (!array_key_exists($users[$key]->id, $assigned_time_array)) {
                $assigned_time_array[$users[$key]->id] = 0;
            }
        }

        $users_total_free_time = $this->free_service->getUsersTotalFreeTime($sprint_id);

        return view('task.create', compact(
            'users',
            'assigned_time_array',
            'users_total_free_time',
            'sprint_id'
        ));
    }

    public function store(Request $request)
    {
        // dd($request->all());
        $sprint_id = (int)$request['sprint'];
        $is_valid = $this->sprint_service->isSprintIdValid($sprint_id);
        //存在しないsprint_idがパラメータで渡されたとき、スプリント一覧画面にリダイレクト
        if (!$is_valid) {
            return redirect('/sprint');
        }

        $request = $request->all();
        $this->task_service->storeNewTask($request);

        return redirect('/task?sprint=' . $sprint_id);
    }

    public function destroy($id)
    {
        $this->task_service->deleteTask($id);
        return back();
    }

    public function latestSprint()
    {
        $latest_sprint_id = $this->sprint_service->getLatestSprintid();
        // sprint_idが最新のものにする
        return redirect('/task?sprint=' . $latest_sprint_id);
    }

    public function copy($task_id)
    {
        // userがいる場合
        $allocated_users = Allocation::where('task_id',$task_id)->get();
        $latest_sprint_id = $this->sprint_service->getLatestSprintid();
        // userがいない場合
        $task = $this->task_service->getTaskEditData($task_id);
        $task_id = $this->task_service->insertTask($task,$latest_sprint_id);

        //allocationsテーブルへのデータ登録
        foreach ($allocated_users as $allocated_user) {
            $assign_data[] =
                [
                    'user_id' => $allocated_user->user_id,
                    'task_id' => $task_id,
                ];
        };
        if(isset($assign_data)){
            DB::table('allocations')
                ->insert($assign_data);
        }
        // sprint_idが最新のものにする
        return redirect()->route('task.edit', ['task_id' => $task_id]);
    }
    //編集
    public function edit($task_id)
    {
        // タスクのスプリントID取得
        $sprint_id = $this->task_service->getTaskSprintId($task_id);
        // ユーザーの名前とID取得
        $users = User::all();
        // ユーザーごとのアサイン時間と空き時間取得
        $assigned_time_array = $this->task_service->usersAssignedTotalTimeArray($sprint_id);
        foreach ($users as $key => $value) {
            if (!array_key_exists($users[$key]->id, $assigned_time_array)) {
                $assigned_time_array[$users[$key]->id] = 0;
            }
        }
        $users_total_free_time = $this->free_service->getUsersTotalFreeTime($sprint_id);
        // 編集画面で表示するために必要な情報を取得
        // 登録時のアサイン情報　スプリントID　タスクの情報を取得
        $registered_allocation_data = $this->task_service->getTaskEditAllocatedData($task_id);
        $registered_task_data = $this->task_service->getTaskEditData($task_id);
        return view('task.edit', compact(
            'users',
            'assigned_time_array',
            'users_total_free_time',
            'sprint_id',
            'registered_allocation_data',
            'registered_task_data',
            'task_id'
        ));
    }

    public function update(Request $request, $task_id)
    {
        $request = $request->all();
        $sprint_id = (int)$request['sprint'];
        $this->task_service->updateAllocatedData($task_id, $request);
        $this->task_service->updateTaskData($task_id, $request);
        return redirect()->to('/task?sprint='.$sprint_id);
    }
}
