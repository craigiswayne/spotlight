@extends('layouts.admin')

@section('content')
    <div class="container">
        <div class="card">
            <div class="card-header card-header-primary card-header-icon">
                <div class="card-icon">
                    <i class="material-icons">public</i>
                </div>
                <h4 class="card-title">
                    Markets

                    @if (Secure::hasAdminAccess('Markets|Add'))
                    <div class="float-right">
                        <form class="form-inline" method="POST" action="/admin/markets">
                            @csrf
                            <countries-select :countries="{{$countries}}"></countries-select>
                            <button type="submit" class="btn btn-primary btn-sm ml-4">Add Market</button>
                        </form>
                    </div>
                    @endif
                </h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <admin-regulated-markets-index :markets="{{$markets}}"></admin-regulated-markets-index>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
