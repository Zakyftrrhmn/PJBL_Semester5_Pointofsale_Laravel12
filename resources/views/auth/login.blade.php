<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Login | Point of Sale</title>

    @vite('resources/css/app.css')
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>

    <style>
        body {
            background-color: #f7f7f7;
            background-image: url("{{ asset('assets/images/default-login.png') }}");
            background-repeat: no-repeat;
            background-position: center;
            background-size: cover;
            position: relative;
        }

        body::before {
            content: "";
            position: absolute;
            inset: 0;
            background: rgba(255, 255, 255, 0.7);
        }

        /* styling disabled fieldset supaya jelas terlihat terkunci */
        fieldset[disabled] {
            opacity: 0.65;
        }

        /* progress bar */
        .countdown-bar {
            height: 6px;
            border-radius: 9999px;
            transition: width 1s linear;
        }
    </style>
</head>

<body class="flex items-center justify-center min-h-screen relative">

    <div class="relative z-10 bg-white bg-opacity-90 rounded-2xl shadow-lg p-10 w-full max-w-md">
        <div class="flex justify-center mb-6">
            <img src="{{ asset('assets/images/logo.png') }}" alt="Logo" class="h-20 object-contain">
        </div>

        <h1 class="text-2xl font-bold text-gray-800 text-center">Masuk ke Akun</h1>
        <p class="text-sm text-gray-500 text-center mt-2">Masukkan detail Anda untuk melanjutkan ke sistem</p>

        @if (session('error'))
            <div class="p-3 text-sm text-red-800 rounded-lg bg-red-100 mt-4">
                {{ session('error') }}
            </div>
        @endif

        <form action="/login" method="POST" class="mt-8">
            @csrf

            {{-- fieldset akan disabled seluruh input jika session('lockout') ada --}}
            <fieldset id="login-fieldset" @if (session('lockout')) disabled @endif class="space-y-5">
                {{-- EMAIL --}}
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" id="email"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-gray-900 focus:ring-2 focus:ring-yellow-400 focus:outline-none"
                        placeholder="Masukkan email anda" required value="{{ old('email') }}">
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- PASSWORD --}}
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input type="password" name="password" id="password"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-gray-900 focus:ring-2 focus:ring-yellow-400 focus:outline-none"
                        placeholder="••••••••" required>
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- reCAPTCHA (bungkus dengan relatif supaya kita bisa overlay saat lockout) --}}
                <div class="relative">
                    <div class="g-recaptcha" data-sitekey="{{ env('RECAPTCHA_SITEKEY') }}"></div>

                    {{-- overlay agar reCAPTCHA tidak bisa diklik selama lockout --}}
                    @if (session('lockout'))
                        <div id="recaptcha-overlay" class="absolute inset-0"></div>
                    @endif
                </div>

                {{-- tombol --}}
                <div class="flex items-center justify-between mt-2">
                    <button id="submit-btn" type="submit"
                        class="px-6 py-2 w-full bg-yellow-400 hover:bg-yellow-500 text-gray-800 font-semibold rounded-lg transition shadow-md disabled:opacity-60 disabled:cursor-not-allowed"
                        @if (session('lockout')) disabled @endif>
                        <i class='bx bx-log-in mr-1'></i> Masuk
                    </button>
                </div>
            </fieldset>
        </form>

        {{-- Lockout message + progress --}}
        @if ($remaining ?? 0)
            <div id="lockout-wrapper" class="mt-4 text-center text-sm text-red-600 font-medium">
                ⚠️ Terlalu banyak percobaan login. Coba lagi dalam
                <span id="lock-seconds">{{ $remaining }}</span> detik.
            </div>

            <div class="w-full bg-gray-200 rounded-full mt-2 overflow-hidden">
                <div id="countdown-bar" class="countdown-bar bg-yellow-400" style="width:100%"></div>
            </div>

            <script>
                (function() {
                    const secondsEl = document.getElementById('lock-seconds');
                    const barEl = document.getElementById('countdown-bar');
                    const submitBtn = document.getElementById('submit-btn');
                    const fieldset = document.getElementById('login-fieldset');
                    const lockWrapper = document.getElementById('lockout-wrapper');
                    let total = parseInt(secondsEl.textContent || '0', 10);
                    let seconds = total;

                    // disable semua input
                    if (fieldset) fieldset.disabled = true;
                    if (submitBtn) submitBtn.disabled = true;

                    const timer = setInterval(() => {
                        seconds--;
                        if (seconds < 0) seconds = 0;
                        secondsEl.textContent = seconds;
                        if (barEl && total > 0) {
                            barEl.style.width = ((seconds / total) * 100) + '%';
                        }
                        if (seconds <= 0) {
                            clearInterval(timer);
                            fieldset.disabled = false;
                            submitBtn.disabled = false;
                            if (lockWrapper) lockWrapper.remove();
                            if (barEl) barEl.remove();
                        }
                    }, 1000);
                })();
            </script>
        @endif


        <p class="text-xs text-center text-gray-400 mt-6">&copy; {{ date('Y') }} Point of Sale System</p>
    </div>

    @vite('resources/js/app.js')
</body>

</html>
