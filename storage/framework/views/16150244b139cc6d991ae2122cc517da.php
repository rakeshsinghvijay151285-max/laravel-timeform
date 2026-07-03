<?php $__env->startSection('title', 'My Leaves'); ?>

<?php $__env->startSection('content'); ?>
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h2>📅 My Leaves</h2>
            <a href="<?php echo e(route('leaves.create')); ?>" class="btn btn-primary">+ Apply for Leave</a>
        </div>
    </div>
</div>

<?php if($leaves->isEmpty()): ?>
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bi bi-calendar-check" style="font-size: 3rem; color: #ccc;"></i>
            <p class="mt-3 text-muted">No leave applications yet. <a href="<?php echo e(route('leaves.create')); ?>">Apply for leave</a></p>
        </div>
    </div>
<?php else: ?>
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
                    <?php $__currentLoopData = $leaves; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $leave): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $days = $leave->end_date->diffInDays($leave->start_date) + 1;
                            $statusColor = match($leave->status) {
                                'approved' => 'success',
                                'rejected' => 'danger',
                                'pending' => 'warning',
                                default => 'secondary'
                            };
                        ?>
                        <tr>
                            <td>
                                <div>
                                    <strong><?php echo e($leave->start_date->format('d M Y')); ?></strong><br>
                                    <i class="bi bi-arrow-right"></i><br>
                                    <strong><?php echo e($leave->end_date->format('d M Y')); ?></strong>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-info"><?php echo e($days); ?> day<?php echo e($days > 1 ? 's' : ''); ?></span>
                            </td>
                            <td>
                                <?php echo e($leave->reason ? Str::limit($leave->reason, 40) : '—'); ?>

                            </td>
                            <td>
                                <?php if($leave->status === 'approved'): ?>
                                    <span class="badge bg-success">✓ Approved</span>
                                <?php elseif($leave->status === 'rejected'): ?>
                                    <span class="badge bg-danger">✗ Rejected</span>
                                <?php else: ?>
                                    <span class="badge bg-warning">⏳ Pending</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <small class="text-muted"><?php echo e($leave->created_at->format('d M Y H:i')); ?></small>
                            </td>
                            <td>
                                <div class="d-flex gap-2 flex-wrap">
                                    <a href="<?php echo e(route('leaves.show', $leave)); ?>" class="btn btn-sm btn-outline-primary" title="View">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                    <?php if($leave->status === 'pending'): ?>
                                        <a href="<?php echo e(route('leaves.edit', $leave)); ?>" class="btn btn-sm btn-outline-warning" title="Edit">
                                            <i class="bi bi-pencil"></i> Edit
                                        </a>
                                        <form method="POST" action="<?php echo e(route('leaves.destroy', $leave)); ?>" style="display: inline;">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"
                                                    onclick="return confirm('Are you sure?')">
                                                <i class="bi bi-trash"></i> Delete
                                            </button>
                                        </form>
                                    <?php endif; ?>
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
        <?php echo e($leaves->links()); ?>

    </div>

    <!-- Summary Card -->
    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card text-center bg-light">
                <div class="card-body">
                    <h6 class="card-title">Approved Leaves</h6>
                    <?php
                        $approvedDays = $leaves->where('status', 'approved')->sum(function($leave) {
                            return $leave->end_date->diffInDays($leave->start_date) + 1;
                        });
                    ?>
                    <p class="card-text" style="font-size: 1.5rem; font-weight: 600; color: #28a745;">
                        <?php echo e($approvedDays); ?>

                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center bg-light">
                <div class="card-body">
                    <h6 class="card-title">Pending Leaves</h6>
                    <?php
                        $pendingCount = $leaves->where('status', 'pending')->count();
                    ?>
                    <p class="card-text" style="font-size: 1.5rem; font-weight: 600; color: #ffc107;">
                        <?php echo e($pendingCount); ?>

                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center bg-light">
                <div class="card-body">
                    <h6 class="card-title">Rejected Leaves</h6>
                    <?php
                        $rejectedCount = $leaves->where('status', 'rejected')->count();
                    ?>
                    <p class="card-text" style="font-size: 1.5rem; font-weight: 600; color: #dc3545;">
                        <?php echo e($rejectedCount); ?>

                    </p>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH E:\projects\taskform\taskform\resources\views/leaves/index.blade.php ENDPATH**/ ?>