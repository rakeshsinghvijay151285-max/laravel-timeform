<?php $__env->startSection('title', 'View Time Log'); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <div class="col-lg-6 mx-auto">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">📋 Time Log Details</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label text-muted">Work Date</label>
                    <p class="h5"><?php echo e($timeLog->work_date->format('d M Y (l)')); ?></p>
                </div>

                <div class="mb-3">
                    <label class="form-label text-muted">Project</label>
                    <p class="h5"><span class="badge bg-info"><?php echo e($timeLog->project->name); ?></span></p>
                </div>

                <div class="mb-3">
                    <label class="form-label text-muted">Task Description</label>
                    <p><?php echo e($timeLog->task_description); ?></p>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label text-muted">Hours</label>
                        <p class="h5"><?php echo e($timeLog->hours); ?>h</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted">Minutes</label>
                        <p class="h5"><?php echo e($timeLog->minutes); ?>m</p>
                    </div>
                </div>

                <hr>

                <div class="mb-3">
                    <label class="form-label text-muted">Total Duration</label>
                    <p class="h4"><?php echo e($timeLog->hours); ?>h <?php echo e($timeLog->minutes); ?>m</p>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <a href="<?php echo e(route('time-logs.edit', $timeLog)); ?>" class="btn btn-warning">✏️ Edit</a>
                    <a href="<?php echo e(route('time-logs.index')); ?>" class="btn btn-secondary">← Back</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH E:\projects\taskform\taskform\resources\views/time-logs/show.blade.php ENDPATH**/ ?>