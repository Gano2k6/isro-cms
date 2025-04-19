<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\SRO\Portal\MuUser;
use App\Models\SRO\Portal\MuVIPInfo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'jid',
        'username',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getJCash()
    {
        return Cache::remember('account_j_cash_'.$this->jid, config('settings.general.cache.data.account'), function () {
            return collect(DB::select("
            Declare @ReturnValue Int
            Declare @PremiumSilk Int
            Declare @Silk Int;
            Declare @VipLevel Int
            Declare @UsageMonth Int
            Declare @Usage3Month Int;
            SET NOCOUNT ON;

            Execute @ReturnValue = [GB_JoymaxPortal].[dbo].[B_GetJCash]
                ".$this->jid.",
                @PremiumSilk Output,
                @Silk Output,
                @VipLevel Output,
                @UsageMonth Output,
                @Usage3Month Output;

            Select
                @ReturnValue AS 'ErrorCode',
                @PremiumSilk AS 'PremiumSilk',
                @Silk AS 'Silk',
                @UsageMonth AS 'MonthUsage',
                @Usage3Month AS 'ThreeMonthUsage'
            "
            ))->first();
        });
    }

    public function getVipLevel()
    {
        return Cache::remember('account_vip_level_'.$this->jid, config('settings.general.cache.data.account'), function () {
            return DB::connection('portal')
            ->table('MU_VIP_Info')
                ->where('JID', $this->jid)
                ->first();
        });
    }

    public static function getUserCount()
    {
        return Cache::remember('account_count', config('settings.general.cache.data.account'), function () {
            return self::count();
        });
    }

    public function getMuUser()
    {
        return $this->hasMany(MuUser::class, 'jid', 'JID');
    }

    public function news()
    {
        return $this->hasMany(News::class);
    }

    public function role()
    {
        return $this->hasOne(UserRole::class);
    }
}
