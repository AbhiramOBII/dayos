<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>DayOS</title>
    <link rel="icon" type="image/png" href="/images/app-icon.png">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet"/>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif;
            min-height: 100svh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background-color: #151828;
            color: #F5F0E8;
            padding: 2rem 1.5rem;
        }
        .card {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1.75rem;
            width: 100%;
            max-width: 320px;
            text-align: center;
        }
        .icon {
            width: 88px;
            height: 88px;
            border-radius: 22px;
            object-fit: cover;
            box-shadow: 0 8px 32px rgba(0,0,0,0.4);
        }
        .wordmark {
            font-size: 2.25rem;
            font-weight: 700;
            letter-spacing: -0.03em;
            color: #F5F0E8;
            line-height: 1;
        }
        .tagline {
            font-size: 0.875rem;
            color: rgba(245,240,232,0.5);
            margin-top: 0.375rem;
            letter-spacing: 0.01em;
        }
        .btn {
            display: block;
            width: 100%;
            padding: 0.875rem 1.5rem;
            background: #F5F0E8;
            color: #151828;
            font-size: 0.9375rem;
            font-weight: 600;
            border-radius: 12px;
            text-decoration: none;
            transition: opacity 0.15s;
        }
        .btn:hover { opacity: 0.88; }
        footer {
            position: fixed;
            bottom: env(safe-area-inset-bottom, 1.5rem);
            font-size: 0.75rem;
            color: rgba(245,240,232,0.2);
        }
    </style>
</head>
<body>
    <div class="card">
        <img src="/images/app-icon.png" alt="DayOS" class="icon">
        <div>
            <div class="wordmark">DayOS</div>
            <p class="tagline">Your personal productivity OS</p>
        </div>
        <a href="{{ route('admin.login') }}" class="btn">Sign in</a>
    </div>
    <footer>&copy; {{ date('Y') }} DayOS</footer>
</body>
</html>
