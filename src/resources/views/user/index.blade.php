@extends('layout.master')

@section('title', 'Home')

@section('content')
    <div class="card p-2">
        <div class="card-body">
            <div class="mb-3">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#userRegistrationModal">
                    ユーザー登録
                </button>
            </div>
            <table class="table table-hover table-sm">
                <thead class="thead-light">
                    <tr class="table-secondary">
                        <th scope="col">ID</th>
                        <th scope="col">登録名</th>
                        <th scope="col">操作</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($activeUsers as $activeUser)
                        <tr>
                            <th scope="row">{{ $activeUser->id }}</th>
                            <td>{{ $activeUser->name }}</td>
                            <td>Otto</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @include('user.user_registration_modal')
@endsection
