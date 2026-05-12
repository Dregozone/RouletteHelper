<?php

use App\Models\Historical;
use App\Models\Stake;
use Flux\Flux;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.blank'), Title('Spin Dashboard')] class extends Component
{
    public array $historicals = [];

    public array $counts = [];

    public array $stakes = [];

    public ?int $newRollNumber = null;

    public string $behaviour = 'SuperSafe';

    public function mount(): void
    {
        $this->reloadData();
    }

    private function findHistoricals(): void
    {
        $this->historicals = Historical::query()
            ->select(
                'historicals.id',
                'historicals.num',
                'historicals.created_at',
                'isEven',
                'isOdd',
                'isLow',
                'isHigh',
                'isRed',
                'isBlack',
            )
            ->join('possible_outcomes', 'historicals.num', '=', 'possible_outcomes.num')
            ->orderBy('historicals.created_at', 'DESC')
            ->take(15)
            ->get()
            ->toArray();
    }

    public function reloadData(): void
    {
        $this->findHistoricals();

        // Reset counts for each type of bet
        $this->counts = [
            "even"  => 0,
            "odd"   => 0,
            "low"   => 0,
            "high"  => 0,
            "red"   => 0,
            "black" => 0,
        ];

        $evenStreak = false;
        $oddStreak = false;
        $highStreak = false;
        $lowStreak = false;
        $redStreak = false;
        $blackStreak = false;

        $prevRoll = false;
        foreach ($this->historicals as $currentRoll) {
            if (! $prevRoll) {
                if ($currentRoll['num'] == 0) {
                    // Account for the 0, 00 spins
                    $evenStreak = false;
                    $oddStreak = false;
                    $highStreak = false;
                    $lowStreak = false;
                    $redStreak = false;
                    $blackStreak = false;

                } else {
                    // This is the first (most recent) roll
                    if ($currentRoll['isEven']) {
                        $evenStreak = true;
                        $this->counts["even"]++;
                    } else {
                        $oddStreak = true;
                        $this->counts["odd"]++;
                    }

                    if ($currentRoll['isLow']) {
                        $lowStreak = true;
                        $this->counts["low"]++;
                    } else {
                        $highStreak = true;
                        $this->counts["high"]++;
                    }

                    if ($currentRoll['isRed']) {
                        $redStreak = true;
                        $this->counts["red"]++;
                    } else {
                        $blackStreak = true;
                        $this->counts["black"]++;
                    }
                }
            } else { // This is NOT the most recent roll, there is a previous to compare against
                if ($currentRoll['num'] == 0) {
                    // Account for the 0, 00 spins
                    $evenStreak = false;
                    $oddStreak = false;
                    $highStreak = false;
                    $lowStreak = false;
                    $redStreak = false;
                    $blackStreak = false;

                } else {
                    if ($evenStreak && $currentRoll['isEven']) {
                        $this->counts["even"]++; // Increment the sequential count
                    } else {
                        $evenStreak = false; // Otherwise, end the streak here
                    }

                    if ($oddStreak && $currentRoll['isOdd']) {
                        $this->counts["odd"]++; // Increment the sequential count
                    } else {
                        $oddStreak = false; // Otherwise, end the streak here
                    }
                    
                    if ($highStreak && $currentRoll['isHigh']) {
                        $this->counts["high"]++; // Increment the sequential count
                    } else {
                        $highStreak = false; // Otherwise, end the streak here
                    }

                    if ($lowStreak && $currentRoll['isLow']) {
                        $this->counts["low"]++; // Increment the sequential count
                    } else {
                        $lowStreak = false; // Otherwise, end the streak here
                    }

                    if ($redStreak && $currentRoll['isRed']) {
                        $this->counts["red"]++; // Increment the sequential count
                    } else {
                        $redStreak = false; // Otherwise, end the streak here
                    }

                    if ($blackStreak && $currentRoll['isBlack']) {
                        $this->counts["black"]++; // Increment the sequential count
                    } else {
                        $blackStreak = false; // Otherwise, end the streak here
                    }
                }
            }

            // Set previous roll as this one for comparisson on next iteration
            $prevRoll = $currentRoll;
        }
        
        $stakeData = Stake::query()
            ->where('name', $this->behaviour)
            ->first();

        if (! $stakeData) {
            Flux::toast('No stake data found for the selected behaviour!', 'error');
            $this->stakes = [];

            return;
        }

        $this->stakes = json_decode($stakeData->stakes, true) ?? [];
    }

    public function doAction(string $actionName, ?int $id = null): void
    {
        if ($actionName === 'recordARoll') {
            $validated = $this->validate(
                [
                    'newRollNumber' => ['required', 'integer', 'between:0,36'],
                ],
                [],
                [
                    'newRollNumber' => 'roulette number',
                ],
            );

            Historical::create([
                'num' => $validated['newRollNumber'],
            ]);

            $this->reset('newRollNumber');
            $this->reloadData();

            Flux::toast('Roll was successfully recorded!', 'success');

        } elseif ($actionName === 'deleteAHistorical') {
            $historical = Historical::find($id);

            if (! $historical) {
                Flux::toast('That historical roll no longer exists.', 'error');

                return;
            }

            $historical->delete();
            
            $this->reloadData();

            Flux::toast('A historical roll was successfully removed!', 'success');

        } elseif ($actionName === 'clearAllHistorical') {
            Historical::truncate();

            $this->reloadData();

            Flux::toast('Cleared all historical rolls!', 'success');
        } else {
            Flux::toast('Unknown action!', 'error');
        }
    }
};
?>

