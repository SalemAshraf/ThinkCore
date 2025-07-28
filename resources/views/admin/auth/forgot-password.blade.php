<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>Forgot password - Tabler - Premium and Open Source dashboard template with responsive and high quality UI.
    </title>
    <!-- CSS files -->
    <link href="{{ asset('admin/assets/dist/css/tabler.min.css?1692870487') }}" rel="stylesheet" />
    <link href="{{ asset('admin/assets/dist/css/tabler-flags.min.css?1692870487') }}" rel="stylesheet" />
    <link href="{{ asset('admin/assets/dist/css/tabler-payments.min.css?1692870487') }}" rel="stylesheet" />
    <link href="{{ asset('admin/assets/dist/css/tabler-vendors.min.css?1692870487') }}" rel="stylesheet" />
    <link href="{{ asset('admin/assets/dist/css/demo.min.css?1692870487') }}" rel="stylesheet" />
    <style>
        @import url('https://rsms.me/inter/inter.css');

        :root {
            --tblr-font-sans-serif: 'Inter Var', -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif;
        }

        body {
            font-feature-settings: "cv03", "cv04", "cv11";
        }
    </style>
</head>

<body class=" d-flex flex-column">
    <script src="{{ asset('admin/assets/dist/js/demo-theme.min.js?1692870487') }}"></script>
    <div class="page page-center">
        <div class="container container-tight py-4">
            <div class="text-center mb-4">
                <a href="." class="navbar-brand navbar-brand-autodark">
                    <img src="{{ asset('admin/assets/static/logo.png') }}" width="110" height="32" alt="Tabler"
                        class="navbar-brand-image">
                </a>
            </div>
            <x-auth-session-status class="mb-4" :status="session('status')" />
            <form class="card card-md" action="{{ route('admin.password.email') }}" method="POST" autocomplete="off"
                novalidate>
                @csrf
                <div class="card-body">
                    <h2 class="card-title text-center mb-4">Forgot password</h2>
                    <p class="text-secondary mb-4">Enter your email address and your password will be reset and emailed
                        to you.</p>

                    <div class="mb-3">
                        <label class="form-label">Email address</label>
                        <input type="email" name="email" value="{{ old('email') }}" class="form-control"
                            placeholder="your@email.com" autocomplete="off" required>
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>
                    <div class="form-footer">
                        <button type="submit" class="btn btn-primary w-100">Send me new password</button>
                    </div>
                </div>
            </form>
            <div class="text-center text-secondary mt-3">
                Forget it, <a href="./sign-in.html">send me back</a> to the sign in screen.
            </div>
        </div>
    </div>
    <!-- Libs JS -->
    <!-- Tabler Core -->
    <script src="{{ asset('admin/assets/dist/js/tabler.min.js?1692870487') }}" defer></script>
    <script src="{{ asset('admin/assets/dist/js/demo.min.js?1692870487') }}" defer></script>
</body>

</html>
