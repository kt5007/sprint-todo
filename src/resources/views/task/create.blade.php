@extends('adminlte::page')
@section('title', 'Taskful')
@section('content_header')
    <h1 class="text-center mt-3">タスクを新規作成</h1>
@stop

@section('content') <div class="container">
        <div class="border p-4">
            <form method="POST" action="{{ url('/task/store') }}">
                @csrf
                <input type="hidden" name="sprint" value="{{ $sprint_id }}'">

                <fieldset class="mb-4">
                    <div class="form-group">
                        <label for="subject">
                            <h2 class="h4">タスク名</h2>
                        </label>
                        <input id="title" name="title" class="form-control" value="" type="text" required>
                    </div>
                    <div class="form-group">
                        <label for="subject">
                            <h2 class="h4">trelloのurl</h2>
                        </label>
                        <input id="trello_url" name="trello_url" class="form-control" value="" type="url">
                    </div>
                    <div class="form-group">
                        <label class="control-label">
                            <h2 class="h4">タスクの分類</h2>
                        </label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="category_name" id="category_name1"
                                value="1_プロト">
                            <label class="form-check-label" for="radio1a">1_プロト</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="category_name" id="category_name2"
                                value="2_htmlプロト">
                            <label class="form-check-label" for="radio1b">2_htmlプロト</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="category_name" id="category_name3"
                                value="3_実装" checked>
                            <label class="form-check-label" for="radio1c">3_実装</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="category_name"
                                value="設計">
                            <label class="form-check-label">設計</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="category_name"
                                value="課題の検討">
                            <label class="form-check-label">課題の検討</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="category_name" id="category_name4"
                                value="4_その他">
                            <label class="form-check-label" for="radio1c">4_その他</label>
                        </div>
                    </div>
                    <div class="form-group" id="members">
                        <label class="control-label">
                            <h2 class="h4">アサイン可能メンバー </h2>
                            [アサイン済時間 / 全空き時間]
                        </label>
                        @foreach (array_keys($users_total_free_time) as $user_id)
                            @isset($users->firstwhere('id', '=', $user_id)->name)
                                <div class="custom-control custom-checkbox" id="user_{{ $user_id }}">
                                    <input type="checkbox" class="custom-control-input" id="{{ 'user_check_' . $user_id }}"
                                        name="{{ 'user_check_' . $user_id }}">
                                    <label class="custom-control-label"
                                        for="{{ 'user_check_' . $user_id }}">{{ $users->firstwhere('id', '=', $user_id)->name . ' [' . $assigned_time_array[$user_id] . 'h / ' . $users_total_free_time[$user_id] . 'h]' }}</label>
                                </div>
                            @endisset
                        @endforeach
                    </div>
                    <div class="form-group">
                        <label for="subject">
                            <h2 class="h4">一人あたり工数（時間）</h2>
                        </label>
                        <input type="number" step="0.5" min="0" max="200" class="form-control" id="estimated_time"
                            name="estimated_time" value="">
                    </div>

                    <div class="form-group">
                        <label for="message">
                            <h2 class="h4">メモ</h2>
                        </label>
                        <textarea id="task_memo" name="task_memo" class="form-control" rows="5"></textarea>
                    </div>

                    <div class="form-group mt-5">
                        <label for="message">
                            <h2 class="h4">実績工数（h)</h2>
                            ※全メンバーの工数合計を入力してください
                        </label>
                        <input type="number" step="0.5" min="0" max="400" class="form-control" id="actual_time"
                            name="actual_time" value="">
                    </div>
                    <div class="form-group ml-4">
                        <input class="form-check-input" type="checkbox" id="task_status" name="task_status"
                            style="transform: scale(1.5);">
                        <label class="form-check-label ml-1" for="task_status">タスクを完了とする</label>
                    </div>

                    <div class="mt-5 text-center">
                        <button type="submit" class="btn btn-primary btn-lg">
                            保存する
                        </button>
                        <a class="btn btn-secondary btn-lg" href="{{ url('/task?sprint=' . $sprint_id) }}">
                            キャンセル
                        </a>
                    </div>
                </fieldset>
            </form>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop
@section('js')
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

    const checkBoxList = document.getElementById("members");
    window.onload = function() {

        const cookies = getCookieArray();
        const user_ids = cookies['user_ids'].split(',');
        let newFreeTimeTableTrs = [];
        user_ids.forEach(user_id => {
            if(document.getElementById(user_id)!= null){
                newFreeTimeTableTrs.push(document.getElementById(user_id));
                console.log(document.getElementById(user_id));
                checkBoxList.removeChild(document.getElementById(user_id));
            }
        });
        console.log(newFreeTimeTableTrs);

        newFreeTimeTableTrs.forEach(tr => {
            checkBoxList.appendChild(tr);
        });
    }
</script>
@endsection