@php
    $latestRoll = $historicals[0] ?? null;

    $recommendations = [
        [
            'label' => 'Low',
            'trigger' => 'high',
            'triggerLabel' => 'high numbers',
            'accent' => 'text-cyan-700 dark:text-cyan-300',
            'badge' => 'bg-cyan-500/15 text-cyan-800 dark:text-cyan-200',
            'panel' => 'from-cyan-400/20 via-cyan-400/8 to-transparent dark:from-cyan-400/18 dark:via-cyan-400/8',
        ],
        [
            'label' => 'Even',
            'trigger' => 'odd',
            'triggerLabel' => 'odd numbers',
            'accent' => 'text-amber-700 dark:text-amber-300',
            'badge' => 'bg-amber-500/15 text-amber-800 dark:text-amber-200',
            'panel' => 'from-amber-400/20 via-amber-400/8 to-transparent dark:from-amber-400/18 dark:via-amber-400/8',
        ],
        [
            'label' => 'Red',
            'trigger' => 'black',
            'triggerLabel' => 'black results',
            'accent' => 'text-rose-700 dark:text-rose-300',
            'badge' => 'bg-rose-500/15 text-rose-800 dark:text-rose-200',
            'panel' => 'from-rose-400/20 via-rose-400/8 to-transparent dark:from-rose-400/18 dark:via-rose-400/8',
        ],
        [
            'label' => 'Black',
            'trigger' => 'red',
            'triggerLabel' => 'red results',
            'accent' => 'text-zinc-800 dark:text-zinc-100',
            'badge' => 'bg-zinc-950/10 text-zinc-800 dark:bg-white/10 dark:text-zinc-100',
            'panel' => 'from-zinc-900/15 via-zinc-900/5 to-transparent dark:from-white/12 dark:via-white/5',
        ],
        [
            'label' => 'Odd',
            'trigger' => 'even',
            'triggerLabel' => 'even numbers',
            'accent' => 'text-violet-700 dark:text-violet-300',
            'badge' => 'bg-violet-500/15 text-violet-800 dark:text-violet-200',
            'panel' => 'from-violet-400/20 via-violet-400/8 to-transparent dark:from-violet-400/18 dark:via-violet-400/8',
        ],
        [
            'label' => 'High',
            'trigger' => 'low',
            'triggerLabel' => 'low numbers',
            'accent' => 'text-emerald-700 dark:text-emerald-300',
            'badge' => 'bg-emerald-500/15 text-emerald-800 dark:text-emerald-200',
            'panel' => 'from-emerald-400/20 via-emerald-400/8 to-transparent dark:from-emerald-400/18 dark:via-emerald-400/8',
        ],
    ];

    $activeRecommendationCount = count(array_filter(
        $recommendations,
        fn (array $recommendation): bool => ($counts[$recommendation['trigger']] ?? 0) >= 2,
    ));

    $activeRecommendationLabel = $activeRecommendationCount === 1 ? 'signal' : 'signals';

    $summaryTiles = [
        [
            'label' => 'Latest spin',
            'value' => $latestRoll['num'] ?? 'Waiting',
            'helper' => $latestRoll ? 'Most recent recorded outcome' : 'Add the first result to begin tracking',
        ],
        [
            'label' => 'Active bets',
            'value' => $activeRecommendationCount,
            'helper' => 'Recommendations currently above the trigger threshold',
        ],
        [
            'label' => 'Tracking window',
            'value' => count($historicals).'/15',
            'helper' => 'Rolling history used for streak analysis',
        ],
        [
            'label' => 'Strategy',
            'value' => $behaviour,
            'helper' => 'Current stake profile applied to recommendations',
        ],
    ];
