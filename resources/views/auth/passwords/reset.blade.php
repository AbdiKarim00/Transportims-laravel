<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Portal') }} - Forgot Password</title>
    @vite(['resources/css/app.css', 'resources/css/login.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-gradient-to-br from-[#E6F4EF] to-[#D4EBE4]">
    <div class="flex min-h-screen items-center justify-center p-4">
        <div class="w-full max-w-md space-y-8">
            <!-- Header -->
            <div class="text-center mb-8">
                <div class="flex items-center justify-center space-x-3 mb-4">
                    <svg class="w-10 h-10 text-[#0B5D4C]" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2L2 7L12 12L22 7L12 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M2 17L12 22L22 17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M2 12L12 17L22 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <span class="text-2xl font-bold text-[#0B5D4C]">Portal</span>
                </div>
                <h2 class="text-2xl font-bold text-[#0B5D4C]">Forgot Password</h2>
                <p class="mt-2 text-[#1F2937]">
                    Enter your email address and we'll send you instructions to reset your password.
                </p>
            </div>

            <!-- Reset Form -->
            <div class="bg-white rounded-xl shadow-lg p-8 card-hover">
                @if (session('status'))
                <div class="mb-4 p-4 text-sm text-green-700 bg-green-100 rounded-lg" role="alert">
                    {{ session('status') }}
                </div>
                @endif

                <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
                    @csrf

                    <!-- Email Input -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-[#1F2937] mb-2">Email Address</label>
                        <input type="email" name="email" id="email" required
                            class="w-full px-4 py-3 rounded-lg border border-[#E6F4EF] focus:ring-2 focus:ring-[#C5A830] focus:border-transparent @error('email') border-red-500 @enderror"
                            value="{{ old('email') }}"
                            placeholder="Enter your email address">
                        @error('email')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Submit Button -->
                    <button type="submit"
                        class="w-full py-3 px-4 bg-gradient-to-r from-[#C5A830] to-[#D4B94A] text-white font-medium rounded-lg shadow-md focus:outline-none focus:ring-2 focus:ring-[#C5A830] focus:ring-offset-2 hover:from-[#D4B94A] hover:to-[#C5A830] transition-all duration-300">
                        Send Reset Link
                    </button>
                </form>

                <!-- Footer -->
                <div class="mt-8 text-center">
                    <p class="text-sm text-[#6B7280]">
                        Remember your password?
                        <a href="{{ route('login') }}" class="font-medium text-[#0B5D4C] hover:text-[#C5A830] link-hover">
                            Back to login
                        </a>
                    </p>
                    <p class="mt-4 text-xs text-[#0B5D4C]">
                        © {{ date('Y') }} Portal. All rights reserved.
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>

</html>