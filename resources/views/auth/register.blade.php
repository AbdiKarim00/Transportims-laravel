<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Portal') }} - Register</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-background">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="w-full max-w-md">
            <div class="login-container">
                <div class="auth-header text-center mb-8">
                    <h1 class="text-2xl font-semibold text-shadow-sm">{{ config('app.name', 'Portal') }}</h1>
                    <h2 class="text-xl mt-2">Create Account</h2>
                    <p class="text-muted-foreground mt-2">
                        Fill in your details to create your account.
                    </p>
                </div>
                
                @if (session('status'))
                    <div class="auth-alert mb-4" role="alert">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('register') }}" class="space-y-4">
                    @csrf
                    
                    <div class="auth-input-group">
                        <label for="name" class="form-label">Full Name</label>
                        <input type="text" name="name" id="name" required
                            class="login-input @error('name') auth-input-error @enderror"
                            value="{{ old('name') }}"
                            placeholder="Enter your full name">
                        @error('name')
                            <span class="auth-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="auth-input-group">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" name="email" id="email" required
                            class="login-input @error('email') auth-input-error @enderror"
                            value="{{ old('email') }}"
                            placeholder="Enter your email address">
                        @error('email')
                            <span class="auth-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="auth-input-group">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" name="password" id="password" required
                            class="login-input @error('password') auth-input-error @enderror"
                            placeholder="Enter your password">
                        @error('password')
                            <span class="auth-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="auth-input-group">
                        <label for="password_confirmation" class="form-label">Confirm Password</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" required
                            class="login-input"
                            placeholder="Confirm your password">
                    </div>

                    <button type="submit" class="gold-button w-full">
                        Create Account
                    </button>
                </form>

                <div class="mt-6 text-center">
                    <p class="text-muted-foreground">
                        Already have an account? 
                        <a href="{{ route('login') }}" class="text-primary hover:text-primary/90">Sign in</a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <footer class="fixed bottom-0 w-full text-center p-4 text-muted-foreground text-sm">
        © {{ date('Y') }} {{ config('app.name', 'Portal') }}. All rights reserved.
    </footer>
</body>
</html>
