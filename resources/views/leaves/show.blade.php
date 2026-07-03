@extends('layouts.app')

@section('title', 'View Leave')

@section('content')
<div class="row">
    <div class="col-lg-6 mx-auto">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">📋 Leave Details</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label text-muted">Status</label>
                    <p>
                        @if($leave->status === 'approved')
                            <span class="badge bg-success">✓ Approved</span>
                        @elseif($leave->status === 'rejected')
                            <span class="badge bg-danger">✗ Rejected</span>
                        @else
                            <span class="badge bg-warning">⏳ Pending</span>
                        @endif
                    </p>
                </div>

                <div class="mb-3">
                    <label class="form-label text-muted">Start Date</label>
                    <p class="h5">{{ $leave->start_date->format('d M Y (l)') }}</p>
                </div>

                <div class="mb-3">
                    <label class="form-label text-muted">End Date</label>
                    <p class="h5">{{ $leave->end_date->format('d M Y (l)') }}</p>
                </div>

                <div class="mb-3">
                    <label class="form-label text-muted">Total Days</label>
                    @php
                        $days = $leave->end_date->diffInDays($leave->start_date) + 1;
                    @endphp
                    <p class="h5">{{ $days }} day{{ $days > 1 ? 's' : '' }}</p>
                </div>

                @if($leave->reason)
                    <div class="mb-3">
                        <label class="form-label text-muted">Reason</label>
                        <p>{{ $leave->reason }}</p>
                    </div>
                @endif

                <div class="mb-3">
                    <label class="form-label text-muted">Applied On</label>
                    <p class="small text-muted">{{ $leave->created_at->format('d M Y H:i:s') }}</p>
                </div>

                <hr>

                <div class="d-flex gap-2 mt-4">
                    @if($leave->status === 'pending')
                        <a href="{{ route('leaves.edit', $leave) }}" class="btn btn-warning">✏️ Edit</a>
                    @endif
                    <a href="{{ route('leaves.index') }}" class="btn btn-secondary">← Back</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
