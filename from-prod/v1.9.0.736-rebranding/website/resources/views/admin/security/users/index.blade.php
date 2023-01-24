@extends('layouts.admin')

@section('content')
    <manage-users :current-user="{{$currentUser}}" :users="{{$users}}"></manage-users>
@endsection
