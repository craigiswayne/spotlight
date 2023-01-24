

<?php $__env->startSection('content'); ?>
    <div class="container">
        <?php if($providers->count() == 0): ?>
        <div class="mt-3 pt-3 position-relative">
            <admin-sortable-resources securable-name="Third Party Providers" view="minimal" enable-category="true" title="Third Party Providers" type="third-party-providers" belongs_to="<?php echo e(now()->format('Y')); ?>" :items="[]"></admin-sortable-resources>
        </div>
        <?php endif; ?>
        <?php $__currentLoopData = $providers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $year => $items): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <admin-sortable-resources securable-name="Third Party Providers" enable-category="true" title="<?php echo e($year.' Third Party Providers'); ?>" type="third-party-providers" belongs_to="<?php echo e($year); ?>" :items="<?php echo e($providers[$year]); ?>"></admin-sortable-resources>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH M:\Websites\spotlight\v1.9.0.736-rebranding\website\resources\views/admin/resource-third-party-providers.blade.php ENDPATH**/ ?>