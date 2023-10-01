@extends('layout.master')

@section('title', 'スプリント')

@section('content')

@if(is_null($template_data->first())) ユーザーを作成してください
@else
<form action="{{url('/sprint/update_template')}}" method="post">
    @csrf
    <div class="table-wrap">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th></th>
                    @foreach ($template_data->keyby('weekday_number')->keys() as $weekday)
                    <th>{{'('.$weekday_array[$weekday].')'}} </th>
                    @endforeach
                </tr>
            </thead>

            <tbody>
                @foreach($template_data->groupBy('user_id') as $user_id => $collection)
                <tr>
                    <td>{{ $collection->first()->name }}</td>
                    @foreach ($collection->keyby('weekday_number') as $weekday_number => $object)
                    <td>
                        <div class="form-group">
                            <input type="number" name={{$user_id . '_' . $weekday_number}} max=10 min=0 step=0.5
                                value={{$object->free_time}} required>
                        </div>
                        <div class="form-group">
                            <textarea type="text" name={{$user_id . '_' . $weekday_number.'_memo'}}
                                class="form-control free-memo-edit" rows="2" cols="9"
                                placeholder="メモ...">{{$object->memo}}</textarea>
                        </div>
                    </td>
                    @endforeach
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="btn-store">
        <button type="submit" class="btn btn-primary btn-lg">保存する</button>
    </div>
</form>

@endif

@endsection
@section('css')
<link rel="stylesheet" href="/css/admin_custom.css">
@stop

<style>
    .table-wrap {
        overflow-x: scroll;
    }

    .table {
        width: 100%;
        border-collapse: collapse;
        white-space: nowrap;
    }

    .table tr:hover td {
        background-color: #87cefa;
    }

    table td nth-child(n+2) {
        width: 150px;
    }

    /* tesxtarea の幅を無理やり揃えるため、 table td より大きい width を設定している*/
    /* boostrap のレスポンシブ表 との css依存関係により、tesxtarea のサイズ制御難しい */
    textarea {
        resize: vertical;
        min-height: 180px;
        min-width: 200px;
    }

    ::placeholder {
        color: #e0e0e0;
    }
</style>