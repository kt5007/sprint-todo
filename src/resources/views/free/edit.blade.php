@extends('adminlte::page')
@section('title', 'Taskful')
@section('content_header')
<h1 class="text-center mb-3 mt-3">各メンバーの空き時間の編集 (h)</h1>
@stop
@section('content')

<form action="{{url('/free/update')}}" method="post">
    @csrf
    <input type="hidden" name="sprint" value="{{$sprint_id}}">
    <div class="table-wrap">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th></th>
                    @foreach ($freetime_data->keyby('registered_date')->keys() as $date)
                    <th>{{ date('m/d', strtotime($date)) }}
                        <br>{{'('.$weekday_array[$date].')'}}
                    </th>
                    @endforeach
                </tr>
            </thead>

            <tbody>
                @foreach($freetime_data->groupBy('user_id') as $user_id => $collection)
                <tr>
                    <td style=" position: sticky; left: 0;" class="bg-white">{{ $collection->first()->name }}</td>
                    @foreach ($collection->keyby('registered_date') as $date => $object)
                    <td>
                        <div class="form-group">
                            <input type="number" name={{$user_id . '_' . $date}} max=10 min=0 step=0.5
                                value={{$object->free_time}} required>
                        </div>
                        <textarea type="text" name={{$user_id . '_' . $date.'_memo'}} value={{$object->memo}}
                            class="form-control free-memo-edit" rows="2" cols="13"
                            placeholder="メモ...">{{$object->memo}}</textarea>
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

@stop
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

    textarea {
        resize: auto;
        min-height: 180px;
        min-width: 200px;
    }

    ::placeholder {
        color: #e0e0e0;
    }
</style>
