<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Ameen - Bootstrap Admin Dashboard</title>
    
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/images/favicon.png') }}">
    
    <!-- Stylesheets -->
    <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet">
    
    <link href="{{ asset('assets/plugins/tables/css/datatable/dataTables.bootstrap4.min.css') }}" rel="stylesheet">

    <!-- Scripts -->
    <script src="{{ asset('assets/js/modernizr-3.6.0.min.js') }}"></script>

    @stack('styles') 
</head>

<body class="v-light vertical-nav fix-header fix-sidebar">
    
    <!-- Preloader -->
    <div id="preloader">
        <div class="loader">
            <svg class="circular" viewBox="25 25 50 50">
                <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="3" stroke-miterlimit="10"/>
            </svg>
        </div>
    </div>

    <!-- Main Wrapper -->
    <div id="main-wrapper">
        
        <!-- Header -->
        @include('partials.header')
        
        <!-- Sidebar -->
        @include('partials.sidebar') 
        
        <!-- Content Body -->
        <div class="content-body">
            <div class="container">
                @yield('content') 
            </div>
        </div>

        <!-- Footer -->
        @include('partials.footer') 
        
    </div>

    <!-- Common JS -->
    <script src="{{ asset('assets/plugins/common/common.min.js') }}"></script>
    
    <!-- Custom Script -->
    <script src="{{ asset('assets/js/custom.min.js') }}"></script>
    
    <!-- Chartjs chart -->
    <script src="{{ asset('assets/plugins/chartjs/Chart.bundle.js') }}"></script>
    
    <!-- Custom dashboard script -->
    <script src="{{ asset('assets/js/dashboard-1.js') }}"></script>
    <script src="{{ asset('assets/plugins/tables/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/tables/js/datatable/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/tables/js/datatable-init/datatable-basic.min.js') }}"></script>

    @stack('scripts') 

</body>

</html>
