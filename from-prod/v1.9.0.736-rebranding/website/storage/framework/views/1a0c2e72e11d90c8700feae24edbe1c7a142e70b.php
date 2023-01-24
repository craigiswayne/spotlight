
<?php $__env->startSection('content'); ?>
    <add-edit-game :game="<?php echo e($game); ?>" :next-game="<?php echo e($nextGame ? $nextGame : 'null'); ?>" :previous-game="<?php echo e($previousGame ? $previousGame : 'null'); ?>" :features='<?php echo json_encode($features, 15, 512) ?>' :maths='<?php echo json_encode($maths, 15, 512) ?>'></add-edit-game>
<?php $__env->stopSection(); ?>



<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH M:\Websites\spotlight\v1.9.0.736-rebranding\website\resources\views/admin/games/show.blade.php ENDPATH**/ ?>