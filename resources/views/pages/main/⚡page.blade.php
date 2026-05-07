<?php

use App\Models\Historical;
use App\Models\Stake;
use Flux\Flux;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.blank')] class extends Component
{
    public array $historicals = [];
    
    public array $counts = [];

    public array $stakes = [];

    public int $newRollNumber = 0;

    public string $behaviour = "SuperSafe"; // This is the suggested betting behaviour, later it will be possible to select between different behaviours

    public function mount()
    {
         $this->reloadData();
    }

    private function findHistoricals()
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

    public function reloadData()
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

        $this->stakes = json_decode($stakeData->stakes);
    }

    public function doAction(string $actionName, ?int $id = null)
    {
        if ("recordARoll" == $actionName) {
            Historical::create([
                'num' => $this->newRollNumber,
            ]);

            $this->reloadData();

            Flux::toast('Roll was successfully recorded!', 'success');

        } else if ("deleteAHistorical" == $actionName) {
            Historical::find($id)->delete();
            
            $this->reloadData();

            Flux::toast('A historical roll was successfully removed!', 'success');

        } else if ("clearAllHistorical" == $actionName) {
            Historical::truncate();

            $this->reloadData();

            Flux::toast('Cleared all historical rolls!', 'success');
        } else {
            Flux::toast('Unknown action!', 'error');
        }
    }
};
?>

