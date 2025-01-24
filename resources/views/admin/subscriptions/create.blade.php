@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mt-4">{{ isset($subscription) ? 'Edit Subscription' : 'Create Subscription' }}</h1>
    
    <form action="{{ isset($subscription) ? route('admin.subscriptions.update', $subscription->id) : route('admin.subscriptions.store') }}" method="POST">
        @csrf
        @if(isset($subscription))
            @method('PUT')
        @endif

        <div class="mb-3">
            <label for="plan_id" class="form-label">Plan</label>
            <select class="form-select" id="plan_id" name="plan_id" required>
                @foreach($plans as $plan)
                    <option value="{{ $plan->id }}" {{ isset($subscription) && $subscription->plan_id == $plan->id ? 'selected' : '' }}>{{ $plan->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="model_id" class="form-label">Model ID</label>
            <input type="number" class="form-control" id="model_id" name="model_id" value="{{ $subscription->model_id ?? old('model_id') }}" required>
        </div>

        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="is_paid" name="is_paid" {{ isset($subscription) && $subscription->is_paid ? 'checked' : '' }}>
            <label class="form-check-label" for="is_paid">Paid</label>
        </div>

        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="is_recurring" name="is_recurring" {{ isset($subscription) && $subscription->is_recurring ? 'checked' : '' }}>
            <label class="form-check-label" for="is_recurring">Recurring</label>
        </div>

        <div class="mb-3">
            <label for="starts_on" class="form-label">Starts On</label>
            <input type="date" class="form-control" id="starts_on" name="starts_on" value="{{ $subscription->starts_on ?? old('starts_on') }}">
        </div>

        <div class="mb-3">
            <label for="expires_on" class="form-label">Expires On</label>
            <input type="date" class="form-control" id="expires_on" name="expires_on" value="{{ $subscription->expires_on ?? old('expires_on') }}">
        </div>

        <button type="submit" class="btn btn-success">{{ isset($subscription) ? 'Update' : 'Create' }} Subscription</button>
    </form>
</div>
@endsection
