<?php

namespace App\Http\Controllers;

use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Helpers\CommonFunctions;

class ViewController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function playersonline()
    {
        return view('stats.online');
    }

    public function ranking()
    {
        return view('stats.top');
    }

    public function player_stats(Request $request)
    {
        $input = $request->all();
        $steamid = isset($input["steamid"]) ? $input["steamid"] : null;
        $player = CommonFunctions::get_player_stats($steamid);
        
        if($player)
        {
            return view('summary.player')
                ->with('stats', $player)
                ->with('is_server', false);
        }
        else
        {
            flash('Player not found')->error();
            return view('generic.blank');
        }
    }

    public function server_awards(Request $request)
    {
        $awards = CommonFunctions::get_awards();

        return view('stats.awards')
                ->with('awards_array', $awards);
    }

    public function server_stats(Request $request)
    {
        $stats = CommonFunctions::get_server_stats();
        
        return view('summary.server')
            ->with('stats', $stats)
            ->with('playercount', Player::count())
            ->with('is_server', true);
    }
}