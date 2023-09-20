<?php

namespace App\Services;

use Illuminate\Support\Collection;
use App\Services;
use DB;

class SprintService
{
    // sprint_id のパラメータが渡されなかったとき、
    // $is_valid = False とするため、sprint_id = 0 をセット
    public const INVALID_SPRINT_ID = 0;

    // sprints tableにデータを格納
    public function storeSprint($request): void
    {
        DB::table('sprints')
            ->insert([
                'start_sprint_date' => $request['sprint_start'],
                'end_sprint_date' => $request['sprint_end'],
            ]);
    }
    // sprints tableから降順で$number件ページネーションして取得
    public function getSprints($number)
    {
        return DB::table('sprints')
            ->orderBy('id', 'desc')
            ->paginate($number);
    }

    // 最新のsprint id取得
    public function getLatestSprintid(): int
    {
        $latest_record =
            DB::table('sprints')
            ->select('id')
            ->orderBy('id', 'desc')
            ->first()
            ->id;

        return $latest_record;
    }

    public function previousAndNextSprintIds($sprint_id): array
    {
        $data =
            DB::table('sprints')
            ->select('id')
            ->orderBy('id')
            ->get();

        // スプリントが削除される可能性があることも考慮すると、
        // $sprint_id-1,$sprint_id+1 が 前後のスプリント id になるとは限らない
        $data_previous_id = $data->where('id', '<', $sprint_id)->last();
        $data_next_id = $data->where('id', '>', $sprint_id)->first();

        $previous_id =
            is_null($data_previous_id) ?  $sprint_id : $data_previous_id->id;
        $next_id =
            is_null($data_next_id) ?  $sprint_id : $data_next_id->id;
        //空き時間表、タスク表bladeにおいて、前後のスプリント期間に切り替えるaタグhref属性に使用
        return [$previous_id, $next_id];
    }

    public function sprintStartAndEnd($sprint_id): array
    {
        $data =
            DB::table('sprints')
            ->select('id', 'start_sprint_date', 'end_sprint_date')
            ->where('id', '=', $sprint_id)
            ->get();

        $start = $data->first()->start_sprint_date;
        $end = $data->first()->end_sprint_date;

        return [$start, $end];
    }

    public function currentSprintId(): int
    {
        $today = date('Y-m-d');
        $data =
            DB::table('sprints')
            ->select('id')
            ->wheredate('start_sprint_date', '<=', $today)
            ->wheredate('end_sprint_date', '>=', $today)
            ->get();

        // 今日を含むスプリントが存在しない場合は、0を返す
        // sprintsテーブルにおいて、sprint_idは 1 から始まるので
        // isSprintIdValid($sprint_id = 0) は False となる
        return is_null($data->first()) ?  0 : $data->first()->id;
    }

    public function isSprintIdValid($sprint_id): bool
    {
        return DB::table('sprints')
            ->select('id')
            ->where('id', '=', $sprint_id)
            ->exists();
    }
}
