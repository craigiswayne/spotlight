

<?php $__env->startSection('content'); ?>
    <div class="container">
        <div class="card">
            <div class="card-header card-header-primary card-header-icon">
                <div class="card-icon">
                    <i class="material-icons">public</i>
                </div>
                <h4 class="card-title">
                    Markets

                    <?php if(Secure::hasAdminAccess('Markets|Add')): ?>
                    <div class="float-right">
                        <form class="form-inline" method="POST" action="/admin/markets">
                            <?php echo csrf_field(); ?>
                            <countries-select :countries="<?php echo e($countries); ?>"></countries-select>
                            <button type="submit" class="btn btn-primary btn-sm ml-4">Add Market</button>
                        </form>
                    </div>
                    <?php endif; ?>
                </h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <admin-regulated-markets-index :markets="<?php echo e($markets); ?>"></admin-regulated-markets-index>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH M:\Websites\spotlight\v1.9.0.736-rebranding\website\resources\views/admin/regulated-markets/index.blade.php ENDPATH**/ ?>