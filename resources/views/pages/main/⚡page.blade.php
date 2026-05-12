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

    public array $availableBehaviours = [];

    public array $availableBehaviourStakes = [];

    public ?int $newRollNumber = null;

    public string $behaviour = 'SuperSafe';

    public bool $showBehaviourModal = false;

    public function mount(): void
    {
        $this->loadAvailableBehaviours();
        $this->reloadData();
    }

    private function loadAvailableBehaviours(): void
    {
        $stakeModes = Stake::query()
            ->select('id', 'name', 'stakes')
            ->orderBy('id')
            ->get();

        $this->availableBehaviours = $stakeModes
            ->pluck('name')
            ->all();

        $this->availableBehaviourStakes = $stakeModes
            ->mapWithKeys(fn (Stake $stake): array => [
                $stake->name => json_decode($stake->stakes, true) ?? [],
            ])
            ->all();

        if ($this->availableBehaviours !== [] && ! in_array($this->behaviour, $this->availableBehaviours, true)) {
            $this->behaviour = $this->availableBehaviours[0];
        }
    }

    public function openBehaviourModal(): void
    {
        $this->showBehaviourModal = true;
    }

    public function setBehaviour(string $behaviour): void
    {
        $this->loadAvailableBehaviours();

        if (! in_array($behaviour, $this->availableBehaviours, true)) {
            Flux::toast('That betting mode is not available.', 'error');

            return;
        }

        if ($behaviour === $this->behaviour) {
            $this->showBehaviourModal = false;

            return;
        }

        $this->behaviour = $behaviour;
        $this->reloadData();
        $this->showBehaviourModal = false;

        Flux::toast('Betting mode updated!', 'success');
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
        if ($this->availableBehaviours === [] || $this->availableBehaviourStakes === []) {
            $this->loadAvailableBehaviours();
        }

        $this->findHistoricals();

        $this->counts = [
            'even' => 0,
            'odd' => 0,
            'low' => 0,
            'high' => 0,
            'red' => 0,
            'black' => 0,
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
                    $evenStreak = false;
                    $oddStreak = false;
                    $highStreak = false;
                    $lowStreak = false;
                    $redStreak = false;
                    $blackStreak = false;
                } else {
                    if ($currentRoll['isEven']) {
                        $evenStreak = true;
                        $this->counts['even']++;
                    } else {
                        $oddStreak = true;
                        $this->counts['odd']++;
                    }

                    if ($currentRoll['isLow']) {
                        $lowStreak = true;
                        $this->counts['low']++;
                    } else {
                        $highStreak = true;
                        $this->counts['high']++;
                    }

                    if ($currentRoll['isRed']) {
                        $redStreak = true;
                        $this->counts['red']++;
                    } else {
                        $blackStreak = true;
                        $this->counts['black']++;
                    }
                }
            } else {
                if ($currentRoll['num'] == 0) {
                    $evenStreak = false;
                    $oddStreak = false;
                    $highStreak = false;
                    $lowStreak = false;
                    $redStreak = false;
                    $blackStreak = false;
                } else {
                    if ($evenStreak && $currentRoll['isEven']) {
                        $this->counts['even']++;
                    } else {
                        $evenStreak = false;
                    }

                    if ($oddStreak && $currentRoll['isOdd']) {
                        $this->counts['odd']++;
                    } else {
                        $oddStreak = false;
                    }

                    if ($highStreak && $currentRoll['isHigh']) {
                        $this->counts['high']++;
                    } else {
                        $highStreak = false;
                    }

                    if ($lowStreak && $currentRoll['isLow']) {
                        $this->counts['low']++;
                    } else {
                        $lowStreak = false;
                    }

                    if ($redStreak && $currentRoll['isRed']) {
                        $this->counts['red']++;
                    } else {
                        $redStreak = false;
                    }

                    if ($blackStreak && $currentRoll['isBlack']) {
                        $this->counts['black']++;
                    } else {
                        $blackStreak = false;
                    }
                }
            }

            $prevRoll = $currentRoll;
        }

        if (! array_key_exists($this->behaviour, $this->availableBehaviourStakes)) {
            Flux::toast('No stake data found for the selected behaviour!', 'error');
            $this->stakes = [];

            return;
        }

        $this->stakes = $this->availableBehaviourStakes[$this->behaviour];
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
    $latestRollDisplay = $latestRoll['num'] ?? '-';
    $historyCount = count($historicals);
    $behaviourCount = count($availableBehaviours);

    $recommendations = [
        [
            'label' => 'Low',
            'trigger' => 'high',
            'triggerLabel' => 'high',
            'accent' => 'text-cyan-700 dark:text-cyan-300',
            'badge' => 'bg-cyan-500/15 text-cyan-800 dark:text-cyan-200',
            'panel' => 'from-cyan-400/20 via-cyan-400/8 to-transparent dark:from-cyan-400/18 dark:via-cyan-400/8',
        ],
        [
            'label' => 'Even',
            'trigger' => 'odd',
            'triggerLabel' => 'odd',
            'accent' => 'text-amber-700 dark:text-amber-300',
            'badge' => 'bg-amber-500/15 text-amber-800 dark:text-amber-200',
            'panel' => 'from-amber-400/20 via-amber-400/8 to-transparent dark:from-amber-400/18 dark:via-amber-400/8',
        ],
        [
            'label' => 'Red',
            'trigger' => 'black',
            'triggerLabel' => 'black',
            'accent' => 'text-rose-700 dark:text-rose-300',
            'badge' => 'bg-rose-500/15 text-rose-800 dark:text-rose-200',
            'panel' => 'from-rose-400/20 via-rose-400/8 to-transparent dark:from-rose-400/18 dark:via-rose-400/8',
        ],
        [
            'label' => 'Black',
            'trigger' => 'red',
            'triggerLabel' => 'red',
            'accent' => 'text-zinc-800 dark:text-zinc-100',
            'badge' => 'bg-zinc-950/10 text-zinc-800 dark:bg-white/10 dark:text-zinc-100',
            'panel' => 'from-zinc-900/15 via-zinc-900/5 to-transparent dark:from-white/12 dark:via-white/5',
        ],
        [
            'label' => 'Odd',
            'trigger' => 'even',
            'triggerLabel' => 'even',
            'accent' => 'text-violet-700 dark:text-violet-300',
            'badge' => 'bg-violet-500/15 text-violet-800 dark:text-violet-200',
            'panel' => 'from-violet-400/20 via-violet-400/8 to-transparent dark:from-violet-400/18 dark:via-violet-400/8',
        ],
        [
            'label' => 'High',
            'trigger' => 'low',
            'triggerLabel' => 'low',
            'accent' => 'text-emerald-700 dark:text-emerald-300',
            'badge' => 'bg-emerald-500/15 text-emerald-800 dark:text-emerald-200',
            'panel' => 'from-emerald-400/20 via-emerald-400/8 to-transparent dark:from-emerald-400/18 dark:via-emerald-400/8',
        ],
    ];

    $recommendationBoard = [];
    $nextBets = [];

    foreach ($recommendations as $recommendation) {
        $streakCount = $counts[$recommendation['trigger']] ?? 0;
        $isActive = $streakCount >= 2;
        $stakeValue = $isActive ? ($stakes[$streakCount - 1] ?? null) : null;

        $recommendationState = $recommendation + [
            'streak' => $streakCount,
            'isActive' => $isActive,
            'stakeValue' => $stakeValue,
            'stakeDisplay' => ! $isActive ? '£0' : ($stakeValue !== null ? '£'.$stakeValue : '--'),
        ];

        $recommendationBoard[] = $recommendationState;

        if ($isActive) {
            $nextBets[] = $recommendationState;
        }
    }

    $activeRecommendationCount = count($nextBets);
    $statusCopy = $activeRecommendationCount > 0
        ? $activeRecommendationCount.' '.($activeRecommendationCount === 1 ? 'bet live' : 'bets live')
        : 'No bet live';
