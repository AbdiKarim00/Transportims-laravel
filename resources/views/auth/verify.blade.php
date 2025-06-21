<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Portal') }} - Verify Email</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-background">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="w-full max-w-md">
            <div class="login-container">
                <div class="auth-header text-center mb-8">
                    <h1 class="text-2xl font-semibold text-shadow-sm">{{ config('app.name', 'Portal') }}</h1>
                    <h2 class="text-xl mt-2">Verify Your Email</h2>
                    <p class="text-muted-foreground mt-2">
                        Before proceeding, please check your email for a verification link.
                    </p>
                </div>
                
                @if (session('resent'))
                    <div class="auth-alert mb-4" role="alert">
                        A fresh verification link has been sent to your email address.
                    </div>
                @endif

                <div class="text-center space-y-4">
                    <p class="text-muted-foreground">
                        If you did not receive the email, click the button below to request another.
                    </p>

                    <form method="POST" action="{{ route('verification.resend') }}" class="inline">
                        @csrf
                        <button type="submit" class="gold-button">
                            Request Another Link
                        </button>
                    </form>
                </div>

                <div class="mt-6 text-center">
                    <p class="text-muted-foreground">
                        <a href="{{ route('login') }}" class="text-primary hover:text-primary/90">Back to login</a>
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
