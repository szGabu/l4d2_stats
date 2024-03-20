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

    public static function query_player(string $steamid)
    {
        $player = Player::withoutGlobalScope('config_filters')->where('steamid', $steamid)->first();

        return $player;
    }

    public static function get_awards()
    {
        $award_list = [
            'award_ppm' => "<a href=\"%s\">%s</a> is The Most Efficient Player with <b>%s Points Per Minute</b>.",
            'playtime' => "<a href=\"%s\">%s</a> has the most total playtime with <b>%s of Play</b>.",
            'kills' => "<a href=\"%s\">%s</a> is The Real Chicago Ted with <b>%s Total Kills</b>.",
            'headshots' => "<a href=\"%s\">%s</a> can Aim For The Top with <b>%s Headshots</b>.",
            'award_ratio' => "<a href=\"%s\">%s</a> is The Headshot King with a <b>%s&#37 Headshot Ratio</b>.",
            'melee_kills' => "<a href=\"%s\">%s</a> is The Martial Artist with <b>%s Total Melee Kills</b>.",
            'versus_kills_survivors' => "<a href=\"%s\">%s</a> Masters The Life Of The Undead with <b>%s Survivor</b> kills.",
            'kill_infected' => "<a href=\"%s\">%s</a> can Kill Anyone He Wants with <b>%s Common Infected</b> kills.",
            'kill_hunter' => "<a href=\"%s\">%s</a> Moves Like They Do with <b>%s Hunter</b> kills.",
            'kill_smoker' => "<a href=\"%s\">%s</a> is In The Non-Smoking Section with <b>%s Smoker</b> kills.",
            'kill_boomer' => "<a href=\"%s\">%s</a> is a Weight Loss Trainer with <b>%s Boomer</b> kills.",
            'award_pills' => "<a href=\"%s\">%s</a> says The First Hit Is Free with <b>%s Pain Pills Given</b>.",
            'award_medkit' => "<a href=\"%s\">%s</a> is Wishing He Had A Medigun with <b>%s Medkits Used on Teammates</b>.",
            'award_hunter' => "<a href=\"%s\">%s</a> is Johnny On The Spot by <b>Saving %s Pounced Teammates From Hunters</b>.",
            'award_smoker' => "<a href=\"%s\">%s</a> is Into Anime, But Not Like That by <b>Saving %s Teammates From Smokers</b>.",
            'award_protect' => "<a href=\"%s\">%s</a> is Saving Your Ass with <b>%s Teammates Protected</b>.",
            'award_revive' => "<a href=\"%s\">%s</a> is There When You Need Him by <b>Reviving %s Teammates</b>.",
            'award_rescue' => "<a href=\"%s\">%s</a> is Checking All The Closets with <b>%s Teammates Rescued</b>.",
            'award_campaigns' => "<a href=\"%s\">%s</a> is Getting Rescued... Again! with <b>%s Campaigns Completed</b>.",
            'award_tankkill' => "<a href=\"%s\">%s</a> is Bringing Down The House by <b>Team Assisting %s Tank Kills</b>.",
            'award_tankkillnodeaths' => "<a href=\"%s\">%s</a> is Bringing Superior Firepower by <b>Team Assisting %s Tank Kills, With No Deaths</b>.",
            'award_allinsafehouse' => "<a href=\"%s\">%s</a> is Leaving No Man Behind with <b>%s Safe Houses Reached With All Survivors</b>.",
            'award_friendlyfire' => "<a href=\"%s\">%s</a> is A Terrible Friend with <b>%s Friendly Fire Incidents</b>.",
            'award_teamkill' => "<a href=\"%s\">%s</a> is Going To Be Banned, BRB with <b>%s Team Kills</b>.",
            'award_fincap' => "<a href=\"%s\">%s</a> is Not Very Friendly with <b>%s Team Incapacitations</b>.",
            'award_left4dead' => "<a href=\"%s\">%s</a> will Leave You For Dead by <b>Allowing %s Teammates To Die In Sight</b>.",
            'award_letinsafehouse' => "<a href=\"%s\">%s</a> is Turning Into One Of Them with <b>%s Infected Let In The Safe Room</b>.",
            'award_witchdisturb' => "<a href=\"%s\">%s</a> is Not A Lady Pleaser by <b>Disturbing %s Witches</b>.",
            'award_pounce_nice' => "<a href=\"%s\">%s</a> is Pain From Above with <b>%s Hunter Nice Pounces</b>.",
            'award_pounce_perfect' => "<a href=\"%s\">%s</a> is Death From Above with <b>%s Hunter Perfect Pounces</b>.",
            'award_perfect_blindness' => "<a href=\"%s\">%s</a> is A Pain Painter causing <b>%s Times Perfect Blindness With A Boomer</b>.",
            'award_infected_win' => "<a href=\"%s\">%s</a> is Driving Survivors In To Extinction with <b>%s Infected Victories</b>.",
            'award_bulldozer' => "<a href=\"%s\">%s</a> is A Tank Bulldozer inflicting <b>Massive Damage %s Times To The Survivors</b>.",
            'award_survivor_down' => "<a href=\"%s\">%s</a> puts Survivors On Their Knees with <b>%s Incapacitations</b>.",
            'award_ledgegrab' => "<a href=\"%s\">%s</a> wants Survivors Of The Map causing <b>%s Survivors Grabbing On The Ledge</b>.",
            'award_witchcrowned' => "<a href=\"%s\">%s</a> Knows How To Handle Women with <b>%s Crowned Witches</b>.",
            'infected_tanksniper' => "<a href=\"%s\">%s</a> is A Tank Sniper hitting <b>%s Survivors With A Rock</b>.",
            'kill_spitter' => "<a href=\"%s\">%s</a> Don't Like Zombies Without Manners with <b>%s Spitter</b> kills.",
            'kill_jockey' => "<a href=\"%s\">%s</a> Likes To Be On Top with <b>%s Jockey</b> kills.",
            'kill_charger' => "<a href=\"%s\">%s</a> Don't Like To Be Pushed Around with <b>%s Charger</b> kills.",
            'award_adrenaline' => "<a href=\"%s\">%s</a> Needs The Teammates To Stay In Top Speed with <b>%s Adrenalines Given</b>.",
            'award_defib' => "<a href=\"%s\">%s</a> is A Life Giver with <b>%s Defibrillators Used on Teammates</b>.",
            'award_jockey' => "<a href=\"%s\">%s</a> is The Freedom Fighter by <b>Saving %s Teammates From Jockeys</b>.",
            'award_matador' => "<a href=\"%s\">%s</a> is The Matador with <b>%s Leveled Charges</b>.",
            'award_scatteringram' => "<a href=\"%s\">%s</a> is a Crowd Breaker with <b>%s Scattering Rams</b>."
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

        return $stats;
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