@endphp

<main id="app-main" class="relative">
    <div class="mx-auto flex max-w-7xl flex-col gap-6 px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
        <div class="grid gap-6 lg:grid-cols-3">
            <section aria-labelledby="next-bets-heading" class="rounded-[2rem] border border-white/60 bg-white/85 p-5 shadow-2xl shadow-zinc-950/8 backdrop-blur-xl dark:border-white/10 dark:bg-white/5 dark:shadow-black/25 sm:p-6 lg:col-span-2 lg:p-7">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-zinc-500 dark:text-zinc-400">Next bets</p>
                        <h2 id="next-bets-heading" class="mt-2 text-3xl font-semibold tracking-tight text-zinc-950 dark:text-white">Place these now</h2>
                    </div>

                    <span class="inline-flex items-center gap-2 self-start rounded-full border border-zinc-950/10 bg-zinc-950/[0.04] px-4 py-2 text-sm font-medium text-zinc-700 dark:border-white/10 dark:bg-white/[0.04] dark:text-zinc-200 sm:self-auto">
                        <span class="size-2 rounded-full {{ $activeRecommendationCount > 0 ? 'bg-emerald-500 motion-safe:animate-pulse' : 'bg-zinc-400 dark:bg-zinc-500' }}"></span>
                        {{ $statusCopy }}
                    </span>
                </div>

                @if ($nextBets === [])
                    <div class="mt-5 flex min-h-64 items-center justify-center rounded-[1.8rem] border border-dashed border-zinc-950/12 bg-zinc-950/[0.03] px-6 py-12 text-center dark:border-white/10 dark:bg-white/[0.03]">
                        <div>
                            <p class="text-2xl font-semibold tracking-tight text-zinc-950 dark:text-white">No bet live</p>
                            <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-300">Record the next spin to refresh the board.</p>
                        </div>
                    </div>
                @else
                    <div class="mt-5 grid gap-4 lg:grid-cols-2" aria-live="polite">
                        @foreach ($nextBets as $bet)
                            <article class="relative overflow-hidden rounded-[1.8rem] border border-zinc-950/8 bg-white/90 shadow-lg shadow-zinc-950/8 dark:border-white/10 dark:bg-zinc-950/70 dark:shadow-black/25">
                                <div aria-hidden="true" class="absolute inset-0 bg-gradient-to-br {{ $bet['panel'] }}"></div>

                                <div class="relative p-5 sm:p-6">
                                    <div class="flex items-start justify-between gap-4">
                                        <div>
                                            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-zinc-500 dark:text-zinc-400">Bet now</p>
                                            <h3 class="mt-2 text-3xl font-semibold tracking-tight {{ $bet['accent'] }}">{{ $bet['label'] }}</h3>
                                        </div>

                                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] {{ $bet['badge'] }}">{{ $bet['streak'] }}x</span>
                                    </div>

                                    <div class="mt-8 flex items-end justify-between gap-4">
                                        <p class="text-5xl font-semibold tracking-tight text-zinc-950 dark:text-white">{{ $bet['stakeDisplay'] }}</p>

                                        <div class="text-right text-sm text-zinc-600 dark:text-zinc-300">
                                            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-zinc-500 dark:text-zinc-400">Trigger</p>
                                            <p class="mt-1 font-medium text-zinc-950 dark:text-white">{{ $bet['streak'] }} {{ $bet['triggerLabel'] }}</p>
                                        </div>
                                    </div>

                                    <p class="mt-4 text-sm text-zinc-600 dark:text-zinc-300">
                                        @if ($bet['stakeValue'] === null)
                                            Stake not set for this step.
                                        @else
                                            Counter the current {{ $bet['triggerLabel'] }} streak.
                                        @endif
                                    </p>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>

            <section aria-labelledby="record-spin-heading" class="relative overflow-hidden rounded-[2rem] border border-zinc-950/8 bg-zinc-950 p-5 text-white shadow-2xl shadow-zinc-950/20 dark:border-white/10 dark:bg-zinc-900 sm:p-6 lg:col-span-1 lg:sticky lg:top-6 lg:self-start">
                <div aria-hidden="true" class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(16,185,129,0.35),transparent_45%),radial-gradient(circle_at_bottom_left,rgba(245,158,11,0.18),transparent_35%)]"></div>

                <div class="relative">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-emerald-200/90">Record spin</p>
                        <h2 id="record-spin-heading" class="mt-2 text-2xl font-semibold tracking-tight">Add the latest result</h2>
                    </div>

                    <form wire:submit="doAction('recordARoll')" class="mt-6 space-y-5">
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
                            <flux:error name="newRollNumber" />
                        </flux:field>

                        <flux:button
                            type="submit"
                            variant="primary"
                            color="emerald"
                            class="w-full justify-center"
                            wire:loading.attr="disabled"
                            wire:target="doAction"
                            aria-label="Record a new roll"
                        >
                            Update board
                        </flux:button>

                        <dl class="grid grid-cols-3 gap-2 text-center">
                            <div class="rounded-2xl border border-white/10 bg-white/5 px-3 py-3">
                                <dt class="text-[0.65rem] font-semibold uppercase tracking-[0.2em] text-zinc-400">Latest</dt>
                                <dd class="mt-2 text-2xl font-semibold text-white">{{ $latestRollDisplay }}</dd>
                            </div>
                            <div class="rounded-2xl border border-white/10 bg-white/5 px-3 py-3">
                                <dt class="text-[0.65rem] font-semibold uppercase tracking-[0.2em] text-zinc-400">Tracked</dt>
                                <dd class="mt-2 text-2xl font-semibold text-white">{{ $historyCount }}</dd>
                            </div>
                            <div class="rounded-2xl border border-white/10 bg-white/5 px-3 py-3">
                                <dt class="text-[0.65rem] font-semibold uppercase tracking-[0.2em] text-zinc-400">Mode</dt>
                                <dd class="mt-2">
                                    <button
                                        type="button"
                                        wire:click="openBehaviourModal"
                                        class="group flex w-full items-center justify-between gap-3 rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-left transition hover:border-emerald-300/60 hover:bg-white/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-300 focus-visible:ring-offset-2 focus-visible:ring-offset-zinc-950"
                                        aria-label="Choose betting mode"
                                        aria-haspopup="dialog"
                                        aria-controls="behaviour-selector-modal"
                                        aria-expanded="{{ $showBehaviourModal ? 'true' : 'false' }}"
                                    >
                                        <span>
                                            <span class="block text-sm font-semibold text-white">{{ $behaviour }}</span>
                                            <span class="mt-1 block text-[0.65rem] font-semibold uppercase tracking-[0.2em] text-emerald-200/80">{{ $behaviourCount }} modes</span>
                                        </span>

                                        <span class="rounded-full border border-white/10 px-2 py-1 text-[0.65rem] font-semibold uppercase tracking-[0.2em] text-zinc-200 transition group-hover:border-emerald-300/50 group-hover:text-white">
                                            Change
                                        </span>
                                    </button>
                                </dd>
                            </div>
                        </dl>
                    </form>
                </div>
            </section>
        </div>

        <flux:modal
            id="behaviour-selector-modal"
            name="behaviour-selector-modal"
            class="max-w-3xl md:min-w-[42rem]"
            focusable
            wire:model="showBehaviourModal"
        >
            <div class="max-h-[80vh] space-y-6 overflow-y-auto pr-1">
                <div class="space-y-2 text-center sm:text-left">
                    <p class="text-xs font-semibold uppercase tracking-[0.28em] text-zinc-500 dark:text-zinc-400">Betting mode</p>
                    <flux:heading id="behaviour-selector-heading" size="xl">Choose the stake ladder</flux:heading>
                    <flux:text>
                        Each mode applies a different multiplier ladder to every live bet. Pick one to reload the board immediately.
                    </flux:text>
                </div>

                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3" role="group" aria-labelledby="behaviour-selector-heading">
                    @foreach ($availableBehaviours as $mode)
                        @php
                            $modeStakes = $availableBehaviourStakes[$mode] ?? [];
                            $modePreview = collect(array_slice($modeStakes, 0, 5))
                                ->map(fn ($stake) => $stake === null ? '--' : '£'.$stake)
                                ->implode(' · ');
                            $isCurrentMode = $mode === $behaviour;
                        @endphp

                        <button
                            type="button"
                            wire:key="behaviour-option-{{ $mode }}"
                            wire:click="setBehaviour('{{ $mode }}')"
                            wire:loading.attr="disabled"
                            wire:target="setBehaviour"
                            class="relative flex h-full flex-col gap-4 overflow-hidden rounded-[1.6rem] border p-4 text-left transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400 focus-visible:ring-offset-2 focus-visible:ring-offset-white dark:focus-visible:ring-offset-zinc-950 {{ $isCurrentMode ? 'border-emerald-300/70 bg-emerald-500/10 shadow-lg shadow-emerald-500/10 dark:border-emerald-400/40 dark:bg-emerald-400/10' : 'border-zinc-950/8 bg-white hover:border-emerald-300/60 hover:bg-emerald-500/[0.04] dark:border-white/10 dark:bg-zinc-950/70 dark:hover:border-emerald-400/40 dark:hover:bg-emerald-400/[0.08]' }}"
                            aria-pressed="{{ $isCurrentMode ? 'true' : 'false' }}"
                            aria-label="Select {{ $mode }} betting mode"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-zinc-500 dark:text-zinc-400">Mode</p>
                                    <p class="mt-2 text-xl font-semibold tracking-tight text-zinc-950 dark:text-white">{{ $mode }}</p>
                                </div>

                                <span class="inline-flex items-center rounded-full px-3 py-1 text-[0.65rem] font-semibold uppercase tracking-[0.2em] {{ $isCurrentMode ? 'bg-emerald-500/15 text-emerald-700 dark:text-emerald-200' : 'bg-zinc-950/[0.05] text-zinc-600 dark:bg-white/[0.06] dark:text-zinc-300' }}">
                                    {{ $isCurrentMode ? 'Current' : 'Select' }}
                                </span>
                            </div>

                            <div class="rounded-[1.25rem] border border-zinc-950/8 bg-zinc-950/[0.03] px-4 py-3 dark:border-white/10 dark:bg-white/[0.04]">
                                <p class="text-[0.65rem] font-semibold uppercase tracking-[0.22em] text-zinc-500 dark:text-zinc-400">First five steps</p>
                                <p class="mt-2 text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ $modePreview !== '' ? $modePreview : 'No stakes configured' }}</p>
                            </div>

                            <p class="text-sm text-zinc-600 dark:text-zinc-300">
                                {{ $isCurrentMode ? 'Already active. Select again to close the picker.' : 'Switch now and recalculate every live recommendation with this ladder.' }}
                            </p>
                        </button>
                    @endforeach
                </div>

                <div class="flex flex-col-reverse gap-3 border-t border-zinc-950/8 pt-4 dark:border-white/10 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-sm text-zinc-600 dark:text-zinc-300">The recommendations board updates as soon as you choose a mode.</p>

                    <flux:modal.close>
                        <flux:button variant="filled" class="w-full justify-center sm:w-auto">Keep current mode</flux:button>
                    </flux:modal.close>
                </div>
            </div>
        </flux:modal>

        <div class="grid gap-6 lg:grid-cols-3">
            <section aria-labelledby="board-heading" class="rounded-[2rem] border border-white/60 bg-white/85 p-5 shadow-2xl shadow-zinc-950/8 backdrop-blur-xl dark:border-white/10 dark:bg-white/5 dark:shadow-black/25 sm:p-6 lg:col-span-2">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-zinc-500 dark:text-zinc-400">Board</p>
                        <h2 id="board-heading" class="mt-2 text-2xl font-semibold tracking-tight text-zinc-950 dark:text-white">All counters</h2>
                    </div>

                    <span class="inline-flex items-center gap-2 self-start rounded-full border border-zinc-950/10 bg-zinc-950/[0.04] px-4 py-2 text-sm font-medium text-zinc-700 dark:border-white/10 dark:bg-white/[0.04] dark:text-zinc-200 sm:self-auto">
                        Latest {{ $latestRollDisplay }}
                    </span>
                </div>

                <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach ($recommendationBoard as $bet)
                        <article class="rounded-[1.4rem] border border-zinc-950/8 p-4 shadow-sm shadow-zinc-950/5 dark:border-white/10 dark:shadow-black/20 {{ $bet['isActive'] ? 'bg-white/90 dark:bg-zinc-950/70' : 'bg-zinc-950/[0.03] dark:bg-white/[0.03]' }}">
                            <div class="flex items-center justify-between gap-3">
                                <h3 class="text-lg font-semibold {{ $bet['accent'] }}">{{ $bet['label'] }}</h3>
                                <span class="text-xs font-semibold uppercase tracking-[0.18em] {{ $bet['isActive'] ? 'text-emerald-700 dark:text-emerald-300' : 'text-zinc-500 dark:text-zinc-400' }}">
                                    {{ $bet['isActive'] ? 'Live' : $bet['streak'].'x' }}
                                </span>
                            </div>

                            <p class="mt-4 text-3xl font-semibold tracking-tight text-zinc-950 dark:text-white">{{ $bet['stakeDisplay'] }}</p>
                            <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-300">Against {{ $bet['triggerLabel'] }}</p>
                        </article>
                    @endforeach
                </div>
            </section>

            <section aria-labelledby="history-heading" class="rounded-[2rem] border border-white/60 bg-white/85 p-5 shadow-2xl shadow-zinc-950/8 backdrop-blur-xl dark:border-white/10 dark:bg-white/5 dark:shadow-black/25 sm:p-6 lg:col-span-1 lg:sticky lg:top-6 lg:self-start">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-zinc-500 dark:text-zinc-400">History</p>
                        <h2 id="history-heading" class="mt-2 text-2xl font-semibold tracking-tight text-zinc-950 dark:text-white">Recent spins</h2>
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
                        <p class="text-lg font-semibold text-zinc-950 dark:text-white">No spins yet</p>
                        <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-300">Your last fifteen results will appear here.</p>
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