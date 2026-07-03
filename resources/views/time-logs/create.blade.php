@extends('layouts.app')

@section('title', 'Add Time Log')

@section('content')
<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">📝 Log Your Work Time</h5>
            </div>
            <div class="card-body p-4">
                <form id="timeLogForm" method="POST" action="{{ route('time-logs.store') }}">
                    @csrf

                    <!-- Date Picker -->
                    <div class="mb-4">
                        <label for="work_date" class="form-label">Work Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('work_date') is-invalid @enderror"
                               id="work_date" name="work_date" value="{{ old('work_date', $today) }}"
                               max="{{ $today }}" required>
                        @error('work_date')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Daily Summary -->
                    <div id="dailySummary" class="daily-total" style="display: none;">
                        Existing Time Today: <span id="existingTime">0h 0m</span>
                    </div>

                    <!-- Tasks Section -->
                    <div class="form-section" id="tasksSection">
                        <h6 class="mb-3">Tasks</h6>
                        <div id="tasksList" class="mb-3">
                            <!-- Tasks will be added here -->
                        </div>

                        <div class="mb-3">
                            <button type="button" class="btn btn-outline-primary btn-sm" id="addTaskBtn">
                                + Add Task
                            </button>
                        </div>

                        @error('tasks')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Total Time Display -->
                    <div class="alert alert-info" id="totalTimeAlert" style="display: none;">
                        <strong>Total Time for This Entry:</strong> <span id="totalTime">0h 0m</span> / 10h 0m
                    </div>

                    <!-- Submit Buttons -->
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Submit Time Log
                        </button>
                        <a href="{{ route('time-logs.index') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Task Template (hidden) -->
<template id="taskTemplate">
    <div class="task-item">
        <div class="row g-3">
            <div class="col-md-5">
                <label class="form-label">Project <span class="text-danger">*</span></label>
                <select class="form-select project-select" name="tasks[INDEX][project_id]" required>
                    <option value="">Select Project</option>
                    @foreach($projects as $project)
                        <option value="{{ $project->id }}">{{ $project->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-5">
                <label class="form-label">Time (HH:MM) <span class="text-danger">*</span></label>
                <input type="text" class="form-control time-input task-time" name="tasks[INDEX][time]"
                       placeholder="2:30" pattern="^\d{1,2}:\d{2}$" required>
                <small class="text-muted">Format: HH:MM (e.g., 2:30, 10:00)</small>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="button" class="btn btn-danger btn-sm remove-task">Remove</button>
            </div>
        </div>
        <div class="row g-3 mt-1">
            <div class="col-12">
                <label class="form-label">Task Description <span class="text-danger">*</span></label>
                <textarea class="form-control" name="tasks[INDEX][task_description]" rows="2"
                          placeholder="Describe the task..." minlength="3" maxlength="500" required></textarea>
            </div>
        </div>
    </div>
</template>

@section('scripts')
<script>
    let taskCount = 0;
    const MAX_DAILY_MINUTES = 10 * 60; // 10 hours

    document.addEventListener('DOMContentLoaded', function() {
        const addTaskBtn = document.getElementById('addTaskBtn');
        const tasksList = document.getElementById('tasksList');
        const workDateInput = document.getElementById('work_date');
        const timeLogForm = document.getElementById('timeLogForm');

        addTaskBtn.addEventListener('click', addTask);
        workDateInput.addEventListener('change', loadDailyTotal);
        timeLogForm.addEventListener('input', updateTotalTime);

        // Load existing daily total on page load
        loadDailyTotal();

        // Add first task by default
        if (taskCount === 0) {
            addTask();
        }

        function addTask() {
            const template = document.getElementById('taskTemplate');
            const taskItem = template.content.cloneNode(true);

            // Update array indices
            const projectSelect = taskItem.querySelector('.project-select');
            const timeInput = taskItem.querySelector('.task-time');
            const descriptionTextarea = taskItem.querySelector('textarea');

            projectSelect.name = `tasks[${taskCount}][project_id]`;
            timeInput.name = `tasks[${taskCount}][time]`;
            descriptionTextarea.name = `tasks[${taskCount}][task_description]`;

            const removeBtn = taskItem.querySelector('.remove-task');
            removeBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.currentTarget.closest('.task-item').remove();
                updateTotalTime();
            });

            timeInput.addEventListener('input', function() {
                validateTimeFormat(this);
                updateTotalTime();
            });

            tasksList.appendChild(taskItem);
            taskCount++;
            updateTotalTime();
        }

        function validateTimeFormat(input) {
            const value = input.value.trim();
            if (value === '') return;

            const regex = /^\d{1,2}:\d{2}$/;
            if (!regex.test(value)) {
                input.classList.add('is-invalid');
            } else {
                const [hours, minutes] = value.split(':').map(Number);
                if (minutes > 59) {
                    input.classList.add('is-invalid');
                } else {
                    input.classList.remove('is-invalid');
                }
            }
        }

        function updateTotalTime() {
            const timeInputs = document.querySelectorAll('.task-time');
            let totalMinutes = 0;

            timeInputs.forEach(input => {
                const value = input.value.trim();
                if (value && /^\d{1,2}:\d{2}$/.test(value)) {
                    const [hours, minutes] = value.split(':').map(Number);
                    if (minutes <= 59) {
                        totalMinutes += (hours * 60) + minutes;
                    }
                }
            });

            const hours = Math.floor(totalMinutes / 60);
            const mins = totalMinutes % 60;

            document.getElementById('totalTime').textContent = `${hours}h ${mins}m`;
            document.getElementById('totalTimeAlert').style.display = totalMinutes > 0 ? 'block' : 'none';

            // Visual feedback if exceeds limit
            if (totalMinutes > MAX_DAILY_MINUTES) {
                document.getElementById('totalTimeAlert').classList.add('alert-warning');
                document.getElementById('totalTimeAlert').classList.remove('alert-info');
            } else {
                document.getElementById('totalTimeAlert').classList.remove('alert-warning');
                document.getElementById('totalTimeAlert').classList.add('alert-info');
            }
        }

        function loadDailyTotal() {
            const workDate = workDateInput.value;
            if (!workDate) {
                document.getElementById('dailySummary').style.display = 'none';
                return;
            }

            fetch(`{{ route('time-logs.daily-total') }}?work_date=${workDate}`)
                .then(response => response.json())
                .then(data => {
                    const totalHours = data.total_hours;
                    const totalMins = data.total_mins;
                    document.getElementById('existingTime').textContent = `${totalHours}h ${totalMins}m`;

                    if (data.total_minutes > 0) {
                        document.getElementById('dailySummary').style.display = 'block';
                    } else {
                        document.getElementById('dailySummary').style.display = 'none';
                    }
                })
                .catch(error => console.error('Error loading daily total:', error));
        }
    });
</script>
@endsection
@endsection
