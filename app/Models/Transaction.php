<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    const TYPE_DEPOSIT = 1;
    const TYPE_WITHDRAWAL = 2;

    protected $table = 'transactions';

    protected $fillable = [
        'type', 'country', 'date', 'amount', 'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
