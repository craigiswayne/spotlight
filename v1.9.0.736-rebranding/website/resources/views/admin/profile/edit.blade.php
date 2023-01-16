@extends('layouts.admin')

@section('content')
    <add-edit-profile :id="'{{$id}}'" :user="{{$user}}" :profile="{{$profile}}" :config="{{$config}}" :navigations="{{$navigations}}" :setting-types="{{$settings}}"></add-edit-profile>
@endsection