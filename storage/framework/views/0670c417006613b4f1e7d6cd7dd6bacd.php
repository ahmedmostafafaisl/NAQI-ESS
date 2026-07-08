<header class="bg-white border-b border-slate-200 px-6 py-4 flex items-center justify-between">
    <h1 class="text-lg font-semibold text-slate-800"><?php echo $__env->yieldContent('title', 'Dashboard'); ?></h1>
    <div class="flex items-center gap-4">
        <a href="<?php echo e(route('admin.notifications.index')); ?>" class="relative text-slate-500 hover:text-slate-700">
            🔔
            <?php if(auth()->user()->unreadNotifications()->count()): ?>
                <span class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] rounded-full w-4 h-4 flex items-center justify-center">
                    <?php echo e(auth()->user()->unreadNotifications()->count()); ?>

                </span>
            <?php endif; ?>
        </a>
        <div class="text-sm text-right">
            <p class="font-medium text-slate-800"><?php echo e(auth()->user()->username); ?></p>
            <p class="text-slate-400"><?php echo e(auth()->user()->getRoleNames()->first()); ?></p>
        </div>
    </div>
</header>
<?php /**PATH /home/faisal/new dev/DEV/Naqi/naqi-ess-full/resources/views/components/navbar.blade.php ENDPATH**/ ?>