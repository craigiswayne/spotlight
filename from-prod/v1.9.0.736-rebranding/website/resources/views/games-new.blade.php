@extends('layouts.app')
@section('content')
    <new-games :new-featured-games="{{$newFeaturedGames}}" :new-non-featured-games="{{$newNonFeaturedGames}}" :featured-games="{{$featuredGames}}"></new-games>
@endsection