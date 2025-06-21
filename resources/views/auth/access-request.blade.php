<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Portal') }} - Request Access</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#E6F4EF]">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="w-full max-w-sm">
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="text-center mb-6">
                    <h1 class="text-xl font-semibold text-[#0B5D4C]">{{ config('app.name', 'Portal') }}</h1>
                    <p class="text-sm text-[#0B5D4C]/70 mt-1">Request access to the portal</p>
                </div>
                
                @if (session('status'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-3 py-2 rounded text-sm mb-4" role="alert">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('access.request.submit') }}" class="space-y-3">
                    @csrf
                    
                    <div>
                        <label for="name" class="block text-xs font-medium text-[#0B5D4C] mb-1">Full Name</label>
                        <input type="text" name="name" id="name" required
                            class="w-full px-3 py-1.5 text-sm rounded-md border border-[#0B5D4C]/20 focus:ring-1 focus:ring-[#C5A830] focus:border-transparent transition-colors"
                            value="{{ old('name') }}"
                            placeholder="Enter your full name">
                        @error('name')
                            <span class="text-red-600 text-xs mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-xs font-medium text-[#0B5D4C] mb-1">Email Address</label>
                        <input type="email" name="email" id="email" required
                            class="w-full px-3 py-1.5 text-sm rounded-md border border-[#0B5D4C]/20 focus:ring-1 focus:ring-[#C5A830] focus:border-transparent transition-colors"
                            value="{{ old('email') }}"
                            placeholder="Enter your email address">
                        @error('email')
                            <span class="text-red-600 text-xs mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label for="company" class="block text-xs font-medium text-[#0B5D4C] mb-1">Company Name</label>
                        <input type="text" name="company" id="company" required
                            class="w-full px-3 py-1.5 text-sm rounded-md border border-[#0B5D4C]/20 focus:ring-1 focus:ring-[#C5A830] focus:border-transparent transition-colors"
                            value="{{ old('company') }}"
                            placeholder="Enter your company name">
                        @error('company')
                            <span class="text-red-600 text-xs mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label for="reason" class="block text-xs font-medium text-[#0B5D4C] mb-1">Reason for Access</label>
                        <textarea name="reason" id="reason" required
                            class="w-full px-3 py-1.5 text-sm rounded-md border border-[#0B5D4C]/20 focus:ring-1 focus:ring-[#C5A830] focus:border-transparent transition-colors"
                            placeholder="Please explain why you need access to the portal"
                            rows="3">{{ old('reason') }}</textarea>
                        @error('reason')
                            <span class="text-red-600 text-xs mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <button type="submit" 
                        class="w-full bg-gradient-to-r from-[#D9BE50] to-[#C5A830] text-white px-4 py-1.5 text-sm rounded-md hover:opacity-90 transition-opacity shadow-sm">
                        Submit Request
                    </button>
                </form>

                <div class="mt-6 text-center">
                    <p class="text-xs text-[#0B5D4C]/70">
                        Already have an account? 
                        <a href="{{ route('login') }}" class="text-[#0B5D4C] hover:text-[#C5A830] transition-colors">Back to login</a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <footer class="text-center p-4 text-xs text-[#0B5D4C]/70">
        © {{ date('Y') }} {{ config('app.name', 'Portal') }}. All rights reserved.
    </footer>
</body>
</html> 