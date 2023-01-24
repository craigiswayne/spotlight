

<?php $__env->startSection('content'); ?>
    <div class="container">
        <div class="row">
            <div class="col">
                <admin-sortable-pages securable-name="Play It Forward" title="title" category="<?php echo e($pages->first()->category); ?>" :pages="<?php echo e($pages); ?>"></admin-sortable-pages>
            </div>
        </div>
        <?php $__currentLoopData = $pages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="row">
                <div class="col">
                    <admin-sortable-resources securable-name="Play It Forward" thumbnail-size="45%" title="<?php echo e($page->title); ?>" base-url="/admin/<?php echo e($page->category); ?>" type="<?php echo e($page->title); ?>" :belongs_to="<?php echo e($page->id); ?>" :items="<?php echo e($page->resources()); ?>"></admin-sortable-resources>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH M:\Websites\spotlight\v1.9.0.736-rebranding\website\resources\views/admin/pages/index-category.blade.php ENDPATH**/ ?>