@endphp

<main id="app-main" class="relative">
    <div class="mx-auto flex max-w-7xl flex-col gap-6 px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
        <section aria-labelledby="dashboard-heading" class="overflow-hidden rounded-[2rem] border border-white/60 bg-white/85 shadow-2xl shadow-zinc-950/8 backdrop-blur-xl dark:border-white/10 dark:bg-white/5 dark:shadow-black/30">
            <div class="grid gap-8 px-5 py-6 sm:px-7 lg:grid-cols-[minmax(0,1.25fr)_minmax(20rem,0.95fr)] lg:px-8 lg:py-8">
                <div class="space-y-6">
                    <div class="space-y-4">
                        <div class="inline-flex items-center gap-2 rounded-full border border-emerald-500/20 bg-emerald-500/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.26em] text-emerald-800 dark:border-emerald-400/20 dark:bg-emerald-400/10 dark:text-emerald-200">
                            <span class="size-2 rounded-full bg-emerald-500 motion-safe:animate-pulse"></span>
                            Live wheel intelligence
                        </div>

                        <div class="space-y-3">
                            <div>
                                <h2 id="dashboard-heading" class="max-w-3xl text-3xl font-semibold tracking-tight text-balance text-zinc-950 sm:text-4xl lg:text-[2.85rem] dark:text-white">
                                    Premium spin tracking with fast recommendations that stay readable under pressure.
                                </h2>

                                <p class="mt-3 max-w-2xl text-sm leading-6 text-zinc-600 sm:text-base dark:text-zinc-300">
                                    Capture each roulette outcome in one tap, spot the live streaks that matter, and keep the last fifteen spins visible in a layout that works equally well on desktop and mobile.
                                </p>
                            </div>

                            <div class="flex flex-wrap gap-2 text-sm text-zinc-600 dark:text-zinc-300">
                                <span class="rounded-full border border-zinc-950/10 bg-zinc-950/[0.03] px-3 py-1.5 dark:border-white/10 dark:bg-white/[0.04]">European wheel only</span>
                                <span class="rounded-full border border-zinc-950/10 bg-zinc-950/[0.03] px-3 py-1.5 dark:border-white/10 dark:bg-white/[0.04]">Automatic counter-streak suggestions</span>
                                <span class="rounded-full border border-zinc-950/10 bg-zinc-950/[0.03] px-3 py-1.5 dark:border-white/10 dark:bg-white/[0.04]">Accessible light and dark themes</span>
                            </div>
                        </div>
                    </div>

                    <dl class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                        @foreach ($summaryTiles as $tile)
                            <div class="rounded-3xl border border-zinc-950/8 bg-white/80 p-4 shadow-sm shadow-zinc-950/5 transition-transform duration-300 hover:-translate-y-0.5 dark:border-white/10 dark:bg-white/[0.04] dark:shadow-black/20">
                                <dt class="text-xs font-semibold uppercase tracking-[0.22em] text-zinc-500 dark:text-zinc-400">{{ $tile['label'] }}</dt>
                                <dd class="mt-3 text-2xl font-semibold tracking-tight text-zinc-950 dark:text-white">{{ $tile['value'] }}</dd>
                                <p class="mt-2 text-sm leading-5 text-zinc-600 dark:text-zinc-300">{{ $tile['helper'] }}</p>
                            </div>
                        @endforeach
                    </dl>
                </div>

                <section aria-labelledby="record-spin-heading" class="relative overflow-hidden rounded-[1.75rem] border border-zinc-950/8 bg-zinc-950 p-5 text-white shadow-2xl shadow-zinc-950/20 dark:border-white/10 dark:bg-zinc-900 sm:p-6">
                    <div aria-hidden="true" class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(16,185,129,0.35),transparent_45%),radial-gradient(circle_at_bottom_left,rgba(245,158,11,0.2),transparent_35%)]"></div>

                    <div class="relative space-y-6">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-emerald-200/90">Capture spin</p>
                            <h3 id="record-spin-heading" class="mt-2 text-2xl font-semibold tracking-tight">Record the next wheel result</h3>
                            <p class="mt-2 text-sm leading-6 text-zinc-300">
                                Enter a value from 0 to 36. The recommendation cards and history panel update immediately after each submission.
                            </p>
                        </div>

                        <form wire:submit="doAction('recordARoll')" class="space-y-5">
                            <flux:field>
                                <flux:label for="number">Roulette number</flux:label>
                                <flux:input
                                    type="number"
                                    wire:model="newRollNumber"
                                    min="0"
                                    max="36"
                                    name="number"
                                    id="number"
                                    inputmode="numeric"
                                    autocomplete="off"
                                    placeholder="0-36"
                                    class="bg-white/95 text-zinc-950"
                                />
                                <p class="text-sm text-zinc-300">Use the exact result from the wheel. Zero is treated as a neutral break in the streak logic.</p>
                                <flux:error name="newRollNumber" />
                            </flux:field>

                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-zinc-200">
                                    Strategy profile <span class="font-semibold text-white">{{ $behaviour }}</span>
                                </div>

                                <flux:button
                                    type="submit"
                                    variant="primary"
                                    color="emerald"
                                    class="w-full justify-center sm:w-auto"
                                    wire:loading.attr="disabled"
                                    wire:target="doAction"
                                    aria-label="Record a new roll"
                                >
                                    Record spin
                                </flux:button>
                            </div>
                        </form>
                    </div>
                </section>
            </div>
        </section>

        <div class="grid gap-6 lg:grid-cols-[minmax(0,1.55fr)_minmax(22rem,0.95fr)]">
            <section aria-labelledby="recommendations-heading" class="rounded-[2rem] border border-white/60 bg-white/85 p-5 shadow-2xl shadow-zinc-950/8 backdrop-blur-xl dark:border-white/10 dark:bg-white/5 dark:shadow-black/25 sm:p-6">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-zinc-500 dark:text-zinc-400">Recommendations</p>
                        <h2 id="recommendations-heading" class="mt-2 text-2xl font-semibold tracking-tight text-zinc-950 dark:text-white">Counter-bets shaped by the current streaks</h2>
                        <p class="mt-2 max-w-2xl text-sm leading-6 text-zinc-600 dark:text-zinc-300">A recommendation goes live after two consecutive opposing results. Values come from the active stake ladder for the selected behaviour profile.</p>
                    </div>

                    <div class="inline-flex items-center gap-2 rounded-full border border-zinc-950/10 bg-zinc-950/[0.04] px-4 py-2 text-sm font-medium text-zinc-700 dark:border-white/10 dark:bg-white/[0.04] dark:text-zinc-200">
                        <span class="size-2 rounded-full bg-emerald-500 motion-safe:animate-pulse"></span>
                        {{ $activeRecommendationCount }} live {{ $activeRecommendationLabel }}
                    </div>
                </div>

                <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-3" aria-live="polite">
                    @foreach ($recommendations as $recommendation)
                        @php
                            $streakCount = $counts[$recommendation['trigger']] ?? 0;
                            $isActive = $streakCount >= 2;
                            $stakeValue = $isActive ? ($stakes[$streakCount - 1] ?? null) : null;
                        @endphp

                        <article class="group relative overflow-hidden rounded-[1.6rem] border border-zinc-950/8 bg-white/80 shadow-sm shadow-zinc-950/5 transition duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-zinc-950/10 dark:border-white/10 dark:bg-zinc-950/70 dark:shadow-black/20">
                            <div aria-hidden="true" class="absolute inset-0 bg-gradient-to-br {{ $recommendation['panel'] }} opacity-90"></div>

                            <div class="relative flex h-full flex-col gap-5 p-5">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-zinc-500 dark:text-zinc-400">Bet target</p>
                                        <h3 class="mt-2 text-2xl font-semibold tracking-tight {{ $recommendation['accent'] }}">{{ $recommendation['label'] }}</h3>
                                    </div>

                                    <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-[0.22em] {{ $isActive ? $recommendation['badge'] : 'bg-zinc-950/6 text-zinc-600 dark:bg-white/8 dark:text-zinc-300' }}">
                                        <span class="size-2 rounded-full {{ $isActive ? 'bg-emerald-500 motion-safe:animate-pulse' : 'bg-zinc-400 dark:bg-zinc-500' }}"></span>
                                        {{ $isActive ? 'Live' : 'Waiting' }}
                                    </span>
                                </div>

                                <div class="flex items-end justify-between gap-4">
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-zinc-500 dark:text-zinc-400">Suggested stake</p>
                                        <p class="mt-2 text-4xl font-semibold tracking-tight text-zinc-950 dark:text-white">
                                            @if (! $isActive)
                                                £0
                                            @elseif ($stakeValue !== null)
                                                £{{ $stakeValue }}
                                            @else
                                                --
                                            @endif
                                        </p>
                                    </div>

                                    <div class="rounded-2xl border border-zinc-950/8 bg-white/70 px-3 py-2 text-right dark:border-white/10 dark:bg-white/5">
                                        <p class="text-xs uppercase tracking-[0.22em] text-zinc-500 dark:text-zinc-400">Streak</p>
                                        <p class="mt-1 text-lg font-semibold text-zinc-950 dark:text-white">{{ $streakCount }}x</p>
                                    </div>
                                </div>

                                <p class="text-sm leading-6 text-zinc-600 dark:text-zinc-300">
                                    Triggered by <span class="font-semibold text-zinc-950 dark:text-white">{{ $streakCount }}</span> consecutive {{ $recommendation['triggerLabel'] }}.
                                    @if ($isActive && $stakeValue === null)
                                        The streak is live, but the configured stake ladder has no value at this position.
                                    @elseif ($isActive)
                                        The counter-bet is active and ready to use.
                                    @else
                                        Waiting for two or more in a row before this recommendation activates.
                                    @endif
                                </p>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>

            <section aria-labelledby="history-heading" class="rounded-[2rem] border border-white/60 bg-white/85 p-5 shadow-2xl shadow-zinc-950/8 backdrop-blur-xl dark:border-white/10 dark:bg-white/5 dark:shadow-black/25 sm:p-6 lg:sticky lg:top-6 lg:self-start">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-zinc-500 dark:text-zinc-400">History</p>
                        <h2 id="history-heading" class="mt-2 text-2xl font-semibold tracking-tight text-zinc-950 dark:text-white">Latest fifteen spins</h2>
                        <p class="mt-2 text-sm leading-6 text-zinc-600 dark:text-zinc-300">Most recent result first. Zero is shown as a neutral break across all three betting dimensions.</p>
                    </div>

                    <form wire:submit="doAction('clearAllHistorical')" class="sm:shrink-0">
                        <flux:button
                            type="submit"
                            variant="danger"
                            class="w-full justify-center sm:w-auto"
                            wire:loading.attr="disabled"
                            wire:target="doAction"
                            aria-label="Clear all historical rolls"
                        >
                            Clear session
                        </flux:button>
                    </form>
                </div>

                @if ($historicals === [])
                    <div class="mt-6 rounded-[1.5rem] border border-dashed border-zinc-950/12 bg-zinc-950/[0.03] px-5 py-10 text-center dark:border-white/10 dark:bg-white/[0.03]">
                        <p class="text-lg font-semibold text-zinc-950 dark:text-white">No spins recorded yet</p>
                        <p class="mt-2 text-sm leading-6 text-zinc-600 dark:text-zinc-300">Enter the first wheel result above to start building the live streak history and recommendation feed.</p>
                    </div>
                @else
                    <ul class="mt-6 grid gap-3 md:hidden">
                        @foreach ($historicals as $historical)
                            @php
                                $isZero = $historical['num'] == 0;
                                $redBlackLabel = $isZero ? '-' : ($historical['isRed'] ? 'Red' : 'Black');
                                $redBlackClass = $isZero ? 'text-zinc-400 dark:text-zinc-500' : ($historical['isRed'] ? 'text-rose-700 dark:text-rose-300' : 'text-zinc-900 dark:text-zinc-100');
                                $oddEvenLabel = $isZero ? '-' : ($historical['isEven'] ? 'Even' : 'Odd');
                                $oddEvenClass = $isZero ? 'text-zinc-400 dark:text-zinc-500' : ($historical['isEven'] ? 'text-amber-700 dark:text-amber-300' : 'text-violet-700 dark:text-violet-300');
                                $highLowLabel = $isZero ? '-' : ($historical['isHigh'] ? 'High' : 'Low');
                                $highLowClass = $isZero ? 'text-zinc-400 dark:text-zinc-500' : ($historical['isHigh'] ? 'text-emerald-700 dark:text-emerald-300' : 'text-cyan-700 dark:text-cyan-300');
                            @endphp

                            <li wire:key="historical-mobile-{{ $historical['id'] }}" class="rounded-[1.5rem] border border-zinc-950/8 bg-white/80 p-4 shadow-sm shadow-zinc-950/5 dark:border-white/10 dark:bg-zinc-950/60 dark:shadow-black/20">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-zinc-500 dark:text-zinc-400">Spin</p>
                                        <p class="mt-1 text-3xl font-semibold tracking-tight text-zinc-950 dark:text-white">{{ $historical['num'] }}</p>
                                    </div>

                                    <form wire:submit="doAction('deleteAHistorical', {{ $historical['id'] }})">
                                        <flux:button
                                            type="submit"
                                            variant="danger"
                                            size="xs"
                                            wire:loading.attr="disabled"
                                            wire:target="doAction"
                                            aria-label="Delete roll {{ $historical['num'] }}"
                                        >Delete</flux:button>
                                    </form>
                                </div>

                                <dl class="mt-4 grid grid-cols-3 gap-3 text-sm">
                                    <div class="rounded-2xl border border-zinc-950/8 bg-zinc-950/[0.03] p-3 dark:border-white/10 dark:bg-white/[0.03]">
                                        <dt class="text-xs font-semibold uppercase tracking-[0.18em] text-zinc-500 dark:text-zinc-400">R/B</dt>
                                        <dd class="mt-2 font-semibold {{ $redBlackClass }}">{{ $redBlackLabel }}</dd>
                                    </div>
                                    <div class="rounded-2xl border border-zinc-950/8 bg-zinc-950/[0.03] p-3 dark:border-white/10 dark:bg-white/[0.03]">
                                        <dt class="text-xs font-semibold uppercase tracking-[0.18em] text-zinc-500 dark:text-zinc-400">O/E</dt>
                                        <dd class="mt-2 font-semibold {{ $oddEvenClass }}">{{ $oddEvenLabel }}</dd>
                                    </div>
                                    <div class="rounded-2xl border border-zinc-950/8 bg-zinc-950/[0.03] p-3 dark:border-white/10 dark:bg-white/[0.03]">
                                        <dt class="text-xs font-semibold uppercase tracking-[0.18em] text-zinc-500 dark:text-zinc-400">H/L</dt>
                                        <dd class="mt-2 font-semibold {{ $highLowClass }}">{{ $highLowLabel }}</dd>
                                    </div>
                                </dl>
                            </li>
                        @endforeach
                    </ul>

                    <div class="mt-6 hidden overflow-x-auto md:block">
                        <table class="min-w-full border-separate border-spacing-y-2">
                            <caption class="sr-only">The last fifteen roulette spins with red or black, odd or even, and high or low classifications.</caption>
                            <thead>
                                <tr>
                                    <th scope="col" class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-[0.22em] text-zinc-500 dark:text-zinc-400">Spin</th>
                                    <th scope="col" class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-[0.22em] text-zinc-500 dark:text-zinc-400">Red or black</th>
                                    <th scope="col" class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-[0.22em] text-zinc-500 dark:text-zinc-400">Odd or even</th>
                                    <th scope="col" class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-[0.22em] text-zinc-500 dark:text-zinc-400">High or low</th>
                                    <th scope="col" class="px-4 py-2 text-right text-xs font-semibold uppercase tracking-[0.22em] text-zinc-500 dark:text-zinc-400">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($historicals as $historical)
                                    @php
                                        $isZero = $historical['num'] == 0;
                                        $redBlackLabel = $isZero ? '-' : ($historical['isRed'] ? 'Red' : 'Black');
                                        $redBlackClass = $isZero ? 'text-zinc-400 dark:text-zinc-500' : ($historical['isRed'] ? 'text-rose-700 dark:text-rose-300' : 'text-zinc-900 dark:text-zinc-100');
                                        $oddEvenLabel = $isZero ? '-' : ($historical['isEven'] ? 'Even' : 'Odd');
                                        $oddEvenClass = $isZero ? 'text-zinc-400 dark:text-zinc-500' : ($historical['isEven'] ? 'text-amber-700 dark:text-amber-300' : 'text-violet-700 dark:text-violet-300');
                                        $highLowLabel = $isZero ? '-' : ($historical['isHigh'] ? 'High' : 'Low');
                                        $highLowClass = $isZero ? 'text-zinc-400 dark:text-zinc-500' : ($historical['isHigh'] ? 'text-emerald-700 dark:text-emerald-300' : 'text-cyan-700 dark:text-cyan-300');
                                    @endphp

                                    <tr wire:key="historical-desktop-{{ $historical['id'] }}" class="transition-transform duration-300 hover:-translate-y-0.5">
                                        <td class="rounded-l-2xl border border-r-0 border-zinc-950/8 bg-white/80 px-4 py-4 text-lg font-semibold tracking-tight text-zinc-950 shadow-sm shadow-zinc-950/5 dark:border-white/10 dark:bg-zinc-950/60 dark:text-white dark:shadow-black/20">{{ $historical['num'] }}</td>
                                        <td class="border border-x-0 border-zinc-950/8 bg-white/80 px-4 py-4 dark:border-white/10 dark:bg-zinc-950/60">
                                            <span class="font-semibold {{ $redBlackClass }}">{{ $redBlackLabel }}</span>
                                        </td>
                                        <td class="border border-x-0 border-zinc-950/8 bg-white/80 px-4 py-4 dark:border-white/10 dark:bg-zinc-950/60">
                                            <span class="font-semibold {{ $oddEvenClass }}">{{ $oddEvenLabel }}</span>
                                        </td>
                                        <td class="border border-x-0 border-zinc-950/8 bg-white/80 px-4 py-4 dark:border-white/10 dark:bg-zinc-950/60">
                                            <span class="font-semibold {{ $highLowClass }}">{{ $highLowLabel }}</span>
                                        </td>
                                        <td class="rounded-r-2xl border border-l-0 border-zinc-950/8 bg-white/80 px-4 py-4 text-right dark:border-white/10 dark:bg-zinc-950/60">
                                            <form wire:submit="doAction('deleteAHistorical', {{ $historical['id'] }})" class="inline-flex">
                                                <flux:button
                                                    type="submit"
                                                    variant="danger"
                                                    size="xs"
                                                    wire:loading.attr="disabled"
                                                    wire:target="doAction"
                                                    aria-label="Delete roll {{ $historical['num'] }}"
                                                >Delete</flux:button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>
        </div>
    </div>
</main>
