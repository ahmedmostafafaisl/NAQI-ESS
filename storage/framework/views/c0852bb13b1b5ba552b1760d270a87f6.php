<?php $__env->startSection('title', __('admin.roles.add')); ?>

<?php $__env->startSection('content'); ?>
<form method="POST" action="<?php echo e(route('admin.roles.store')); ?>" class="bg-white rounded-xl shadow-sm p-6 max-w-2xl space-y-5">
    <?php echo csrf_field(); ?>
    <?php echo $__env->make('roles._form', ['role' => null, 'rolePermissions' => []], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <button class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-lg text-sm"><?php echo e(__('admin.roles.create')); ?></button>
</form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/faisal/new dev/DEV/Naqi/naqi-ess-full/resources/views/roles/create.blade.php ENDPATH**/ ?>