<div class="flex gap-2 p-2">
    <div class="flex flex-col gap-2 w-[60%] p-2">
        <section class="w-full p-2">
            <form wire:submit.prevent="doAction('recordARoll')">
                <fieldset>
                    <legend class="inline-block w-full text-center text-2xl mb-3">Record a roll</legend>

                    <div class="flex justify-center items-center gap-2">
                        <div class="flex justify-center items-center gap-2">
                            <label for="number">Number:</label>
                            <flux:input 
                                type="number" 
                                wire:model="newRollNumber" 
                                class="form-control" 
                                min="0" 
                                max="36" 
                                name="number" 
                                id="number"  
                            />
                        </div>

                        <div class="form-group">
                            <flux:button 
                                type="submit" 
                                variant="primary"
                                color="emerald" 
                                aria-label="Record a new roll"
                            >+</flux:button>
                        </div>
                    </div>
                </fieldset>
            </form>
        </section>

        <section class="my-2 p-2 border-t border-gray-300">
            <h2 class="text-center">
                Recommendations
            </h2>

            <div class="flex gap-2 justify-evenly items-center mt-4">
                <div class="w-[16.6%] border border-black text-center">
                    <h3 class="text-center">
                        Low
                    </h3>

                    @if ( $counts["high"] >= 2 )
                        <div class="min-h-[100px] text-5xl bg-black bg-cyan-400 text-black">
                            £{{ $stakes[$counts["high"] - 1] ?? 'x' }}
                        </div>
                    @else 
                        <div class="min-h-[100px] text-5xl">
                            0
                        </div>
                    @endif
                </div>

                <div class="w-[16.6%] border border-black text-center">
                    <h3 class="text-center">
                        Even
                    </h3>

                    @if ( $counts["odd"] >= 2 )
                        <div class="min-h-[100px] text-5xl bg-amber-400 text-white">
                            £{{ $stakes[$counts["odd"] - 1] ?? 'x' }}
                        </div>
                    @else 
                        <div class="min-h-[100px] text-5xl">
                            0
                        </div>
                    @endif
                </div>

                <div class="w-[16.6%] border border-black text-center">
                    <h3 class="text-center">
                        Red
                    </h3>

                    @if ( $counts["black"] >= 2 )
                        <div class="min-h-[100px] text-5xl bg-red-400 text-white">
                            £{{ $stakes[$counts["black"] - 1] ?? 'x' }}
                        </div>
                    @else 
                        <div class="min-h-[100px] text-5xl">
                            0
                        </div>
                    @endif
                </div>

                <div class="w-[16.6%] border border-black text-center">
                    <h3 class="text-center">
                        Black
                    </h3>

                    @if ( $counts["red"] >= 2 )
                        <div class="min-h-[100px] text-5xl bg-black text-white">
                            £{{ $stakes[$counts["red"] - 1] ?? 'x' }}
                        </div>
                    @else 
                        <div class="min-h-[100px] text-5xl">
                            0
                        </div>
                    @endif
                </div>

                <div class="w-[16.6%] border border-black text-center">
                    <h3 class="text-center">
                        Odd
                    </h3>

                    @if ( $counts["even"] >= 2 )
                        <div class="min-h-[100px] text-5xl bg-purple-400 text-white">
                            £{{ $stakes[$counts["even"] - 1] ?? 'x' }}
                        </div>
                    @else 
                        <div class="min-h-[100px] text-5xl">
                            0
                        </div>
                    @endif
                </div>

                <div class="w-[16.6%] border border-black text-center">
                    <h3 class="text-center">
                        High
                    </h3>

                    @if ( $counts["low"] >= 2 )
                        <div class="min-h-[100px] text-5xl bg-green-400 text-white">
                            £{{ $stakes[$counts["low"] - 1] ?? 'x' }}
                        </div>
                    @else 
                        <div class="min-h-[100px] text-5xl">
                            0
                        </div>
                    @endif
                </div>
            </div>
        </section>
    </div>

    <div class="w-[40%] border-l border-gray-300 p-2">
        <section class="p-4">
            <div class="flex justify-center items-center gap-2">
                <flux:heading level="2" size="xl" class="text-center">
                    Historical
                </flux:heading>

                <form wire:submit.prevent="doAction('clearAllHistorical')">
                    <flux:button 
                        type="submit" 
                        variant="danger" 
                        size="sm" 
                        class="cursor-pointer" 
                        aria-label="Clear all historical rolls"
                    >
                        Clear all
                    </flux:button>
                </form>
            </div>

            <div class="tableContainer">
                <small class="inline-block w-full italic text-center">
                    (Most recent roll at the top)
                </small>
                
                <table class="w-full border-collapse">
                    <thead class="text-center">
                        <tr>
                            <th class="py-2" title="Number">Num.</th>
                            <th class="py-2" title="Red / Black">R/B</th>
                            <th class="py-2" title="Odd / Even">O/E</th>
                            <th class="py-2" title="High / Low">H/L</th>
                            <th class="py-2">Action</th>
                        </tr>
                    </thead>
                    <tbody class="font-bold text-center">
                        @foreach ($historicals as $historical)
                            <tr class="border-t border-gray-300 odd:bg-gray-100 dark:odd:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600">
                                <td>{{ $historical['num'] }}</td>
                                
                                <td style="color: {{ $historical['isRed']  ? 'red' : ($historical['num'] == 0 ? 'lightgrey' : 'black') }};">
                                    {{ $historical['isRed']  ? 'Red' : ($historical['num'] == 0 ? '-' : 'Black') }}
                                </td>

                                <td style="color: {{ $historical['isEven'] ? 'darkorange' : ($historical['num'] == 0 ? 'lightgrey' : 'purple') }};">
                                    {{ $historical['isEven'] ? 'Even' : ($historical['num'] == 0 ? '-' : 'Odd') }}
                                </td>
                                
                                <td style="color: {{ $historical['isHigh'] ? 'green' : ($historical['num'] == 0 ? 'lightgrey' : 'cyan') }};">
                                    {{ $historical['isHigh'] ? 'High' : ($historical['num'] == 0 ? '-' : 'Low') }}
                                </td>
                                
                                <td class="py-2">
                                    <form wire:submit.prevent="doAction('deleteAHistorical', {{ $historical['id'] }})">
                                        <flux:button 
                                            type="submit" 
                                            variant="danger" 
                                            size="xs" 
                                            class="cursor-pointer" 
                                            aria-label="Delete this roll"
                                        >Delete</flux:button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach 
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>
