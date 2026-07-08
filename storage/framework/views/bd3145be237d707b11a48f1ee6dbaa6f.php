<!DOCTYPE html>
<html lang="<?php echo e(app()->getLocale()); ?>" dir="<?php echo e(app()->getLocale() === 'ar' ? 'rtl' : 'ltr'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e(__('admin.auth.login_title')); ?> - <?php echo e(__('admin.app_name')); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Cairo:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: <?php echo e(app()->getLocale() === 'ar' ? "'Cairo'" : "'Inter'"); ?>, sans-serif; }
    </style>
</head>
<body class="bg-slate-900 min-h-screen flex items-center justify-center">
    <div class="absolute top-6 end-6">
        <div class="relative group inline-block">
            <button type="button" class="flex items-center gap-1.5 bg-slate-800 hover:bg-slate-700 transition rounded-full px-3 py-1.5 text-sm font-medium text-slate-200">
                🌐 <?php echo e(strtoupper(app()->getLocale())); ?>

            </button>
            <div class="absolute end-0 mt-1 w-32 bg-white rounded-lg shadow-lg border border-slate-100 py-1 hidden group-hover:block z-20">
                <a href="<?php echo e(route('locale.switch', 'en')); ?>" class="block px-4 py-2 text-sm hover:bg-slate-50 text-slate-600"><?php echo e(__('admin.common.english')); ?></a>
                <a href="<?php echo e(route('locale.switch', 'ar')); ?>" class="block px-4 py-2 text-sm hover:bg-slate-50 text-slate-600"><?php echo e(__('admin.common.arabic')); ?></a>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-8">
        <div class="text-center mb-8">
            <div class="w-12 h-12 mx-auto rounded-xl bg-indigo-600 flex items-center justify-center text-white font-bold text-lg mb-3">
                <?php echo e(mb_substr(__('admin.app_name'), 0, 1)); ?>

            </div>
            <h1 class="text-2xl font-bold text-slate-800"><?php echo e(__('admin.app_name')); ?></h1>
            <p class="text-slate-400 text-sm mt-1"><?php echo e(__('admin.auth.login_title')); ?></p>
        </div>

        <?php if($errors->any()): ?>
            <div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm">
                <?php echo e($errors->first()); ?>

            </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo e(route('admin.login.attempt')); ?>" class="space-y-4">
            <?php echo csrf_field(); ?>
            <div>
                <label class="block text-sm font-medium text-slate-600 mb-1"><?php echo e(__('admin.auth.login_field')); ?></label>
                <input type="text" name="login" value="<?php echo e(old('login')); ?>" required autofocus
                       class="w-full rounded-lg border border-slate-300 px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-600 mb-1"><?php echo e(__('admin.auth.password')); ?></label>
                <input type="password" name="password" required
                       class="w-full rounded-lg border border-slate-300 px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>
            <label class="flex items-center gap-2 text-sm text-slate-500">
                <input type="checkbox" name="remember"> <?php echo e(__('admin.auth.remember_me')); ?>

            </label>
            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2.5 rounded-lg transition">
                <?php echo e(__('admin.auth.sign_in')); ?>

            </button>
        </form>
    </div>
</body>
</html>
<?php /**PATH /home/faisal/new dev/DEV/Naqi/naqi-ess-full/resources/views/auth/login.blade.php ENDPATH**/ ?>