<?php

namespace App\Http\Controllers;

use App\Http\Helpers\CommonFunctions;
use App\Models\Player;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApiController extends Controller
{
    public static function query_player_stats(string $steamid)
    {
        $player = CommonFunctions::get_player_stats($steamid);

        return response()->json($player);
    }

    public static function query_server_stats()
    {
        $server = CommonFunctions::get_server_stats();

        return response()->json($server);
    }

    public static function query_awards()
    {
        $awards = CommonFunctions::get_awards();

        return response()->json($awards);
    }

    public static function query_playercount()
    {
        return response()->json(Player::count());
    }

    public static function query_stats(Request $request)
    {
        $input = $request->all();  

        $busqueda = isset($input["search"]) && isset($input["search"]["value"]) ? sprintf('%%%s%%', $input["search"]["value"]) : null;
        $start = isset($input["start"]) ? intval($input["start"]) : 0;
        $length = isset($input["length"]) ? intval($input["length"]) : 10;
        $draw = isset($input["draw"]) ? intval($input["draw"]) : 10;
        $url = isset($input["url"]) ? $input["url"] : null;

        $players = null;
        
        $players = Player::select(
                                [
                                    'players.steamid',
                                    'players.name', 
                                    'players.points', 
                                    'players.playtime', 
                                    'players.lastontime', 
                                    DB::raw('(SELECT count(DISTINCT b.points)+1 FROM players AS b WHERE b.points > players.points) AS position')
                                ])
                            ->orderBy('points', 'DESC');

        $cantidad_orig = $players->count();

        if($busqueda)
        {
            $players = $players->where(function($query) use ($busqueda){
                    return $query->where('name', 'LIKE', $busqueda);
                });
        }

        $cantidad_filtrada = $players->count();

        $page = ($start+$length)/intval($length);

        $players = $players->orderBy('points', 'DESC');

        $players = $players->paginate(intval($length), ['*'], 'page', $page);

        $players_optimized = [];

        foreach($players as $player)
        {
            $buffer = [];

            array_push($buffer, $player->position);
            if(is_null($url))
                array_push($buffer, sprintf("<a href=\"%s\">%s</a>", route('stats.individual', ['steamid' => $player->steamid ]), htmlspecialchars($player->name)));
            elseif(strcmp($url, "no") == 0) 
                array_push($buffer, sprintf("%s", htmlspecialchars($player->name)));
            else
                array_push($buffer, sprintf("<a href=\"https://%s?steamid=%s\">%s</a>", $url, $player->steamid, htmlspecialchars($player->name)));
            array_push($buffer, $player->points);
            array_push($buffer, CarbonInterval::minutes($player->playtime)->cascade()->forHumans());
            array_push($buffer, Carbon::createFromTimestamp($player->lastontime)->diffForHumans());

            array_push($players_optimized, $buffer);
        }

        return CommonFunctions::return_data_table($players_optimized, $cantidad_orig, $draw, $cantidad_filtrada);
    }

    public static function query_online()
    {
        $players = null;
        $players = Player::select(
                                [
                                    'players.name', 
                                    'players.points', 
                                    'players.lastgamemode', 
                                    'players.lastontime', 
                                    DB::raw('(SELECT count(DISTINCT b.points)+1 FROM players AS b WHERE b.points > players.points) AS position')
                                ])
                            ->where('lastontime', '>', Carbon::now()->subMinutes(10)->timestamp)
                            ->orderBy('points', 'DESC')
                            ->get();

        $cantidad_orig = $players->count();

        $players_optimized = [];

        foreach($players as $player)
        {
            $buffer = [];

            array_push($buffer, $player->name);
            array_push($buffer, $player->points);
            $lastgamemode = "Unknown";
            switch($player->lastgamemode)
            {
                case 0:
                    $lastgamemode = "Coop";
                    break;
                case 1:
                    $lastgamemode = "Versus";
                    break;
                case 2:
                    $lastgamemode = "Realism";
                    break;
                case 3:
                    $lastgamemode = "Survival";
                    break;
                case 4:
                    $lastgamemode = "Scavenge";
                    break;
                case 5:
                    $lastgamemode = "Realism Versus";
                    break;
                case 6:
                    $lastgamemode = "Mutation";
                    break;
            }
            array_push($buffer, $lastgamemode);
            array_push($buffer, sprintf("#%d", $player->position));
            array_push($buffer, CarbonInterval::minutes($player->playtime)->cascade()->forHumans());
            array_push($players_optimized, $buffer);
        }

        return CommonFunctions::return_data_table($players_optimized, $cantidad_orig);
    }

    public static function query_top_10()
    {
        $top10 = CommonFunctions::top_10_players();

        return response()->json($top10);
    }
}