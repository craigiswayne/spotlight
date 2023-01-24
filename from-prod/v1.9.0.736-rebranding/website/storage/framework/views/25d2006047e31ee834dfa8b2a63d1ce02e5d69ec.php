

<?php $__env->startSection('content'); ?>
    <admin-games-list securable-name="Games"

                      :new-featured-games="<?php echo e($games->newFeaturedGames); ?>"
                      :new-non-featured-games="<?php echo e($games->newNonFeaturedGames); ?>"
                      :not-new-featured-games="<?php echo e($games->notNewFeaturedGames); ?>"
                      :not-new-non-featured-games="<?php echo e($games->notNewNonFeaturedGames); ?>">
    </admin-games-list>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH M:\Websites\spotlight\v1.9.0.736-rebranding\website\resources\views/admin/games/index.blade.php ENDPATH**/ ?>