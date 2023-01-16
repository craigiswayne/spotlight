
<?php $__env->startSection('content'); ?>
    <add-edit-game :features='<?php echo json_encode($features, 15, 512) ?>'></add-edit-game>
<?php $__env->stopSection(); ?>



<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH M:\Websites\spotlight\v1.9.0.736-rebranding\website\resources\views/admin/games/add.blade.php ENDPATH**/ ?>