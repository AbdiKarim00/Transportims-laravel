<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - Admin</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const userDropdown = document.getElementById('userDropdown');
            const userDropdownMenu = document.getElementById('userDropdownMenu');

            userDropdown.addEventListener('click', function(e) {
                e.stopPropagation();
                userDropdownMenu.classList.toggle('hidden');
            });

            document.addEventListener('click', function(e) {
                if (!userDropdown.contains(e.target) && !userDropdownMenu.contains(e.target)) {
                    userDropdownMenu.classList.add('hidden');
                }
            });
        });
    </script>
</head>

<body class="font-sans antialiased">
    <div class="min-h-screen bg-background">
        <div class="flex">
            <!-- Sidebar -->
            <div class="w-64 min-h-screen bg-secondary text-secondary-foreground">
                <div class="p-4">
                    <h2 class="text-xl font-semibold">{{ config('app.name', 'Laravel') }}</h2>
                    <p class="text-xs text-secondary-foreground/70">Management Dashboard</p>
                </div>
                <nav class="mt-4">
                    <!-- Management Analytics Dashboard Links -->
                    <div class="px-4 mb-2 text-xs font-semibold text-secondary-foreground/50 uppercase">
                        Management Dashboard
                    </div>
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center px-4 py-2 text-secondary-foreground/70 hover:bg-secondary/80 hover:text-secondary-foreground {{ request()->routeIs('admin.dashboard') ? 'bg-secondary/80 text-secondary-foreground' : '' }}">
                        <i class="fas fa-tachometer-alt w-5"></i>
                        <span class="ml-2">Analytics Overview</span>
                    </a>

                    <!-- Fleet Analytics Section -->
                    <div class="px-4 mt-4 mb-2 text-xs font-semibold text-secondary-foreground/50 uppercase">
                        Fleet Analytics
                    </div>
                    <a href="{{ route('admin.vehicle-analytics') }}" class="flex items-center px-4 py-2 text-secondary-foreground/70 hover:bg-secondary/80 hover:text-secondary-foreground {{ request()->routeIs('admin.vehicle-analytics') ? 'bg-secondary/80 text-secondary-foreground' : '' }}">
                        <i class="fas fa-chart-line w-5"></i>
                        <span class="ml-2">Vehicle Analytics</span>
                    </a>
                    <a href="{{ route('admin.driver-analytics') }}" class="flex items-center px-4 py-2 text-secondary-foreground/70 hover:bg-secondary/80 hover:text-secondary-foreground {{ request()->routeIs('admin.driver-analytics') ? 'bg-secondary/80 text-secondary-foreground' : '' }}">
                        <i class="fas fa-user-chart w-5"></i>
                        <span class="ml-2">Driver Analytics</span>
                    </a>
                    <a href="{{ route('admin.trip-analytics') }}" class="flex items-center px-4 py-2 text-secondary-foreground/70 hover:bg-secondary/80 hover:text-secondary-foreground {{ request()->routeIs('admin.trip-analytics') ? 'bg-secondary/80 text-secondary-foreground' : '' }}">
                        <i class="fas fa-route w-5"></i>
                        <span class="ml-2">Trip Analytics</span>
                    </a>

                    <!-- Safety Management Section -->
                    <div class="px-4 mt-4 mb-2 text-xs font-semibold text-secondary-foreground/50 uppercase">
                        Safety Management
                    </div>
                    <a href="{{ route('admin.safety-dashboard') }}" class="flex items-center px-4 py-2 text-secondary-foreground/70 hover:bg-secondary/80 hover:text-secondary-foreground {{ request()->routeIs('admin.safety-dashboard') ? 'bg-secondary/80 text-secondary-foreground' : '' }}">
                        <i class="fas fa-shield-alt w-5"></i>
                        <span class="ml-2">Safety Dashboard</span>
                    </a>
                    <a href="{{ route('admin.safety.incidents') }}" class="flex items-center px-4 py-2 text-secondary-foreground/70 hover:bg-secondary/80 hover:text-secondary-foreground {{ request()->routeIs('admin.safety.incidents') ? 'bg-secondary/80 text-secondary-foreground' : '' }}">
                        <i class="fas fa-exclamation-triangle w-5"></i>
                        <span class="ml-2">Incident Reports</span>
                    </a>
                    <a href="{{ route('admin.safety.compliance') }}" class="flex items-center px-4 py-2 text-secondary-foreground/70 hover:bg-secondary/80 hover:text-secondary-foreground {{ request()->routeIs('admin.safety.compliance') ? 'bg-secondary/80 text-secondary-foreground' : '' }}">
                        <i class="fas fa-clipboard-check w-5"></i>
                        <span class="ml-2">Safety Compliance</span>
                    </a>
                    <a href="{{ route('admin.safety.risk-assessment') }}" class="flex items-center px-4 py-2 text-secondary-foreground/70 hover:bg-secondary/80 hover:text-secondary-foreground {{ request()->routeIs('admin.safety.risk-assessment') ? 'bg-secondary/80 text-secondary-foreground' : '' }}">
                        <i class="fas fa-chart-bar w-5"></i>
                        <span class="ml-2">Risk Assessment</span>
                    </a>

                    <!-- Financial Management Section -->
                    <div class="px-4 mt-4 mb-2 text-xs font-semibold text-secondary-foreground/50 uppercase">
                        Financial Management
                    </div>
                    <a href="{{ route('admin.financial-management.index') }}" class="flex items-center px-4 py-2 text-secondary-foreground/70 hover:bg-secondary/80 hover:text-secondary-foreground {{ request()->routeIs('admin.financial-management.index') ? 'bg-secondary/80 text-secondary-foreground' : '' }}">
                        <i class="fas fa-chart-pie w-5"></i>
                        <span class="ml-2">Financial Overview</span>
                    </a>

                    <!-- Reports Section -->
                    <div class="px-4 mt-4 mb-2 text-xs font-semibold text-secondary-foreground/50 uppercase">
                        Reports
                    </div>
                    <a href="{{ route('admin.maintenance-reports') }}" class="flex items-center px-4 py-2 text-secondary-foreground/70 hover:bg-secondary/80 hover:text-secondary-foreground {{ request()->routeIs('admin.maintenance-reports') ? 'bg-secondary/80 text-secondary-foreground' : '' }}">
                        <i class="fas fa-tools w-5"></i>
                        <span class="ml-2">Maintenance Reports</span>
                    </a>
                    <a href="{{ route('admin.export-reports') }}" class="flex items-center px-4 py-2 text-secondary-foreground/70 hover:bg-secondary/80 hover:text-secondary-foreground {{ request()->routeIs('admin.export-reports') ? 'bg-secondary/80 text-secondary-foreground' : '' }}">
                        <i class="fas fa-file-export w-5"></i>
                        <span class="ml-2">Export Reports</span>
                    </a>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="flex-1">
                <!-- Top Navigation -->
                <nav class="bg-card border-b border-border">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div class="flex justify-between h-16">
                            <div class="flex">
                                <button class="lg:hidden px-4 text-muted-foreground hover:text-foreground focus:outline-none" id="sidebarToggle">
                                    <i class="fas fa-bars"></i>
                                </button>
                            </div>

                            <!-- Settings Dropdown -->
                            <div class="flex items-center">
                                <div class="relative">
                                    <button class="flex items-center text-sm font-medium text-muted-foreground hover:text-foreground focus:outline-none transition duration-150 ease-in-out" type="button" id="userDropdown" data-bs-toggle="dropdown">
                                        <i class="fas fa-user-circle mr-2"></i>
                                        {{ Auth::user()->name }}
                                    </button>
                                    <div class="absolute right-0 mt-2 w-48 bg-card rounded-md shadow-lg py-1 hidden" id="userDropdownMenu">
                                        <a href="{{ route('password.change') }}" class="block px-4 py-2 text-sm text-muted-foreground hover:bg-muted">
                                            <i class="fas fa-key mr-2"></i> Change Password
                                        </a>
                                        <form method="POST" action="{{ route('logout') }}" class="block">
                                            @csrf
                                            <button type="submit" class="w-full text-left px-4 py-2 text-sm text-muted-foreground hover:bg-muted">
                                                <i class="fas fa-sign-out-alt mr-2"></i> Logout
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </nav>

                <!-- Page Content -->
                <main class="p-6">
                    @yield('content')
                </main>
            </div>
        </div>
    </div>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // Toggle sidebar on mobile
        document.getElementById('sidebarToggle')?.addEventListener('click', function() {
            document.querySelector('.w-64').classList.toggle('hidden');
        });
    </script>
</body>

</html>