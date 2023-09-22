@extends('layout.master')

@section('title', 'Home')
@section('guide', 'チームのタスク')

<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

{{-- jquery --}}
<script src="https://code.jquery.com/jquery-3.3.1.js"></script>
<script src="{{ asset('js/excel-bootstrap-table-filter-bundle.js') }}"></script>
<link rel="stylesheet" href="{{ asset('css/excel-bootstrap-table-filter-style.css') }}">


    @section('content')
    @include('task.copy_task_to_latest_sprint')
    <div class="page-header">
        <div class="btn-splint">
            <div class="inner">
                <a href=" {{ url('task?sprint=' . $previous_next_sprint_ids[0]) }}" class="arrow_l"></a>
                <div class="sprint-date">
                    {{ date('y/m/d', strtotime($start_and_end[0])) . ' ~ ' . date('y/m/d', strtotime($start_and_end[1])) }}
                    <br>
                    @if ($is_current_sprint)
                    <div class="inner">
                        <span class="badge badge-pill badge-primary">今回のスプリント</span>
                    </div>
                    @endif
                    <a href=" {{ url('/task/latest_sprint') }}" class="text-center">最新スプリントへ移動</a>
                </div>
                <a href=" {{ url('task?sprint=' . $previous_next_sprint_ids[1]) }}" class="arrow_r"></a>
            </div>
        </div>
    </div>
    <div class="mt-4 mb-3">
        <ul>
            <div  class=" mb-4">
                <a href="{{ url('/task/create?sprint=' . $sprint_id) }} " class="btn btn-primary btn-lg">新規作成</a>
            </div>
        </ul>
    </div>

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
                        <td>{{ $team_total_task_time }}</td>
                        <td>{{ $team_total_free_time }}</td>
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


    <table class="table table-hover mt-5" id="sort-table">
        <thead>
            <tr class="table-active nowrpap-header">
                <th class="sort-apply" style="width: 5%">状況</th>
                <th class="sort-apply" style="width: 5%">最終更新</th>
                <th class="sort-apply" style="width: 5%">分類</th>
                <th >タスク内容</th>
                <th class="sort-apply" style="width: 25%" id="person">担当者</th>
                <th  style="width: 5%">時間内訳</th>
                <th style="width: 5%">
                    <span class="badge badge-primary">合計 {{ $tasks->sum('estimated_sum_time') }} h</span>
                    <br>想定(h)
                </th>
                <th style="width: 5%">
                    <span class="badge badge-primary">合計 {{ $tasks->sum('actual_time') }} h</span>
                    <br>実績(h)
                </th>
                <th class="thead-btn" style="width: 15%">操作</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($tasks as $task)
            <tr class="">
                <td><span class="badge badge-pill @if ($task->status_num == 3) badge-secondary @elseif($task->status_num==2)badge-warning @else
                                badge-success @endif">
                        {{ $task->status_label }} </span></td>
                <td>
                    @if (isset($task->updated_at))
                        {{ date('Y/n/j', strtotime($task->updated_at)) }}<br>{{ date('G:i:s', strtotime($task->updated_at)) }}
                    @else

                    @endif
                </td>
                <td class="td-category">{{ $task->category_name }}</td>
                @if (isset($task->trello_url))
                <td><a href="{{ $task->trello_url }}" target="_blank">{{ $task->title }}</a>
                @else
                <td>{{ $task->title }}
                @endif
                    @if (isset($task->task_memo))
                    <br>
                    <div class="tab">
                        <input class="memo-accordion" id="{{'tab-'.$task->id}}" type="checkbox" name="tabs">
                        <label for="{{'tab-'.$task->id}}">メモを表示</label>
                        <div class="task-comment">{!! nl2br(e($task->task_memo)) !!}</div>
                    </div>
                    @endif
                </td>
                <td class="members">
                    @foreach ($task->members as $member){{ $member . ' ' }}
                    @endforeach
                </td>
                <td class="assignment">
                    @if(count($task->members)!=0)
                    {{$task->estimated_time.'h × '.count($task->members)}}
                    @endif
                </td>
                <td class="cell-time">
                    @if ($task->estimated_sum_time != 0)
                    {{ $task->estimated_sum_time }}@endif
                </td>
                <td class="cell-time">{{ $task->actual_time }}</td>
                <td class="d-flex align-items-center justify-content-center ">
                    <a href="{{ url('/task/edit/' . $task->id) }} " class="btn btn-outline-dark btn-sm">編集</a>
                    <form action='task/destroy/{{ $task->id }}' method='post' class="mb-0">
                        @csrf
                        <input type="submit" value='削除' class="btn btn-outline-danger btn-sm" onclick='return confirm("削除しますか？");'>
                    </form>
                    <button id="task-id-{{ $task->id }}" data-task-title="{{ $task->title }}" type="button" class="btn btn-outline-info btn-sm"  data-toggle="modal" data-target="#task-copy">複製</button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>
    @endsection

    {{-- @section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
    @stop --}}

    <style>
        .arrow_r,
        .arrow_l {
            position: relative;
            display: inline-block;
            padding-left: 12px;
            color: #333;
            text-decoration: none;
        }

        .arrow_r:before {
            content: '';
            width: 0;
            height: 0;
            border-style: solid;
            border-width: 12px 0 12px 16px;
            border-color: transparent transparent transparent #333;
            position: absolute;
            top: 50%;
            left: 0;
            margin-top: -6px;
        }

        .arrow_l:before {
            content: '';
            width: 0;
            height: 0;
            border-style: solid;
            border-width: 12px 0 12px 16px;
            border-color: transparent transparent transparent #333;
            position: absolute;
            top: 50%;
            left: 0;
            margin-top: -6px;
            transform: rotate(180deg);
        }

        .btn-splint {
            width: 300px;
            height: 20px;
            margin: 0 0 0 auto;
        }

        .inner {
            /* padding-top: 20px; */
            display: flex;
            justify-content: space-evenly;
        }

        .table tr:hover td {
            background-color: #87cefa;
        }

        td.status-total {
            border-bottom: 1px solid black;
            font-weight: bold;
        }

        tr.nowrpap-header {
            white-space: nowrap;
        }

        td{
            font-size: small;
        }
        th{
            font-size: small;
        }

        td.assignment {
            word-break: keep-all;
        }

        td.td-category {
            white-space: nowrap;
            font-size: small;
        }

        td.cell-time {
            text-align: left;
        }

        .sprint-date,
        .current-splint-badge,
        .thead-btn {
            text-align: center;
        }

        .btn-wrapp {
            display: flex;
            flex-direction: column;
            font: smaller;
            align-items: center;
            white-space: nowrap;
        }

        .tab {
            position: relative;
            width: 100%;
            overflow: hidden;
        }

        input.memo-accordion {
            position: absolute;
            opacity: 0;
            z-index: -1;
        }

        label {
            position: relative;
            display: block;
            color: #1e90ff;
            line-height: 2;
            cursor: pointer;
        }

        .task-comment {
            margin-left: 1.5em;
            font-size: smaller;
            max-height: 0;
            overflow: hidden;
            -webkit-transition: max-height .35s;
            -o-transition: max-height .35s;
            transition: max-height .35s;
        }

        .tab-content {
            max-height: 0;
            overflow: hidden;
            -webkit-transition: max-height .35s;
            -o-transition: max-height .35s;
            transition: max-height .35s;
            color: #000;
        }

        input.memo-accordion:checked~.task-comment {
            max-height: 100%;
        }

        /* Icon */
        label::after {
            position: absolute;
            left: 4.2em;
            top: -0.6em;
            display: block;
            width: 3em;
            height: 3em;
            line-height: 3;
            text-align: center;
            -webkit-transition: all .35s;
            -o-transition: all .35s;
            transition: all .35s;
        }

        input.memo-accordion[type=checkbox]+label::after {
            content: "+";
        }

        input.memo-accordion[type=radio]+label::after {
            content: "\25BC";
        }

        input.memo-accordion[type=checkbox]:checked+label::after {
            transform: rotate(315deg);
        }

        input.memo-accordion[type=radio]:checked+label::after {
            transform: rotateX(180deg);
        }
    </style>
