@extends('layouts.admin')
@section('content')
    <add-edit-game :features='@json($features)'></add-edit-game>
@endsection


