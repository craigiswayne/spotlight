<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
 
    <title>{{ config('app.name', 'Spotlight') }} {{ WEBSITE_VERSION }}</title>

    <link href="/app.build.css?{{ BUILD_VERSION }}" rel='stylesheet'>
    <script src="/shell.build.js?{{ BUILD_VERSION }}" defer></script>
    <script src="/app.build.js?{{ BUILD_VERSION }}" defer></script>
                
    
    <link rel="icon" href="/favicon.png">
</head>
<?php 
$permissions = isset(auth()->user()->permissions['Normal']) ? auth()->user()->permissions['Normal'] : '[]';
$userTypeId = auth()->user()->role->typeId;
$settings = \App\ProfileSetting::settings();
?>
<body class="{{ Setting::Boolean('particles') ? 'particles-enabled': '' }}">
    <script>
        window.user = {permissions: JSON.parse('{!! $permissions !!}')};        
        window.user.typeId = <?php echo $userTypeId; ?>;
        window.offline =  <?php echo is_export() ? 'true' : 'false'; ?>;
        window.settings = <?php echo $settings; ?>;
    </script>

    <div id="app">        
        @yield('content')
    </div>	
</body>
</html>
