<?php

namespace App\Models\SRO\Account;

use App\Models\SRO\Portal\MuUser;
use App\Models\SRO\Shard\Char;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class TbUser extends Model
{
    use HasFactory;

    /**
     * The Database connection name for the model.
     *
     * @var string
     */
    protected $connection = 'account';

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
    protected $table = 'dbo.TB_User';

    /**
     * The table primary Key
     *
     * @var string
     */
    protected $primaryKey = 'JID';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'PortalJID',
        'StrUserID',
        'ServiceCompany',
        'password',
        'Active',
        'UserIP',
        'CountryCode',
        'VisitDate',
        'RegDate',
        'sec_primary',
        'sec_content',
        'sec_grade',
    ];

    protected $hidden = [
        'password'
    ];

    public static function setGameAccount($jid, $username, $password, $ip)
    {
        return self::create([
            'PortalJID' => $jid,
            'StrUserID' => $username,
            'ServiceCompany' => 11,
            'password' => md5($password),
            'Active' => 1,
            'UserIP' => $ip,
            'CountryCode' => 'EG',
            'VisitDate' => now(),
            'RegDate' => now(),
            'sec_primary' => 3,
            'sec_content' => 3,
            'sec_grade' => 0,
        ]);
    }

    public static function getTbUserCount()
    {
        return Cache::remember('game_account_count', config('global.general.cache.data.account'), function () {
            return self::count();
        });
    }

    public function getShardUser()
    {
        return $this->belongsToMany(Char::class, '_User', 'UserJID', 'CharID');
    }

    public function getMuUser()
    {
        return $this->hasOne(MuUser::class, 'JID', 'PortalJID');
    }

    public function getWebUser()
    {
        return $this->belongsTo(User::class, 'jid', 'PortalJID');
    }
}
