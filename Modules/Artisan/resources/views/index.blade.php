@extends('artisan::layouts.master')

@section('content')
    <h1>Hello World</h1>

    <p>Module: {!! config('artisan.name') !!}</p>
@endsection
