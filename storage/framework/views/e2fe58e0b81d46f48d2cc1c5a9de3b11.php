<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['variant' => 'view', 'href' => null, 'type' => 'link']));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['variant' => 'view', 'href' => null, 'type' => 'link']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>

<?php
    $variants = [
        'delete' => 'text-red-600 border-red-300 hover:bg-red-50',
        'edit' => 'text-blue-600 border-blue-300 hover:bg-blue-50',
        'view' => 'text-slate-600 border-slate-300 hover:bg-slate-50',
    ];
    $classes = 'inline-flex items-center justify-center px-4 py-1.5 rounded-full text-xs font-semibold border transition whitespace-nowrap '
        . ($variants[$variant] ?? $variants['view']);
?>

<?php if($type === 'link'): ?>
    <a href="<?php echo e($href); ?>" <?php echo e($attributes->merge(['class' => $classes])); ?>><?php echo e($slot); ?></a>
<?php else: ?>
    <button type="<?php echo e($type); ?>" <?php echo e($attributes->merge(['class' => $classes])); ?>><?php echo e($slot); ?></button>
<?php endif; ?>
<?php /**PATH /home/faisal/new dev/DEV/Naqi/naqi-ess-prod/resources/views/components/action-button.blade.php ENDPATH**/ ?>