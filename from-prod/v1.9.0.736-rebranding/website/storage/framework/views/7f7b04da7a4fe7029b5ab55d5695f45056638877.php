

<?php $__env->startSection('content'); ?>
    <view-profiles :user="<?php echo e($user); ?>" :profiles="<?php echo e($profiles); ?>"></view-profiles>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH M:\Websites\spotlight\v1.9.0.736-rebranding\website\resources\views/admin/profile/index.blade.php ENDPATH**/ ?>