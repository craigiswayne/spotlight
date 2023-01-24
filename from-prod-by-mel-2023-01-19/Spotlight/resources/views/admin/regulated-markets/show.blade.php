@extends('layouts.admin')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col">
                <admin-regulated-markets-show :market="{{$regulatedMarket}}" :countries="{{$countries}}"></admin-regulated-markets-show>
            </div>
        </div>
        <div class="row mt-5">
            <div class="col text-left">
                {!! $regulatedMarket->prev() !!}
            </div>
            <div class="col text-right">
                {!! $regulatedMarket->next() !!}
            </div>
        </div>
    </div>
@endsection
