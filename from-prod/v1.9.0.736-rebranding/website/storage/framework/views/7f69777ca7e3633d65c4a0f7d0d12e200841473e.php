

<?php $__env->startSection('content'); ?>
    <manage-users :current-user="<?php echo e($currentUser); ?>" :users="<?php echo e($users); ?>"></manage-users>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH M:\Websites\spotlight\v1.9.0.736-rebranding\website\resources\views/admin/security/users/index.blade.php ENDPATH**/ ?>