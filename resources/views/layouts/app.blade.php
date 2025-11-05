<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-NBPFTVQL3L"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag() { dataLayer.push(arguments); }
        gtag('js', new Date());

        gtag('config', 'G-NBPFTVQL3L');
    </script>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <link href="https://cdn.jsdelivr.net/npm/@coreui/coreui@5.3.1/dist/css/coreui.min.css" rel="stylesheet"
        integrity="sha384-PDUiPu3vDllMfrUHnurV430Qg8chPZTNhY8RUpq89lq22R3PzypXQifBpcpE1eoB" crossorigin="anonymous">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/simplebar/6.3.0/simplebar.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/simplebar/6.3.0/simplebar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/simplebar/6.3.0/simplebar.min.css">

    <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.css"> -->
    <link rel="stylesheet" href="{{ asset('css/toastr/toastr.css') }}">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css"> -->

    <link
        href="https://cdn.datatables.net/v/bs5/jszip-3.10.1/dt-2.0.5/b-3.0.2/b-colvis-3.0.2/b-html5-3.0.2/b-print-3.0.2/fc-5.0.0/fh-4.0.1/r-3.0.2/datatables.min.css"
        rel="stylesheet">
    <script
        src="https://cdn.datatables.net/v/bs5/jq-3.7.0/jszip-3.10.1/dt-2.0.5/b-3.0.2/b-colvis-3.0.2/b-html5-3.0.2/b-print-3.0.2/fc-5.0.0/fh-4.0.1/r-3.0.2/datatables.min.js">
        </script>

    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/gh/rk4bir/simple-tags-input@latest/src/simple-tag-input.min.css">
    <script src="https://cdn.jsdelivr.net/gh/rk4bir/simple-tags-input@latest/src/simple-tag-input.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify"></script>
    <script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.polyfills.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.css" rel="stylesheet" type="text/css" />

    {{--
    <link rel="stylesheet" href="{{ asset('simplebar/dist/simplebar.css') }}"> --}}
    {{--
    <link rel="stylesheet" href="{{ asset('css/vendors/simplebar.css') }}"> --}}
    <!-- Main styles for this application-->
    <link href="{{ asset('css/style.css ') }}" rel="stylesheet">
    <!-- We use those styles to show code examples, you should remove them in your application.-->
    {{--
    <link href="{{ asset('css/examples.css') }}" rel="stylesheet"> --}}
    <script src="{{ asset('js/config.js') }}"></script>
    <script src="{{ asset('js/color-modes.js') }}"></script>

    {{--
    <link href="{{ asset('coreui/chartjs/dist/css/coreui-chartjs.css') }}" rel="stylesheet"> --}}

    {{-- Icon --}}
    <link rel="icon" type="image/png" sizes="192x192"
        href="{{ asset('assets/favicon/SubMeet-android-icon-192x192.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/favicon/SubMeet-favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('assets/favicon/SubMeet-favicon-96x96.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/favicon/SubMeet-favicon-96x96.png') }}">

    <link rel="apple-touch-icon-precomposed" sizes="57x57"
        href="{{ asset('assets/favicon/apple-touch-icon-57x57.png') }}" />
    <link rel="apple-touch-icon-precomposed" sizes="114x114"
        href="{{ asset('assets/favicon/apple-touch-icon-114x114.png') }}" />
    <link rel="apple-touch-icon-precomposed" sizes="72x72"
        href="{{ asset('assets/favicon/apple-touch-icon-72x72.png') }}" />
    <link rel="apple-touch-icon-precomposed" sizes="144x144"
        href="{{ asset('assets/favicon/apple-touch-icon-144x144.png') }}" />
    <link rel="apple-touch-icon-precomposed" sizes="60x60"
        href="{{ asset('assets/favicon/apple-touch-icon-60x60.png') }}" />
    <link rel="apple-touch-icon-precomposed" sizes="120x120"
        href="{{ asset('assets/favicon/apple-touch-icon-120x120.png') }}" />
    <link rel="apple-touch-icon-precomposed" sizes="76x76"
        href="{{ asset('assets/favicon/apple-touch-icon-76x76.png') }}" />
    <link rel="apple-touch-icon-precomposed" sizes="152x152"
        href="{{ asset('assets/favicon/apple-touch-icon-152x152.png') }}" />
    <link rel="icon" type="image/png" href="{{ asset('assets/favicon/favicon-196x196.png') }}" sizes="196x196" />
    <link rel="icon" type="image/png" href="{{ asset('assets/favicon/favicon-96x96.png') }}" sizes="96x96" />
    <link rel="icon" type="image/png" href="{{ asset('assets/favicon/favicon-32x32.png') }}" sizes="32x32" />
    <link rel="icon" type="image/png" href="{{ asset('assets/favicon/favicon-16x16.png') }}" sizes="16x16" />
    <link rel="icon" type="image/png" href="{{ asset('assets/favicon/favicon-128.png') }}" sizes="128x128" />
    <meta name="application-name" content="&nbsp;" />
    <meta name="msapplication-TileColor" content="#FFFFFF" />
    <meta name="msapplication-TileImage" content="{{ asset('assets/favicon/mstile-144x144.png') }}" />
    <meta name="msapplication-square70x70logo" content="{{ asset('assets/favicon/mstile-70x70.png') }}" />
    <meta name="msapplication-square150x150logo" content="{{ asset('assets/favicon/mstile-150x150.png') }}" />
    <meta name="msapplication-wide310x150logo" content="{{ asset('assets/favicon/mstile-310x150.png') }}" />
    <meta name="msapplication-square310x310logo" content="{{ asset('assets/favicon/mstile-310x310.png') }}" />

    {{-- Chartjs --}}
    <script src="https://cdn.jsdelivr.net/npm/@coreui/chartjs@4.0.0/dist/js/coreui-chartjs.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@coreui/chartjs@4.0.0/dist/css/coreui-chartjs.min.css">

    {{-- CoreUI Icon --}}
    <script src="https://cdn.jsdelivr.net/npm/@coreui/icons@3.0.1/dist/cjs/index.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@coreui/icons@3.0.1/css/all.min.css">

    {{-- Fontawesom Icon --}}
    <script src="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@7.0.0/js/all.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@7.0.0/css/fontawesome.min.css"
        rel="stylesheet">

    {{-- Date Picker --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-datepicker@1.10.0/dist/js/bootstrap-datepicker.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-datepicker@1.10.0/dist/css/bootstrap-datepicker3.min.css"
        rel="stylesheet">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ env('APP_NAME') }} | @yield('title')</title>

    <style>
        fieldset {
            margin-bottom: 1em !important;
            border-radius: 10px !important;
            border: 3px solid var(--cui-input-group-addon-bg, var(--cui-tertiary-bg)) !important;
            padding: 20px !important;
        }

        legend {
            padding: 1px 10px !important;
            float: none;
            width: auto;
        }
    </style>

