@extends('layouts.admin')

@section('content')
    <add-edit-profile :config="{{$config}}" :user="{{$user}}" :navigations="{{$navigations}}" :setting-types="{{$settings}}"></add-edit-profile>
@endsection