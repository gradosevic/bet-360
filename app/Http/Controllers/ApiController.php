<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Transaction;

class ApiController extends Controller
{
    public function transactions()
    {
        return Transaction::all();
    }
}
