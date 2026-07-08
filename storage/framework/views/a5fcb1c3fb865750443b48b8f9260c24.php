<?php $__env->startSection('title', 'Permissions'); ?>

<?php $__env->startSection('content'); ?>
<form method="POST" action="<?php echo e(route('admin.permissions.store')); ?>" class="bg-white rounded-xl shadow-sm p-5 mb-6 flex gap-3">
    <?php echo csrf_field(); ?>
    <input type="text" name="name" placeholder="e.g. leaves.approve" required
           class="flex-1 rounded-lg border border-slate-300 px-4 py-2 text-sm">
    <button class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 rounded-lg text-sm">Add Permission</button>
</form>

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-500 text-left">
            <tr>
                <th class="px-5 py-3">Permission</th>
                <th class="px-5 py-3 text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            <?php $__currentLoopData = $permissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $permission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td class="px-5 py-3"><?php echo e($permission->name); ?></td>
                <td class="px-5 py-3 text-right">
                    <form action="<?php echo e(route('admin.permissions.destroy', $permission)); ?>" method="POST" class="inline" onsubmit="return confirm('Delete this permission?')">
                        <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                        <button class="text-red-600 hover:underline">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
</div>
<div class="mt-4"><?php echo e($permissions->links()); ?></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/faisal/new dev/DEV/Naqi/naqi-ess-full/resources/views/permissions/index.blade.php ENDPATH**/ ?>