@extends('referrer::layouts.master')

@section('content')
    <h1>Hello World</h1>

    <p>Module: {!! config('referrer.name') !!}</p>
@endsection
