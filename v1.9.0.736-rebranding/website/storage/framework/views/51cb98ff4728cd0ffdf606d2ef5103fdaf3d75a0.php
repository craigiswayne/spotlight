
<?php $__env->startSection('content'); ?>
    <new-games :new-featured-games="<?php echo e($newFeaturedGames); ?>" :new-non-featured-games="<?php echo e($newNonFeaturedGames); ?>" :featured-games="<?php echo e($featuredGames); ?>"></new-games>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH M:\Websites\spotlight\v1.9.0.736-rebranding\website\resources\views/games-new.blade.php ENDPATH**/ ?>