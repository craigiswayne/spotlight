
<?php $__env->startSection('content'); ?>
    

    <?php if(Setting::Boolean('carousel')): ?>
        <card-slider :navigations="<?php echo e($navigations); ?>"></card-slider>
    <?php else: ?>
        <landing-page :navigations="<?php echo e($navigations); ?>"></landing-page>
    <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH M:\Websites\spotlight\v1.9.0.736-rebranding\website\resources\views/app.blade.php ENDPATH**/ ?>