

<?php $__env->startSection('content'); ?>
    <add-edit-profile :user="<?php echo e($user); ?>" :navigations="<?php echo e($navigations); ?>" :setting-types="<?php echo e($settings); ?>"></add-edit-profile>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH M:\Websites\spotlight\v1.9.0.736-rebranding\website\resources\views/admin/profile/add.blade.php ENDPATH**/ ?>