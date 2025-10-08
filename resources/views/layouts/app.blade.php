<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <title>Setup Box Management - Unikit</title>

        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta content="Premium Multipurpose Admin & Dashboard Template" name="description" />
        <meta content="" name="author" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />

        <!-- App css -->
        <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
        <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
        <link href="{{ asset('assets/css/app.min.css') }}" rel="stylesheet" type="text/css" />

        <!-- Global Loader Styles -->
        <style>
            :root{
                --loader-bg: rgba(17, 18, 20, 0.55);
                --loader-size: 58px;
                --loader-border: 6px;
            }
            #globalLoader{
                position: fixed;
                inset: 0;
                background: var(--loader-bg);
                backdrop-filter: blur(2px);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 2147483000; /* above modals */
                opacity: 0;
                pointer-events: none;
                transition: opacity .2s ease;
            }
            #globalLoader.active{
                opacity: 1;
                pointer-events: all;
            }
            .loader-wrap{
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: .75rem;
                padding: 1.25rem 1.5rem;
                border-radius: 1rem;
                background: rgba(255,255,255,.9);
                box-shadow: 0 10px 30px rgba(0,0,0,.15);
            }
            .spinner{
                width: var(--loader-size);
                height: var(--loader-size);
                border-radius: 50%;
                border: var(--loader-border) solid rgba(0,0,0,.12);
                border-top-color: #0d6efd; /* bootstrap primary */
                animation: spin 1s linear infinite;
            }
            @keyframes spin{ to { transform: rotate(360deg); } }
            .loader-text{
                font-size: .9rem;
                font-weight: 600;
                color: #0d0f12;
                letter-spacing: .2px;
                display: inline-flex;
                align-items: center;
                gap: .4rem;
            }
            .no-scroll{
                overflow: hidden !important;
            }
            /* Optional: dim the page a bit when loader is active using :has(), ignored by old browsers */
            body:has(#globalLoader.active) .page-wrapper{
                filter: blur(.2px) saturate(.96);
            }
        </style>
    </head>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('assets/images/logo_new.jpg') }}">

    <body id="body" class="dark-sidebar">
        <!-- Global Loader -->
        <!-- <div id="globalLoader" aria-live="polite" aria-busy="true">
            <div class="loader-wrap" role="status" aria-label="Loading">
                <div class="spinner" aria-hidden="true"></div>
                <span class="loader-text">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-lightning-charge" viewBox="0 0 16 16" aria-hidden="true">
                        <path d="M11.3 1L6 8h3l-1 7 5.3-7H10l1.3-7z"/>
                    </svg>
                    Loading…
                </span>
            </div>
        </div> -->
        <!-- /Global Loader -->

        <div class="left-sidebar">

            <!-- User -->
            <div class="sidebar-user-pro media border-end">
                <div class="position-relative mx-auto">
                    <img src="{{ asset('assets/images/logo_new.jpg') }}" alt="user" class="rounded-circle thumb-md" />
                    <span class="online-icon position-absolute end-0"><i class="mdi mdi-record text-success"></i></span>
                </div>
                <div class="media-body ms-2 user-detail align-self-center">
                    <h5 class="font-14 m-0 fw-bold">{{ Auth::user()->name ?? 'Guest User' }}</h5>
                    <p class="opacity-50 mb-0">{{ Auth::user()->email ?? 'No Email' }}</p>
                </div>
            </div>

            <!-- Sidebar Menu -->
            <div class="menu-content h-100" data-simplebar>
                <div class="menu-body navbar-vertical">
                    <div class="collapse navbar-collapse tab-content" id="sidebarCollapse">
                        <ul class="navbar-nav tab-pane active" id="Main" role="tabpanel">
                            <li class="menu-label mt-0 text-primary font-12 fw-semibold">
                                Channel <span>Management</span>
                            </li>

                            <!-- ✅ Channel Management Menu -->
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('clients.*') ? 'active' : '' }}" href="{{ route('clients.index') }}">
                                    <i class="ti ti-users menu-icon"></i> <span>Subscribers</span>
                                </a>
                            </li>

                            @if(auth()->user()->hasRole('Admin'))
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">
                                        <i class="ti ti-user menu-icon"></i> <span>Manage Users</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('permissions.*') || request()->routeIs('roles.*') ? 'active' : '' }}" href="#permissionSubmenu" data-bs-toggle="collapse" aria-expanded="false">
                                        <i class="ti ti-key menu-icon"></i> <span>Permissions</span>
                                    </a>
                                    <ul class="collapse list-unstyled ps-3 {{ request()->routeIs('permissions.*') || request()->routeIs('roles.*') ? 'show' : '' }}" id="permissionSubmenu">
                                        <li>
                                            <a class="nav-link {{ request()->routeIs('permissions.index') ? 'active' : '' }}" href="{{ route('permissions.index') }}">Manage Permissions</a>
                                        </li>
                                        <li>
                                            <a class="nav-link {{ request()->routeIs('roles.index') ? 'active' : '' }}" href="{{ route('roles.index') }}">Manage Roles</a>
                                        </li>
                                    </ul>
                                </li>
                            @endif

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
                                <a class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.index') }}">
                                    <i class="ti ti-report menu-icon"></i> <span>Reports</span>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('help.*') ? 'active' : '' }}" href="{{ route('help.index') }}">
                                    <i class="ti ti-help menu-icon"></i> <span>Help</span>
                                </a>
                            </li>
                        </ul>
                        <!--end navbar-nav--->
                    </div>
                </div>
            </div>
        </div>
        <!-- end left-sidenav-->

        <!-- Top Bar Start -->
        <div class="topbar">
            <nav class="navbar-custom" id="navbar-custom">
                <ul class="list-unstyled topbar-nav float-end mb-0">
                    <li class="dropdown">
                        <a class="nav-link dropdown-toggle nav-user" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                            <div class="d-flex align-items-center">
                                <img src="{{ asset('assets/images/logo_new.jpg') }}" alt="profile-user" class="rounded-circle me-2 thumb-sm" />
                                <div>
                                    <small class="d-none d-md-block font-11">User</small>
                                    <span class="d-none d-md-block fw-semibold font-12">{{ Auth::user()->name ?? 'Guest' }} <i class="mdi mdi-chevron-down"></i></span>
                                </div>
                            </div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="ti ti-user font-16 me-1 align-text-bottom"></i> Profile</a>
                            <div class="dropdown-divider mb-0"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item"><i class="ti ti-power font-16 me-1 align-text-bottom"></i> Logout</button>
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
        <!-- Top Bar End -->

        <div class="page-wrapper">
            <div class="page-content-tab">
                <main class="main-content" id="mainContent">
                    @yield('content')
                </main>
                <!-- Footer -->
                <footer class="footer text-center text-sm-start">
                    &copy;
                    <script>document.write(new Date().getFullYear());</script>
                    Setup Box Management <span class="text-muted d-none d-sm-inline-block float-end">Crafted with <i class="mdi mdi-heart text-danger"></i></span>
                </footer>
            </div>
        </div>

        <!-- JS -->
        <script src="{{ asset('assets/js/app.js') }}"></script>

        <!-- Global Loader Logic -->
        <!-- <script>
            (function () {
                const el = document.getElementById('globalLoader');
                let requestsInFlight = 0;
                const body = document.body;

                function show() {
                    if (!el.classList.contains('active')) {
                        el.classList.add('active');
                        body.classList.add('no-scroll');
                    }
                }
                function hide() {
                    if (requestsInFlight <= 0) {
                        el.classList.remove('active');
                        body.classList.remove('no-scroll');
                    }
                }
                function safeShow(){ requestsInFlight++; show(); }
                function safeHide(){ requestsInFlight = Math.max(0, requestsInFlight - 1); hide(); }

                // Expose for manual usage: window.AppLoader.show()/hide()
                window.AppLoader = {
                    show: safeShow,
                    hide: safeHide,
                    hardShow: show,
                    hardHide: hide
                };

                // Initial page load: show early, hide when fully loaded
                document.addEventListener('DOMContentLoaded', () => { show(); });
                window.addEventListener('load', () => { requestsInFlight = 0; hide(); });

                // Navigation & clicks: show for internal page navigations
                document.addEventListener('click', (e) => {
                    const a = e.target.closest('a');
                    if (!a) return;

                    // Opt-out: data-no-loader="true"
                    if (a.dataset.noLoader === 'true') return;

                    // Ignore modifiers/new tabs/downloads/hash
                    if (e.defaultPrevented || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
                    if (a.target && a.target !== '' && a.target !== '_self') return;
                    if (a.hasAttribute('download')) return;
                    const href = a.getAttribute('href') || '';
                    if (href.startsWith('#') || href === '') return;

                    try {
                        const url = new URL(href, window.location.href);
                        // same origin only
                        if (url.origin === window.location.origin) {
                            show();
                        }
                    } catch (_) {}
                }, { capture: true });

                // beforeunload fallback for full navigations
                window.addEventListener('beforeunload', () => { show(); });

                // Forms: show on submit
                document.addEventListener('submit', (e) => {
                    const form = e.target;
                    if (form && form.dataset.noLoader === 'true') return;
                    show();
                }, { capture: true });

                // CSRF helper
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                // Hook fetch() to auto-show loader
                if (window.fetch) {
                    const _fetch = window.fetch.bind(window);
                    window.fetch = function(input, init = {}) {
                        // Skip loader for requests explicitly marked opt-out
                        const headers = new Headers(init.headers || {});
                        if (headers.get('X-No-Loader') === 'true') {
                            // still ensure CSRF if needed
                            if (csrfToken && !headers.has('X-CSRF-TOKEN')) headers.set('X-CSRF-TOKEN', csrfToken);
                            init.headers = headers;
                            return _fetch(input, init);
                        }

                        safeShow();
                        // Ensure CSRF on same-origin non-GET
                        try {
                            const url = typeof input === 'string' ? new URL(input, location.href) :
                                        (input instanceof Request ? new URL(input.url, location.href) : null);
                            const method = (init.method || (input instanceof Request ? input.method : 'GET')).toUpperCase();
                            if (url && url.origin === location.origin && method !== 'GET' && csrfToken && !headers.has('X-CSRF-TOKEN')) {
                                headers.set('X-CSRF-TOKEN', csrfToken);
                                init.headers = headers;
                            }
                        } catch (_) {}

                        return _fetch(input, init).finally(safeHide);
                    };
                }

                // jQuery AJAX global handlers (if jQuery present)
                if (window.jQuery) {
                    const $ = window.jQuery;
                    $(document).ajaxSend(function(_evt, _jqxhr, settings){
                        if (settings && settings.headers && settings.headers['X-No-Loader'] === 'true') return;
                        safeShow();
                    });
                    $(document).ajaxComplete(function(){ safeHide(); });
                    $(document).ajaxError(function(){ safeHide(); });
                }

                // Promise-based utilities for manual operations
                window.withLoader = async (promiseLike) => {
                    safeShow();
                    try { return await promiseLike; }
                    finally { safeHide(); }
                };
            })();
        </script> -->
    </body>
</html>
