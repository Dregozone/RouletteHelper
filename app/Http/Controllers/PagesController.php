<?php

namespace App\Http\Controllers;

use App\Models\Stake;
use App\Models\Historical;
use Illuminate\Http\Request;

class PagesController extends Controller
{
    
    public function main() {

        $historicals = Historical:: 
              select(
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
            ->get();

        $counts = [
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
        foreach ( $historicals as $currentRoll ) {
            
            if ( !$prevRoll ) {
                // This is the first (most recent) roll
                if ( $currentRoll->isEven ) {
                    $evenStreak = true;
                    $counts["even"]++;
                } else {
                    $oddStreak = true;
                    $counts["odd"]++;
                }

                if ( $currentRoll->isLow ) {
                    $lowStreak = true;
                    $counts["low"]++;
                } else {
                    $highStreak = true;
                    $counts["high"]++;
                }

                if ( $currentRoll->isRed ) {
                    $redStreak = true;
                    $counts["red"]++;
                } else {
                    $blackStreak = true;
                    $counts["black"]++;
                }
            } else {
                // This is NOT the most recent roll, there is a previous to compare against
                if ( $evenStreak && $currentRoll->isEven ) {
                    $counts["even"]++; // Increment the sequential count
                } else {
                    $evenStreak = false; // Otherwise, end the streak here
                }

                if ( $oddStreak && $currentRoll->isOdd ) {
                    $counts["odd"]++; // Increment the sequential count
                } else {
                    $oddStreak = false; // Otherwise, end the streak here
                }

                
                if ( $highStreak && $currentRoll->isHigh ) {
                    $counts["high"]++; // Increment the sequential count
                } else {
                    $highStreak = false; // Otherwise, end the streak here
                }

                if ( $lowStreak && $currentRoll->isLow ) {
                    $counts["low"]++; // Increment the sequential count
                } else {
                    $lowStreak = false; // Otherwise, end the streak here
                }


                if ( $redStreak && $currentRoll->isRed ) {
                    $counts["red"]++; // Increment the sequential count
                } else {
                    $redStreak = false; // Otherwise, end the streak here
                }

                if ( $blackStreak && $currentRoll->isBlack ) {
                    $counts["black"]++; // Increment the sequential count
                } else {
                    $blackStreak = false; // Otherwise, end the streak here
                }
            }

            // Set previous roll as this one for comparisson on next iteration
            $prevRoll = $currentRoll;
        }
        
        $behaviour = "Plain";
        $stakes = json_decode( Stake:: 
              where('name', $behaviour)
            ->first()
            ->stakes );

        return view('main', [
            'page' => 'main',

            'historicals' => $historicals,
            'counts' => $counts,
            'stakes' => $stakes,
        ]);
    }

    public function action(Request $request) {

        if ( "recordARoll" == $request->action ) {

            Historical::create([
                'num' => $request->number,
            ]);

            return redirect()
                ->route('main')
                ->with('msg', 'Roll was successfully recorded!');

        } else if ( "deleteAHistorical" == $request->action) {
            
            Historical::
                  find($request->id)
                ->delete();
            
            return redirect()
                ->route('main')
                ->with('msg', 'A historical roll was successfully removed!');

        } else if ( "clearAllHistorical" == $request->action ) {
            Historical::truncate();

            return redirect()
                ->route('main')
                ->with('msg', 'Cleared all historical rolls!');
        } else {
            dd($request->all());
        }
    }
}
