@extends('layouts.admin')
@section('content')
    <add-edit-game :game="{{$game}}" :next-game="{{$nextGame ? $nextGame : 'null'}}" :previous-game="{{$previousGame ? $previousGame : 'null'}}" :features='@json($features)' :maths='@json($maths)'></add-edit-game>
@endsection


