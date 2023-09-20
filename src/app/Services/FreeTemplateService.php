<?php

namespace App\Services;

use App\Services;
use Illuminate\Support\Collection;
use Prophecy\Call\Call;
use Carbon\Carbon;
use DB;

class FreeTemplateService
{
    public function insertNewUserToTemplate($user_id): void
    {
        $records = [];
        foreach (range(1, 5) as $weekday_number) {
            $records[] = ['user_id' => $user_id, 'weekday_number' => $weekday_number, 'free_time' => 0];
        }
        DB::table('templates')->insert($records);
    }

    public function templateTableData(): collection
    {
        $template_data =
            DB::table('templates')
            ->select('users.id as user_id', 'users.name', 'templates.weekday_number', 'templates.free_time', 'templates.memo')
            ->join('users', 'users.id', '=', 'templates.user_id')
            ->whereNull('deleted_at')
            ->orderBy('user_id')
            ->orderBy('weekday_number')
            ->get();

        return $template_data;
    }

    public function updateTemplate($request, $template_data): void
    {
        $records = $this->convertRequestToRecords($request);
        $records_to_update = $this->recordsToUpdate($records, $template_data);

        foreach ($records_to_update as $record) {
            DB::table('templates')
                ->where('user_id', '=', $record['user_id'])
                ->where('weekday_number', '=', $record['weekday_number'])
                ->update([
                    'free_time' => $record['free_time'],
                    'memo' => $record['memo'],
                ]);
        };
    }

    public function applyFreetimeTemplate($sprint_id): void
    {
        $template_data = DB::table('templates')
            ->select('user_id', 'weekday_number', 'free_time', 'memo')
            ->join('users', 'users.id', '=', 'templates.user_id')
            ->whereNull('deleted_at')
            ->get();

        $sprint_data = $this->sprintStartAndEnd($sprint_id);
        $sprint_start_at = $sprint_data[0];
        $sprint_end_at = $sprint_data[1];
        $dates = $this->generateSprintDays($sprint_start_at, $sprint_end_at);

        $records = [];
        foreach ($template_data->groupby('user_id') as $user_id => $collection) {
            foreach ($dates as $date) {
                $weekday = (int)date('w', strtotime($date));
                $object = $collection->firstwhere('weekday_number', '=', $weekday);
                $records[] =
                    [
                        'sprint_id' => $sprint_id,
                        'user_id' => $user_id,
                        'registered_date' => $date,
                        'free_time' => $object->free_time,
                        'memo' => $object->memo,
                    ];
            }
        }

        DB::table('frees')->insert($records);
    }

    public function createZeroFreetimeData($sprint_id, $user_ids): void
    {
        $sprint_data = $this->sprintStartAndEnd($sprint_id);
        $sprint_start_at = $sprint_data[0];
        $sprint_end_at = $sprint_data[1];

        $dates = $this->generateSprintDays($sprint_start_at, $sprint_end_at);
        $records = $this->generateZeroFreetimeRecords($sprint_id, $user_ids, $dates);

        DB::table('frees')->insert($records);
    }

    private function convertRequestToRecords($request)
    {
        //requestの配列の中身を、時間とメモに関するものだけにする
        unset($request['_token']);
        unset($request['sprint']);

        $request_time = $request;
        $request_memo = [];

        // $request_time と $request_memo を、
        // 共通のキー($user_id.'_'.$weekday_number)を持つ配列にする
        foreach ($request as $key => $value) {
            // name属性に _memo を含む場合。文字検索はsrtstrよりstropsのほうが高速
            if (strpos($key, '_memo') !== false) {
                //時間については、$request_time から不要な要素を削除する
                unset($request_time[$key]);
                //メモについては、該当する要素を $request_memo に追加する
                $request_memo[str_replace("_memo", "", $key)] = $value;
            }
        }

        $records = [];
        foreach ($request_time as $key => $free_time) {
            $user_id = (int)(explode('_', $key)[0]);
            $weekday_number = (int)(explode('_', $key)[1]);

            $records[] = [
                'user_id' => $user_id,
                'weekday_number' => $weekday_number,
                'free_time' => $free_time,
                'memo' => $request_memo[$key],
            ];
        }

        return $records;
    }

    private function recordsToUpdate($records, $template_data)
    {
        $records_to_update = [];

        foreach ($records as $record) {

            $previous_data =
                $template_data
                ->where('user_id', '=', $record['user_id'])
                ->firstwhere('weekday_number', '=', $record['weekday_number']);
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

    private function sprintStartAndEnd($sprint_id): array
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

    private function checkStartAndEnd($start, $end): bool
    {
        $start_date = new Carbon(strtotime($start));
        $end_date = new Carbon(strtotime($end));

        return $start_date <= $end_date;
    }

    private function generateSprintDays($start, $end): array
    {
        // 万が一日付の前後が逆だったのときの処理
        if (!$this->checkStartAndEnd($start, $end)) {
            list($_start, $_end) = array($start, $end);
            list($start, $end) = array($_end, $_start);
        }
        $start_date = new Carbon($start);
        $end_date = new Carbon($end);
        $dates_diff = $start_date->diffInDays($end_date);

        $weekday_array = [];
        for ($i = 0; $i <= $dates_diff; $i++) {
            $new_day = $start_date->copy()->addDay($i);
            if ($new_day->isWeekday()) {
                $weekday_array[] = $new_day->format('Y-m-d');
            }
        }

        return $weekday_array;
    }

    private function generateZeroFreetimeRecords($sprint_id, $user_ids, $dates): array
    {
        $records = [];
        foreach ($user_ids as $user_id) {
            foreach ($dates as $date) {
                $records[] = [
                    'sprint_id' => $sprint_id,
                    'user_id' => $user_id,
                    'registered_date' => $date,
                    'free_time' => 0,
                ];
            };
        };
        return $records;
    }
}
