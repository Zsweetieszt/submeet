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

    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@25.3.1/build/css/intlTelInput.css">

    <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.css"> -->
    <link rel="stylesheet" href="{{ asset('css/toastr/toastr.css') }}">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css"> -->


    <link href="https://cdn.jsdelivr.net/npm/@coreui/coreui@5.3.1/dist/css/coreui.min.css" rel="stylesheet"
        integrity="sha384-PDUiPu3vDllMfrUHnurV430Qg8chPZTNhY8RUpq89lq22R3PzypXQifBpcpE1eoB" crossorigin="anonymous">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/simplebar/6.3.0/simplebar.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/simplebar/6.3.0/simplebar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/simplebar/6.3.0/simplebar.min.css">

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
    <script src="https://kit.fontawesome.com/ef63730a45.js" crossorigin="anonymous"></script>

    {{-- Chartjs --}}
    <script src="https://cdn.jsdelivr.net/npm/@coreui/chartjs@4.0.0/dist/js/coreui-chartjs.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@coreui/chartjs@4.0.0/dist/css/coreui-chartjs.min.css">

    {{-- CoreUI Icon --}}
    <script src="https://cdn.jsdelivr.net/npm/@coreui/icons@3.0.1/dist/cjs/index.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@coreui/icons@3.0.1/css/all.min.css">

    {{-- Fontawesome Icon --}}
    <script src="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@7.0.0/js/all.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@7.0.0/css/fontawesome.min.css"
        rel="stylesheet">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ env('APP_NAME') }} | @yield('title')</title>

    <style>
        .recaptcha-wrapper {
            transform: scale(0.85);
            /* Adjust scale as needed */
            transform-origin: 0 0;
            /* Ensures the reCAPTCHA doesn't shift position */
            border-radius: 10px;
            overflow: hidden;
            /* Applies the rounded effect */
            display: inline-block;
        }

        .toast-body {
            /* max-width: 300px; */
            word-wrap: break-word;
            white-space: normal;
            overflow: hidden;
            text-overflow: ellipsis;
        }

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
    @yield('content')

    <footer class="footer w-100 align-items-center justify-content-center">
        <div>
            <small>

            </small>
        </div>
    </footer>

    <script>
        @yield('script')
    </script>

    <script>
        function getAgentInfo() {
            const ua = navigator.userAgent;
            let os = 'Unknown OS',
                os_version = '',
                browser = 'Unknown Browser',
                browser_version = '';

            // OS detection
            if (/Windows NT ([0-9.]+)/i.test(ua)) {
                os = 'Windows';
                os_version = ua.match(/Windows NT ([0-9.]+)/i)[1];
            } else if (/Mac OS X ([0-9_]+)/i.test(ua)) {
                os = 'Mac OS X';
                os_version = ua.match(/Mac OS X ([0-9_]+)/i)[1].replace(/_/g, '.');
            } else if (/Android ([0-9.]+)/i.test(ua)) {
                os = 'Android';
                os_version = ua.match(/Android ([0-9.]+)/i)[1];
            } else if (/iPhone OS ([0-9_]+)/i.test(ua)) {
                os = 'iOS';
                os_version = ua.match(/iPhone OS ([0-9_]+)/i)[1].replace(/_/g, '.');
            } else if (/Linux/i.test(ua)) {
                os = 'Linux';
            }

            // Browser detection
            if (/Chrome\/([0-9.]+)/i.test(ua)) {
                browser = 'Chrome';
                browser_version = ua.match(/Chrome\/([0-9.]+)/i)[1];
            } else if (/Firefox\/([0-9.]+)/i.test(ua)) {
                browser = 'Firefox';
                browser_version = ua.match(/Firefox\/([0-9.]+)/i)[1];
            } else if (/MSIE ([0-9.]+)/i.test(ua)) {
                browser = 'Internet Explorer';
                browser_version = ua.match(/MSIE ([0-9.]+)/i)[1];
            } else if (/Trident\/.*rv:([0-9.]+)/i.test(ua)) {
                browser = 'Internet Explorer';
                browser_version = ua.match(/Trident\/.*rv:([0-9.]+)/i)[1];
            } else if (/Edge\/([0-9.]+)/i.test(ua)) {
                browser = 'Edge';
                browser_version = ua.match(/Edge\/([0-9.]+)/i)[1];
            } else if (/Safari\/([0-9.]+)/i.test(ua) && /Version\/([0-9.]+)/i.test(ua)) {
                browser = 'Safari';
                browser_version = ua.match(/Version\/([0-9.]+)/i)[1];
            }

            return {
                user_agent: ua,
                os: os,
                os_version: os_version,
                browser: browser,
                browser_version: browser_version
            };
        }

        document.addEventListener('DOMContentLoaded', function() {
            const agent = getAgentInfo();
            const footer = document.querySelector('footer.footer small');
            if (footer) {
                footer.style.textAlign = 'center';
                footer.style.display = 'block';
                footer.innerHTML = `© 2025. SubMeet. Version 1.0 - Beta. <br> ({{ now()->format('D, d M Y H:i:s O') }} UTC)
                [User using ${agent.os} ${agent.os_version}: ${agent.browser} ${agent.browser_version} ${Number((performance.now() / 1000).toFixed(3))} s] <br> Please contact <a href="mailto:submeet.cms@gmail.com">submeet.cms@gmail.com</a> if you having any difficulties.`;
            }
        });
    </script>

    <script>
        const header = document.querySelector('header.header');

        document.addEventListener('scroll', () => {
            if (header) {
                header.classList.toggle('shadow-sm', document.documentElement.scrollTop > 0);
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@coreui/utils@2.0.2/dist/umd/index.js"></script>
    <script src="{{ asset('js/main.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
        integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/@coreui/coreui@5.3.1/dist/js/coreui.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/intl-tel-input@25.3.1/build/js/intlTelInput.min.js"></script>

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
    @yield('script')
    @yield('script')
</script>

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
    @elseif (session('resent'))
        toastr.success("{{ __('A fresh verification link has been sent to your email address.') }}");
    @elseif (session('status'))
        toastr.success("{{ session('status') }}");
    @endif
</script>

</html>
