<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <title><?php echo e(config('app.name', 'Spotlight')); ?> Admin <?php echo e(WEBSITE_VERSION); ?></title>

    <link href="/admin.build.css?<?php echo e(BUILD_VERSION); ?>" rel='stylesheet'>
    <script src="/shell.build.js?<?php echo e(BUILD_VERSION); ?>" defer></script>
    <script src="/admin.build.js?<?php echo e(BUILD_VERSION); ?>" defer></script>

    <link rel="icon" href="/favicon.png">
</head>
<?php
$permissions = isset(auth()->user()->permissions['Admin']) ? auth()->user()->permissions['Admin'] : '[]';
$userTypeId = auth()->user()->role->typeId;
?>
<body>
    <script>
        window.user = {permissions: JSON.parse('<?php echo $permissions; ?>')};
        window.user.typeId = <?php echo $userTypeId; ?>;
    </script>

    <div id="app" class="wrapper">
        <?php if(Auth::check()): ?>
        <div class="sidebar" data-background-color="black">
            <sidebar-loader></sidebar-loader>
            <div class="logo text-center mb-4">
                <a href="/admin/" class="simple-text logo-mini">
                    <img width="28" src="<?php echo e(asset('/assets/img/gg-symbol.png')); ?>" alt="image">
                </a>
                <a href="/admin/" class="simple-text logo-normal" style="text-align: initial;">                    
                    <img width="60" src="<?php echo e(asset('/assets/img/gg-title.png')); ?>" alt="image">
                </a>

                <a class="simple-text logo-mini user-info"  href="#" id="navbarDropdownProfile" data-toggle="dropdown"
                    aria-haspopup="true" aria-expanded="false">
                    <i class="material-icons">person</i>
                </a>

                <a class="simple-text user-info logo-normal dropdown-toggle text-capitalize" href="#" id="navbarDropdownProfile" data-toggle="dropdown"
                    aria-haspopup="true" aria-expanded="false">
                    <span><?php echo e(auth()->user()->name); ?></span>
                </a>

                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdownProfile">
                    <a class="dropdown-item" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Log Out</a>
                    <form id="logout-form" action="/logout" method="POST" style="display: none;"><?php echo csrf_field(); ?></form>
                </div>

            </div>
            <div class="sidebar-wrapper">
                <ul class="nav">
                    <li class="nav-item preview">
                        <a class="nav-link" href="/" target="_blank">
                            <i class="material-icons">pageview</i>
                            <p>Preview App</p>
                        </a>
                    </li>
                    <?php if(Secure::hasAdminAccess('Profiles|View')): ?>
                    <li class="nav-item <?php echo e(Request::is('admin/profile') ? 'active' : ''); ?>">
                        <a class="nav-link" href="/admin/profile">
                            <i class="material-icons">save_alt</i>
                            <p>Profiles</p>
                        </a>
                    </li>
                    <li class="nav-item text-center <?php echo e(Request::is('admin/profile/add') || Request::is('admin/profile/edit*') || Request::is('admin/profile/clone*')  ? 'active' : 'd-none'); ?>">
                        <a class="nav-link" href="/admin/profile">
                            <i class="material-icons">close</i>
                            <p>Cancel</p>
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php
                        $hasAccessToAnyNavigationItem = Secure::hasAdminAccess('Showreel|View', 'Third Party Providers|View', 'Games|View','Studios|View','Play It Forward|View','Markets|View','Products|View');
                        $hasAccessToAnySecurityItem = Secure::hasAdminAccess('Users|View', 'Roles|View');
                    ?>

                    <li class="nav-item <?php echo e(Request::is('admin/pages') ? 'active' : ''); ?>">

                        <?php if($hasAccessToAnyNavigationItem): ?>

                            <?php if(Request::is('admin/pages')): ?>

                            <a class="nav-link" data-toggle="collapse" href="#resources">
                                <i class="material-icons">view_carousel</i>
                                <p>
                                    <span>Pages</span>
                                    <b class="caret"></b>
                                </p>
                            </a>

                            <?php endif; ?>

                            <?php if(!Request::is('admin/pages')): ?>

                            <a class="nav-link" href="/admin/pages">
                                <i class="material-icons">view_carousel</i>
                                <p>
                                    <span>Pages</span>
                                    <b class="caret"></b>
                                </p>
                            </a>

                            <?php endif; ?>

                        <?php else: ?>
                            <a class="nav-link" href="/admin/pages">
                                <i class="material-icons">view_carousel</i>
                                <p>
                                    <span>Pages</span>
                                </p>
                            </a>
                        <?php endif; ?>

                        <div id="resources" class="<?php echo e($hasAccessToAnyNavigationItem ? 'collapse'  : ''); ?>

                                                   <?php echo e(' '); ?>

                                                   <?php echo e(!Request::is('admin/pages') && Request::is('admin/showreel*','admin/third-party-providers*', 'admin/games*', 'admin/studios*', 'admin/products*', 'admin/play-it-forward*', 'admin/markets*')  ? 'show' : ''); ?>" >
                            <ul class="nav">

                                <?php if(Secure::hasAdminAccess('Studios|View')): ?>
                                <li class="nav-item <?php echo e(Request::is('admin/studios*') ? 'active' : ''); ?>">
                                    <a class="nav-link" href="/admin/studios">
                                        <i class="material-icons">code</i>
                                        <p>Studios</p>
                                    </a>
                                </li>
                                <?php endif; ?>

                                <?php if(Secure::hasAdminAccess('Games|View')): ?>
                                <li class="nav-item <?php echo e(Request::is('admin/games*') ? 'active' : ''); ?>">
                                    <a class="nav-link" href="/admin/games">
                                        <i class="material-icons">casino</i>
                                        <p>Games</p>
                                    </a>
                                </li>
                                <?php endif; ?>

                                <?php if(Secure::hasAdminAccess('Markets|View')): ?>
                                <li class="nav-item <?php echo e(Request::is('admin/markets*') ? 'active' : ''); ?>">
                                    <a class="nav-link" href="/admin/markets">
                                        <i class="material-icons">public</i>
                                        <p>Markets</p>
                                    </a>
                                </li>
                                <?php endif; ?>

                                <?php if(Secure::hasAdminAccess('Third Party Providers|View')): ?>
                                <li class="nav-item <?php echo e(Request::is('admin/third-party-providers') ? 'active' : ''); ?>">
                                    <a class="nav-link" href="/admin/third-party-providers">
                                        <i class="material-icons">how_to_reg</i>
                                        <p>Third Party Providers</p>
                                    </a>
                                </li>
                                <?php endif; ?>

                                <?php if(Secure::hasAdminAccess('Showreel|View')): ?>
                                <li class="nav-item <?php echo e(Request::is('admin/showreel') ? 'active' : ''); ?>">
                                    <a class="nav-link" href="/admin/showreel">
                                        <i class="material-icons">video_library</i>
                                        <p>Showreel</p>
                                    </a>
                                </li>
                                <?php endif; ?>

                                <?php if(Secure::hasAdminAccess('Products|View')): ?>
                                <li class="nav-item <?php echo e(Request::is('admin/products*') ? 'active' : ''); ?>">
                                    <a class="nav-link" href="/admin/products">
                                        <i class="material-icons">search</i>
                                        <p>Products</p>
                                    </a>
                                </li>
                                <?php endif; ?>

                                <?php if(Secure::hasAdminAccess('Play It Forward|View')): ?>
                                <li class="nav-item <?php echo e(Request::is('admin/play-it-forward') ? 'active' : ''); ?>">
                                    <a class="nav-link" href="/admin/play-it-forward">
                                        <i class="material-icons">favorite</i>
                                        <p>Play It Forward</p>
                                    </a>
                                </li>
                                <?php endif; ?>


                            </ul>
                        </div>
                    </li>

                    <?php if($hasAccessToAnySecurityItem): ?>

                    <li class="nav-item <?php echo e(Request::is('admin/users*') ? 'active' : ''); ?>">
                        <a class="nav-link" data-toggle="collapse" href="#security">
                            <i class="material-icons">security</i>
                            <p>Security<b class="caret"></b></p>
                        </a>
                    </li>
                    <div id="security" class="collapse <?php echo e(Request::is('admin/users*') || Request::is('admin/roles*')  ? 'show' : ''); ?>" >
                        <ul class="nav">
                            <?php if(Secure::hasAdminAccess('Users|View')): ?>
                            <li class="nav-item <?php echo e(Request::is('admin/users*') ? 'active' : ''); ?>">
                                <a class="nav-link" href="/admin/users">
                                    <i class="material-icons">person</i>
                                    <p>Users</p>
                                </a>
                            </li>
                            <?php endif; ?>
                            <?php if(Secure::hasAdminAccess('Roles|View')): ?>
                            <li class="nav-item <?php echo e(Request::is('admin/roles*') ? 'active' : ''); ?>">
                                <a class="nav-link" href="/admin/roles">
                                    <i class="material-icons">group</i>
                                    <p>Roles</p>
                                </a>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </div>

                    <?php endif; ?>
                </ul>
            </div>
        </div>
        <?php endif; ?>
        <div class="main-panel <?php echo e(Auth::check() ? '' : 'w-100'); ?>">
            <admin-page-loader delay="500"></admin-page-loader>
            <?php if(Auth::check()): ?>
                <nav class="navbar navbar-expand-lg navbar-transparent navbar-absolute fixed-top ">
                    <div class="container-fluid">
                        <div class="navbar-wrapper">
                            <div class="navbar-minimize">
                                <button id="minimizeSidebar" class="btn btn-just-icon btn-white btn-fab btn-round">
                                    <i class="material-icons text_align-center visible-on-sidebar-regular">more_vert</i>
                                    <i class="material-icons design_bullet-list-67 visible-on-sidebar-mini">view_list</i>
                                </button>
                            </div>
                        </div>

                    </div>
                </nav>
            <?php endif; ?>
            <div class="content">
                <common-handler></common-handler>
                <?php echo $__env->make('layouts.form-response', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                <?php echo $__env->yieldContent('content'); ?>
            </div>
        </div>
    </div>
</body>

</html><?php /**PATH M:\Websites\spotlight\v1.9.0.736-rebranding\website\resources\views/layouts/admin.blade.php ENDPATH**/ ?>