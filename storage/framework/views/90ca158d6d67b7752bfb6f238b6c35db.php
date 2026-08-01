<?php $s = $setting ?? null; ?>

<?php if($errors->any()): ?>
    <div class="rounded-lg bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm">
        <?php echo e($errors->first()); ?>

    </div>
<?php endif; ?>

<?php if($s): ?>
    <div>
        <label class="block text-sm font-medium text-slate-600 mb-1"><?php echo e(__('admin.settings.key')); ?></label>
        <input type="text" value="<?php echo e($s->key); ?>" disabled
            class="w-full rounded-lg border border-slate-200 bg-slate-50 text-slate-500 px-4 py-2">
        <p class="text-xs text-slate-400 mt-1"><?php echo e(__('admin.settings.key_immutable')); ?></p>
    </div>
<?php else: ?>
    <div>
        <label class="block text-sm font-medium text-slate-600 mb-1"><?php echo e(__('admin.settings.key')); ?></label>
        <input type="text" name="key" value="<?php echo e(old('key')); ?>" required placeholder="e.g. maintenance_mode"
            class="w-full rounded-lg border border-slate-300 px-4 py-2 font-mono text-sm">
        <p class="text-xs text-slate-400 mt-1"><?php echo e(__('admin.settings.key_hint')); ?></p>
    </div>
<?php endif; ?>

<div>
    <label class="block text-sm font-medium text-slate-600 mb-1"><?php echo e(__('admin.settings.type')); ?></label>
    <select name="type" id="type" class="w-full rounded-lg border border-slate-300 px-4 py-2">
        <?php $__currentLoopData = ['string', 'integer', 'boolean', 'json']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($type); ?>" <?php if(old('type', $s->type ?? 'string') === $type): echo 'selected'; endif; ?>><?php echo e($type); ?></option>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </select>
</div>

<div>
    <label class="block text-sm font-medium text-slate-600 mb-1"><?php echo e(__('admin.settings.value')); ?></label>
    <textarea name="value" rows="3"
        class="w-full rounded-lg border border-slate-300 px-4 py-2 font-mono text-sm"><?php echo e(old('value', $s->value ?? '')); ?></textarea>
    <p class="text-xs text-slate-400 mt-1"><?php echo e(__('admin.settings.value_hint')); ?></p>
</div>

<div>
    <label class="block text-sm font-medium text-slate-600 mb-1"><?php echo e(__('admin.settings.description')); ?></label>
    <input type="text" name="description" value="<?php echo e(old('description', $s->description ?? '')); ?>"
        class="w-full rounded-lg border border-slate-300 px-4 py-2">
</div>

<label class="flex items-center gap-2 text-sm text-slate-600">
    <input type="checkbox" name="is_public" value="1" <?php if(old('is_public', $s->is_public ?? false)): echo 'checked'; endif; ?>>
    <?php echo e(__('admin.settings.is_public_label')); ?>

</label>
<?php /**PATH /home/faisal/new dev/DEV/Naqi/naqi-ess-prod/resources/views/settings/_form.blade.php ENDPATH**/ ?>