@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mt-4">{{ isset($plan) ? 'Edit Plan' : 'Create Plan' }}</h1>
    
    <form action="{{ isset($plan) ? route('admin.plans.update', $plan->id) : route('admin.plans.store') }}" method="POST">
        @csrf
        @if(isset($plan))
            @method('PUT')
        @endif
        
        <div class="mb-3">
            <label for="name" class="form-label">Plan Name</label>
            <input type="text" class="form-control" id="name" name="name" value="{{ $plan->name ?? old('name') }}" required>
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" name="description">{{ $plan->description ?? old('description') }}</textarea>
        </div>

        <div class="mb-3">
            <label for="price" class="form-label">Price</label>
            <input type="number" class="form-control" id="price" name="price" value="{{ $plan->price ?? old('price') }}" step="0.01" required>
        </div>

        <div class="mb-3">
            <label for="currency" class="form-label">Currency</label>
            <input type="text" class="form-control" id="currency" name="currency" value="{{ $plan->currency ?? old('currency') }}" maxlength="3" required>
        </div>

        <div class="mb-3">
            <label for="duration" class="form-label">Duration (Days)</label>
            <input type="number" class="form-control" id="duration" name="duration" value="{{ $plan->duration ?? old('duration') }}" required>
        </div>

        <button type="submit" class="btn btn-success">{{ isset($plan) ? 'Update' : 'Create' }} Plan</button>
    </form>
</div>
@endsection
