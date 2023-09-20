<?php

namespace App\Services;

use App\Services;
use Illuminate\Support\Collection;
use Prophecy\Call\Call;
use DB;

class FreeService
{
    public function freetimeTableData($sprint_id): collection
    {
        $freetime_data =  DB::table('frees')
            ->select('sprint_id', 'users.id as user_id', 'users.name',  'frees.registered_date', 'frees.free_time', 'frees.memo')
            ->join('users', 'users.id', '=', 'frees.user_id')
            ->where('sprint_id', '=', $sprint_id)
            ->orderBy('user_id')
            ->orderBy('registered_date')
            ->get();

        return $freetime_data;
    }

    public function updateFreetime($request, $freetime_data): void
    {
        $records = $this->convertRequestToRecords($request);
        $records_to_update = $this->recordsToUpdate($records, $freetime_data);

        foreach ($records_to_update as $record) {
            DB::table('frees')
                ->where('sprint_id', '=', $record['sprint_id'])
                ->where('user_id', '=', $record['user_id'])
                ->where('registered_date', '=', $record['registered_date'])
                ->update([
                    'free_time' => $record['free_time'],
                    'memo' => $record['memo'],
                ]);
        }
    }
    // 各ユーザーの空き時間の合計時間の取得
    public function getUsersTotalFreeTime($sprint_id): array
    {
        $sprint_data = DB::table('sprints')
            ->select('start_sprint_date', 'end_sprint_date')
            ->where('id', '=', $sprint_id)
            ->get()
            ->first();

        $sprint_start_at = $sprint_data->start_sprint_date;
        $sprint_end_at = $sprint_data->end_sprint_date;

        $data = DB::table('frees')
            ->select('user_id', 'registered_date', 'free_time')
            ->whereDate('registered_date', '>=', $sprint_start_at)
            ->whereDate('registered_date', '<=', $sprint_end_at)
            ->get()
            ->groupby('user_id');

        $users_total_free_time = [];
        foreach ($data as $user_id => $collection) {
            $users_total_free_time[$user_id] = $collection->sum('free_time');
        };

        return $users_total_free_time;
    }

    // アクセス時点での全登録ユーザーの合計空き時間の取得
    public function getTeamTotalFreetimeWhenAccessed($user_ids, $sprint_id, $target_date)
    {
        $team_total = DB::table('frees')
            ->whereIn('user_id', $user_ids)
            ->whereDate('registered_date', '>=', $target_date)
            ->where('sprint_id', '=', $sprint_id)
            ->sum('free_time');
        return $team_total;
    }

    // アクセス時点での各ユーザーの合計空き時間の取得
    public function getUserTotalFreetimeWhenAccessed($user_ids, $sprint_id, $target_date): array
    {
        $user_totals = DB::table('frees')
            ->select('user_id', DB::raw('sum(free_time) as total_free_time'))
            ->whereIn('user_id', $user_ids)
            ->whereDate('registered_date', '>=', $target_date)
            ->where('sprint_id', '=', $sprint_id)
            ->groupBy('user_id')
            ->get();
        $users = DB::table('users')->select('id')->get();
        foreach ($users as $user) {
            $user_totals_array[$user->id] = 0;
        }
        foreach ($user_totals as $user_total) {
            $user_totals_array[$user_total->user_id] = $user_total->total_free_time;
        }
        return $user_totals_array;
    }
    
    public function weekdayArray($freetime_data): array
    {
        $week = ['日', '月', '火', '水', '木', '金', '土'];

        $weekday_array = [];
        foreach ($freetime_data->keyby('registered_date')->keys() as $date) {
            $week_number = date('w', strtotime($date));
            $weekday_array[$date] = $week[$week_number];
        }

        return $weekday_array;
    }

    private function convertRequestToRecords($request)
    {
        $sprint_id = $request['sprint'];
        //requestの配列の中身を、時間とメモに関するものだけにする
        unset($request['_token']);
        unset($request['sprint']);

        $request_time = $request;
        $request_memo = [];

        // $request_time と $request_memo を、
        // 共通のキー($user_id.'_'.$registered_date)を持つ配列にする
        foreach ($request as $key => $value) {
            // name属性に _memo を含む場合。文字検索はsrtstrよりstropsのほうが高速
            if (strpos($key, '_memo') !== false) {
                //時間については、不要な要素を削除する
                unset($request_time[$key]);
                //メモについては、該当する要素を追加する
                $request_memo[str_replace("_memo", "", $key)] = $value;
            }
        }

        $records = [];
        foreach ($request_time as $key => $free_time) {
            $user_id = (int)(explode('_', $key)[0]);
            $registered_date = date(explode('_', $key)[1]);

            $records[] = [
                'user_id' => $user_id,
                'sprint_id' => $sprint_id,
                'registered_date' => $registered_date,
                'free_time' => $free_time,
                'memo' => $request_memo[$key],
            ];
        }

        return $records;
    }

    private function recordsToUpdate($records, $freetime_data)
    {
        $records_to_update = [];

        foreach ($records as $record) {

            $previous_data =
                $freetime_data
                ->where('sprint_id', '=', $record['sprint_id'])
                ->where('user_id', '=', $record['user_id'])
                ->firstwhere('registered_date', '=', $record['registered_date']);
            $previous_time = $previous_data->free_time;
            $previous_memo = $previous_data->memo;

            $is_changed_time =
                $record['free_time'] != $previous_time;
            $is_changed_memo =
                $record['memo'] != $previous_memo;

            if ($is_changed_time || $is_changed_memo) {
                $records_to_update[] = $record;
            }
        }
        return $records_to_update;
    }
}
