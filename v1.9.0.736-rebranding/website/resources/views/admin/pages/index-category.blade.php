@extends('layouts.admin')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col">
                <admin-sortable-pages securable-name="Play It Forward" title="title" category="{{$pages->first()->category}}" :pages="{{$pages}}"></admin-sortable-pages>
            </div>
        </div>
        @foreach ($pages as $page)
            <div class="row">
                <div class="col">
                    <admin-sortable-resources securable-name="Play It Forward" thumbnail-size="45%" title="{{$page->title}}" base-url="/admin/{{$page->category}}" type="{{$page->title}}" :belongs_to="{{$page->id}}" :items="{{$page->resources()}}"></admin-sortable-resources>
                </div>
            </div>
        @endforeach
    </div>
@endsection
