<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Auth\RegisterController;
use App\User;
use Illuminate\Http\Request;
use App\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
/***
 * API Interface to Bet 360 app
 * Class ApiController
 * @package App\Http\Controllers
 */
class ApiController extends Controller
{
    /***
     * Get an array of users
     * @return array User
     */
    public function users()
    {
        return User::orderBy('name')->get();
    }

    /***
     * Get user by ID
     * @param $id User ID
     * @return User
     */
    public function getUser($id)
    {
        return User::find($id);
    }

    /***
     * Create a new user
     * @param Request $request
     * @return User
     * @throws \Exception validation errors
     */
    public function createUser(Request $request)
    {

        $user = new User;
        $user->name = $request->get('name');
        $user->email = $request->get('email');

        $user->percent_bonus = User::generateBonus();
        $user->country = $request->get('country');
        $user->balance = $request->get('balance');
        $password = $request->get('password');
        $password2 = $request->get('password2');

        if (!$user->name || !$user->email || !$user->country || !$user->balance || !$password || !$password2) {
            throw new \Exception('Please fill in all fields.');
        }

        if ($password != $password2) {
            throw new \Exception('Passwords do not match');
        }
        $user->password = Hash::make($password);

        if (RegisterController::validator($request->all())) {
            $user->save();
        };

        return $user;
    }

    /***
     * Update an existing user
     * @param Request $request
     * @return User
     */
    public function updateUser(Request $request)
    {
        $user = User::find($request->get('id'));
        if ($user) {
            $user->fill($request->all())->save();
        }
        return $user;
    }

    /***
     * Creates a new deposit transaction for a specified user
     * @param Request $request
     * @return Transaction
     * @throws \Exception Minimal deposit amount
     */
    public function deposit(Request $request)
    {
        $user = User::find($request->get('user_id'));

        if ($user) {
            $amount = $request->get('amount');
            if ($amount < 5) {
                throw new \Exception('Minimal deposit amount is 5');
            }
            $user->balance += $amount;

            //Check if current transactions will be third in a row
            //If so, add bonus
            //+1 Count this transaction as well
            if (($user->deposits()->count() + 1) % 3 == 0) {
                $user->balance += $amount * $user->percent_bonus / 100;
            }

            $t = Transaction::create([
                'type' => Transaction::TYPE_DEPOSIT,
                'country' => $user->country,
                'date' => Carbon::now(),
                'amount' => $amount,
                'user_id' => $user->id
            ]);
            $user->transactions()->save($t);

            $user->save();
        }
        return $t;
    }

    /**
     * Creates a new withdrawal transaction for a specified user
     * @param Request $request
     * @return mixed
     * @throws \Exception Minimal withdrawal amount
     */
    public function withdrawal(Request $request)
    {
        $user = User::find($request->get('user_id'));
        if ($user) {
            $amount = $request->get('amount');
            if ($amount < 100) {
                throw new \Exception('Minimal withdrawal amount is 100');
            } elseif ($amount > $user->balance) {
                throw new \Exception('User does not have sufficient funds for this transaction');
            }
            $user->balance -= $amount;

            $t = Transaction::create([
                'type' => Transaction::TYPE_DEPOSIT,
                'country' => $user->country,
                'date' => Carbon::now(),
                'amount' => $amount,
                'user_id' => $user->id
            ]);

            $user->transactions()->save($t);
            $user->save();
        }
        return $t;
    }

    /**
     * Get an existing transaction by ID
     * @param $id Transaction ID
     * @return Transaction
     */
    public function transaction($id)
    {
        return Transaction::find($id);
    }

    /**
     * Returns an array of transactions
     * Optional filters: user_id, from, to
     * @param Request $request
     * @return array Transaction
     */
    public function transactions(Request $request)
    {
        $from = $request->get('from');
        $to = $request->get('to');
        $user_id = $request->get('user_id');

        $request = Transaction::with(array('user' => function ($query) {
            $query->select('name', 'id');
        }));

        if ($user_id) {
            $request->where('user_id', $user_id);
        }

        if ($from) {
            $request->where('date', '>=', $from);
        }

        if ($to) {
            $request->where('date', '<=', $to);
        }

        $request->orderBy('date', 'type');

        return $request->get();
    }

    /***
     * Returns an array of report data
     * Optional filters: from(date), to(date)
     * @param Request $request
     * @return array<array(Report data)>
     */
    public function reports(Request $request)
    {
        $from = $request->get('from');
        $to = $request->get('to');

        $request = \DB::table('transactions')
        ->selectRaw("
                    sum(case when type = '1' then amount else 0 end) as total_deposits,
                    sum(case when type = '2' then amount else 0 end) as total_withdrawals,
                    count(case when type = '1' then 1 else null end) as deposits,
                    count(case when type = '2' then 1 else null end) as withdrawals,
                    count(distinct user_id) as users,
                    date,
                    country
        ");

        if ($from) {
            $request->where('date', '>=', $from);
        }

        if ($to) {
            $request->where('date', '<=', $to);
        }

        $request->orderBy('date', 'country')
        ->groupBy('date', 'country');

        return $request->get();
    }
}
