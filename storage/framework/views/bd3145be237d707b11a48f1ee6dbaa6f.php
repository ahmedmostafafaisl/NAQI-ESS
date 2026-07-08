<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Naqi ESS</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-900 min-h-screen flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-8">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-slate-800">Naqi <span class="text-indigo-600">ESS</span></h1>
            <p class="text-slate-400 text-sm mt-1">Admin Dashboard</p>
        </div>

        <?php if($errors->any()): ?>
            <div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm">
                <?php echo e($errors->first()); ?>

            </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo e(route('admin.login.attempt')); ?>" class="space-y-4">
            <?php echo csrf_field(); ?>
            <div>
                <label class="block text-sm font-medium text-slate-600 mb-1">Email or Username</label>
                <input type="text" name="login" value="<?php echo e(old('login')); ?>" required autofocus
                       class="w-full rounded-lg border border-slate-300 px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-600 mb-1">Password</label>
                <input type="password" name="password" required
                       class="w-full rounded-lg border border-slate-300 px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>
            <label class="flex items-center gap-2 text-sm text-slate-500">
                <input type="checkbox" name="remember"> Remember me
            </label>
            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2.5 rounded-lg transition">
                Sign in
            </button>
        </form>
    </div>
</body>
</html>
<?php /**PATH /home/faisal/new dev/DEV/Naqi/naqi-ess-full/resources/views/auth/login.blade.php ENDPATH**/ ?>