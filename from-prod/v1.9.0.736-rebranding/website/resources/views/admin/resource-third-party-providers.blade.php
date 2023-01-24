@extends('layouts.admin')

@section('content')
    <div class="container">
        @if ($providers->count() == 0)
        <div class="mt-3 pt-3 position-relative">
            <admin-sortable-resources securable-name="Third Party Providers" view="minimal" enable-category="true" title="Third Party Providers" type="third-party-providers" belongs_to="{{now()->format('Y')}}" :items="[]"></admin-sortable-resources>
        </div>
        @endif
        @foreach ($providers as $year => $items)
            <admin-sortable-resources securable-name="Third Party Providers" enable-category="true" title="{{$year.' Third Party Providers'}}" type="third-party-providers" belongs_to="{{$year}}" :items="{{$providers[$year]}}"></admin-sortable-resources>
        @endforeach
    </div>
@endsection
