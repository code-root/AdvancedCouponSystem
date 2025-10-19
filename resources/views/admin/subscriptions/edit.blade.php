@extends('admin.layouts.app')

@section('admin-content')
<h4 class="mb-3">Edit Subscription</h4>
<div class="card card-body">
    <form method="POST" action="{{ route('admin.subscriptions.update', $subscription->id) }}">
        @csrf
        @method('PUT')
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">User</label>
                <input type="text" class="form-control" value="{{ $subscription->user->email }}" disabled>
            </div>
            <div class="col-md-4">
                <label class="form-label">Plan</label>
                <select name="plan_id" class="form-select">
                    @foreach($plans as $plan)
                        <option value="{{ $plan->id }}" @selected($plan->id===$subscription->plan_id)>{{ $plan->name }} ({{ $plan->price }} {{ $plan->currency }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    @foreach(['active','trialing','canceled','past_due','expired'] as $status)
                        <option value="{{ $status }}" @selected($status===$subscription->status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="mt-3">
            <button class="btn btn-primary">Save</button>
            <a href="{{ route('admin.subscriptions.index') }}" class="btn btn-light">Cancel</a>
        </div>
    </form>
</div>
@endsection


