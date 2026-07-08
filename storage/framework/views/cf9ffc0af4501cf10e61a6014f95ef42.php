<?php $__env->startSection('title', __('admin.notifications.send')); ?>

<?php $__env->startSection('content'); ?>
<form method="POST" action="<?php echo e(route('admin.notifications.store')); ?>" class="bg-white rounded-xl shadow-sm p-6 max-w-2xl space-y-4">
    <?php echo csrf_field(); ?>
    <?php if($errors->any()): ?>
        <div class="rounded-lg bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm"><?php echo e($errors->first()); ?></div>
    <?php endif; ?>

    <div>
        <label class="block text-sm font-medium text-slate-600 mb-1"><?php echo e(__('admin.notifications.audience')); ?></label>
        <select name="audience" id="audience" class="w-full rounded-lg border border-slate-300 px-4 py-2">
            <option value="all"><?php echo e(__('admin.notifications.audience_all')); ?></option>
            <option value="employees"><?php echo e(__('admin.notifications.audience_employees')); ?></option>
            <option value="customers"><?php echo e(__('admin.notifications.audience_customers')); ?></option>
            <option value="specific"><?php echo e(__('admin.notifications.audience_specific')); ?></option>
        </select>
    </div>

    <div id="specific-users" class="hidden">
        <label class="block text-sm font-medium text-slate-600 mb-1"><?php echo e(__('admin.notifications.select_users')); ?></label>
        <select name="user_ids[]" multiple class="w-full rounded-lg border border-slate-300 px-4 py-2 h-40">
            <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($user->id); ?>"><?php echo e($user->username); ?> (<?php echo e($user->phone); ?>)</option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-600 mb-1"><?php echo e(__('admin.notifications.title_field')); ?></label>
        <input type="text" name="title" required class="w-full rounded-lg border border-slate-300 px-4 py-2">
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-600 mb-1"><?php echo e(__('admin.notifications.message')); ?></label>
        <textarea name="body" rows="4" required class="w-full rounded-lg border border-slate-300 px-4 py-2"></textarea>
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-600 mb-1"><?php echo e(__('admin.notifications.category')); ?></label>
        <input type="text" name="category" placeholder="<?php echo e(__('admin.notifications.category_placeholder')); ?>" class="w-full rounded-lg border border-slate-300 px-4 py-2">
    </div>

    <button class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-lg text-sm"><?php echo e(__('admin.notifications.send')); ?></button>
</form>

<script>
    document.getElementById('audience').addEventListener('change', function (e) {
        document.getElementById('specific-users').classList.toggle('hidden', e.target.value !== 'specific');
    });
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/faisal/new dev/DEV/Naqi/naqi-ess-full/resources/views/notifications/create.blade.php ENDPATH**/ ?>