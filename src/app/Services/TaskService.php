<?php

namespace App\Services;

use App\Models\Task;
use App\Services;
use Illuminate\Support\Collection;
use DB;
use Carbon\Carbon;

class TaskService
{
    public function getTaskData($sprint_id): collection
    {
        $task_data = DB::table('tasks')
            ->where('sprint_id', '=', $sprint_id)
            ->whereNull('deleted_at')
            ->get();

        $allocaton_data =
            DB::table('allocations')
            ->select('users.name', 'allocations.task_id', 'allocations.user_id')
            ->join('users', 'users.id', '=', 'allocations.user_id')
            ->get();
        foreach ($task_data as $task) {
            $task_id = $task->id;
            $assined_members = [];
            if ($allocaton_data->contains('task_id', $task_id)) {
                $assined_members = $allocaton_data->where('task_id', '=', $task_id)->pluck('name')->toArray();
                $assined_members_ids = $allocaton_data->where('task_id', '=', $task_id)->pluck('user_id')->toArray();
            }
            //各タスクに関するcollectionに、担当者の情報を追加
            $task->members = $assined_members;
            $task->members_ids = $assined_members_ids;
        }
        foreach ($task_data as $task) {
            $task_status_definition = [1 => '仕掛中', 2 => '未着手', 3 => '完了'];
            if ($task->task_status == 1) {
                $task->status_num = 3;
                $task->status_label = $task_status_definition[3];
            } elseif ($task->task_status == 0 && $task->actual_time == 0) {
                $task->status_num = 2;
                $task->status_label = $task_status_definition[2];
            } else {
                $task->status_num = 1;
                $task->status_label = $task_status_definition[1];
            }
        }

        foreach ($task_data as $task) {
            $task->sort_index = $task->status_num . '-' . $task->category_name;
        }

        return $task_data->sortBy('sort_index');
    }

    public function storeNewTask($request): void
    {
        $checked_users = $this->checkedUsers($request);
        $estimated_time = $request['estimated_time'];
        $estimated_sum_time = $estimated_time * count($checked_users);

        //tasksテーブルへのデータ登録
        DB::table('tasks')
            ->insert([
                'sprint_id' => (int)$request['sprint'],
                'title' => $request['title'],
                'trello_url' => $request['trello_url'],
                'category_name' => $request['category_name'],
                'estimated_time' => $estimated_time,
                'estimated_sum_time' => $estimated_sum_time,
                'task_memo' => $request['task_memo'],
                'actual_time' => $request['actual_time'],
                'task_status' => $this->isFinished($request),
            ]);

        $this_task_id =
            DB::table('tasks')
            ->select('id')
            ->orderBy('id')
            ->get()
            ->last()
            ->id;

        //allocationsテーブルへのデータ登録
        $assign_data = [];
        foreach ($checked_users as $user_id) {
            $assign_data[] =
                [
                    'user_id' => $user_id,
                    'task_id' => $this_task_id,
                ];
        };

        DB::table('allocations')
            ->insert($assign_data);
    }

    // task_idを用いてタスクの削除を行う
    public function deleteTask($task_id): void
    {
        $now = Carbon::now();
        DB::table('tasks')
            ->where('id', '=', $task_id)
            ->update(["deleted_at" => $now]);
    }

    // 各ユーザーのアサイン済の合計を取得
    public function usersAssignedTotalTimeArray($sprint_id): array
    {
        $data = DB::table('allocations')
            ->select('allocations.user_id', 'tasks.estimated_time', 'tasks.task_status')
            ->join('tasks', 'tasks.id', '=', 'allocations.task_id')
            ->where('sprint_id', '=', $sprint_id)
            ->get()
            ->groupby('user_id');

        $assigned_time_array = [];
        foreach ($data as $user_id => $object) {
            $assigned_time_array[$user_id] = $object->sum('estimated_time');
        };

        return $assigned_time_array;
    }

    // アクセス時点での全登録ユーザーの未完了のタスクの合計工数
    public function getTeamTotalTaskTimeWhenAccessed($user_ids, $sprint_id)
    {
        $result = DB::table('allocations')
            ->join('tasks', 'tasks.id', '=', 'allocations.task_id')
            ->whereIn('user_id', $user_ids)
            ->where('sprint_id', '=', $sprint_id)
            ->where('task_status', '=', false)
            ->whereNull('deleted_at')
            ->sum('estimated_time');

        return $result;
    }

