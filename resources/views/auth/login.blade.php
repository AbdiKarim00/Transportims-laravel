<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Portal') }} - Login</title>
    @vite(['resources/css/app.css', 'resources/css/login.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-gradient-to-br from-[#E6F4EF] to-[#D4EBE4]">
    <div class="flex min-h-screen flex-col md:flex-row">
        <!-- Left Panel - Welcome Section (Desktop Only) -->
        <div class="hidden md:flex md:w-1/2 items-center justify-center p-8">
            <div class="max-w-lg w-full space-y-8 relative">
                <div class="absolute inset-0 bg-gradient-to-r from-[#E6F4EF]/50 to-[#D4EBE4]/50 rounded-3xl welcome-gradient"></div>
                <div class="relative">
                    <!-- Logo -->
                    <div class="flex items-center space-x-3 mb-8">
                        <svg class="w-12 h-12 text-[#0B5D4C]" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 2L2 7L12 12L22 7L12 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M2 17L12 22L22 17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M2 12L12 17L22 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <span class="text-3xl font-bold text-[#0B5D4C]">Portal</span>
                    </div>

                    <!-- Welcome Text -->
                    <h1 class="text-4xl font-bold text-[#0B5D4C] mb-4">Welcome to Portal</h1>
                    <p class="text-lg text-[#1F2937] leading-relaxed mb-12">
                        Access your secure workspace with enterprise-grade security and seamless experience.
                    </p>

                    <!-- Feature Highlights -->
                    <div class="space-y-6">
                        <div class="flex items-center space-x-4">
                            <div class="p-2 bg-[#0B5D4C]/10 rounded-lg">
                                <svg class="w-6 h-6 text-[#0B5D4C]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <span class="text-[#1F2937]">Secure Login</span>
                        </div>
                        <div class="flex items-center space-x-4">
                            <div class="p-2 bg-[#0B5D4C]/10 rounded-lg">
                                <svg class="w-6 h-6 text-[#0B5D4C]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                            </div>
                            <span class="text-[#1F2937]">Fast Access</span>
                        </div>
                        <div class="flex items-center space-x-4">
                            <div class="p-2 bg-[#0B5D4C]/10 rounded-lg">
                                <svg class="w-6 h-6 text-[#0B5D4C]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            </div>
                            <span class="text-[#1F2937]">24/7 Support</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Panel - Login Form -->
        <div class="w-full md:w-1/2 flex items-center justify-center p-4 md:p-8">
            <div class="w-full max-w-md space-y-8">
                <!-- Mobile Header -->
                <div class="md:hidden text-center mb-8">
                    <div class="flex items-center justify-center space-x-3 mb-4">
                        <svg class="w-10 h-10 text-[#0B5D4C]" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 2L2 7L12 12L22 7L12 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M2 17L12 22L22 17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M2 12L12 17L22 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <span class="text-2xl font-bold text-[#0B5D4C]">Portal</span>
                    </div>
                    <h2 class="text-2xl font-bold text-[#0B5D4C]">Welcome Back</h2>
                </div>

                <!-- Login Form -->
                <div class="bg-white rounded-xl shadow-lg p-8">
                    <!-- Error Display -->
                    @if ($errors->any())
                        <div class="alert alert-danger mb-4">
                            <ul class="list-disc list-inside text-red-600">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger mb-4 text-red-600">
                            {{ session('error') }}
                        </div>
                    @endif
                    <form method="POST" action="{{ route('login') }}" class="space-y-6">
                        @csrf

                        <!-- Personal Number Input -->
                        <div>
                            <label for="personal_number" class="block text-sm font-medium text-[#1F2937] mb-2">Personal Number</label>
                            <input type="text" name="personal_number" id="personal_number" required
                                class="w-full px-4 py-3 rounded-lg border border-[#E6F4EF] focus:ring-2 focus:ring-[#C5A830] focus:border-transparent"
                                placeholder="Enter your personal number">
                        </div>

                        <!-- Password Input -->
                        <div>
                            <label for="password" class="block text-sm font-medium text-[#1F2937] mb-2">Password</label>
                            <input type="password" name="password" id="password" required
                                class="w-full px-4 py-3 rounded-lg border border-[#E6F4EF] focus:ring-2 focus:ring-[#C5A830] focus:border-transparent"
                                placeholder="Enter your password">
                        </div>

                        <!-- Remember Me & Forgot Password -->
                        <div class="flex items-center justify-between">
                            <label class="custom-checkbox">
                                <input type="checkbox" name="remember" id="remember">
                                <span class="checkmark"></span>
                                <span class="ml-2 text-sm text-[#6B7280]">Remember me</span>
                            </label>
                            <a href="{{ route('password.request') }}"
                                class="text-sm font-medium text-[#0B5D4C] hover:text-[#C5A830]">
                                Forgot password?
                            </a>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit"
                            class="w-full py-3 px-4 bg-gradient-to-r from-[#C5A830] to-[#D4B94A] text-white font-medium rounded-lg shadow-md focus:outline-none focus:ring-2 focus:ring-[#C5A830] focus:ring-offset-2">
                            Sign in
                        </button>
                    </form>

                    <!-- Footer -->
                    <div class="mt-8 text-center">
                        <p class="text-sm text-[#6B7280]">
                            Need an account?
                            <a href="{{ route('access.request') }}" class="font-medium text-[#0B5D4C] hover:text-[#C5A830] link-hover">
                                Contact administrator
                            </a>
                        </p>
                        <p class="mt-4 text-xs text-[#0B5D4C]">
                            © {{ date('Y') }} Portal. All rights reserved.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>