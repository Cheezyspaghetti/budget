<?php namespace App\Models;

use App\Traits\ForCurrentUserTrait;
use Auth;
use DB;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Account
 * @package App\Models
 */
class Account extends Model
{

    use ForCurrentUserTrait;

    /**
     * @var array
     */
    protected $fillable = ['name'];

    /**
     * @var array
     */
    protected $appends = ['path'];

    /**
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }

    /**
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function transactions()
    {
        return $this->hasMany('App\Models\Transaction');
    }

    /**
     * Return the URL of the resource
     * @return string
     */
    public function getPathAttribute()
    {
        return route('accounts.show', $this->id);
    }

    /**
     *
     * @return float
     */
    public function getBalanceAttribute()
    {
        $balance = 0;

        foreach ($this->transactions as $transaction) {
            $balance+= $transaction->total;
        }

        return (float) $balance;
    }

}
