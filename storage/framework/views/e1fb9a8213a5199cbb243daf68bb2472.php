<?php if($errors->any()): ?>
    <div class="rounded-lg bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm">
        <?php echo e($errors->first()); ?>

    </div>
<?php endif; ?>

<div>
    <label class="block text-sm font-medium text-slate-600 mb-1"><?php echo e(__('admin.roles.name')); ?></label>
    <input type="text" name="name" value="<?php echo e(old('name', $role->name ?? '')); ?>" required
           class="w-full rounded-lg border border-slate-300 px-4 py-2">
</div>

<div>
    <label class="block text-sm font-medium text-slate-600 mb-2"><?php echo e(__('admin.roles.permissions')); ?></label>
    <div class="space-y-4">
        <?php $__currentLoopData = $permissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group => $items): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div>
            <p class="text-xs font-semibold uppercase text-slate-400 mb-1"><?php echo e($group); ?></p>
            <div class="grid grid-cols-2 gap-2">
                <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $permission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="permissions[]" value="<?php echo e($permission->name); ?>"
                        <?php if(in_array($permission->name, old('permissions', $rolePermissions ?? []))): echo 'checked'; endif; ?>>
                    <?php echo e($permission->name); ?>

                </label>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</div>
<?php /**PATH /home/faisal/new dev/DEV/Naqi/naqi-ess-full/resources/views/roles/_form.blade.php ENDPATH**/ ?>