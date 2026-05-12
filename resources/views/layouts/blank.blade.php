<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-stone-100 text-zinc-950 antialiased dark:bg-zinc-950 dark:text-zinc-50">
        <a
            href="#app-main"
            class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50 focus:rounded-full focus:bg-zinc-950 focus:px-4 focus:py-2 focus:text-sm focus:font-medium focus:text-white dark:focus:bg-white dark:focus:text-zinc-950"
        >
            {{ __('Skip to content') }}
        </a>

        <div class="relative min-h-screen overflow-hidden">
            <div aria-hidden="true" class="pointer-events-none absolute inset-0">
                <div class="absolute inset-x-0 top-0 h-72 bg-[radial-gradient(circle_at_top,rgba(16,185,129,0.18),transparent_65%)] dark:bg-[radial-gradient(circle_at_top,rgba(16,185,129,0.22),transparent_60%)]"></div>
                <div class="absolute right-[-6rem] top-16 size-64 rounded-full bg-amber-300/20 blur-3xl dark:bg-amber-200/10 motion-safe:animate-[pulse_8s_ease-in-out_infinite]"></div>
                <div class="absolute left-[-8rem] top-56 size-72 rounded-full bg-emerald-400/15 blur-3xl dark:bg-emerald-400/12 motion-safe:animate-[pulse_10s_ease-in-out_infinite]"></div>
                <div class="absolute inset-x-0 bottom-0 h-80 bg-[linear-gradient(to_bottom,transparent,rgba(255,255,255,0.92))] dark:bg-[linear-gradient(to_bottom,transparent,rgba(9,9,11,0.98))]"></div>
            </div>

            <div class="relative">
                <header class="border-b border-zinc-950/5 bg-white/80 backdrop-blur-xl dark:border-white/10 dark:bg-zinc-950/70">
                    <div class="mx-auto flex max-w-7xl flex-col gap-4 px-4 py-4 sm:px-6 lg:flex-row lg:items-center lg:justify-between lg:px-8">
                        <div class="flex items-center gap-4">
                            <div class="flex size-12 items-center justify-center rounded-2xl bg-zinc-950 text-lg font-semibold text-white shadow-lg shadow-zinc-950/15 dark:bg-white dark:text-zinc-950">
                                R
                            </div>

                            <div class="space-y-1">
                                <p class="text-[0.7rem] font-semibold uppercase tracking-[0.34em] text-emerald-700 dark:text-emerald-300">
                                    {{ __('Premium roulette tracking') }}
                                </p>

                                <div>
                                    <h1 class="text-lg font-semibold sm:text-xl">{{ config('app.name', 'Roulette Helper') }}</h1>
                                    <p class="text-sm text-zinc-600 dark:text-zinc-300">{{ __('Fast spin capture, live counter-bet guidance, and clean session history.') }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col items-start gap-2 sm:items-end">
                            <p class="text-[0.7rem] font-semibold uppercase tracking-[0.28em] text-zinc-500 dark:text-zinc-400">
                                {{ __('Appearance') }}
                            </p>

                            <flux:radio.group
                                x-data
                                variant="segmented"
                                x-model="$flux.appearance"
                                class="rounded-2xl border border-zinc-950/10 bg-white/90 p-1 shadow-sm shadow-zinc-950/5 dark:border-white/10 dark:bg-white/5"
                                aria-label="{{ __('Switch page appearance') }}"
                            >
                                <flux:radio value="light" icon="sun">{{ __('Light') }}</flux:radio>
                                <flux:radio value="dark" icon="moon">{{ __('Dark') }}</flux:radio>
                            </flux:radio.group>
                        </div>
                    </div>
                </header>

                {{ $slot }}
            </div>
        </div>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
