<?php $__env->startSection('title', 'Users'); ?>

<?php $__env->startSection('content'); ?>
<div class="flex items-center justify-between mb-5">
    <form method="GET" class="flex gap-2">
        <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Search users..."
               class="rounded-lg border border-slate-300 px-4 py-2 text-sm w-64">
        <select name="type" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <option value="">All types</option>
            <option value="employee" <?php if(request('type')==='employee'): echo 'selected'; endif; ?>>Employee</option>
            <option value="customer" <?php if(request('type')==='customer'): echo 'selected'; endif; ?>>Customer</option>
        </select>
        <button class="bg-slate-800 text-white px-4 py-2 rounded-lg text-sm">Filter</button>
    </form>
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('users.create')): ?>
    <a href="<?php echo e(route('admin.users.create')); ?>" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm">
        + Add User
    </a>
    <?php endif; ?>
</div>

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-500 text-left">
            <tr>
                <th class="px-5 py-3">Name</th>
                <th class="px-5 py-3">Email</th>
                <th class="px-5 py-3">Phone</th>
                <th class="px-5 py-3">Type</th>
                <th class="px-5 py-3">Role</th>
                <th class="px-5 py-3">Status</th>
                <th class="px-5 py-3 text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            <?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
                <td class="px-5 py-3"><?php echo e($user->username); ?></td>
                <td class="px-5 py-3"><?php echo e($user->email ?? '—'); ?></td>
                <td class="px-5 py-3"><?php echo e($user->phone); ?></td>
                <td class="px-5 py-3 capitalize"><?php echo e($user->type); ?></td>
                <td class="px-5 py-3"><?php echo e($user->getRoleNames()->first() ?? '—'); ?></td>
                <td class="px-5 py-3">
                    <span class="px-2 py-1 rounded-full text-xs <?php echo e($user->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700'); ?>">
                        <?php echo e(ucfirst($user->status)); ?>

                    </span>
                </td>
                <td class="px-5 py-3 text-right space-x-2">
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('users.edit')): ?>
                    <a href="<?php echo e(route('admin.users.edit', $user)); ?>" class="text-indigo-600 hover:underline">Edit</a>
                    <?php endif; ?>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('users.delete')): ?>
                    <form action="<?php echo e(route('admin.users.destroy', $user)); ?>" method="POST" class="inline" onsubmit="return confirm('Delete this user?')">
                        <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                        <button class="text-red-600 hover:underline">Delete</button>
                    </form>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr><td colspan="7" class="px-5 py-6 text-center text-slate-400">No users found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="mt-4"><?php echo e($users->links()); ?></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/faisal/new dev/DEV/Naqi/naqi-ess-full/resources/views/users/index.blade.php ENDPATH**/ ?>