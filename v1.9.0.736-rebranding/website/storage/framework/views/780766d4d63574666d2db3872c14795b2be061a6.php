<div class="container">
<?php if($errors->{$bag ?? 'default'}->any()): ?>
    <div class="alert alert-danger w-100 alert-dismissible fade show fadeout-5">
        <?php $__currentLoopData = $errors->{$bag ?? 'default'}->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <span class="d-block"><?php echo e($error); ?></span>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<?php if(session()->has('success')): ?>
    <div class="alert alert-success w-100 alert-dismissible fade show fadeout-5">
        <?php echo e(session()->get('success')); ?>

        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<?php if(session()->has('error')): ?>
    <div class="alert alert-danger w-100 alert-dismissible fade show fadeout-7">
        <?php echo e(session()->get('error')); ?>

        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<?php if(session()->has('warning')): ?>
    <div class="alert alert-warning w-100 alert-dismissible fade show fadeout-7">
        <?php echo e(session()->get('warning')); ?>

        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<?php if(session()->has('info')): ?>
    <div class="alert alert-primary w-100 alert-dismissible fade show fadeout-5">
        <?php echo e(session()->get('info')); ?>

        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>
</div><?php /**PATH M:\Websites\spotlight\v1.9.0.736-rebranding\website\resources\views/layouts/form-response.blade.php ENDPATH**/ ?>