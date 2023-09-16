<!-- resources/views/home.blade.php -->
<!-- Specify that we want to extend the index file -->
@extends('layout.master')
<!-- Set the title content to "Home" -->
@section('title', 'Home')
<!-- Set the "content" section, which will replace "@yield('content')" in the index file we're extending -->

@section('content')
    <!-- Add User Registration Button -->
    <div>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#userRegistrationModal">
            ユーザー登録
        </button>
    </div>
    <div class="card p-2">
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
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