@section('js')
<script>
    $('#sort-table').excelTableFilter({
        columnSelector: '.sort-apply',    // (optional) if present, will only select <th> with specified class
        sort: true,                         // (optional) default true
        search: true                        // (optional) default true
        // captions: Object                    // (optional) default { a_to_z: 'A to Z', z_to_a: 'Z to A', search: 'Search', select_all: 'Select All' }
    });
    // $('table').excelTableFilter();
</script>
<script src="{{ asset('js/task.js') }}"></script>
<script>
    function getCookie(name) {
    let matches = document.cookie.match(new RegExp(
        "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
    ));
    return matches ? decodeURIComponent(matches[1]) : undefined;
    }

    function getCookieArray(){
    var arr = new Array();
    if(document.cookie != ''){
        var tmp = document.cookie.split('; ');
        for(var i=0;i<tmp.length;i++){
        var data = tmp[i].split('=');
        arr[data[0]] = decodeURIComponent(data[1]);
        }
    }
    return arr;
    }

    const checkBox = document.querySelector("#person .checkbox-container");
    const checkBoxList = document.querySelectorAll("#person .checkbox-container .dropdown-filter-item");
    window.onload = function() {

        const cookies = getCookieArray();
        const freeTimeTableTr= document.querySelectorAll('#free-time-table tr');
        const user_ids = cookies['user_ids'].split(',');

        let newFreeTimeTableTrs = [];
        const node = document.getElementById("free-time-table");
        user_ids.forEach(user_id => {
            newFreeTimeTableTrs.push(document.getElementById(user_id));
            node.removeChild(document.getElementById(user_id));
        });

        newFreeTimeTableTrs.forEach(tr => {
            node.appendChild(tr);
        });

        // console.log(Object.keys(cookies));
        if(cookies["Select All"]=="true"){
            console.log('select all');
        } else {
            const filterTr = document.querySelectorAll("#sort-table tbody tr");
            for (let tr = 0; tr < filterTr.length; tr++) {
                const member = filterTr[tr].querySelector(".members").textContent.replace(/\s/g,'');
                filterTr[tr].style.display ="none";
            }
            for (let index = 0; index < checkBoxList.length; index++) {
                const element = checkBoxList[index].querySelector("input[type=checkbox]");
                element.checked = false;
            }
            for (let index = 0; index < checkBoxList.length; index++) {
                const element = checkBoxList[index].querySelector("input[type=checkbox]");
                Object.entries(cookies).forEach(([key, value]) => {
                    // console.log(key);
                    console.log(element.checked);
                    if (key == element.value) {
                        if(value == 'true'){
                            element.checked = true;
                        }
                        // console.log(element.value);
                        // console.log(value);
                        // console.log(element.checked);
                        for (let tr = 0; tr < filterTr.length; tr++) {
                            const member = filterTr[tr].querySelector(".members").textContent.replace(/\s/g,'');
                            // console.log(member);
                            if( key.replace(/\s/g,'') == member && value == 'true'){
                                // console.log(member, key.replace(/\s/g,''));
                                // console.log(element.checked);
                                filterTr[tr].style.display ="";
                            }
                        }

                        // console.log(filterTr);
                    }
                });
                // if(cookies. == element.value);
                // const value = getCookie(element.value)
                // element.value;
                // element.checked = false;
                // console.log(element);
                // display: none;
            }
        }
        // var str  = "";
        // str += "クッキーで保存されている内容 ： " + getCookie() + "<br>\n";
        // document.getElementById("dat").innerHTML = str;
    }

    checkBox.addEventListener('click', function(){
        let checkList = document.querySelectorAll("#person input[type=checkbox]");
        checkList.forEach(element => {
            // console.log(element.checked);
            document.cookie = element.value + '=' + element.checked;
        });
        // console.log('click');
        // console.log(getCookie('山崎賢人'));
    }, false);
    // console.log(document.cookie);

    const sortElement = document.getElementById('free-time-table');
    new Sortable(sortElement, {
        animation: 150,
        handle: '.handle',
        ghostClass: 'blue-background-class',
    });

    // window.onload = function(){
    //     const cookies = getCookieArray();
    //     const freeTimeTableTr= document.querySelectorAll('#free-time-table tr');
    //     const user_ids = cookies['user_ids'].split(',');

    //     let newFreeTimeTableTrs = [];
    //     const node = document.getElementById("free-time-table");
    //     user_ids.forEach(user_id => {
    //         newFreeTimeTableTrs.push(document.getElementById(user_id));
    //         node.removeChild(document.getElementById(user_id));
    //     });

    //     newFreeTimeTableTrs.forEach(tr => {
    //         node.appendChild(tr);
    //     });
    // }

    window.onunload = function(){
        const freeTimeTableTr= document.querySelectorAll('#free-time-table tr');
        let user_ids = [];
        for (let tr = 0; tr < freeTimeTableTr.length; tr++) {
                user_ids.push(freeTimeTableTr[tr].id);
        }
        document.cookie = 'user_ids=' + user_ids;
    }

</script>
@endsection
