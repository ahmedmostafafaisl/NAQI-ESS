<?php $__env->startSection('title', __('admin.users.title')); ?>

<?php $__env->startSection('content'); ?>
    <div class="flex items-center justify-between mb-5">
        <form method="GET" class="flex gap-2">
            <input type="text" name="search" value="<?php echo e(request('search')); ?>"
                placeholder="<?php echo e(__('admin.common.search')); ?>..."
                class="rounded-lg border border-slate-300 px-4 py-2 text-sm w-64">
            <select name="type" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value=""><?php echo e(__('admin.common.all')); ?></option>
                <option value="employee" <?php if(request('type') === 'employee'): echo 'selected'; endif; ?>><?php echo e(__('admin.users.employee')); ?></option>
                <option value="customer" <?php if(request('type') === 'customer'): echo 'selected'; endif; ?>><?php echo e(__('admin.users.customer')); ?></option>
            </select>
            <button class="bg-slate-800 text-white px-4 py-2 rounded-lg text-sm"><?php echo e(__('admin.common.filter')); ?></button>
        </form>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('users.create')): ?>
            <a href="<?php echo e(route('admin.users.create')); ?>"
                class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm">
                + <?php echo e(__('admin.users.add')); ?>

            </a>
        <?php endif; ?>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-500">
                <tr>
                    <th class="px-5 py-3 text-start"><?php echo e(__('admin.common.name')); ?></th>
                    <th class="px-5 py-3 text-start"><?php echo e(__('admin.common.email')); ?></th>
                    <th class="px-5 py-3 text-start"><?php echo e(__('admin.common.phone')); ?></th>
                    <th class="px-5 py-3 text-start"><?php echo e(__('admin.common.type')); ?></th>
                    <th class="px-5 py-3 text-start"><?php echo e(__('admin.common.role')); ?></th>
                    <th class="px-5 py-3 text-start"><?php echo e(__('admin.common.status')); ?></th>
                    <th class="px-5 py-3 text-end"><?php echo e(__('admin.common.actions')); ?></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td class="px-5 py-3"><?php echo e($user->username); ?></td>
                        <td class="px-5 py-3"><?php echo e($user->email ?? '—'); ?></td>
                        <td class="px-5 py-3"><?php echo e($user->phone); ?></td>
                        <td class="px-5 py-3">
                            <?php echo e($user->type === 'employee' ? __('admin.users.employee') : __('admin.users.customer')); ?></td>
                        <td class="px-5 py-3"><?php echo e($user->getRoleNames()->first() ?? '—'); ?></td>
                        <td class="px-5 py-3">
                            <span
                                class="px-2 py-1 rounded-full text-xs <?php echo e($user->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700'); ?>">
                                <?php echo e($user->status === 'active' ? __('admin.common.active') : __('admin.common.inactive')); ?>

                            </span>
                        </td>
                        <td class="px-5 py-3 text-end">
                            <div class="flex items-center justify-end gap-2">
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('users.edit')): ?>
                                    <?php if (isset($component)) { $__componentOriginald4c6978101b1c254eb70511d3c21c03f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald4c6978101b1c254eb70511d3c21c03f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.action-button','data' => ['variant' => 'edit','href' => route('admin.users.edit', $user)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('action-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'edit','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.users.edit', $user))]); ?><?php echo e(__('admin.common.edit')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald4c6978101b1c254eb70511d3c21c03f)): ?>
<?php $attributes = $__attributesOriginald4c6978101b1c254eb70511d3c21c03f; ?>
<?php unset($__attributesOriginald4c6978101b1c254eb70511d3c21c03f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald4c6978101b1c254eb70511d3c21c03f)): ?>
<?php $component = $__componentOriginald4c6978101b1c254eb70511d3c21c03f; ?>
<?php unset($__componentOriginald4c6978101b1c254eb70511d3c21c03f); ?>
<?php endif; ?>
                                <?php endif; ?>
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('users.delete')): ?>
                                    <form action="<?php echo e(route('admin.users.destroy', $user)); ?>" method="POST"
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
                        <td colspan="7" class="px-5 py-6 text-center text-slate-400"><?php echo e(__('admin.common.no_results')); ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="mt-4"><?php echo e($users->links()); ?></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/faisal/new dev/DEV/Naqi/naqi-ess-full/resources/views/users/index.blade.php ENDPATH**/ ?>