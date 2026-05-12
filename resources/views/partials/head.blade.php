<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>
    {{ filled($title ?? null) ? $title.' - '.config('app.name', 'Laravel') : config('app.name', 'Laravel') }}
</title>

<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">

<script>
    (() => {
        try {
            const appearanceKey = 'flux.appearance';
            const appearance = window.localStorage.getItem(appearanceKey);

            if (appearance === null || appearance === 'system') {
                window.localStorage.setItem(appearanceKey, 'light');
            }
        } catch (error) {
            // Ignore storage issues and let Flux fall back to its defaults.
        }
    })();
</script>

@fonts

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
