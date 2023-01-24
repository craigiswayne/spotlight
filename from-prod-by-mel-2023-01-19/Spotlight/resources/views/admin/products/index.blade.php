@extends('layouts.admin')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col">
                <admin-products-index :page="{{$page}}" :products="{{$products}}"></admin-products-index>
            </div>
        </div>
    </div>
@endsection
