<?php $__env->startSection('title', 'Time Logs'); ?>

<?php $__env->startSection('content'); ?>
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h2>📊 My Time Logs</h2>
            <a href="<?php echo e(route('time-logs.create')); ?>" class="btn btn-primary">+ Add New Time Log</a>
        </div>
    </div>
</div>

<?php if($timeLogs->isEmpty()): ?>
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
            <p class="mt-3 text-muted">No time logs yet. <a href="<?php echo e(route('time-logs.create')); ?>">Create your first time log</a></p>
        </div>
    </div>
<?php else: ?>
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Project</th>
                        <th>Task Description</th>
                        <th>Time</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $timeLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td>
                                <strong><?php echo e($log->work_date->format('d M Y')); ?></strong><br>
                                <small class="text-muted"><?php echo e($log->work_date->format('l')); ?></small>
                            </td>
                            <td>
                                <span class="badge bg-info"><?php echo e($log->project->name); ?></span>
                            </td>
                            <td>
                                <div><?php echo e(Str::limit($log->task_description, 50)); ?></div>
                                <?php if(strlen($log->task_description) > 50): ?>
                                    <small class="text-muted"><?php echo e($log->task_description); ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?php echo e($log->hours); ?>h <?php echo e($log->minutes); ?>m</strong>
                            </td>
                            <td>
                                <div class="d-flex gap-2 flex-wrap">
                                    <a href="<?php echo e(route('time-logs.show', $log)); ?>" class="btn btn-sm btn-outline-primary" title="View">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                    <a href="<?php echo e(route('time-logs.edit', $log)); ?>" class="btn btn-sm btn-outline-warning" title="Edit">
                                        <i class="bi bi-pencil"></i> Edit
                                    </a>
                                    <form method="POST" action="<?php echo e(route('time-logs.destroy', $log)); ?>" style="display: inline;">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"
                                                onclick="return confirm('Are you sure?')">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-center mt-4">
        <?php echo e($timeLogs->links()); ?>

    </div>

    <!-- Summary Card -->
    <div class="row mt-4">
        <div class="col-md-6 mx-auto">
            <div class="card bg-light">
                <div class="card-body text-center">
                    <h6 class="card-title">Total Hours Logged</h6>
                    <?php
                        $totalMinutes = $timeLogs->sum(function($log) {
                            return ($log->hours * 60) + $log->minutes;
                        });
                        $totalHours = floor($totalMinutes / 60);
                        $totalMins = $totalMinutes % 60;
                    ?>
                    <p class="card-text" style="font-size: 1.5rem; font-weight: 600; color: #0d6efd;">
                        <?php echo e($totalHours); ?>h <?php echo e($totalMins); ?>m
                    </p>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH E:\projects\taskform\taskform\resources\views/time-logs/index.blade.php ENDPATH**/ ?>