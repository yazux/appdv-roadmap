@extends('index')

@section('content')
    @include('common.header')
    <div class="content page page-project">
        <h1 class="page-title" style="display: none;">
            <span class="page-title__value">Объекты</span>
        </h1>
        <all-projects id="all-projects" :vendors="{{ ((isset($vendors) && $vendors) ? json_encode($vendors->toArray()) : json_encode([]) )  }}"></all-projects>
    </div>
    @include('common.footer')
@endsection