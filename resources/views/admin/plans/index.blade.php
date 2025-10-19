@extends('admin.layouts.app')

@section('admin-content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Plans</h4>
    <a href="{{ route('admin.plans.create') }}" class="btn btn-primary btn-sm">Add Plan</a>
    </div>

<div class="card">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Price</th>
                    <th>Trial</th>
                    <th>Max Networks</th>
                    <th>Window</th>
                    <th>Active</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            @foreach($plans as $plan)
                <tr>
                    <td>{{ $plan->name }}</td>
                    <td>{{ $plan->price }} {{ $plan->currency }}</td>
                    <td>{{ $plan->trial_days }} d</td>
                    <td>{{ $plan->max_networks }}</td>
                    <td>{{ $plan->sync_window_size }} {{ $plan->sync_window_unit }}</td>
                    <td>{!! $plan->is_active ? '<span class="badge bg-success">On</span>' : '<span class="badge bg-secondary">Off</span>' !!}</td>
                    <td class="text-end">
                        <a href="{{ route('admin.plans.edit', $plan) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                        <form action="{{ route('admin.plans.destroy', $plan) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete plan?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        {{ $plans->links() }}
    </div>
    </div>
@endsection




