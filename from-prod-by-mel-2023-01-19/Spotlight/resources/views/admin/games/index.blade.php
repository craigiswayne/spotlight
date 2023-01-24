@extends('layouts.admin')

@section('content')
    <admin-games-list securable-name="Games"

                      :new-featured-games="{{$games->newFeaturedGames}}"
                      :new-non-featured-games="{{$games->newNonFeaturedGames}}"
                      :not-new-featured-games="{{$games->notNewFeaturedGames}}"
                      :not-new-non-featured-games="{{$games->notNewNonFeaturedGames}}">
    </admin-games-list>
@endsection
