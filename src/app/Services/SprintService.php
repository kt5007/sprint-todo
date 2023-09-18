<?php

namespace App\Services;

use App\Models\Sprint;

class SprintService
{
    // sprints tableから降順で$number件ページネーションして取得
    public function getSprints($number)
    {
        return Sprint::orderBy('id', 'desc')->paginate($number);
    }
}
