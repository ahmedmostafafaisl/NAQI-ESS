<header class="bg-white border-b border-slate-200 px-6 py-3.5 flex items-center justify-between gap-4">
    <div class="flex items-center gap-3 min-w-0">
        <div class="flex items-center gap-2 bg-slate-100 rounded-full pl-1 pr-3 py-1 rtl:pl-3 rtl:pr-1">
            <span class="w-7 h-7 rounded-full bg-indigo-600 text-white flex items-center justify-center text-xs font-semibold">
                <?php echo e(mb_substr(auth()->user()->username ?? 'U', 0, 1)); ?>

            </span>
            <div class="leading-tight">
                <p class="text-sm font-medium text-slate-800"><?php echo e(auth()->user()->username); ?></p>
                <p class="text-[11px] text-slate-400"><?php echo e(auth()->user()->email ?? auth()->user()->phone); ?></p>
            </div>
        </div>
        <h1 class="hidden md:block text-base font-semibold text-slate-700 border-s ps-3 border-slate-200"><?php echo $__env->yieldContent('title', __('admin.dashboard.title')); ?></h1>
    </div>

    <div class="flex items-center gap-3">
        <!-- Language switcher -->
        <div class="relative group">
            <button type="button" class="flex items-center gap-1.5 bg-slate-100 hover:bg-slate-200 transition rounded-full px-3 py-1.5 text-sm font-medium text-slate-600">
                🌐 <?php echo e(strtoupper(app()->getLocale())); ?>

            </button>
            <div class="absolute end-0 mt-1 w-32 bg-white rounded-lg shadow-lg border border-slate-100 py-1 hidden group-hover:block z-20">
                <a href="<?php echo e(route('locale.switch', 'en')); ?>" class="block px-4 py-2 text-sm hover:bg-slate-50 <?php echo e(app()->getLocale() === 'en' ? 'text-indigo-600 font-medium' : 'text-slate-600'); ?>">
                    <?php echo e(__('admin.common.english')); ?>

                </a>
                <a href="<?php echo e(route('locale.switch', 'ar')); ?>" class="block px-4 py-2 text-sm hover:bg-slate-50 <?php echo e(app()->getLocale() === 'ar' ? 'text-indigo-600 font-medium' : 'text-slate-600'); ?>">
                    <?php echo e(__('admin.common.arabic')); ?>

                </a>
            </div>
        </div>

        <a href="<?php echo e(route('admin.notifications.index')); ?>"
           class="relative flex items-center gap-1.5 bg-slate-100 hover:bg-slate-200 transition rounded-full px-3 py-1.5 text-sm font-medium text-slate-600">
            🔔 <?php echo e(__('admin.topbar.notifications')); ?>

            <?php if(auth()->user()->unreadNotifications()->count()): ?>
                <span class="absolute -top-1.5 -end-1.5 bg-red-500 text-white text-[10px] rounded-full w-4 h-4 flex items-center justify-center">
                    <?php echo e(auth()->user()->unreadNotifications()->count()); ?>

                </span>
            <?php endif; ?>
        </a>

        <form method="POST" action="<?php echo e(route('admin.logout')); ?>">
            <?php echo csrf_field(); ?>
            <button class="bg-slate-900 hover:bg-slate-800 transition text-white rounded-full px-4 py-1.5 text-sm font-medium">
                <?php echo e(__('admin.topbar.logout')); ?>

            </button>
        </form>
    </div>
</header>
<?php /**PATH /home/faisal/new dev/DEV/Naqi/naqi-ess-prod/resources/views/components/navbar.blade.php ENDPATH**/ ?>