</head>

<body>
    <div class="sidebar sidebar-dark sidebar-fixed border-end" id="sidebar">
        <div class="sidebar-header border-bottom">
            <div class="sidebar-brand">
                <img class="sidebar-brand-full" width="128" height="43"
                    src="{{ asset(path: 'assets/brand/Logo-SubMeet.png') }}" alt="">
                <img class="sidebar-brand-narrow" width="32" height="32"
                    src="{{ asset(path: 'assets/brand/Logo-SubMeet.png') }}" alt="">
            </div>
            <button class="btn-close d-lg-none" type="button" data-coreui-theme="dark" aria-label="Close"
                onclick="coreui.Sidebar.getInstance(document.querySelector(&quot;#sidebar&quot;)).toggle()"></button>
        </div>
        <ul class="sidebar-nav" data-coreui="navigation" data-simplebar>
            <li class="nav-item"><a class="nav-link" href="/dashboard">
                    <i class="cil-speedometer nav-icon"></i> Dashboard</a></li>
            <li class="nav-item"><a class="nav-link" href="{{ route('events') }}">
                    <i class="cil-calendar nav-icon"></i> Events</a></li>
            @if (Auth::user()->root)
                <li class="nav-item"><a class="nav-link" href="{{ route('users.index') }}">
                        <i class="cil-people nav-icon"></i> Users </a></li>
            @endif
        </ul>
        <div class="sidebar-footer border-top d-none d-md-flex">
            <button class="sidebar-toggler" type="button" data-coreui-toggle="unfoldable"></button>
        </div>
    </div>
    <div class="wrapper d-flex flex-column min-vh-100">
        <header class="header header-sticky p-0 mb-4">
            <div class="container-fluid border-bottom px-4">
                <button class="header-toggler" type="button"
                    onclick="coreui.Sidebar.getInstance(document.querySelector('#sidebar')).toggle()"
                    style="margin-inline-start: -14px;">
                    <i class="cil-menu"></i>
                </button>
                {{-- <ul class="header-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="#">
                            <i class="cil-bell"></i></a></li>
                    <li class="nav-item"><a class="nav-link" href="#">
                            <i class="cil-list-rich"></i></a></li>
                    <li class="nav-item"><a class="nav-link" href="#">
                            <i class="cil-envelope-open"></i></a></li>
                </ul> --}}
                <ul class="header-nav">
                    {{-- <li class="nav-item py-1">
                        <div class="vr h-100 mx-2 text-body text-opacity-75"></div>
                    </li> --}}
                    <li class="nav-item dropdown">
                        <button class="btn btn-link nav-link py-2 px-2 d-flex align-items-center" type="button"
                            aria-expanded="false" data-coreui-toggle="dropdown">
                            <i class="cil-contrast theme-icon-active"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" style="--cui-dropdown-min-width: 8rem;">
                            <li>
                                <button class="dropdown-item d-flex align-items-center" type="button"
                                    data-coreui-theme-value="light">
                                    <i class="cil-sun me-3"></i>Light
                                </button>
                            </li>
                            <li>
                                <button class="dropdown-item d-flex align-items-center" type="button"
                                    data-coreui-theme-value="dark">
                                    <i class="cil-moon me-3"></i>Dark
                                </button>
                            </li>
                            <li>
                                <button class="dropdown-item d-flex align-items-center active" type="button"
                                    data-coreui-theme-value="auto">
                                    <i class="cil-contrast me-3"></i>Auto
                                </button>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item py-1">
                        <div class="vr h-100 mx-2 text-body text-opacity-75"></div>
                    </li>
                    <li class="nav-item dropdown"><a class="nav-link py-0 pe-0" data-coreui-toggle="dropdown" href="#"
                            role="button" aria-haspopup="true" aria-expanded="false">
                            <div class="avatar avatar-md"><img class="avatar-img"
                                    src="{{ asset('assets/img/avatars/user.png') }}" alt="user profile"></div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end pt-0">
                            <div
                                class="dropdown-header bg-body-tertiary text-body-secondary fw-semibold rounded-top mb-2">
                                {{ Auth::user()->username }}
                            </div>
                            {{-- <a class="dropdown-item" href="#"> --}}
                                {{-- <i class="cil-bell me-2"></i> Updates<span
                                    class="badge badge-sm bg-info ms-2">42</span></a><a class="dropdown-item" href="#">
                                <i class="cil-envelope-open me-2"></i> Messages<span
                                    class="badge badge-sm bg-success ms-2">42</span></a><a class="dropdown-item"
                                href="#">
                                <i class="cil-task me-2"></i> Tasks<span
                                    class="badge badge-sm bg-danger ms-2">42</span></a><a class="dropdown-item"
                                href="#">
                                <i class="cil-comment-square me-2"></i> Comments<span
                                    class="badge badge-sm bg-warning ms-2">42</span></a>
                            <div class="dropdown-header bg-body-tertiary text-body-secondary fw-semibold my-2">
                                <div class="fw-semibold">Settings</div>
                            </div><a class="dropdown-item" href="#">
                                <i class="cil-user me-2"></i> Profile</a><a class="dropdown-item" href="#">
                                <i class="cil-settings me-2"></i> Settings</a><a class="dropdown-item" href="#">
                                <i class="cil-credit-card me-2"></i> Payments<span
                                    class="badge badge-sm bg-secondary ms-2">42</span></a><a class="dropdown-item"
                                href="#">
                                <i class="cil-file me-2"></i> Projects<span
                                    class="badge badge-sm bg-primary ms-2">42</span></a> --}}
                            {{-- <div class="dropdown-divider"></div><a class="dropdown-item" href="#"> --}}
                                {{-- <i class="cil-lock-locked me-2"></i> Lock Account</a> --}}
                            <a class="dropdown-item" href="{{ route('users.edit-profile') }}">
                                <i class="cil-settings me-2"></i> Profile Settings
                            </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                @csrf
                            </form>
                            <a class="dropdown-item" href="{{ route('logout') }}"
                                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="cil-account-logout me-2"></i> Logout
                            </a>
                        </div>
                    </li>
                </ul>
            </div>
            <div class="container-fluid px-4">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb my-0">
                        @yield('breadcrumb')
                    </ol>
                </nav>
            </div>
        </header>
        <div class="body flex-grow-1">

            @yield('content')
            <script>
                // Example starter JavaScript for disabling form submissions if there are invalid fields
                (function () {
                    'use strict'

                    // Fetch all the forms we want to apply custom Bootstrap validation styles to
                    const forms = document.querySelectorAll('.needs-validation')

                    // Loop over them and prevent submission
                    Array.prototype.slice.call(forms)
                        .forEach(form => {
                            form.addEventListener('submit', event => {
                                if (!form.checkValidity()) {
                                    event.preventDefault()
                                    event.stopPropagation()
                                }

                                form.classList.add('was-validated')
                            }, false)
                        })
                })()
            </script>
        </div>
        <footer class="footer px-4">
            <div>&copy; {{ date('Y') }}. {{ env('MAIL_FROM_NAME') }}. Version 1.0 - Beta. Please contact <a
                    href="mailto:submeet.cms@gmail.com">submeet.cms@gmail.com</a> if you having any difficulties.</div>
            {{-- <div class="ms-auto">Powered by&nbsp;<a href="https://coreui.io/docs/">CoreUI UI Components</a></div>
            --}}
        </footer>
    </div>

    <button id="backToTopBtn" title="Back to Top"
        style="width:45px; height:45px; display:none; position:fixed; bottom:40px; right:40px; z-index:9999;"
        class="btn btn-primary shadow text-center"><i class="cil-arrow-thick-to-top"
            style="width:50px; height:50px;"></i></button>

    <script>
        // Back to Top Button
        const backToTopBtn = document.getElementById('backToTopBtn');
        window.onscroll = function () {
            if (document.body.scrollTop > 200 || document.documentElement.scrollTop > 200) {
                backToTopBtn.style.display = "block";
            } else {
                backToTopBtn.style.display = "none";
            }
        };
        backToTopBtn.onclick = function () {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        };
    </script>

    <script>
        @yield('script')
    </script>
    {{--
    <script src="{{ asset('simplebar/dist/simplebar.min.js') }}"></script> --}}

    <script>
        const header = document.querySelector('header.header');

        document.addEventListener('scroll', () => {
            if (header) {
                header.classList.toggle('shadow-sm', document.documentElement.scrollTop > 0);
            }
        });
    </script>
    {{--
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> --}}
    {{--
    <script src="{{ asset('chart.js/dist/chart.umd.js') }}"></script> --}}
    {{--
    <script src="{{ asset('coreui/chartjs/dist/js/coreui-chartjs.js') }}"></script> --}}
    {{--
    <script src="{{ asset('coreui/utils/dist/umd/index.js') }}"></script> --}}
    <script src="https://cdn.jsdelivr.net/npm/@coreui/utils@2.0.2/dist/umd/index.js"></script>
    {{--
    <script src="{{ asset('js/main.js') }}"></script> --}}
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
        integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous">
        </script>
    <script src="https://cdn.jsdelivr.net/npm/@coreui/coreui@5.3.1/dist/js/coreui.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Default Statcounter code for SubMeet Website http://submeet.id -->
    <script type="text/javascript">
        var sc_project = 13159098;
        var sc_invisible = 1;
        var sc_security = "5e7cfcf1"; 
    </script>
    <script type="text/javascript" src="https://www.statcounter.com/counter/counter.js" async></script>
    <noscript>
        <div class="statcounter"><a title="Web Analytics Made Easy -
        Statcounter" href="https://statcounter.com/" target="_blank"><img class="statcounter"
                    src="https://c.statcounter.com/13159098/0/5e7cfcf1/1/" alt="Web Analytics Made Easy - Statcounter"
                    referrerPolicy="no-referrer-when-downgrade"></a>
        </div>
    </noscript>
    <!-- End of Statcounter Code -->   
