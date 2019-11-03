@extends('index')

@section('content')
    @include('common.header')
    <div class="content page page-vendor">
        @if($vendor)
            <h1 class="page-title">
                @if($vendor->logo && false)
                    <div class="page-title__img-container">
                        <img src="/storage/{{$vendor->logo}}" alt="{{$vendor->name}}" class="page-title__img">
                    </div>
                @endif
                <span class="page-title__value">{{$vendor->name}}</span>
            </h1>
            @if($vendor->projects_count && $vendor->projecs_in_works_count)
                <work-progress
                    id="work-progress"
                    :all="{{$vendor->projects_count}}" 
                    :complete="{{($vendor->projects_count - $vendor->projecs_in_works_count)}}" 
                    :work="{{$vendor->projecs_in_works_count}}"
                    :price="{{$vendor->full_price}}"
                ></work-progress>
            @endif
            @if($vendor->description)
                <p class="page-vendor__description page-description">{{$vendor->description}}</p>
            @endif
            <vendor-projects id="vendor-projects" :vendor-id="{{$vendor->id}}" :projects="{{ (($vendor->projects) ? json_encode($vendor->projects->toArray()) : json_encode([]) )  }}"></vendor-projects>
        @endif
    </div>
    @include('common.footer')
@endsection