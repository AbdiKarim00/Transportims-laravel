<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Portal') }} - Request Access</title>
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
                <h2 class="text-2xl font-bold text-[#0B5D4C]">Request Access</h2>
                <p class="mt-2 text-[#1F2937]">
                    Fill out the form below to request access to the portal.
                </p>
            </div>

            <!-- Request Form -->
            <div class="bg-white rounded-xl shadow-lg p-8 card-hover">
                @if (session('status'))
                <div class="mb-4 p-4 text-sm text-green-700 bg-green-100 rounded-lg" role="alert">
                    {{ session('status') }}
                </div>
                @endif

                <form method="POST" action="{{ route('access.request') }}" class="space-y-6">
                    @csrf

                    <!-- Name Input -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-[#1F2937] mb-2">Full Name</label>
                        <input type="text" name="name" id="name" required
                            class="w-full px-4 py-3 rounded-lg border border-[#E6F4EF] focus:ring-2 focus:ring-[#C5A830] focus:border-transparent @error('name') border-red-500 @enderror"
                            value="{{ old('name') }}"
                            placeholder="Enter your full name">
                        @error('name')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

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

                    <!-- Department Input -->
                    <div>
                        <label for="department" class="block text-sm font-medium text-[#1F2937] mb-2">Department</label>
                        <input type="text" name="department" id="department" required
                            class="w-full px-4 py-3 rounded-lg border border-[#E6F4EF] focus:ring-2 focus:ring-[#C5A830] focus:border-transparent @error('department') border-red-500 @enderror"
                            value="{{ old('department') }}"
                            placeholder="Enter your department">
                        @error('department')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Message Input -->
                    <div>
                        <label for="message" class="block text-sm font-medium text-[#1F2937] mb-2">Message (Optional)</label>
                        <textarea name="message" id="message" rows="3"
                            class="w-full px-4 py-3 rounded-lg border border-[#E6F4EF] focus:ring-2 focus:ring-[#C5A830] focus:border-transparent @error('message') border-red-500 @enderror"
                            placeholder="Any additional information you'd like to provide">{{ old('message') }}</textarea>
                        @error('message')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Submit Button -->
                    <button type="submit"
                        class="w-full py-3 px-4 bg-gradient-to-r from-[#C5A830] to-[#D4B94A] text-white font-medium rounded-lg shadow-md focus:outline-none focus:ring-2 focus:ring-[#C5A830] focus:ring-offset-2 hover:from-[#D4B94A] hover:to-[#C5A830] transition-all duration-300">
                        Submit Request
                    </button>
                </form>

                <!-- Footer -->
                <div class="mt-8 text-center">
                    <p class="text-sm text-[#6B7280]">
                        Already have an account?
                        <a href="{{ route('login') }}" class="font-medium text-[#0B5D4C] hover:text-[#C5A830] link-hover">
                            Sign in
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