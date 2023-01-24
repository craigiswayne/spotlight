@extends('layouts.app')
@section('content')
    <third-party-providers :providers="{{$providers}}"></third-party-providers>
@endsection