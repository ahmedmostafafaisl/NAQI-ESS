<?php $__env->startSection('title', 'Notifications'); ?>

<?php $__env->startSection('content'); ?>
<div class="flex justify-end mb-5">
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('notifications.send')): ?>
    <a href="<?php echo e(route('admin.notifications.create')); ?>" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm">+ Send Notification</a>
    <?php endif; ?>
</div>

<div class="bg-white rounded-xl shadow-sm divide-y divide-slate-100">
    <?php $__empty_1 = true; $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
    <div class="px-5 py-4 flex items-start justify-between <?php echo e($item->read_at ? '' : 'bg-indigo-50/40'); ?>">
        <div>
            <p class="font-medium text-slate-800"><?php echo e($item->data['title'] ?? ''); ?></p>
            <p class="text-sm text-slate-500"><?php echo e($item->data['body'] ?? ''); ?></p>
            <p class="text-xs text-slate-400 mt-1"><?php echo e($item->created_at->diffForHumans()); ?></p>
        </div>
        <?php if (! ($item->read_at)): ?>
        <form action="<?php echo e(route('admin.notifications.read', $item->id)); ?>" method="POST">
            <?php echo csrf_field(); ?>
            <button class="text-xs text-indigo-600 hover:underline">Mark as read</button>
        </form>
        <?php endif; ?>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
    <div class="px-5 py-8 text-center text-slate-400">No notifications yet.</div>
    <?php endif; ?>
</div>
<div class="mt-4"><?php echo e($items->links()); ?></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/faisal/new dev/DEV/Naqi/naqi-ess-full/resources/views/notifications/index.blade.php ENDPATH**/ ?>