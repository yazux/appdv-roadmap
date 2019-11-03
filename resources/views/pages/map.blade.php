@extends('index')

@section('content')
    @include('common.header')
    <div class="content page page-project">
        <h1 class="page-title">
            <span class="page-title__value">Карта дорог</span>
        </h1>
        <roads-map-new id="roads-map-new"></roads-map-new>
    </div>
    @include('common.footer')
@endsection