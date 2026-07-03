@extends('layouts.app')

@section('title', 'Edit Leave')

@section('content')
<div class="row">
    <div class="col-lg-6 mx-auto">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">✏️ Edit Leave Application</h5>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('leaves.update', $leave) }}" id="leaveForm">
                    @csrf
                    @method('PUT')

                    <div class="alert alert-info">
                        <strong>Note:</strong> You can only edit pending leave applications.
                    </div>

                    <div class="mb-3">
                        <label for="start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('start_date') is-invalid @enderror"
                               id="start_date" name="start_date" value="{{ old('start_date', $leave->start_date->toDateString()) }}"
                               min="{{ now()->toDateString() }}" required>
                        @error('start_date')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="end_date" class="form-label">End Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('end_date') is-invalid @enderror"
                               id="end_date" name="end_date" value="{{ old('end_date', $leave->end_date->toDateString()) }}"
                               min="{{ now()->toDateString() }}" required>
                        @error('end_date')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Conflict Alert -->
                    <div id="conflictAlert" class="alert alert-warning d-none">
                        <strong>⚠️ Conflict Detected!</strong>
                        <p class="mb-0">You have work reports on the following dates:</p>
                        <ul class="mb-0" id="conflictDates"></ul>
                        <p class="mt-2 mb-0 small">Please delete these work reports before updating your leave.</p>
                    </div>

                    <div class="mb-3">
                        <label for="reason" class="form-label">Reason (Optional)</label>
                        <textarea class="form-control @error('reason') is-invalid @enderror"
                                  id="reason" name="reason" rows="3"
                                  maxlength="500" placeholder="Enter the reason for your leave">{{ old('reason', $leave->reason) }}</textarea>
                        <small class="text-muted">Maximum 500 characters</small>
                        @error('reason')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Duration Summary -->
                    <div class="alert alert-info" id="durationAlert" style="display: none;">
                        <strong>Leave Duration:</strong> <span id="leaveDays">0</span> day(s)
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            💾 Save Changes
                        </button>
                        <a href="{{ route('leaves.show', $leave) }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    const conflictAlert = document.getElementById('conflictAlert');
    const conflictDates = document.getElementById('conflictDates');
    const submitBtn = document.getElementById('submitBtn');
    const durationAlert = document.getElementById('durationAlert');
    const leaveDays = document.getElementById('leaveDays');

    function checkConflict() {
        const startDate = startDateInput.value;
        const endDate = endDateInput.value;

        if (!startDate || !endDate) {
            conflictAlert.classList.add('d-none');
            submitBtn.disabled = false;
            return;
        }

        fetch(`{{ route('leaves.check-conflict') }}?start_date=${startDate}&end_date=${endDate}`)
            .then(response => response.json())
            .then(data => {
                if (data.has_conflict) {
                    conflictAlert.classList.remove('d-none');
                    submitBtn.disabled = true;

                    const dates = data.conflicting_dates;
                    conflictDates.innerHTML = '';
                    dates.forEach(date => {
                        const li = document.createElement('li');
                        li.textContent = new Date(date).toLocaleDateString('en-US', {
                            weekday: 'short',
                            year: 'numeric',
                            month: 'short',
                            day: 'numeric'
                        });
                        conflictDates.appendChild(li);
                    });
                } else {
                    conflictAlert.classList.add('d-none');
                    submitBtn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error checking conflict:', error);
                conflictAlert.classList.add('d-none');
                submitBtn.disabled = false;
            });
    }

    function calculateDuration() {
        const startDate = new Date(startDateInput.value);
        const endDate = new Date(endDateInput.value);

        if (startDateInput.value && endDateInput.value && endDate >= startDate) {
            const days = Math.floor((endDate - startDate) / (1000 * 60 * 60 * 24)) + 1;
            leaveDays.textContent = days;
            durationAlert.style.display = 'block';
        } else {
            durationAlert.style.display = 'none';
        }
    }

    startDateInput.addEventListener('change', function() {
        // Set minimum end date to start date
        endDateInput.min = this.value;
        if (endDateInput.value < this.value) {
            endDateInput.value = this.value;
        }
        checkConflict();
        calculateDuration();
    });

    endDateInput.addEventListener('change', function() {
        checkConflict();
        calculateDuration();
    });

    // Initial check
    checkConflict();
    calculateDuration();
</script>
@endsection
@endsection
