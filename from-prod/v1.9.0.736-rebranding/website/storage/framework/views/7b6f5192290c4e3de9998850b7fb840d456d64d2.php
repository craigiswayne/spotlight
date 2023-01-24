<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
 
    <title><?php echo e(config('app.name', 'Spotlight')); ?> <?php echo e(WEBSITE_VERSION); ?></title>

    <link href="/app.build.css?<?php echo e(BUILD_VERSION); ?>" rel='stylesheet'>
    <script src="/shell.build.js?<?php echo e(BUILD_VERSION); ?>" defer></script>
    <script src="/app.build.js?<?php echo e(BUILD_VERSION); ?>" defer></script>
                
    
    <link rel="icon" href="/favicon.png">
</head>
<?php 
$permissions = isset(auth()->user()->permissions['Normal']) ? auth()->user()->permissions['Normal'] : '[]';
$userTypeId = auth()->user()->role->typeId;
$settings = \App\ProfileSetting::settings();
?>
<body class="<?php echo e(Setting::Boolean('particles') ? 'particles-enabled': ''); ?>">
    <script>
        window.user = {permissions: JSON.parse('<?php echo $permissions; ?>')};        
        window.user.typeId = <?php echo $userTypeId; ?>;
        window.offline =  <?php echo is_export() ? 'true' : 'false'; ?>;
        window.settings = <?php echo $settings; ?>;
    </script>

    <div id="app">        
        <?php echo $__env->yieldContent('content'); ?>
    </div>	
</body>
</html>
<?php /**PATH M:\Websites\spotlight\v1.9.0.736-rebranding\website\resources\views/layouts/app.blade.php ENDPATH**/ ?>