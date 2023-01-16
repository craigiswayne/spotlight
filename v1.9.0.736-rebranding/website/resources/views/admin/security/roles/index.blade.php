@extends('layouts.admin')

@section('content')
    <manage-roles :roles="{{$roles}}"></manage-roles>
@endsection
