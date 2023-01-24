@extends('layouts.app')
@section('content')
    <regulated-markets countries="{{$countries}}" market-details="{{$details}}"></regulated-markets>
@endsection