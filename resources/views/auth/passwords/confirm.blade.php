<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Portal') }} - Confirm Password</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-background">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="w-full max-w-md">
            <div class="login-container">
                <div class="auth-header text-center mb-8">
                    <h1 class="text-2xl font-semibold text-shadow-sm">{{ config('app.name', 'Portal') }}</h1>
                    <h2 class="text-xl mt-2">Confirm Password</h2>
                    <p class="text-muted-foreground mt-2">
                        Please confirm your password before continuing.
                    </p>
                </div>
                
                @if (session('status'))
                    <div class="auth-alert mb-4" role="alert">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('password.confirm') }}" class="space-y-4">
                    @csrf
                    
                    <div class="auth-input-group">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" name="password" id="password" required
                            class="login-input @error('password') auth-input-error @enderror"
                            placeholder="Enter your password">
                        @error('password')
                            <span class="auth-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <button type="submit" class="gold-button w-full">
                        Confirm Password
                    </button>

                    @if (Route::has('password.request'))
                        <div class="text-center">
                            <a href="{{ route('password.request') }}" class="text-sm text-primary hover:text-primary/90">
                                Forgot your password?
                            </a>
                        </div>
                    @endif
                </form>
            </div>
        </div>
    </div>

    <footer class="fixed bottom-0 w-full text-center p-4 text-muted-foreground text-sm">
        © {{ date('Y') }} {{ config('app.name', 'Portal') }}. All rights reserved.
    </footer>
</body>
</html>
