<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Setop Box Management - Unikit</title>

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta content="Premium Multipurpose Admin & Dashboard Template" name="description" />
    <meta content="" name="author" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />

    <!-- App css -->
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/app.min.css') }}" rel="stylesheet" type="text/css" />

    <!-- Ultra-light Loader Styles -->
    <style>
        :root {
            --ql-bg: rgba(255,255,255,.75);
            --ql-size: 42px;
            --ql-border: 4px;
            --ql-color: #0d6efd;
        }
        #quickLoader {
            position: fixed;
            inset: 0;
            background: var(--ql-bg);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 2147483000;
            opacity: 0;
            pointer-events: none;
            transition: opacity .15s ease;
        }
        #quickLoader.active {
            opacity: 1;
            pointer-events: auto;
        }
        .ql-spinner {
            width: var(--ql-size);
            height: var(--ql-size);
            border-radius: 50%;
            border: var(--ql-border) solid rgba(0,0,0,.12);
            border-top-color: var(--ql-color);
            animation: ql-spin .8s linear infinite;
        }
        @keyframes ql-spin { to { transform: rotate(360deg); } }
        .ql-text { font-size: .85rem; margin-top: .5rem; color: #111; font-weight: 600; }
        .ql-wrap { display:flex; flex-direction:column; align-items:center; }
    </style>

    <link rel="icon" type="image/png" href="{{ asset('assets/images/logo_new.jpg') }}">
</head>

<body id="body" class="dark-sidebar">

<!-- Loader -->
<div id="quickLoader" aria-live="polite" aria-busy="true">
    <div class="ql-wrap" role="status" aria-label="Loading">
        <div class="ql-spinner" aria-hidden="true"></div>
        <div class="ql-text">Loading…</div>
    </div>
</div>

<!-- Sidebar -->
<div class="left-sidebar">
    <div class="sidebar-user-pro media border-end">
        <div class="position-relative mx-auto">
            <img src="{{ asset('assets/images/logo_new.jpg') }}" alt="user" class="rounded-circle thumb-md" />
            <span class="online-icon position-absolute end-0">
                <i class="mdi mdi-record text-success"></i>
            </span>
        </div>
        <div class="media-body ms-2 user-detail align-self-center">
            <h5 class="font-14 m-0 fw-bold">{{ Auth::user()->name ?? 'Guest User' }}</h5>
            <p class="opacity-50 mb-0">{{ Auth::user()->email ?? 'No Email' }}</p>
        </div>
    </div>

    <!-- Menu -->
    <div class="menu-content h-100" data-simplebar>
        <div class="menu-body navbar-vertical">
            <div class="collapse navbar-collapse tab-content" id="sidebarCollapse">
                <ul class="navbar-nav tab-pane active" id="Main" role="tabpanel">
                    <li class="menu-label mt-0 text-primary font-12 fw-semibold">
                        Channel <span>Management</span>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('clients.*') ? 'active' : '' }}" href="{{ route('clients.index') }}">
                            <i class="ti ti-users menu-icon"></i> <span>Subscribers</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('inventories.*') ? 'active' : '' }}" href="{{ route('inventories.index') }}">
                            <i class="ti ti-archive menu-icon"></i> <span>Inventory</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('channels.*') ? 'active' : '' }}" href="{{ route('channels.index') }}">
                            <i class="ti ti-video menu-icon"></i> <span>Channels</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('packages.*') ? 'active' : '' }}" href="{{ route('packages.index') }}">
                            <i class="ti ti-package menu-icon"></i> <span>Packages</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('inventory-packages.*') ? 'active' : '' }}" href="{{ route('inventory-packages.index') }}">
                            <i class="ti ti-link menu-icon"></i> <span>Allocations</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('utility.*') ? 'active' : '' }}" href="{{ route('utility.online') }}">
                            <i class="ti ti-settings menu-icon"></i> <span>Utilities</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('live-reports.*') || request()->routeIs('channel-reports.*') || request()->routeIs('package-reports.*') || request()->routeIs('installed-reports.*') ? 'active' : '' }}"
                            href="#reportSubmenu" data-bs-toggle="collapse" aria-expanded="false">
                            <i class="ti ti-report menu-icon"></i> <span>Reports</span>
                        </a>
                        <ul class="collapse list-unstyled ps-3 {{ request()->routeIs('live-reports.*') || request()->routeIs('channel-reports.*') || request()->routeIs('package-reports.*') || request()->routeIs('installed-reports.*') ? 'show' : '' }}" id="reportSubmenu">
                            <li>
                                <a class="nav-link {{ request()->routeIs('installed-reports.index') ? 'active' : '' }}" href="{{ route('installed-reports.index') }}">Installed Boxes</a>
                            </li>
                            <li>
                                <a class="nav-link {{ request()->routeIs('live-reports.index') ? 'active' : '' }}" href="{{ route('live-reports.index') }}">Live Boxes</a>
                            </li>
                            <li>
                                <a class="nav-link {{ request()->routeIs('channel-reports.index') ? 'active' : '' }}" href="{{ route('channel-reports.index') }}">Channels</a>
                            </li>
                            <li>
                                <a class="nav-link {{ request()->routeIs('package-reports.index') ? 'active' : '' }}" href="{{ route('package-reports.index') }}">Packages</a>
                            </li>
                        </ul>
                    </li>

                          @if(auth()->user()->hasRole('Admin'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">
                                <i class="ti ti-user menu-icon"></i> <span>Manage Users</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('permissions.*') || request()->routeIs('roles.*') ? 'active' : '' }}"
                               href="#permissionSubmenu" data-bs-toggle="collapse" aria-expanded="false">
                                <i class="ti ti-key menu-icon"></i> <span>Permissions</span>
                            </a>
                            <ul class="collapse list-unstyled ps-3 {{ request()->routeIs('permissions.*') || request()->routeIs('roles.*') ? 'show' : '' }}" id="permissionSubmenu">
                                <li><a class="nav-link {{ request()->routeIs('permissions.index') ? 'active' : '' }}" href="{{ route('permissions.index') }}">Manage Permissions</a></li>
                                <li><a class="nav-link {{ request()->routeIs('roles.index') ? 'active' : '' }}" href="{{ route('roles.index') }}">Manage Roles</a></li>
                            </ul>
                        </li>
                    @endif

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('help.*') ? 'active' : '' }}" href="{{ route('help.index') }}">
                            <i class="ti ti-help menu-icon"></i> <span>Help</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Topbar -->
