<?php $__env->startSection('title', 'Roles'); ?>

<?php $__env->startSection('content'); ?>
<div class="flex justify-end mb-5">
    <a href="<?php echo e(route('admin.roles.create')); ?>" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm">+ Add Role</a>
</div>

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-500 text-left">
            <tr>
                <th class="px-5 py-3">Role</th>
                <th class="px-5 py-3">Permissions</th>
                <th class="px-5 py-3 text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td class="px-5 py-3 font-medium"><?php echo e($role->name); ?></td>
                <td class="px-5 py-3"><?php echo e($role->permissions_count); ?> permissions</td>
                <td class="px-5 py-3 text-right space-x-2">
                    <a href="<?php echo e(route('admin.roles.edit', $role)); ?>" class="text-indigo-600 hover:underline">Edit</a>
                    <?php if (! (in_array($role->name, ['admin', 'super-admin']))): ?>
                    <form action="<?php echo e(route('admin.roles.destroy', $role)); ?>" method="POST" class="inline" onsubmit="return confirm('Delete this role?')">
                        <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                        <button class="text-red-600 hover:underline">Delete</button>
                    </form>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
</div>
<div class="mt-4"><?php echo e($roles->links()); ?></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/faisal/new dev/DEV/Naqi/naqi-ess-full/resources/views/roles/index.blade.php ENDPATH**/ ?>