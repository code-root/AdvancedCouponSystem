@extends('admin.layouts.app')

@section('admin-content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">User Subscriptions</h4>
    </div>

<div class="card">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Plan</th>
                    <th>Status</th>
                    <th>Trial Ends</th>
                    <th>Updated</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            @foreach($subscriptions as $sub)
                <tr>
                    <td>{{ $sub->user->email ?? '-' }}</td>
                    <td>{{ $sub->plan->name ?? '-' }}</td>
                    <td><span class="badge bg-primary">{{ ucfirst($sub->status) }}</span></td>
                    <td>{{ $sub->trial_ends_at ? $sub->trial_ends_at->format('Y-m-d') : '-' }}</td>
                    <td>{{ $sub->updated_at->diffForHumans() }}</td>
                    <td class="text-end">
                        <a href="{{ route('admin.subscriptions.edit', $sub->id) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        {{ $subscriptions->links() }}
    </div>
    </div>
@endsection


