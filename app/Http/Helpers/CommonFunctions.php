<?php
namespace App\Http\Helpers;
use App\Models\Player;
use App\Models\ServerSettings;
use Illuminate\Support\Facades\DB;

class CommonFunctions
{
    public static function top_10_players()
    {
        $players = Player::select([
                                    'steamid',
                                    DB::raw('(SELECT count(DISTINCT b.points)+1 FROM players AS b WHERE b.points > players.points) AS position'),
                                    'name', 
                                    'points'
                                    ])->orderBy('points', 'DESC')->limit(10)->get();
        return $players;
    }

    public static function get_message_of_the_day()
    {
        $serversettings = ServerSettings::where('sname', 'motdmessage')->first();

        return $serversettings->svalue;
    }

    public static function get_random_header_image()
    {
        return asset(sprintf('img/render%d.jpg', rand(1, 29)));
    }

    public static function get_player_stats(string $steamid)
    {
        $player = Player::withoutGlobalScope('config_filters')->where('steamid', $steamid)->first();

        $player->survivors_killed = self::get_survivors_killed($steamid);
        $player->headshot_ratio = self::get_headshot_ratio($steamid);
        $player->boomer_average = self::get_infected_average('boomer', $steamid);
        $player->hunter_average = self::get_infected_average('hunter', $steamid);
        $player->smoker_average = self::get_infected_average('smoker', $steamid);
        $player->spitter_average = self::get_infected_average('spitter', $steamid);
        $player->jockey_average = self::get_infected_average('jockey', $steamid);
        $player->charger_average = self::get_infected_average('charger', $steamid);
        $player->tank_average = self::get_infected_average('tank', $steamid);

        return $player;
    }

    public static function get_awards()
    {
        $award_list = [
            'award_ppm' => "is The Most Efficient Player with <b>&VALUE& Points Per Minute</b>.",
            'playtime' => "has the most total playtime with <b>&VALUE& of Play</b>.",
            'kills' => "is The Real Chicago Ted with <b>&VALUE& Total Kills</b>.",
            'headshots' => "can Aim For The Top with <b>&VALUE& Headshots</b>.",
            'award_ratio' => "is The Headshot King with a <b>&VALUE&&#37 Headshot Ratio</b>.",
            'melee_kills' => "is The Martial Artist with <b>&VALUE& Total Melee Kills</b>.",
            'versus_kills_survivors' => "Masters The Life Of The Undead with <b>&VALUE& Survivor</b> kills.",
            'kill_infected' => "can Kill Anyone He Wants with <b>&VALUE& Common Infected</b> kills.",
            'kill_hunter' => "Moves Like They Do with <b>&VALUE& Hunter</b> kills.",
            'kill_smoker' => "is In The Non-Smoking Section with <b>&VALUE& Smoker</b> kills.",
            'kill_boomer' => "is a Weight Loss Trainer with <b>&VALUE& Boomer</b> kills.",
            'award_pills' => "says The First Hit Is Free with <b>&VALUE& Pain Pills Given</b>.",
            'award_medkit' => "is Wishing He Had A Medigun with <b>&VALUE& Medkits Used on Teammates</b>.",
            'award_hunter' => "is Johnny On The Spot by <b>Saving &VALUE& Pounced Teammates From Hunters</b>.",
            'award_smoker' => "is Into Anime, But Not Like That by <b>Saving &VALUE& Teammates From Smokers</b>.",
            'award_protect' => "is Saving Your Ass with <b>&VALUE& Teammates Protected</b>.",
            'award_revive' => "is There When You Need Him by <b>Reviving &VALUE& Teammates</b>.",
            'award_rescue' => "is Checking All The Closets with <b>&VALUE& Teammates Rescued</b>.",
            'award_campaigns' => "is Getting Rescued... Again! with <b>&VALUE& Campaigns Completed</b>.",
            'award_tankkill' => "is Bringing Down The House by <b>Team Assisting &VALUE& Tank Kills</b>.",
            'award_tankkillnodeaths' => "is Bringing Superior Firepower by <b>Team Assisting &VALUE& Tank Kills, With No Deaths</b>.",
            'award_allinsafehouse' => "is Leaving No Man Behind with <b>&VALUE& Safe Houses Reached With All Survivors</b>.",
            'award_friendlyfire' => "is A Terrible Friend with <b>&VALUE& Friendly Fire Incidents</b>.",
            'award_teamkill' => "is Going To Be Banned, BRB with <b>&VALUE& Team Kills</b>.",
            'award_fincap' => "is Not Very Friendly with <b>&VALUE& Team Incapacitations</b>.",
            'award_left4dead' => "will Leave You For Dead by <b>Allowing &VALUE& Teammates To Die In Sight</b>.",
            'award_letinsafehouse' => "is Turning Into One Of Them with <b>&VALUE& Infected Let In The Safe Room</b>.",
            'award_witchdisturb' => "is Not A Lady Pleaser by <b>Disturbing &VALUE& Witches</b>.",
            'award_pounce_nice' => "is Pain From Above with <b>&VALUE& Hunter Nice Pounces</b>.",
            'award_pounce_perfect' => "is Death From Above with <b>&VALUE& Hunter Perfect Pounces</b>.",
            'award_perfect_blindness' => "is A Pain Painter causing <b>&VALUE& Times Perfect Blindness With A Boomer</b>.",
            'award_infected_win' => "is Driving Survivors In To Extinction with <b>&VALUE& Infected Victories</b>.",
            'award_bulldozer' => "is A Tank Bulldozer inflicting <b>Massive Damage &VALUE& Times To The Survivors</b>.",
            'award_survivor_down' => "puts Survivors On Their Knees with <b>&VALUE& Incapacitations</b>.",
            'award_ledgegrab' => "wants Survivors Of The Map causing <b>&VALUE& Survivors Grabbing On The Ledge</b>.",
            'award_witchcrowned' => "Knows How To Handle Women with <b>&VALUE& Crowned Witches</b>.",
            'infected_tanksniper' => "is A Tank Sniper hitting <b>&VALUE& Survivors With A Rock</b>.",
            'kill_spitter' => "Don't Like Zombies Without Manners with <b>&VALUE& Spitter</b> kills.",
            'kill_jockey' => "Likes To Be On Top with <b>&VALUE& Jockey</b> kills.",
            'kill_charger' => "Don't Like To Be Pushed Around with <b>&VALUE& Charger</b> kills.",
            'award_adrenaline' => "Needs The Teammates To Stay In Top Speed with <b>&VALUE& Adrenalines Given</b>.",
            'award_defib' => "is A Life Giver with <b>&VALUE& Defibrillators Used on Teammates</b>.",
            'award_jockey' => "is The Freedom Fighter by <b>Saving &VALUE& Teammates From Jockeys</b>.",
            'award_matador' => "is The Matador with <b>&VALUE& Leveled Charges</b>.",
            'award_scatteringram' => "is a Crowd Breaker with <b>&VALUE& Scattering Rams</b>."
        ];

        $awards = [];

        foreach($award_list as $key => $award_ind)
        {
            $awards[$key] = [];
            $awards[$key]["description"] = $award_ind;
            if($key == 'award_ppm')
                $awards[$key]['top'] = Player::select('steamid', 'name', DB::raw('points/playtime as "score"'))->orderBy(DB::raw('points/playtime'), 'DESC')->limit(3)->get();
            elseif($key == 'award_ratio')
                $awards[$key]['top'] = Player::select('steamid', 'name', DB::raw('kills/headshots as "score"'))->orderBy(DB::raw('kills/headshots'), 'DESC')->limit(3)->get();
            else
                $awards[$key]['top'] = Player::select('steamid', 'name', DB::raw(sprintf('%s as "score"', $key)))->orderBy($key, 'DESC')->limit(3)->get();
        }

        return $awards;
    }

