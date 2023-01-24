@extends('layouts.admin')

@section('content')
      
    <div class="container">
        <div class="mt-3 pt-3 position-relative">
            <admin-sortable-resources
                href="false"
                securable-name="Pages"                
                thumbnail-size="250px"
                view="minimal"
                title="Pages"                 
                belongs_to="0" 
                :items="{{$navigations}}"
                asset-property="thumbnail"                
                type="pages"
                :can-add="false"
                :can-delete="false"
                :dark="true"
            ></admin-sortable-resources>
        </div>
    </div>

@endsection
