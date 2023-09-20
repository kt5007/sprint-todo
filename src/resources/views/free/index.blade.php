@extends('layout.master')

@section('title', 'Home')
@section('guide', '各メンバーの空き時間一覧 (h)')

@if (is_null($freetime_data->first())) ユーザーを作成してください
@endif

@section('content')
<div class="mt-4 mb-3">
    <div class="btn-splint">
        <div class="inner">
            <a href=" {{ url('free?sprint=' . $previous_next_sprint_ids[0]) }}" class="arrow_l"></a>
            <div class="sprint-date">
                {{ date('y/m/d', strtotime($start_and_end[0])) . ' ~ ' . date('y/m/d', strtotime($start_and_end[1])) }}
                <br>
                @if ($is_current_sprint)
                <div class="inner">
                    <span class="badge badge-pill badge-primary">今回のスプリント</span>
                </div>
                @endif
            </div>
            <a href=" {{ url('free?sprint=' . $previous_next_sprint_ids[1]) }}" class="arrow_r"></a>
        </div>
    </div>
</div>

<div class="table-wrap">
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th></th>
                @foreach ($freetime_data->keyby('registered_date')->keys() as $date)
                <th class="th-date">{{ date('m/d', strtotime($date)) }}
                    <br>{{ '(' . $weekday_array[$date] . ')' }}
                </th>
                @endforeach
                <th>【合計】</th>
            </tr>
        </thead>

        <tbody>
            @foreach ($freetime_data->groupBy('user_id') as $user_id => $collection)
            <tr>
                <td style=" position: sticky; left: 0;"  class="bg-white">{{ $collection->first()->name }}</td>
                @foreach ($collection->keyby('registered_date') as $date => $object)
                <td>{{ $object->free_time }}
                    <br>
                    <div class="free-memo">{!! nl2br(e($object->memo)) !!}
                    </div>
                </td>
                @endforeach
                <td>{{ $collection->sum('free_time') }}</td>
            </tr>
            @endforeach
            <tr class="table-success">
                <td style=" position: sticky; left: 0;">【合計】</td>
                @foreach ($freetime_data->groupby('registered_date') as $date => $collection)
                <td>{{ $collection->sum('free_time') }}</td>
                @endforeach
                <td>{{ $freetime_data->sum('free_time') }}</td>
            </tr>
        </tbody>
    </table>
</div>

<div class="btn-edit">
    <a href="{{ url('free/edit?sprint=' . $sprint_id) }}" class="btn btn-primary btn-lg">空き時間を編集する</a>
</div>

@endsection
{{-- @endif --}}

@section('css')
<link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
<script>
    console.log('Hi!');

</script>
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

    h2 {
        text-align: center;
    }

    .btn-edit,
    .sprint-date {
        text-align: center;
    }

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
        height: 40px;
        margin: 0 0 0 auto;
    }

    .inner {
        /* padding-top: 20px; */
        display: flex;
        justify-content: space-evenly;
    }

    .free-memo {
        margin-top: 0.25em;
        border-top: dashed 0.05em black;
        padding: 0.25em;
        font-size: smaller;
    }
</style>
