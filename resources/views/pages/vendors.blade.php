@extends('index')

@section('content')
    @include('common.header')
    <div class="content page">
        <vendors id="vendors" :show-all-btn="false" :show-title="true"></vendors>
    </div>
    @include('common.footer')
@endsection