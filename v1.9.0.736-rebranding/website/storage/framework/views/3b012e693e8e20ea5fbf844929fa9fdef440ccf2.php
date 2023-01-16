

<?php $__env->startSection('content'); ?>
    <div class="container">
        <div class="position-relative mt-3 mb-5 pb-3">
            <?php if(Secure::hasAdminAccess('Studios|Add')): ?>
            <button style="top:10px; right: 30px;" data-toggle="modal" data-target="#add_studio" class="position-absolute btn btn-primary btn-round">
                <i class="material-icons mr-1">add_photo_alternate</i> Add New Studio
            </button>
            <?php endif; ?>
        </div>
        <div id="<?php echo e(Secure::hasAdminAccess('Studios|Edit') ? 'studios_sortable' : 'studios_static'); ?>" class="row m-auto mt-5 pt-3">
            <?php $__currentLoopData = $studios; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $studio): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="col-6" data-id="<?php echo e($studio->id); ?>">

                <div class="card dark p-3 admin-studios">
                    <div class="row">
                        <div class="col">
                            <image-upload securable-name="Studios" width="285px" image_path="<?php echo e($studio->image); ?>" route="/admin/studios/<?php echo e($studio->id); ?>" name="image" method="patch" :dark-background="true"></image-upload>
                        </div>
                        <div class="col">
                            <video-upload securable-name="Studios" video_path="<?php echo e($studio->video); ?>" route="/admin/studios/<?php echo e($studio->id); ?>" name="video" method="patch" :dark-background="true"></video-upload>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <text-upload securable-name="Studios" studio_name="<?php echo e($studio->name); ?>" route="/admin/studios/<?php echo e($studio->id); ?>" name="studio_name" method="patch" :dark-background="true"></text-upload>
                        </div>
						<?php if(Secure::hasAdminAccess('Studios|Delete')): ?>
                        <div class="col">
                            <a class="delete-item btn btn-sm btn-danger p-2 float-right" data-id="<?php echo e($studio->id); ?>" data-route="/admin/studios/<?php echo e($studio->id); ?>" href="#">Delete Studio</a>
                        </div>
						<?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <div class="row">
            <div class="col">
                <div class="float-right"><?php echo e($studios->links()); ?></div>
            </div>
        </div>

        <?php if(Secure::hasAdminAccess('Studios|Add')): ?>
        <div class="modal fade" id="add_studio" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content text-center">
                    <form method="POST" class="d-inline" enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>
                        <div class="fileinput fileinput-new text-center mb-5" data-provides="fileinput">
                            <h3>Add New Studio</h3>
                            <p class="fileinput-preview fileinput-exists mw-200 m-auto"></p>
                            <div>
                                <span class="btn btn-raised btn-default btn-file btn-sm">
                                    <span class="fileinput-new">Upload Image</span>
                                    <span class="fileinput-exists">Change</span>
                                    <input required type="file" accept=".png,.jpg,.jpeg,.svg" name="image" />
                                </span>
                                <a href="#" class="btn btn-danger fileinput-exists btn-sm" data-dismiss="fileinput"><i class="fa fa-times"></i> Discard</a>
                                <button type="submit" class="btn btn-primary btn-sm">Save</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH M:\Websites\spotlight\v1.9.0.736-rebranding\website\resources\views/admin/studios/index.blade.php ENDPATH**/ ?>