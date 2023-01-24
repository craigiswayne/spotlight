@extends('layouts.app')
@section('content')
    {{-- <div id="particle-js" class=" position-absolute w-100 h-100vh"></div> --}}

    @if (Setting::Boolean('carousel'))
        <card-slider :navigations="{{$navigations}}"></card-slider>
    @else
        <landing-page :navigations="{{$navigations}}"></landing-page>
    @endif
@endsection
