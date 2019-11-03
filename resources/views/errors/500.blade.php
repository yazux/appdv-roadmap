@extends('index')

@section('content')
    @include('common.header')
    <div class="content page page-project">
        <h1 class="page-title">
            <span class="page-title__value">Ошибка на стороне сервере</span>
        </h1>

        <p>Произошла непредвиденная ошибка, мы уже работаем над этим, пожалуйста зайдите позже.</p>
        
    </div>
    @include('common.footer')
@endsection