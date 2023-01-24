@extends('layouts.app')
@section('content')
    <products-page :products="{{$products}}" :page="{{$page}}">
@endsection