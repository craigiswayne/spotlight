<div class="container">
@if ($errors->{$bag ?? 'default'}->any())
    <div class="alert alert-danger w-100 alert-dismissible fade show fadeout-5">
        @foreach ($errors->{$bag ?? 'default'}->all() as $error)
            <span class="d-block">{{ $error }}</span>
        @endforeach
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

@if(session()->has('success'))
    <div class="alert alert-success w-100 alert-dismissible fade show fadeout-5">
        {{session()->get('success')}}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

@if(session()->has('error'))
    <div class="alert alert-danger w-100 alert-dismissible fade show fadeout-7">
        {{session()->get('error')}}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

@if(session()->has('warning'))
    <div class="alert alert-warning w-100 alert-dismissible fade show fadeout-7">
        {{session()->get('warning')}}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

@if(session()->has('info'))
    <div class="alert alert-primary w-100 alert-dismissible fade show fadeout-5">
        {{session()->get('info')}}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif
</div>