<aside class="w-64 bg-slate-900 text-slate-200 flex flex-col">
    <div class="px-6 py-5 text-xl font-bold text-white border-b border-slate-800">
        Naqi <span class="text-indigo-400">ESS</span>
    </div>
    <nav class="flex-1 px-3 py-4 space-y-1 text-sm">
        <a href="<?php echo e(route('admin.dashboard')); ?>" class="flex items-center gap-3 px-3 py-2 rounded-lg <?php echo e(request()->routeIs('admin.dashboard') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800'); ?>">
            Dashboard
        </a>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('users.view')): ?>
        <a href="<?php echo e(route('admin.users.index')); ?>" class="flex items-center gap-3 px-3 py-2 rounded-lg <?php echo e(request()->routeIs('admin.users.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800'); ?>">
            Users
        </a>
        <?php endif; ?>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('roles.manage')): ?>
        <a href="<?php echo e(route('admin.roles.index')); ?>" class="flex items-center gap-3 px-3 py-2 rounded-lg <?php echo e(request()->routeIs('admin.roles.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800'); ?>">
            Roles
        </a>
        <?php endif; ?>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('permissions.manage')): ?>
        <a href="<?php echo e(route('admin.permissions.index')); ?>" class="flex items-center gap-3 px-3 py-2 rounded-lg <?php echo e(request()->routeIs('admin.permissions.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800'); ?>">
            Permissions
        </a>
        <?php endif; ?>
        <a href="<?php echo e(route('admin.notifications.index')); ?>" class="flex items-center gap-3 px-3 py-2 rounded-lg <?php echo e(request()->routeIs('admin.notifications.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800'); ?>">
            Notifications
        </a>
    </nav>
    <div class="px-3 py-4 border-t border-slate-800">
        <form method="POST" action="<?php echo e(route('admin.logout')); ?>">
            <?php echo csrf_field(); ?>
            <button class="w-full text-left px-3 py-2 rounded-lg hover:bg-slate-800 text-sm">Logout</button>
        </form>
    </div>
</aside>
<?php /**PATH /home/faisal/new dev/DEV/Naqi/naqi-ess-full/resources/views/components/sidebar.blade.php ENDPATH**/ ?>