<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- PWA Manifest -->
        <link rel="manifest" href="/manifest.json">
        <meta name="theme-color" content="#1f2937">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
        <meta name="apple-mobile-web-app-title" content="Logje">
        <meta name="apple-touch-fullscreen" content="yes">
        <meta name="mobile-web-app-capable" content="yes">
        <link rel="apple-touch-icon" href="/images/icons/icon-192x192.png">
        <link rel="apple-touch-icon" sizes="72x72" href="/images/icons/icon-72x72.png">
        <link rel="apple-touch-icon" sizes="96x96" href="/images/icons/icon-96x96.png">
        <link rel="apple-touch-icon" sizes="144x144" href="/images/icons/icon-144x144.png">
        <link rel="apple-touch-icon" sizes="192x192" href="/images/icons/icon-192x192.png">
        <link rel="icon" type="image/png" sizes="192x192" href="/images/icons/icon-192x192.png">
        <link rel="icon" type="image/png" sizes="512x512" href="/images/icons/icon-512x512.png">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            <livewire:layout.navigation />

            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>

        <!-- PWA Service Worker Registration -->
        <script>
            if ('serviceWorker' in navigator) {
                window.addEventListener('load', function() {
                    navigator.serviceWorker.register('/sw.js')
                        .then(function(registration) {
                            console.log('ServiceWorker registration successful with scope: ', registration.scope);
                        }, function(err) {
                            console.log('ServiceWorker registration failed: ', err);
                        });
                });
            }

            // PWA Install Prompt
            let deferredPrompt;
            const installButton = document.createElement('button');
            installButton.textContent = 'Install App';
            installButton.style.cssText = 'position: fixed; bottom: 20px; right: 20px; background: #1f2937; color: white; border: none; padding: 10px 15px; border-radius: 5px; cursor: pointer; display: none; z-index: 1000;';
            document.body.appendChild(installButton);

            window.addEventListener('beforeinstallprompt', (e) => {
                console.log('beforeinstallprompt event fired');
                e.preventDefault();
                deferredPrompt = e;
                installButton.style.display = 'block';
            });

            installButton.addEventListener('click', (e) => {
                installButton.style.display = 'none';
                if (deferredPrompt) {
                    deferredPrompt.prompt();
                    deferredPrompt.userChoice.then((choiceResult) => {
                        if (choiceResult.outcome === 'accepted') {
                            console.log('User accepted the install prompt');
                        } else {
                            console.log('User dismissed the install prompt');
                        }
                        deferredPrompt = null;
                    });
                }
            });

            window.addEventListener('appinstalled', (evt) => {
                console.log('App was installed');
                installButton.style.display = 'none';
            });
        </script>
    </body>
</html>