</body>

<script>
    // Example starter JavaScript for disabling form submissions if there are invalid fields
    (function () {
        'use strict'

        // Fetch all the forms we want to apply custom Bootstrap validation styles to
        const forms = document.querySelectorAll('.needs-validation')

        // Loop over them and prevent submission
        Array.prototype.slice.call(forms)
            .forEach(form => {
                form.addEventListener('submit', event => {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }

                    form.classList.add('was-validated')
                }, false)
            })
    })()

    toastr.options = {
        "closeButton": true,
        "debug": false,
        "newestOnTop": false,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "preventDuplicates": false,
        "onclick": null,
        "showDuration": "150",
        "hideDuration": "500",
        "timeOut": "3000",
        "extendedTimeOut": "500",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
    }
</script>
<script>
    @if (session('success'))
        toastr.success("{{ session('success') }}");
    @elseif (session('error'))
        console.log('error: {{ session('error') }}');
        toastr.error("{{ session('error') }}");
    @elseif ($errors->any())
        toastr.error("{{ $errors->first() }}");
    @endif
</script>
<style>
    .tagify {
        display: flex;
        --placeholder-color: rgb(166 168 172);
        --placeholder-color-focus: rgb(166 168 172);
    }

    .btn {
        color: #fff;
    }
</style>

</html>