<div class="topbar">
    <nav class="navbar-custom" id="navbar-custom">
        <ul class="list-unstyled topbar-nav float-end mb-0">
            <li class="dropdown">
                <a class="nav-link dropdown-toggle nav-user" data-bs-toggle="dropdown" href="#">
                    <div class="d-flex align-items-center">
                        <img src="{{ asset('assets/images/logo_new.jpg') }}" alt="profile-user" class="rounded-circle me-2 thumb-sm" />
                        <div>
                            <small class="d-none d-md-block font-11">User</small>
                            <span class="d-none d-md-block fw-semibold font-12">
                                {{ Auth::user()->name ?? 'Guest' }} <i class="mdi mdi-chevron-down"></i>
                            </span>
                        </div>
                    </div>
                </a>
                <div class="dropdown-menu dropdown-menu-end">
                    <a class="dropdown-item" href="{{ route('profile.edit') }}">
                        <i class="ti ti-user font-16 me-1 align-text-bottom"></i> Profile
                    </a>
                    <div class="dropdown-divider mb-0"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item">
                            <i class="ti ti-power font-16 me-1 align-text-bottom"></i> Logout
                        </button>
                    </form>
                </div>
            </li>
        </ul>

        <ul class="list-unstyled topbar-nav mb-0">
            <li>
                <button class="nav-link button-menu-mobile nav-icon" id="togglemenu">
                    <i class="ti ti-menu-2"></i>
                </button>
            </li>
        </ul>
    </nav>
</div>

<!-- Page -->
<div class="page-wrapper">
    <div class="page-content-tab">
        <main class="main-content" id="mainContent">
            @yield('content')
        </main>

        <footer class="footer text-center text-sm-start">
            &copy; <script>document.write(new Date().getFullYear());</script>
            Setop Box Management
            <span class="text-muted d-none d-sm-inline-block float-end">
                Crafted with <i class="mdi mdi-heart text-danger"></i>
            </span>
        </footer>
    </div>
</div>

<!-- JS -->
<script src="{{ asset('assets/js/app.js') }}"></script>

<!-- Loader Logic -->
<script>
(function() {
    const loader = document.getElementById('quickLoader');
    if (!loader) return;

    const show = () => loader.classList.add('active');
    const hide = () => loader.classList.remove('active');

    document.addEventListener('DOMContentLoaded', show, { once: true });
    window.addEventListener('load', () => setTimeout(hide, 100), { once: true });
    setTimeout(hide, 1500);

    // Disable loader for buttons with [data-no-loader="true"]
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('button,a');
        if (btn?.dataset?.noLoader === 'true') return; // 👇 skip loader

        const a = e.target.closest('a');
        if (!a) return;
        if (a.dataset.noLoader === 'true') return;
        if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
        if (a.target && a.target !== '' && a.target !== '_self') return;
        if (a.hasAttribute('download')) return;
        const href = a.getAttribute('href') || '';
        if (href.startsWith('#') || href === '') return;
        try {
            const url = new URL(href, window.location.href);
            if (url.origin === window.location.origin) show();
        } catch(_) {}
    }, { capture: true });

    document.addEventListener('submit', (e) => {
        const form = e.target;
        if (form?.dataset?.noLoader === 'true') return; // 👇 skip loader
        show();
    }, { capture: true });

    window.QuickLoader = { show, hide };
})();
</script>

</body>
</html>
