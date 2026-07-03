<?php $__env->startSection('title', 'Apply for Leave'); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <div class="col-lg-6 mx-auto">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">📅 Apply for Leave</h5>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="<?php echo e(route('leaves.store')); ?>" id="leaveForm">
                    <?php echo csrf_field(); ?>

                    <div class="alert alert-info">
                        <strong>Note:</strong> You cannot apply leave for dates that have existing work reports.
                    </div>

                    <div class="mb-3">
                        <label for="start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control <?php $__errorArgs = ['start_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                               id="start_date" name="start_date" value="<?php echo e(old('start_date', $today)); ?>"
                               min="<?php echo e($today); ?>" required>
                        <?php $__errorArgs = ['start_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="invalid-feedback d-block"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="mb-3">
                        <label for="end_date" class="form-label">End Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control <?php $__errorArgs = ['end_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                               id="end_date" name="end_date" value="<?php echo e(old('end_date', $today)); ?>"
                               min="<?php echo e($today); ?>" required>
                        <?php $__errorArgs = ['end_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="invalid-feedback d-block"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <!-- Conflict Alert -->
                    <div id="conflictAlert" class="alert alert-warning d-none">
                        <strong>⚠️ Conflict Detected!</strong>
                        <p class="mb-0">You have work reports on the following dates:</p>
                        <ul class="mb-0" id="conflictDates"></ul>
                        <p class="mt-2 mb-0 small">Please delete these work reports before applying for leave.</p>
                    </div>

                    <div class="mb-3">
                        <label for="reason" class="form-label">Reason (Optional)</label>
                        <textarea class="form-control <?php $__errorArgs = ['reason'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                  id="reason" name="reason" rows="3"
                                  maxlength="500" placeholder="Enter the reason for your leave"><?php echo e(old('reason')); ?></textarea>
                        <small class="text-muted">Maximum 500 characters</small>
                        <?php $__errorArgs = ['reason'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="invalid-feedback d-block"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <!-- Duration Summary -->
                    <div class="alert alert-info" id="durationAlert" style="display: none;">
                        <strong>Leave Duration:</strong> <span id="leaveDays">0</span> day(s)
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            ✅ Submit Leave Application
                        </button>
                        <a href="<?php echo e(route('leaves.index')); ?>" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php $__env->startSection('scripts'); ?>
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

        fetch(`<?php echo e(route('leaves.check-conflict')); ?>?start_date=${startDate}&end_date=${endDate}`)
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
<?php $__env->stopSection(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH E:\projects\taskform\taskform\resources\views/leaves/create.blade.php ENDPATH**/ ?>