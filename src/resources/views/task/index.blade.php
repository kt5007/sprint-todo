@extends('layout.master')

@section('title', 'Home')
@section('guide', 'チームのタスク')

@section('content')
    @include('task.copy_task_to_latest_sprint')
    <nav aria-label="sprint-pagenation">
        <ul class="pagination">
            <li class="page-item align-middle">
                <a class="page-link" href="{{ url('task?sprint=' . $previous_next_sprint_ids[0]) }}"><span
                        aria-hidden="true">&laquo;</span></a>
            </li>
            <li class="page-item mx-3 align-middle text-center">
                {{ date('y/m/d', strtotime($start_and_end[0])) }} ~ {{ date('y/m/d', strtotime($start_and_end[1])) }} <br>
                @if ($is_current_sprint)
                    <span class="badge bg-info">今回のスプリント</span>
                @else
                    <a href=" {{ url('/task/latest_sprint') }}" class="text-center">最新スプリントへ移動</a>
                @endif
            </li>
            <li class="page-item">
                <a class="page-link" href="{{ url('task?sprint=' . $previous_next_sprint_ids[1]) }}">
                    <span aria-hidden="true">
                        &raquo;
                    </span>
                </a>
            </li>
        </ul>
        <ul>
            <div class=" mb-4">
                <a href="{{ url('/task/create?sprint=' . $sprint_id) }} " class="btn btn-primary btn-lg">新規作成</a>
            </div>
        </ul>
    </nav>
    <div class="card p-2">
        <div class="card-body">
            <table class="table table-hover table-sm text-center">
                <thead class="thead-light">
                    <tr class="table-secondary">
                        <th>状態</th>
                        <th>メンバー</th>
                        <th>残りタスク工数</th>
                        <th>残り空き時間</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <span
                                class="badge {{ $team_total_free_time >= $team_total_task_time ? 'bg-success' : 'bg-warning' }}">
                                {{ $team_total_free_time >= $team_total_task_time ? '順調' : '遅延' }}
                            </span>
                        </td>
                        <td>全体</td>
                        <td>
                            {{ $team_total_task_time }}
                        </td>
                        <td>
                            {{ $team_total_free_time }}
                        </td>
                    </tr>
                    @foreach ($users as $user)
                        <tr>
                            <td>
                                <span
                                    class="badge {{ $user_total_free_times[$user->id] >= $user_total_task_times[$user->id] ? 'bg-success' : 'bg-warning' }}">
                                    {{ $user_total_free_times[$user->id] >= $user_total_task_times[$user->id] ? '順調' : '遅延' }}
                                </span>
                            </td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user_total_task_times[$user->id] }}</td>
                            <td>{{ $user_total_free_times[$user->id] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="form-group">
        <label>担当者フィルター：</label>
        <div class="form-check form-check-inline">
            <input class="form-check-input user-checkbox" type="checkbox" value="all" id="user_all">
            <label class="form-check-label" for="user_all">全て</label>
        </div>
        @foreach ($users as $user)
            <div class="form-check form-check-inline">
                <input class="form-check-input user-checkbox" type="checkbox" value="{{ $user->id }}"
                    id="user_{{ $user->id }}">
                <label class="form-check-label" for="user_{{ $user->id }}">{{ $user->name }}</label>
            </div>
        @endforeach
    </div>

    <div class="card p-2 mt-3">
        <table class="table table-hover table-sm text-center" id="sort-table">
            <thead class="thead-light">
                <tr class="table-secondary">
                    <th>状況</th>
                    <th>最終更新</th>
                    <th>分類</th>
                    <th>タスク内容</th>
                    <th>担当者</th>
                    <th>時間内訳</th>
                    <th>
                        <span class="badge bg-primary">合計 {{ $tasks->sum('estimated_sum_time') }} h</span>
                        <br>想定(h)
                    </th>
                    <th>
                        <span class="badge bg-primary">合計 {{ $tasks->sum('actual_time') }} h</span>
                        <br>実績(h)
                    </th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($tasks as $task)
                    <tr data-user-ids="{{ implode(',', $task->members_ids) }}">
                        <td><span
                                class="badge
                                    @if ($task->status_num == 3) bg-secondary
                                    @elseif ($task->status_num == 2) bg-warning
                                    @else bg-success @endif">
                                {{ $task->status_label }} </span>
                        </td>
                        <td>
                            @isset($task->updated_at)
                                {{ date('Y-n-j', strtotime($task->updated_at)) }}<br>
                                {{ date('G:i:s', strtotime($task->updated_at)) }}
                            @endisset
                        </td>
                        <td>
                            {{ $task->category_name }}
                        </td>
                        <td>
                            @if (isset($task->trello_url))
                                <a href="{{ $task->trello_url }}" target="_blank">{{ $task->title }}</a>
                            @else
                                {{ $task->title }}
                            @endif
                        <td>
                            @foreach ($task->members as $member)
                                {{ $member . ' ' }}
                            @endforeach
                        </td>
                        <td>
                            @if (count($task->members) != 0)
                                {{ $task->estimated_time . 'h × ' . count($task->members) }}
                            @endif
                        </td>
                        <td>
                            {{ $task->estimated_sum_time ?? '-' }}
                        </td>
                        <td>
                            {{ $task->actual_time ?? '-' }}
                        </td>
                        <td class="d-flex align-items-center justify-content-center ">
                            <a href="{{ route('task.edit', ['task_id' => $task->id]) }}"
                                class="btn btn-outline-dark btn-sm">編集</a>
                            <form action='task/destroy/{{ $task->id }}' method='post' class="mb-0">
                                @csrf
                                <input type="submit" value='削除' class="btn btn-outline-danger btn-sm"
                                    onclick='return confirm("削除しますか？");'>
                            </form>
                            <button id="task-id-{{ $task->id }}" data-task-title="{{ $task->title }}" type="button"
                                class="btn btn-outline-info btn-sm" data-toggle="modal" data-target="#task-copy">複製</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection

@push('scripts')
    <script>
        // チェックボックスが変更されたときに実行される処理
        var checkboxes = document.querySelectorAll('.user-checkbox');

        // 「全て」チェックボックス
        var selectAllCheckbox = document.querySelector('#user_all');

        selectAllCheckbox.addEventListener('change', function() {
            checkboxes.forEach(function(cb) {
                cb.checked = selectAllCheckbox.checked;
            });

            updateTableVisibility();
        });

        checkboxes.forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                updateTableVisibility();
            });
        });

        function updateTableVisibility() {
            var selectedUserIds = [];
            checkboxes.forEach(function(cb) {
                if (cb.checked && cb.value !== 'all') {
                    selectedUserIds.push(cb.value);
                }
            });

            checkboxes.forEach(function(cb) {
                if (cb.value === 'all') {
                    cb.checked = selectedUserIds.length === checkboxes.length - 1;
                }
            });

            var tableRows = document.querySelectorAll('#sort-table tbody tr');

            tableRows.forEach(function(row) {
                var userIds = row.getAttribute('data-user-ids').split(',').map(function(id) {
                    return id.trim();
                });

                // 「全て」チェックボックスがチェックされているか、選択されたユーザーIDを含む行を表示
                if (selectAllCheckbox.checked || selectedUserIds.length === 0 || selectedUserIds.every(function(
                    id) {
                        return userIds.includes(id);
                    })) {
                    row.style.display = ''; // 表示
                } else {
                    row.style.display = 'none'; // 非表示
                }
            });
        }

        // 初回ページ読み込み時にも実行
        updateTableVisibility();
    </script>
@endpush
