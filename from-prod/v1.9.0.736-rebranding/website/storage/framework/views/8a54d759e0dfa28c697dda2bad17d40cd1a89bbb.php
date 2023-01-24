

<?php $__env->startSection('content'); ?>
    <div class="container">
        <div class="row">
            <div class="col">
                <admin-products-index :page="<?php echo e($page); ?>" :products="<?php echo e($products); ?>"></admin-products-index>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH M:\Websites\spotlight\v1.9.0.736-rebranding\website\resources\views/admin/products/index.blade.php ENDPATH**/ ?>