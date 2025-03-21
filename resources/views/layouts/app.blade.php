<!DOCTYPE html>
<html lang="en" class="light-style layout-navbar-fixed layout-menu-fixed layout-compact " dir="ltr"
    data-theme="theme-default" data-assets-path="{{ url('/') }}/assets/" data-template="vertical-menu-template"
    data-style="light">

<head>
    <meta charset="utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

    <title>@yield('title', "Call2Fix") - Admin Dashboard</title>

    <meta name="description" content="Start your development with a Dashboard for Bootstrap 5" />
    <meta name="keywords" content="dashboard, bootstrap 5 dashboard, bootstrap 5 design, bootstrap 5">
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon"
        href="//demos.pixinvent.com/vuexy-html-admin-template/assets/img/favicon/favicon.ico" />

    <!-- Fonts -->
    <link rel="preconnect" href="//fonts.googleapis.com/">
    <link rel="preconnect" href="//fonts.gstatic.com/" crossorigin>
    <link
        href="//fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&amp;ampdisplay=swap"
        rel="stylesheet">

    <!-- Icons -->
    <link rel="stylesheet" href="{{ url('/') }}/assets/vendor/fonts/fontawesome.css" />
    <link rel="stylesheet" href="{{ url('/') }}/assets/vendor/fonts/tabler-icons.css" />
    <link rel="stylesheet" href="{{ url('/') }}/assets/vendor/fonts/flag-icons.css" />
    <!-- Core CSS -->

    <link rel="stylesheet" href="{{ url('/') }}/assets/css/demo.css" />

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="{{ url('/') }}/assets/vendor/libs/node-waves/node-waves.css" />

    <link rel="stylesheet" href="{{ url('/') }}/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
    <link rel="stylesheet" href="{{ url('/') }}/assets/vendor/libs/typeahead-js/typeahead.css" />
    <link rel="stylesheet" href="{{ url('/') }}/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css">
    <link rel="stylesheet"
        href="{{ url('/') }}/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css">
    <link rel="stylesheet" href="{{ url('/') }}/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css">
    <link rel="stylesheet" href="{{ url('/') }}/assets/vendor/libs/select2/select2.css" />
    <link rel="stylesheet" href="{{ url('/') }}/assets/vendor/libs/%40form-validation/form-validation.css" />

    <!-- Page CSS -->

    <!-- Helpers -->
    <script src="{{ url('/') }}/assets/vendor/js/helpers.js"></script>
    <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->

    <!--? Template customizer: To hide customizer set displayCustomizer value false in config.js.  -->
    <script src="{{ url('/') }}/assets/vendor/js/template-customizer.js"></script>

    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
    <script src="{{ url('/') }}/assets/js/config.js"></script>

</head>

<body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar  ">
        <div class="layout-container">

            <!-- Menu -->
            @include('layouts.sidebar')
            <!-- / Menu -->

            <!-- Layout container -->
            <div class="layout-page">

                <!-- Navbar -->
                @include('layouts.nav')
                <!-- / Navbar -->

                <!-- Content wrapper -->
                <div class="content-wrapper">

                    @yield('content')
                    <!-- / Content -->

                    @include('layouts.footer')

                    <div class="content-backdrop fade"></div>
                </div>
                <!-- Content wrapper -->
            </div>
            <!-- / Layout page -->
        </div>

        <!-- Overlay -->
        <div class="layout-overlay layout-menu-toggle"></div>

        <!-- Drag Target Area To SlideIn Menu On Small Screens -->
        <div class="drag-target"></div>

    </div>
    <!-- / Layout wrapper -->

    <!-- Core JS -->
    <!-- build:js assets/vendor/js/core.js -->

    <script src="{{ url('/') }}/assets/vendor/libs/jquery/jquery.js"></script>
    <script src="{{ url('/') }}/assets/vendor/libs/popper/popper.js"></script>
    <script src="{{ url('/') }}/assets/vendor/js/bootstrap.js"></script>
    <script src="{{ url('/') }}/assets/vendor/libs/node-waves/node-waves.js"></script>
    <script src="{{ url('/') }}/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="{{ url('/') }}/assets/vendor/libs/hammer/hammer.js"></script>
    <script src="{{ url('/') }}/assets/vendor/libs/i18n/i18n.js"></script>
    <script src="{{ url('/') }}/assets/vendor/libs/typeahead-js/typeahead.js"></script>
    <script src="{{ url('/') }}/assets/vendor/js/menu.js"></script>
    <!-- endbuild -->

    <!-- Vendors JS -->
    <script src="{{ url('/') }}/assets/vendor/libs/moment/moment.js"></script>
    <script src="{{ url('/') }}/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js"></script>
    <script src="{{ url('/') }}/assets/vendor/libs/select2/select2.js"></script>
    <script src="{{ url('/') }}/assets/vendor/libs/%40form-validation/popular.js"></script>
    <script src="{{ url('/') }}/assets/vendor/libs/%40form-validation/bootstrap5.js"></script>
    <script src="{{ url('/') }}/assets/vendor/libs/%40form-validation/auto-focus.js"></script>
    <script src="{{ url('/') }}/assets/vendor/libs/cleavejs/cleave.js"></script>
    <script src="{{ url('/') }}/assets/vendor/libs/cleavejs/cleave-phone.js"></script>

    <!-- Main JS -->
    <script src="{{ url('/') }}/assets/js/main.js"></script>
    <link rel="stylesheet" href="//cdn.bootcss.com/toastr.js/latest/css/toastr.min.css">
    <script src="//cdn.bootcss.com/toastr.js/latest/js/toastr.min.js"></script>
    @stack('scripts')
    @stack('script')
    {!! Toastr::message() !!}
    {{-- // toastr notification --}}
    @if ($errors->any())
        @foreach ($errors->all() as $error)
            <script>
                toastr.error('{{ $error }}');
            </script>
        @endforeach
    @endif

    @if (session('error'))
        <script>
            toastr.error('{{ session('error') }}');
        </script>
    @endif

    @if (session('success'))
        <script>
            toastr.success('{{ session('success') }}');
        </script>
    @endif

    @if (session('info'))
        <script>
            toastr.info('{{ session('info') }}');
        </script>
    @endif

    @if (session('message'))
        <script>
            toastr.info('{{ session('message') }}');
        </script>
    @endif
    <script>
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "timeOut": "5000"
        };
    </script>


</body>

</html>