    public static function get_server_stats()
    {
        $cols = [];
        foreach(Player::get_summable_fields() as $field)
            array_push($cols, DB::raw(sprintf('SUM(%1$s) as %1$s', $field)));

        $stats = Player::select($cols)->first();

        $stats->playercount = self::get_player_count();
        $stats->survivors_killed = self::get_survivors_killed();
        $stats->headshot_ratio = self::get_headshot_ratio();
        $stats->boomer_average = self::get_infected_average('boomer');
        $stats->hunter_average = self::get_infected_average('hunter');
        $stats->smoker_average = self::get_infected_average('smoker');
        $stats->spitter_average = self::get_infected_average('spitter');
        $stats->jockey_average = self::get_infected_average('jockey');
        $stats->charger_average = self::get_infected_average('charger');
        $stats->tank_average = self::get_infected_average('tank');

        return $stats;
    }

    public static function get_player_count()
    {
        $playercount = Player::count();
        
        return $playercount;
    }

    public static function get_survivors_killed(string $steamid = null)
    {
        $stats = Player::selectRaw('SUM(versus_kills_survivors)+SUM(scavenge_kills_survivors)+SUM(realism_kills_survivors) as survivors_killed');
        if(!is_null($steamid))
            $stats = $stats->where('steamid', $steamid);

        $stats = $stats->first();

        return $stats?->survivors_killed;
    }

    public static function get_headshot_ratio(string $steamid = null)
    {
        //taken directly from the plugin
        // Headshots == 0 ? 0.00 : float(Headshots) / float(InfectedKilled)*100;
        $stats = Player::selectRaw('SUM(headshots) / SUM(kill_infected+kill_hunter+kill_smoker+kill_boomer+kill_spitter+kill_jockey+kill_charger)*100 as headshot_ratio');
        if(!is_null($steamid))
            $stats = $stats->where('steamid', $steamid);

        $stats = $stats->first();

        return $stats?->headshot_ratio;
    }

    public static function get_infected_average(string $infected, string $steamid = null)
    {
        $column1 = null;
        $column2 = sprintf('infected_%s_damage', $infected);
        switch($infected)
        {
            case 'boomer':
            {
                $column1 = 'infected_spawn_2';
                $column2 = 'infected_boomer_blinded';
                break;
            }
            case 'hunter':
            {
                $column1 = 'infected_spawn_3';
                $column2 = 'infected_hunter_pounce_dmg';
                break;
            }
            case 'smoker':
            {
                $column1 = 'infected_spawn_1';
                break;
            }
            case 'spitter':
            {
                $column1 = 'infected_spawn_4';
                break;
            }
            case 'jockey':
            {
                $column1 = 'infected_spawn_5';
                break;
            }
            case 'charger':
            {
                $column1 = 'infected_spawn_6';
                break;
            }
            case 'tank':
            {
                $column1 = 'infected_spawn_8';
                break;
            }
            default:
            {
                return 0.0;
            }
        }

        $stats = Player::selectRaw(sprintf('SUM(%s)/SUM(%s) as average', $column1, $column2));
        if(!is_null($steamid))
            $stats = $stats->where('steamid', $steamid);

        $stats = $stats->first();

        return $stats?->average;
    }

    public static function return_data_table($object, $total, $draw = 10, $filtered = null)
    {   
        if(is_null($filtered))
            $filtered = $total;

        $output = new \stdClass();

        $output->draw = $draw;
        $output->recordsTotal = $total;
        $output->recordsFiltered = $filtered;

        $output->data = $object;

        return response()->json($output);
    }
}