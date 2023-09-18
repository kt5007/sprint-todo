@extends('layout.master')

@section('title', 'スプリント')

@section('content')
    <div class="card p-2">
        <div class="card-body">
            <div class="mb-3">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#sprintRegistrationModal">
                    スプリント新規作成
                </button>
            </div>
            <table class="table table-hover table-sm">
                <thead class="thead-light">
                    <tr class="table-secondary">
                        <th scope="col">ID</th>
                        <th scope="col">期間</th>
                        <th scope="col">詳細</th>
                        <th scope="col">メモ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sprints as $sprint)
                        <tr>
                            <td>{{ $sprint->id }}</td>
                            <td>{{ date('y/m/d', strtotime($sprint->start_sprint_date)) }}&nbsp;~&nbsp;{{ date('y/m/d', strtotime($sprint->end_sprint_date)) }}
                            </td>
                            <td>
                                <a href="{{ url('/task?sprint=' . $sprint->id) }}">タスク</a>&nbsp;&nbsp;&nbsp;&nbsp;
                                <a href="{{ url('/free?sprint=' . $sprint->id) }}">空き時間 </a>
                            </td>
                            <td>{{ $sprint->sprint_memo }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @include('sprint.sprint_registration_modal')
@endsection
