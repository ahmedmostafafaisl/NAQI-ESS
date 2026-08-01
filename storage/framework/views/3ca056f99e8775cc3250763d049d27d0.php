<?php $__env->startSection('title', __('admin.settings.title')); ?>

<?php $__env->startSection('content'); ?>
    <div class="flex items-center justify-between mb-5">
        <form method="GET" class="flex gap-2">
            <input type="text" name="search" value="<?php echo e(request('search')); ?>"
                placeholder="<?php echo e(__('admin.common.search')); ?>..."
                class="rounded-lg border border-slate-300 px-4 py-2 text-sm w-64">
            <button class="bg-slate-800 text-white px-4 py-2 rounded-lg text-sm"><?php echo e(__('admin.common.filter')); ?></button>
        </form>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('settings.manage')): ?>
            <a href="<?php echo e(route('admin.settings.create')); ?>"
                class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm">
                + <?php echo e(__('admin.settings.add')); ?>

            </a>
        <?php endif; ?>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-500">
                <tr>
                    <th class="px-5 py-3 text-start"><?php echo e(__('admin.settings.key')); ?></th>
                    <th class="px-5 py-3 text-start"><?php echo e(__('admin.settings.value')); ?></th>
                    <th class="px-5 py-3 text-start"><?php echo e(__('admin.settings.type')); ?></th>
                    <th class="px-5 py-3 text-start"><?php echo e(__('admin.settings.visibility')); ?></th>
                    <th class="px-5 py-3 text-end"><?php echo e(__('admin.common.actions')); ?></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php $__empty_1 = true; $__currentLoopData = $settings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $setting): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td class="px-5 py-3 font-mono text-xs"><?php echo e($setting->key); ?></td>
                        <td class="px-5 py-3 max-w-xs truncate" title="<?php echo e($setting->value); ?>"><?php echo e($setting->value ?? '—'); ?></td>
                        <td class="px-5 py-3">
                            <span class="px-2 py-1 rounded-full text-xs bg-slate-100 text-slate-600"><?php echo e($setting->type); ?></span>
                        </td>
                        <td class="px-5 py-3">
                            <?php if($setting->is_public): ?>
                                <span
                                    class="px-2 py-1 rounded-full text-xs bg-emerald-100 text-emerald-700"><?php echo e(__('admin.settings.public')); ?></span>
                            <?php else: ?>
                                <span
                                    class="px-2 py-1 rounded-full text-xs bg-slate-100 text-slate-500"><?php echo e(__('admin.settings.private')); ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="px-5 py-3 text-end">
                            <div class="flex items-center justify-end gap-2">
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('settings.manage')): ?>
                                    <?php if (isset($component)) { $__componentOriginald4c6978101b1c254eb70511d3c21c03f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald4c6978101b1c254eb70511d3c21c03f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.action-button','data' => ['variant' => 'edit','href' => route('admin.settings.edit', $setting)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('action-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'edit','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.settings.edit', $setting))]); ?><?php echo e(__('admin.common.edit')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald4c6978101b1c254eb70511d3c21c03f)): ?>
<?php $attributes = $__attributesOriginald4c6978101b1c254eb70511d3c21c03f; ?>
<?php unset($__attributesOriginald4c6978101b1c254eb70511d3c21c03f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald4c6978101b1c254eb70511d3c21c03f)): ?>
<?php $component = $__componentOriginald4c6978101b1c254eb70511d3c21c03f; ?>
<?php unset($__componentOriginald4c6978101b1c254eb70511d3c21c03f); ?>
<?php endif; ?>
                                    <form action="<?php echo e(route('admin.settings.destroy', $setting)); ?>" method="POST"
                                        onsubmit="return confirm('<?php echo e(__('admin.common.confirm_delete')); ?>')">
                                        <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                        <?php if (isset($component)) { $__componentOriginald4c6978101b1c254eb70511d3c21c03f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald4c6978101b1c254eb70511d3c21c03f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.action-button','data' => ['variant' => 'delete','type' => 'submit']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('action-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'delete','type' => 'submit']); ?><?php echo e(__('admin.common.delete')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald4c6978101b1c254eb70511d3c21c03f)): ?>
<?php $attributes = $__attributesOriginald4c6978101b1c254eb70511d3c21c03f; ?>
<?php unset($__attributesOriginald4c6978101b1c254eb70511d3c21c03f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald4c6978101b1c254eb70511d3c21c03f)): ?>
<?php $component = $__componentOriginald4c6978101b1c254eb70511d3c21c03f; ?>
<?php unset($__componentOriginald4c6978101b1c254eb70511d3c21c03f); ?>
<?php endif; ?>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="5" class="px-5 py-6 text-center text-slate-400"><?php echo e(__('admin.common.no_results')); ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="mt-4"><?php echo e($settings->links()); ?></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/faisal/new dev/DEV/Naqi/naqi-ess-full/resources/views/settings/index.blade.php ENDPATH**/ ?>