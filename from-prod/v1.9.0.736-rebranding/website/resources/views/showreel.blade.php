@extends('layouts.app')
@section('content')
    <showreel :videos="{{$videos}}"></showreel>
@endsection
