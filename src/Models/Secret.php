<?php

namespace Aldemco\Secrets\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Secret
 *
 * @property int $id
 * @property string $context
 * @property string|null $context_id
 * @property string|null $owner
 * @property string|null $owner_id
 * @property string $secret
 * @property bool $is_crypt
 * @property string|null $valid_until
 * @property string|null $valid_from
 * @property string|null $store_until
 * @property string|null $last_enter
 * @property string|null $success_enter
 * @property int $attemps_cnt
 * @property \Illuminate\Support\Carbon $created_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Secret newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Secret newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Secret query()
 * @method static \Illuminate\Database\Eloquent\Builder|Secret whereAttempsCnt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Secret whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Secret whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Secret whereIsCrypt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Secret whereLastEnter($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Secret whereOwnerClass($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Secret whereOwnerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Secret whereSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Secret whereSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Secret whereSourceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Secret whereStoreUntil($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Secret whereSuccessEnter($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Secret whereValidFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Secret whereValidUntil($value)
 * @mixin \Eloquent
 */
class Secret extends Model
{
    use HasFactory;

    protected $table = 'secrets';

    public int $status = 0;

    const UPDATED_AT = null;

    public function __construct()
    {
        parent::__construct();
        $this->table = config('secrets.table', 'secrets');
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUnactive($query)
    {
        return $query->where('valid_until', '<=', Carbon::now())
            ->where('valid_from', '>=', Carbon::now());
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeExpired($query)
    {
        return $query->where('store_until', '>=', Carbon::now());
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUsed($query)
    {
        return $query->whereNotNull('success_enter');
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithoutAttemps($query)
    {
        return $query->where('attemps_cnt', '<', 1);
    }
}
