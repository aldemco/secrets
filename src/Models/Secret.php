<?php

namespace Aldemco\Secrets\Models;

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

    const UPDATED_AT = null;
}
