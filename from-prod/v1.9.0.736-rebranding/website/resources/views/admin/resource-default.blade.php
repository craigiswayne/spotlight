@extends('layouts.admin')

@section('content')
    <div class="container">
        <div class="mt-3 pt-3 position-relative">
            <admin-sortable-resources
                securable-name="{{$securable}}"
                href="{{$type == 'navigation-cards' ? 'true':'false'}}"
                thumbnail-size="{{$thumbnail_size}}"
                view="minimal"
                title="{{str_replace('-',' ', $type)}}" 
                type="{{$type}}"
                belongs_to="0" 
                :items="{{$resources}}"
            ></admin-sortable-resources>
        </div>
    </div>
@endsection
