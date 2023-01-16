

<?php $__env->startSection('content'); ?>
      
    <div class="container">
        <div class="mt-3 pt-3 position-relative">
            <admin-sortable-resources
                href="false"
                securable-name="Pages"                
                thumbnail-size="250px"
                view="minimal"
                title="Pages"                 
                belongs_to="0" 
                :items="<?php echo e($navigations); ?>"
                asset-property="thumbnail"                
                type="pages"
                :can-add="false"
                :can-delete="false"
                :dark="true"
            ></admin-sortable-resources>
        </div>
    </div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH M:\Websites\spotlight\v1.9.0.736-rebranding\website\resources\views/admin/navigation/index.blade.php ENDPATH**/ ?>