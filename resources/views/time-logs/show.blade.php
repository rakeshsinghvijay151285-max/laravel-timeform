@extends('layouts.app')

@section('title', 'View Time Log')

@section('content')
<div class="row">
    <div class="col-lg-6 mx-auto">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">📋 Time Log Details</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label text-muted">Work Date</label>
                    <p class="h5">{{ $timeLog->work_date->format('d M Y (l)') }}</p>
                </div>

                <div class="mb-3">
                    <label class="form-label text-muted">Project</label>
                    <p class="h5"><span class="badge bg-info">{{ $timeLog->project->name }}</span></p>
                </div>

                <div class="mb-3">
                    <label class="form-label text-muted">Task Description</label>
                    <p>{{ $timeLog->task_description }}</p>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label text-muted">Hours</label>
                        <p class="h5">{{ $timeLog->hours }}h</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted">Minutes</label>
                        <p class="h5">{{ $timeLog->minutes }}m</p>
                    </div>
                </div>

                <hr>

                <div class="mb-3">
                    <label class="form-label text-muted">Total Duration</label>
                    <p class="h4">{{ $timeLog->hours }}h {{ $timeLog->minutes }}m</p>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <a href="{{ route('time-logs.edit', $timeLog) }}" class="btn btn-warning">✏️ Edit</a>
                    <a href="{{ route('time-logs.index') }}" class="btn btn-secondary">← Back</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
