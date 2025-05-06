<?php

namespace App\Models\SRO\Account;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class MagOptDesc extends Model
{
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
    protected $table = 'dbo._MagOptDesc';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'desc',
        'mLevel',
        'extension',
        'sortkey'
    ];

    public static function getBlues($aItem, &$aSpecialInfo): array
    {
        $minutes = config('global.cache.character_info', 5);

        return Cache::remember('character_info_MagOptDesc', now()->addMinutes($minutes), static function () {
            $aData = self::all()->sortBy('id');
            $aList = [];
            foreach ($aData as $iKey => $aCurData) {
                $aList[$aCurData['id']] = [
                    'name' => $aCurData['name'],
                    'desc' => $aCurData['desc'],
                    'mLevel' => $aCurData['mLevel'],
                    'extension' => $aCurData['extension'],
                    'sortkey' => $aCurData['sortkey'],
                ];
            }
            return $aList;
        });
    }
}
