<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Admin Login — Dashboard Pelatihan</title>
    @livewireStyles
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1565c0 0%, #0d47a1 50%, #01579b 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
        }
        .login-card {
            background: white;
            border-radius: 16px;
            padding: 40px 32px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .login-logo {
            text-align: center;
            margin-bottom: 28px;
        }
        .login-logo .logo-img {
            width: 72px;
            height: 72px;
            border-radius: 14px;
            object-fit: cover;
            margin-bottom: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .login-logo h1 { font-size: 1.3rem; font-weight: 800; color: #1565c0; }
        .login-logo p { font-size: 0.82rem; color: #888; margin-top: 4px; }
        .form-group { margin-bottom: 18px; }
        .form-group label {
            display: block; font-size: 0.82rem; font-weight: 600; color: #444; margin-bottom: 6px;
        }
        .form-group input {
            width: 100%; padding: 11px 14px; border: 2px solid #e0e0e0;
            border-radius: 10px; font-size: 0.92rem; outline: none; transition: border-color 0.2s; color: #333;
        }
        .form-group input:focus { border-color: #1565c0; }
        .btn-login {
            width: 100%; padding: 13px; background: #1565c0; color: white; border: none;
            border-radius: 10px; font-size: 0.95rem; font-weight: 700; cursor: pointer;
            transition: all 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
            margin-top: 6px;
        }
        .btn-login:hover {
            background: #0d47a1;
            transform: translateY(-2px) scale(1.02);
            box-shadow: 0 8px 20px rgba(13, 71, 161, 0.4);
        }
        .btn-login:active { transform: scale(0.98); }
        .error-box {
            background: #ffebee; border: 1px solid #ef9a9a; border-radius: 8px;
            padding: 10px 14px; font-size: 0.82rem; color: #c62828; margin-bottom: 14px;
            display: flex; align-items: center; gap: 8px;
        }
        .back-link {
            display: block; text-align: center; margin-top: 18px;
            font-size: 0.82rem; color: #1565c0; text-decoration: none;
        }
        .back-link:hover { text-decoration: underline; }
        @media (max-width: 480px) {
            .login-card { padding: 28px 20px; }
            .login-logo .logo-img { width: 60px; height: 60px; }
            .login-logo h1 { font-size: 1.15rem; }
        }
    </style>
</head>
<body>
    {{ $slot }}
    @livewireScripts
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof gsap !== 'undefined') {
                gsap.from('.login-card', { y: 60, scale: 0.85, opacity: 0, duration: 0.75, ease: 'back.out(2)' });
                gsap.from('.login-logo .logo-img', { scale: 0, rotation: -20, duration: 0.7, delay: 0.2, ease: 'back.out(2.5)' });
                gsap.from('.form-group, .btn-login, .back-link', { y: 20, opacity: 0, stagger: 0.06, duration: 0.5, delay: 0.35, ease: 'power2.out' });
            }
        });
    </script>
</body>
</html>
