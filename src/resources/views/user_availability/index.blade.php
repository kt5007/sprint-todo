@extends('layout.master')

@section('title', '空き時間')
<style>
    .table th:first-child {
        position: sticky;
        left: 0;
        background-color: white;
    }
    .table tbody td:first-child {
        position: sticky;
        left: 0;
        background-color: white;
    }
</style>
@section('content')
    <div class="card p-2">
        <div class="card-body">
            <div class="mb-3">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#userRegistrationModal">
                    更新
                </button>
            </div>
            <form action="保存するためのエンドポイントを指定" method="POST">
                @csrf <!-- LaravelのCSRFトークンを含める必要があります -->

                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th></th>
                                <!-- 1週間分の日付 -->
                                @for ($i = 0; $i < 14; $i++)
                                    <th>{{ date('m/d', strtotime('+' . $i . ' day')) }}</th>
                                @endfor
                            </tr>
                        </thead>
                        <tbody>
                            <!-- ユーザーごとの行 -->
                            @for ($i = 0; $i < 7; $i++)
                                <tr>
                                    <td>{{ 'kento' }}</td>
                                    <!-- 1週間分の時間帯 -->
                                    @for ($i = 0; $i < 14; $i++)
                                        <td>
                                            <input type="text" class="form-control"
                                                name="availability[{{ 1 }}][{{ date('Y-m-d', strtotime('+' . $i . ' day')) }}]"
                                                placeholder="空き時間" style="width: 100px; font-size: 13px;">
                                            <br>
                                            <textarea class="form-control" name="comment[{{ 1 }}][{{ date('Y-m-d', strtotime('+' . $i . ' day')) }}]" rows="8"
                                                style="width: 200px; font-size: 13px;" placeholder="コメント"></textarea>
                                        </td>
                                    @endfor
                                </tr>
                            @endfor
                        </tbody>
                    </table>
                </div>
            </form>

        </div>
    </div>
    @include('user.user_registration_modal')
@endsection
