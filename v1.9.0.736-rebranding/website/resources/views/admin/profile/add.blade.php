@extends('layouts.admin')

@section('content')
    <add-edit-profile :user="{{$user}}" :navigations="{{$navigations}}" :setting-types="{{$settings}}"></add-edit-profile>
@endsection