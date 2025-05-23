<?php

namespace App\Models\SRO\Shard;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class GuildMember extends Model
{
    use HasFactory;

    /**
     * The Database connection name for the model.
     *
     * @var string
     */
    protected $connection = 'shard';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dbo._GuildMember';

    /**
     * The table primary Key
     *
     * @var string
     */
    protected $primaryKey = 'GuildID';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];

    /**
     * The attributes format for dates.
     *
     * @var array
     */
    protected $dates = [
        'JoinDate'
    ];

    protected $dateFormat = 'Y-m-d H:i:s';

    public static function getFortressPlayerRanking($limit = 25)
    {
        $minutes = config('global.cache.ranking_fortress_player', 60);

        return Cache::remember("ranking_fortress_player_{$limit}", now()->addMinutes($minutes), function () use ($limit) {
            return self::select(
                '_Char.CharID',
                '_Char.CharName16',
                '_Char.RefObjID',
                '_GuildMember.GuildWarKill',
                '_GuildMember.GuildWarKilled'
            )
            ->join('_Char', '_Char.CharID', '=', '_GuildMember.CharID')
            ->where('_Char.deleted', '=', 0)
            ->where('_Char.CharID', '>', 0)
            ->groupBy(
                '_Char.CharID',
                '_Char.CharName16',
                '_Char.RefObjID',
                '_GuildMember.GuildWarKill',
                '_GuildMember.GuildWarKilled',
            )
            ->orderByDesc('_GuildMember.GuildWarKill')
            ->limit($limit)
            ->get();
        });
    }

    public static function getGuildInfoMembers($GuildID)
    {
        $minutes = config('global.cache.guild_info', 1440);

        return Cache::remember("guild_info_members_{$GuildID}", now()->addMinutes($minutes), function () use ($GuildID) {
            return self::where('GuildID', $GuildID)
            ->orderBy('MemberClass', 'asc')
            ->orderBy('Contribution', 'desc')
            ->orderBy('GuildWarKill', 'desc')
            ->orderBy('CharLevel', 'desc')
            ->orderBy('GP_Donation', 'desc')
            ->get();
        });
    }
}
