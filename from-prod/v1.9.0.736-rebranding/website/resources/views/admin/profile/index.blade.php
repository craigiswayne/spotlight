@extends('layouts.admin')

@section('content')
    <view-profiles :user="{{$user}}" :profiles="{{$profiles}}"></view-profiles>
@endsection