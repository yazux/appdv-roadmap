@extends('index')

@section('content')
    @include('common.header')
    <div class="content page page-project">
        <h1 class="page-title">
            <span class="page-title__value">Такой страницы не существует</span>
        </h1>

        <p>Запрашиваемой страницы не существует, пожалуйста проверьте корректность указанного адреса</p>
        
    </div>
    @include('common.footer')
@endsection