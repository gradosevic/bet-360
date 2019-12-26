<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'percent_bonus', 'country', 'balance',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function transactions()
    {
        return $this->hasMany('App\Transaction');
    }

    public function deposits()
    {
        return $this->transactions()->where('type', 1)->get();
    }

    /**
     * Returns random bonus value between 5 and 20 (%).
     *
     * @return int Bonus between 5 and 20 (%)
     */
    public static function generateBonus()
    {
        return rand(5, 20);
    }
}
