<?php $__env->startSection('title', __('admin.dashboard.title')); ?>

<?php $__env->startSection('content'); ?>
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
    <div class="bg-white rounded-xl p-5 shadow-sm">
        <p class="text-slate-400 text-sm"><?php echo e(__('admin.dashboard.total_employees')); ?></p>
        <p class="text-2xl font-bold text-slate-800 mt-1"><?php echo e($stats['total_employees']); ?></p>
    </div>
    <div class="bg-white rounded-xl p-5 shadow-sm">
        <p class="text-slate-400 text-sm"><?php echo e(__('admin.dashboard.active_employees')); ?></p>
        <p class="text-2xl font-bold text-slate-800 mt-1"><?php echo e($stats['active_employees']); ?></p>
    </div>
    <div class="bg-white rounded-xl p-5 shadow-sm">
        <p class="text-slate-400 text-sm"><?php echo e(__('admin.dashboard.total_customers')); ?></p>
        <p class="text-2xl font-bold text-slate-800 mt-1"><?php echo e($stats['total_customers']); ?></p>
    </div>
    <div class="bg-white rounded-xl p-5 shadow-sm">
        <p class="text-slate-400 text-sm"><?php echo e(__('admin.dashboard.unread_notifications')); ?></p>
        <p class="text-2xl font-bold text-slate-800 mt-1"><?php echo e($stats['unread_notifications']); ?></p>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-100 font-semibold text-slate-700"><?php echo e(__('admin.dashboard.recent_users')); ?></div>
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-500 text-start">
            <tr>
                <th class="px-5 py-3 text-start"><?php echo e(__('admin.common.name')); ?></th>
                <th class="px-5 py-3 text-start"><?php echo e(__('admin.common.phone')); ?></th>
                <th class="px-5 py-3 text-start"><?php echo e(__('admin.common.type')); ?></th>
                <th class="px-5 py-3 text-start"><?php echo e(__('admin.common.status')); ?></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            <?php $__currentLoopData = $recentUsers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td class="px-5 py-3"><?php echo e($user->username); ?></td>
                <td class="px-5 py-3"><?php echo e($user->phone); ?></td>
                <td class="px-5 py-3"><?php echo e($user->type === 'employee' ? __('admin.users.employee') : __('admin.users.customer')); ?></td>
                <td class="px-5 py-3">
                    <span class="px-2 py-1 rounded-full text-xs <?php echo e($user->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700'); ?>">
                        <?php echo e($user->status === 'active' ? __('admin.common.active') : __('admin.common.inactive')); ?>

                    </span>
                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/faisal/new dev/DEV/Naqi/naqi-ess-full/resources/views/dashboard/index.blade.php ENDPATH**/ ?>