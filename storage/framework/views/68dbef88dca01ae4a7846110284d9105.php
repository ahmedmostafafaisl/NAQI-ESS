<?php $__env->startSection('title', __('admin.dynamics.title')); ?>

<?php $__env->startSection('content'); ?>
    <div class="max-w-2xl space-y-6">

        <div class="flex justify-end">
            <a href="<?php echo e(route('admin.dynamics.attendance.index')); ?>" class="text-sm text-indigo-600 hover:underline">
                <?php echo e(__('admin.dynamics.go_to_attendance')); ?> →
            </a>
        </div>

        <!-- Panel 1: app-level connection (Azure AD client credentials) -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-slate-700 mb-1"><?php echo e(__('admin.dynamics.test_title')); ?></h3>
            <p class="text-sm text-slate-500 mb-4"><?php echo e(__('admin.dynamics.test_description')); ?></p>

            <form method="POST" action="<?php echo e(route('admin.dynamics.test')); ?>">
                <?php echo csrf_field(); ?>
                <button class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-lg text-sm">
                    <?php echo e(__('admin.dynamics.test_button')); ?>

                </button>
            </form>
        </div>

        <?php if(isset($result)): ?>
            <?php if($result['success']): ?>
                <div class="bg-white rounded-xl shadow-sm border-2 border-emerald-500 p-6 space-y-3">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                        <h3 class="font-semibold text-emerald-700"><?php echo e(__('admin.dynamics.success_title')); ?></h3>
                    </div>

                    <table class="w-full text-sm">
                        <tr class="border-t border-slate-100">
                            <td class="py-2 text-slate-500"><?php echo e(__('admin.dynamics.token_type')); ?></td>
                            <td class="py-2 font-mono text-slate-700"><?php echo e($result['token_type']); ?></td>
                        </tr>
                        <tr class="border-t border-slate-100">
                            <td class="py-2 text-slate-500"><?php echo e(__('admin.dynamics.token_preview')); ?></td>
                            <td class="py-2 font-mono text-slate-700"><?php echo e($result['access_token_preview']); ?></td>
                        </tr>
                        <tr class="border-t border-slate-100">
                            <td class="py-2 text-slate-500"><?php echo e(__('admin.dynamics.expires_in')); ?></td>
                            <td class="py-2 text-slate-700"><?php echo e($result['expires_in']); ?> <?php echo e(__('admin.dynamics.seconds')); ?></td>
                        </tr>
                        <tr class="border-t border-slate-100">
                            <td class="py-2 text-slate-500"><?php echo e(__('admin.dynamics.expires_at')); ?></td>
                            <td class="py-2 text-slate-700"><?php echo e($result['expires_at']); ?></td>
                        </tr>
                        <tr class="border-t border-slate-100">
                            <td class="py-2 text-slate-500"><?php echo e(__('admin.dynamics.cached_until')); ?></td>
                            <td class="py-2 text-slate-700"><?php echo e($result['cached_until']); ?></td>
                        </tr>
                    </table>
                    <p class="text-xs text-slate-400"><?php echo e(__('admin.dynamics.success_hint')); ?></p>
                </div>
            <?php else: ?>
                <div class="bg-white rounded-xl shadow-sm border-2 border-red-500 p-6 space-y-2">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-red-500"></span>
                        <h3 class="font-semibold text-red-700"><?php echo e(__('admin.dynamics.failure_title')); ?></h3>
                    </div>
                    <p class="text-sm text-red-600 font-mono break-all"><?php echo e($result['error']); ?></p>
                    <p class="text-xs text-slate-400"><?php echo e(__('admin.dynamics.failure_hint')); ?></p>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Panel 2: user login (INDXNaqiEssAuthSvc/Login) -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-slate-700 mb-1"><?php echo e(__('admin.dynamics.login_test_title')); ?></h3>
            <p class="text-sm text-slate-500 mb-4"><?php echo e(__('admin.dynamics.login_test_description')); ?></p>

            <form method="POST" action="<?php echo e(route('admin.dynamics.test-login')); ?>" class="space-y-3">
                <?php echo csrf_field(); ?>
                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-1"><?php echo e(__('admin.common.email')); ?></label>
                    <input type="email" name="test_email" value="<?php echo e(old('test_email')); ?>" required
                        class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-1"><?php echo e(__('admin.common.password')); ?></label>
                    <input type="password" name="test_password" required
                        class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label
                            class="block text-sm font-medium text-slate-600 mb-1"><?php echo e(__('admin.common.language')); ?></label>
                        <select name="test_lang" class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm">
                            <option value=""><?php echo e(__('admin.dynamics.use_default_lang')); ?>

                                (<?php echo e(config('dynamics365.default_lang')); ?>)</option>
                            <option value="en-us" <?php if(old('test_lang') === 'en-us'): echo 'selected'; endif; ?>>en-us</option>
                            <option value="ar-sa" <?php if(old('test_lang') === 'ar-sa'): echo 'selected'; endif; ?>>ar-sa</option>
                        </select>
                    </div>
                    <div>
                        <label
                            class="block text-sm font-medium text-slate-600 mb-1"><?php echo e(__('admin.dynamics.device_token')); ?></label>
                        <input type="text" name="test_device_token" value="<?php echo e(old('test_device_token')); ?>"
                            placeholder="<?php echo e(__('admin.dynamics.device_token_optional')); ?>"
                            class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm font-mono">
                    </div>
                </div>
                <?php $__errorArgs = ['test_email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <p class="text-xs text-red-600"><?php echo e($message); ?></p>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                <button class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-lg text-sm">
                    <?php echo e(__('admin.dynamics.login_test_button')); ?>

                </button>
            </form>
        </div>

        <?php if(isset($loginResult)): ?>
            <?php if($loginResult['success']): ?>
                <div class="bg-white rounded-xl shadow-sm border-2 border-emerald-500 p-6 space-y-3">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                        <h3 class="font-semibold text-emerald-700"><?php echo e(__('admin.dynamics.login_success_title')); ?></h3>
                    </div>

                    <table class="w-full text-sm">
                        <tr class="border-t border-slate-100">
                            <td class="py-2 text-slate-500"><?php echo e(__('admin.dynamics.worker')); ?></td>
                            <td class="py-2 font-mono text-slate-700"><?php echo e($loginResult['worker'] ?? '—'); ?></td>
                        </tr>
                        <tr class="border-t border-slate-100">
                            <td class="py-2 text-slate-500"><?php echo e(__('admin.dynamics.is_manager')); ?></td>
                            <td class="py-2 text-slate-700">
                                <?php echo e($loginResult['is_manager'] ? __('admin.common.active') : __('admin.common.inactive')); ?></td>
                        </tr>
                        <tr class="border-t border-slate-100">
                            <td class="py-2 text-slate-500"><?php echo e(__('admin.dynamics.first_login')); ?></td>
                            <td class="py-2 text-slate-700"><?php echo e($loginResult['first_login'] ? 'true' : 'false'); ?></td>
                        </tr>
                        <tr class="border-t border-slate-100">
                            <td class="py-2 text-slate-500"><?php echo e(__('admin.dynamics.session_token_preview')); ?></td>
                            <td class="py-2 font-mono text-slate-700">
                                <?php echo e($loginResult['token'] ? substr($loginResult['token'], 0, 8) . '...' : '—'); ?></td>
                        </tr>
                        <tr class="border-t border-slate-100">
                            <td class="py-2 text-slate-500"><?php echo e(__('admin.dynamics.services_access_list')); ?></td>
                            <td class="py-2 text-slate-700"><?php echo e(count($loginResult['services_access_list'])); ?></td>
                        </tr>
                    </table>
                    <p class="text-xs text-slate-400"><?php echo e(__('admin.dynamics.login_success_hint')); ?></p>
                </div>
            <?php else: ?>
                <div class="bg-white rounded-xl shadow-sm border-2 border-red-500 p-6 space-y-2">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-red-500"></span>
                        <h3 class="font-semibold text-red-700"><?php echo e(__('admin.dynamics.login_failure_title')); ?></h3>
                    </div>
                    <p class="text-sm text-red-600 font-mono break-all"><?php echo e($loginResult['error']); ?></p>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Panel 3: get team members (INDXNaqiEssActionMyTeamSvc/getWorkerTeam) -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-slate-700 mb-1"><?php echo e(__('admin.dynamics.team_test_title')); ?></h3>
            <p class="text-sm text-slate-500 mb-4"><?php echo e(__('admin.dynamics.team_test_description')); ?></p>

            <form method="POST" action="<?php echo e(route('admin.dynamics.test-team-members')); ?>" class="space-y-3">
                <?php echo csrf_field(); ?>
                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-1"><?php echo e(__('admin.common.email')); ?></label>
                    <input type="email" name="team_email" value="<?php echo e(old('team_email')); ?>" required
                        class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm">
                </div>
                <div>
                    <label
                        class="block text-sm font-medium text-slate-600 mb-1"><?php echo e(__('admin.dynamics.session_token')); ?></label>
                    <input type="text" name="team_token" value="<?php echo e(old('team_token')); ?>" required
                        placeholder="<?php echo e(__('admin.dynamics.session_token_hint')); ?>"
                        class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm font-mono">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-1"><?php echo e(__('admin.common.language')); ?></label>
                    <select name="team_lang" class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm">
                        <option value=""><?php echo e(__('admin.dynamics.use_default_lang')); ?>

                            (<?php echo e(config('dynamics365.default_lang')); ?>)</option>
                        <option value="en-us" <?php if(old('team_lang') === 'en-us'): echo 'selected'; endif; ?>>en-us</option>
                        <option value="ar-sa" <?php if(old('team_lang') === 'ar-sa'): echo 'selected'; endif; ?>>ar-sa</option>
                    </select>
                </div>
                <?php $__errorArgs = ['team_email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <p class="text-xs text-red-600"><?php echo e($message); ?></p>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                <button class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-lg text-sm">
                    <?php echo e(__('admin.dynamics.team_test_button')); ?>

                </button>
            </form>
        </div>

        <?php if(isset($teamResult)): ?>
            <?php if($teamResult['success']): ?>
                <div class="bg-white rounded-xl shadow-sm border-2 border-emerald-500 p-6 space-y-4">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                        <h3 class="font-semibold text-emerald-700"><?php echo e(__('admin.dynamics.team_success_title')); ?></h3>
                        <span
                            class="ms-auto text-xs px-2 py-1 rounded-full <?php echo e($teamResult['is_manager'] ? 'bg-indigo-100 text-indigo-600' : 'bg-slate-100 text-slate-500'); ?>">
                            <?php echo e($teamResult['is_manager'] ? __('admin.dynamics.is_manager') : __('admin.dynamics.not_manager')); ?>

                        </span>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase text-slate-400 mb-2"><?php echo e(__('admin.dynamics.my_team')); ?>

                            (<?php echo e(count($teamResult['team'])); ?>)</p>
                        <table class="w-full text-sm">
                            <thead class="text-slate-400 text-xs">
                                <tr>
                                    <th class="text-start font-normal pb-1"><?php echo e(__('admin.common.name')); ?></th>
                                    <th class="text-start font-normal pb-1"><?php echo e(__('admin.dynamics.position')); ?></th>
                                    <th class="text-start font-normal pb-1"><?php echo e(__('admin.dynamics.personnel_number')); ?></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php $__empty_1 = true; $__currentLoopData = $teamResult['team']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $member): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td class="py-1.5"><?php echo e($member['name'] ?: '—'); ?></td>
                                        <td class="py-1.5"><?php echo e($member['position'] ?: '—'); ?></td>
                                        <td class="py-1.5 font-mono"><?php echo e($member['personnel_number'] ?: '—'); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="3" class="py-2 text-slate-400"><?php echo e(__('admin.common.no_results')); ?></td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase text-slate-400 mb-2"><?php echo e(__('admin.dynamics.my_managers')); ?>

                            (<?php echo e(count($teamResult['managers'])); ?>)</p>
                        <table class="w-full text-sm">
                            <tbody class="divide-y divide-slate-100">
                                <?php $__empty_1 = true; $__currentLoopData = $teamResult['managers']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $manager): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td class="py-1.5"><?php echo e($manager['name'] ?: '—'); ?></td>
                                        <td class="py-1.5"><?php echo e($manager['position'] ?: '—'); ?></td>
                                        <td class="py-1.5 font-mono"><?php echo e($manager['personnel_number'] ?: '—'); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="3" class="py-2 text-slate-400"><?php echo e(__('admin.common.no_results')); ?></td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php else: ?>
                <div class="bg-white rounded-xl shadow-sm border-2 border-red-500 p-6 space-y-2">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-red-500"></span>
                        <h3 class="font-semibold text-red-700"><?php echo e(__('admin.dynamics.team_failure_title')); ?></h3>
                    </div>
                    <p class="text-sm text-red-600 font-mono break-all"><?php echo e($teamResult['error']); ?></p>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/faisal/new dev/DEV/Naqi/naqi-ess-full/resources/views/dynamics/test.blade.php ENDPATH**/ ?>