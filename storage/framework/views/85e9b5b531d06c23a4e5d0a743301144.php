<?php $__env->startSection('title', 'Dashboard'); ?>

<?php $__env->startSection('content'); ?>
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
    <div class="bg-white rounded-xl p-5 shadow-sm">
        <p class="text-slate-400 text-sm">Total Employees</p>
        <p class="text-2xl font-bold text-slate-800 mt-1"><?php echo e($stats['total_employees']); ?></p>
    </div>
    <div class="bg-white rounded-xl p-5 shadow-sm">
        <p class="text-slate-400 text-sm">Active Employees</p>
        <p class="text-2xl font-bold text-slate-800 mt-1"><?php echo e($stats['active_employees']); ?></p>
    </div>
    <div class="bg-white rounded-xl p-5 shadow-sm">
        <p class="text-slate-400 text-sm">Customers</p>
        <p class="text-2xl font-bold text-slate-800 mt-1"><?php echo e($stats['total_customers']); ?></p>
    </div>
    <div class="bg-white rounded-xl p-5 shadow-sm">
        <p class="text-slate-400 text-sm">Unread Notifications</p>
        <p class="text-2xl font-bold text-slate-800 mt-1"><?php echo e($stats['unread_notifications']); ?></p>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-100 font-semibold text-slate-700">Recently Added Users</div>
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-500 text-left">
            <tr>
                <th class="px-5 py-3">Name</th>
                <th class="px-5 py-3">Phone</th>
                <th class="px-5 py-3">Type</th>
                <th class="px-5 py-3">Status</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            <?php $__currentLoopData = $recentUsers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td class="px-5 py-3"><?php echo e($user->username); ?></td>
                <td class="px-5 py-3"><?php echo e($user->phone); ?></td>
                <td class="px-5 py-3 capitalize"><?php echo e($user->type); ?></td>
                <td class="px-5 py-3">
                    <span class="px-2 py-1 rounded-full text-xs <?php echo e($user->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700'); ?>">
                        <?php echo e(ucfirst($user->status)); ?>

                    </span>
                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/faisal/new dev/DEV/Naqi/naqi-ess-full/resources/views/dashboard/index.blade.php ENDPATH**/ ?>