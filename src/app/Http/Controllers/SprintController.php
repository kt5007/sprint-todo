<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\SprintService;
use Illuminate\Http\Request;

class SprintController extends Controller
{
    const NUM_PAGINATION = 10;

    public function __construct(
        SprintService $sprintService
    ) {
        $this->sprintService = $sprintService;
    }

    public function index()
    {
        // sprints dbからid,stat_sprint_date,end_sprint_date,memoをcollection型で取得
        $sprints = $this->sprintService->getSprints(self::NUM_PAGINATION);
        $active_users = User::all();

        return view('sprint.index', compact('sprints', 'active_users'));
    }
}
