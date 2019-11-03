@extends('index')

@section('content')
    @include('common.header')
    <div class="content page page-project">
        @if($page)
            <h1 class="page-title">
                <span class="page-title__value">{{$page->title}}</span>
            </h1>
            <div>{{$page->text}}</div>
        @endif
    </div>
    @include('common.footer')
@endsection