@extends('layouts.app')
@section('content')
    <all-games :studios="{{$studios}}" :games="{{$games}}"></all-games>
@endsection