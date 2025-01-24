<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Call2Fix</title>
    <!-- ======= Styles ====== -->
    @vite('resources/css/app.css')
    <link rel="stylesheet" href="{{ asset('admin/assets/css/style.css') }}">
    <link rel="stylesheet" href="//cdn.datatables.net/2.1.3/css/dataTables.tailwindcss.css">
    <link rel="stylesheet" href="//cdn.bootcss.com/toastr.js/latest/css/toastr.min.css">
    @stack('css')
    <script src="//cdn.bootcss.com/jquery/2.2.4/jquery.min.js"></script>
</head>

<body>
    <!-- =============== Navigation ================ -->
    <div class="container">
        @include('layouts.sidebar')

        <!-- ========================= Main ==================== -->
        <div class="main">
            @yield('content')
        </div>
    </div>

    <!-- =========== Scripts =========  -->
    <script src="{{ asset('assets/js/main.js') }}"></script>

    <!-- ====== ionicons ======= -->
    <script type="module" src="//unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="//unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
    <!-- =========== Scripts =========  -->
    @stack('script')
    <script src="//cdn.bootcss.com/toastr.js/latest/js/toastr.min.js"></script>
    {!! Toastr::message() !!}
</body>

</html>
