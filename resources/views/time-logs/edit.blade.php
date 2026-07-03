@extends('layouts.app')

@section('title', 'Edit Time Log')

@section('content')
<div class="row">
    <div class="col-lg-6 mx-auto">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">✏️ Edit Time Log</h5>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('time-logs.update', $timeLog) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="project_id" class="form-label">Project <span class="text-danger">*</span></label>
                        <select class="form-select @error('project_id') is-invalid @enderror"
                                id="project_id" name="project_id" required>
                            <option value="">Select Project</option>
                            @foreach($projects as $project)
                                <option value="{{ $project->id }}"
                                        @selected(old('project_id', $timeLog->project_id) == $project->id)>
                                    {{ $project->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('project_id')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="time" class="form-label">Time (HH:MM) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('time') is-invalid @enderror"
                               id="time" name="time"
                               value="{{ old('time', sprintf('%d:%02d', $timeLog->hours, $timeLog->minutes)) }}"
                               placeholder="2:30" pattern="^\d{1,2}:\d{2}$" required>
                        <small class="text-muted">Format: HH:MM (e.g., 2:30, 10:00)</small>
                        @error('time')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="task_description" class="form-label">Task Description <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('task_description') is-invalid @enderror"
                                  id="task_description" name="task_description" rows="3"
                                  minlength="3" maxlength="500" required>{{ old('task_description', $timeLog->task_description) }}</textarea>
                        @error('task_description')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">💾 Save Changes</button>
                        <a href="{{ route('time-logs.show', $timeLog) }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
    document.getElementById('time').addEventListener('input', function() {
        const value = this.value.trim();
        const regex = /^\d{1,2}:\d{2}$/;

        if (value && !regex.test(value)) {
            this.classList.add('is-invalid');
        } else {
            this.classList.remove('is-invalid');
        }
    });
</script>
@endsection
@endsection
