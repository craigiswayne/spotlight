@extends('layouts.admin')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col">
                @foreach ($categories as $category)
                    <div class="card-deck">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title text-capitalize">{{str_replace('-', ' ', $category)}}</h5>
                                <p class="card-text"><a class="text-capitalize" href="/admin/pages/{{$category}}">View {{str_replace('-', ' ', $category)}} Pages</a></p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection
