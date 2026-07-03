@extends('layouts.app')

@section('title', 'My Leaves')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h2>📅 My Leaves</h2>
            <a href="{{ route('leaves.create') }}" class="btn btn-primary">+ Apply for Leave</a>
        </div>
    </div>
</div>

@if($leaves->isEmpty())
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bi bi-calendar-check" style="font-size: 3rem; color: #ccc;"></i>
            <p class="mt-3 text-muted">No leave applications yet. <a href="{{ route('leaves.create') }}">Apply for leave</a></p>
        </div>
    </div>
@else
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Duration</th>
                        <th>Days</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th>Applied On</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($leaves as $leave)
                        @php
                            $days = $leave->end_date->diffInDays($leave->start_date) + 1;
                            $statusColor = match($leave->status) {
                                'approved' => 'success',
                                'rejected' => 'danger',
                                'pending' => 'warning',
                                default => 'secondary'
                            };
                        @endphp
                        <tr>
                            <td>
                                <div>
                                    <strong>{{ $leave->start_date->format('d M Y') }}</strong><br>
                                    <i class="bi bi-arrow-right"></i><br>
                                    <strong>{{ $leave->end_date->format('d M Y') }}</strong>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-info">{{ $days }} day{{ $days > 1 ? 's' : '' }}</span>
                            </td>
                            <td>
                                {{ $leave->reason ? Str::limit($leave->reason, 40) : '—' }}
                            </td>
                            <td>
                                @if($leave->status === 'approved')
                                    <span class="badge bg-success">✓ Approved</span>
                                @elseif($leave->status === 'rejected')
                                    <span class="badge bg-danger">✗ Rejected</span>
                                @else
                                    <span class="badge bg-warning">⏳ Pending</span>
                                @endif
                            </td>
                            <td>
                                <small class="text-muted">{{ $leave->created_at->format('d M Y H:i') }}</small>
                            </td>
                            <td>
                                <div class="d-flex gap-2 flex-wrap">
                                    <a href="{{ route('leaves.show', $leave) }}" class="btn btn-sm btn-outline-primary" title="View">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                    @if($leave->status === 'pending')
                                        <a href="{{ route('leaves.edit', $leave) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                                            <i class="bi bi-pencil"></i> Edit
                                        </a>
                                        <form method="POST" action="{{ route('leaves.destroy', $leave) }}" style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"
                                                    onclick="return confirm('Are you sure?')">
                                                <i class="bi bi-trash"></i> Delete
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-center mt-4">
        {{ $leaves->links() }}
    </div>

    <!-- Summary Card -->
    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card text-center bg-light">
                <div class="card-body">
                    <h6 class="card-title">Approved Leaves</h6>
                    @php
                        $approvedDays = $leaves->where('status', 'approved')->sum(function($leave) {
                            return $leave->end_date->diffInDays($leave->start_date) + 1;
                        });
                    @endphp
                    <p class="card-text" style="font-size: 1.5rem; font-weight: 600; color: #28a745;">
                        {{ $approvedDays }}
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center bg-light">
                <div class="card-body">
                    <h6 class="card-title">Pending Leaves</h6>
                    @php
                        $pendingCount = $leaves->where('status', 'pending')->count();
                    @endphp
                    <p class="card-text" style="font-size: 1.5rem; font-weight: 600; color: #ffc107;">
                        {{ $pendingCount }}
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center bg-light">
                <div class="card-body">
                    <h6 class="card-title">Rejected Leaves</h6>
                    @php
                        $rejectedCount = $leaves->where('status', 'rejected')->count();
                    @endphp
                    <p class="card-text" style="font-size: 1.5rem; font-weight: 600; color: #dc3545;">
                        {{ $rejectedCount }}
                    </p>
                </div>
            </div>
        </div>
    </div>
@endif
@endsection
