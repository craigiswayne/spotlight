

<?php $__env->startSection('content'); ?>
    <div class="container">
        <div class="mt-3 pt-3 position-relative">
            <admin-sortable-resources
                securable-name="<?php echo e($securable); ?>"
                href="<?php echo e($type == 'navigation-cards' ? 'true':'false'); ?>"
                thumbnail-size="<?php echo e($thumbnail_size); ?>"
                view="minimal"
                title="<?php echo e(str_replace('-',' ', $type)); ?>" 
                type="<?php echo e($type); ?>"
                belongs_to="0" 
                :items="<?php echo e($resources); ?>"
            ></admin-sortable-resources>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH M:\Websites\spotlight\v1.9.0.736-rebranding\website\resources\views/admin/resource-default.blade.php ENDPATH**/ ?>