    // アクセス時点での各ユーザーの未完了のタスク工数
    public function getUserTotalTaskTimeWhenAccessed($user_ids, $sprint_id)
    {
        $user_totals = DB::table('allocations')
            ->select('user_id', DB::raw('sum(estimated_time) as total_task_time'))
            ->join('tasks', 'tasks.id', '=', 'allocations.task_id')
            ->whereIn('user_id', $user_ids)
            ->where('sprint_id', $sprint_id)
            ->where('task_status', '=', false)
            ->whereNull('deleted_at')
            ->groupBy('user_id')
            ->get();
        $users = DB::table('users')->select('id')->get();
        foreach ($users as $user) {
            $user_totals_array[$user->id] = 0;
        }
        foreach ($user_totals as $user_total) {
            $user_totals_array[$user_total->user_id] = $user_total->total_task_time;
        }
        return $user_totals_array;
    }

    // タスク編集画面で以前に入力されたデータの取得
    // タスク名、タスクの分類、一人あたり工数、メモ、実績工数、タスク完了の取得
    public function getTaskEditData($task_id)
    {
        $tasks = DB::table('tasks')
            ->where('id', '=', $task_id)
            ->get()
            ->first();
        return $tasks;
    }

    // タスクのsprint_id取得
    public function getTaskSprintId($task_id)
    {
        $sprint_id = DB::table('tasks')
            ->where('id', '=', $task_id)
            ->select('sprint_id')
            ->get()
            ->first()
            ->sprint_id;
        return $sprint_id;
    }

    // タスク編集画面で以前に入力されたデータの取得
    // アサインメンバー
    public function getTaskEditAllocatedData($task_id)
    {
        $allocated_users_ids = DB::table('allocations')
            ->join('tasks', 'tasks.id', '=', 'allocations.task_id')
            ->where('task_id', '=', $task_id)
            ->select('user_id')
            ->get();
        $allocated_users = [];
        $users = DB::table('users')->select('id')->get();
        foreach ($users as $user) {
            $allocated_users[$user->id] = false;
        }
        foreach ($allocated_users_ids as $allocated_users_id) {
            $allocated_users[$allocated_users_id->user_id] = true;
        }
        return $allocated_users;
    }

    // タスクを編集してtaks tableに格納
    public function updateTaskData($task_id, $request): void
    {
        $checked_users = $this->checkedUsers($request);
        $estimated_time = $request['estimated_time'];
        $estimated_sum_time = $estimated_time * count($checked_users);
        //tasksテーブルでの更新
        DB::table('tasks')
            ->where('id', '=', $task_id)
            ->update([
                'title' => $request['title'],
                'trello_url' => $request['trello_url'],
                'category_name' => $request['category_name'],
                'estimated_time' => $estimated_time,
                'estimated_sum_time' => $estimated_sum_time,
                'task_memo' => $request['task_memo'],
                'actual_time' => $request['actual_time'],
                'task_status' => $this->isFinished($request),
                'updated_at' => Carbon::now()
            ]);
    }

    public function insertTask($task, $latest_sprint_id)
    {
        //tasksテーブルへのデータ登録
        return DB::table('tasks')
            ->insertGetId([
                'sprint_id' => $latest_sprint_id,
                'title' => $task->title,
                'trello_url' => $task->trello_url,
                'category_name' => $task->category_name,
                'estimated_time' => $task->estimated_time,
                'estimated_sum_time' => $task->estimated_sum_time,
                'task_memo' => $task->task_memo,
                'actual_time' => $task->actual_time,
                'task_status' => $task->task_status
            ]);
    }

    // allocationテーブルから削除して更新する
    public function updateAllocatedData($task_id, $request)
    {
        $checked_users = $this->checkedUsers($request);
        DB::table('allocations')
            ->where('task_id', '=', $task_id)
            ->delete();

        //allocationsテーブルへのデータ登録
        $assign_data = [];
        foreach ($checked_users as $user_id) {
            $assign_data[] =
                [
                    'user_id' => $user_id,
                    'task_id' => $task_id,
                ];
        };

        DB::table('allocations')
            ->insert($assign_data);
    }


    private function isFinished($request): bool
    {
        if (isset($request['task_status'])) {
            return $request['task_status'] === 'on';
        } else {
            return False;
        }
    }

    private function checkedUsers($request): array
    {
        $checked_users = [];
        foreach ($request as $key => $value) {
            if (strstr($key, 'user_check_')) {
                $string = strstr($key, 'user_check_');
                $checked_users[] = (int)str_replace('user_check_', '', $string);
            };
        }
        return $checked_users;
    }
}
