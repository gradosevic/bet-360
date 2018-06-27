<?php

namespace Tests\Feature;

use App\Transaction;
use App\User;
use Carbon\Carbon;
use PHPUnit\Framework\TestResult;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ApiTest extends TestCase
{
    /**
     * Tests GET /api/users method. It should return an array of users
     */
    public function test_get_users()
    {
        $this->withoutMiddleware();
        $response = $this->json('GET', 'api/users');

        $response->assertStatus(200);

        $data = $response->json();
        $this->assertTrue(sizeof($data) > 0);

        $item = $data[0];
        $this->assertTrue(!empty($item['id']));
        $this->assertTrue(!empty($item['name']));
        $this->assertTrue(!empty($item['email']));
        $this->assertEquals($item['email'], filter_var($item['email'], FILTER_VALIDATE_EMAIL));
        $this->assertTrue(!empty($item['percent_bonus']));
        $this->assertTrue(!empty($item['country']));
    }

    /**
     * Tests GET /api/user/{id} method
     */
    public function test_get_user(){
        $this->withoutMiddleware();

        $this->clearTestUser();
        $user = $this->addTestUser();

        $response = $this->json('GET', 'api/user/'.$user->id);
        $data = $response->json();

        $this->assertEquals($user->id, $data['id']);
        $this->assertEquals($user->name, $data['name']);
        $this->assertEquals($user->email, $data['email']);
        $this->assertEquals($user->percent_bonus, $data['percent_bonus']);
        $this->assertEquals($user->country, $data['country']);
        $this->assertEquals($user->balance, $data['balance']);
    }

    /**
     * Tests PUT /api/user method
     */
    public function test_create_user(){
        $this->withoutMiddleware();
        $this->clearTestUser();

        $userEmail = 'test@test.com';
        $userName = 'Test User';
        $country = 'BA';
        $balance = '10.00';

        $response = $this->json('PUT', 'api/user', [
            'name'=> $userName,
            'email' => $userEmail,
            'country' => $country,
            'password' => 'password',
            'password2' => 'password',
            'balance' => '10.00'
        ]);

        $data = $response->json();

        $this->assertEquals($userName, $data['name']);
        $this->assertEquals($userEmail, $data['email']);
        $this->assertEquals($balance, $data['balance']);
        $this->assertEquals($country, $data['country']);
        $this->assertTrue(is_numeric($data['id']));
        $this->assertTrue($data['percent_bonus']>=5);
        $this->assertTrue($data['percent_bonus']<=20);
        $this->assertTrue(empty($data['exception']));
    }

    /***
     * Tests PUT /api/user method
     */
    public function test_try_to_create_a_user_with_the_same_email_address(){
        $this->withoutMiddleware();

        $this->clearTestUser();
        $this->addTestUser();

        $userEmail = 'test@test.com';

        $response = $this->json('PUT', 'api/user', [
            'email' => $userEmail,
            'name'=> 'Test',
            'country' => 'BA',
            'password' => 'password',
            'password2' => 'password',
            'balance' => '10.00'
        ]);
        $data = $response->json();
        $this->assertTrue(empty($data['email']));
        $this->assertFalse(empty($data['exception']));
    }

    /**
     * Tests POST /api/user method
     */
    public function test_update_existing_user(){
        $this->withoutMiddleware();

        $this->clearTestUser();
        $this->addTestUser();

        $userEmail = 'test@test.com';

        $user = User::where('email', $userEmail)->first();

        $updatedUserEmail = 'test2@test.com';
        $updatedUserName = 'Test User';
        $updatedCountry = 'BA';
        $updatedBalance = '10.00';

        //Clear updated test user from previous testing
        $updatedTestUser = User::where('email', $updatedUserEmail)->first();
        if($updatedTestUser){
            $updatedTestUser->delete();
        }

        $response = $this->json('POST', 'api/user', [
            'email' => $updatedUserEmail,
            'name'=> $updatedUserName,
            'country' => $updatedCountry,
            'balance' => $updatedBalance,
            'id' => $user->id
        ]);

        $data = $response->json();

        $this->assertEquals($updatedUserEmail, $data['email']);
        $this->assertEquals($updatedUserName, $data['name']);
        $this->assertEquals($updatedCountry, $data['country']);
        $this->assertEquals($updatedBalance, $data['balance']);


        //Make sure that user with the previous email does not exist
        $previousUser = User::where('email', $userEmail)->first();
        $this->assertNull($previousUser);

    }

    /**
     * Tests that changing user's country does not affect previous transactions
     */
    public function test_user_changing_country_does_not_affect_transaction_coutry(){
        $this->withoutMiddleware();

        $this->clearTestUser();
        $user = $this->addTestUser();

        $response = $this->json('POST', 'api/deposit', [
            'user_id' => $user->id,
            'amount' => 10,
            'type'=> Transaction::TYPE_DEPOSIT,
        ]);
        $transaction = $response->json();

        $this->assertEquals($user->country, $transaction['country']);

        //Change user country
        $user->country = 'US';
        $user->save();

        $t = Transaction::where('id',$transaction['id'])->first();
        $this->assertEquals($t->country, $transaction['country']);
    }

    /**
     * Tests POST /api/deposit method
     */
    public function test_deposit(){
        $this->withoutMiddleware();

        $this->clearTestUser();
        $user = $this->addTestUser();

        $balance = $user->balance;

        //Deposit first time
        $firstDeposit = 14.23;
        $response = $this->json('POST', 'api/deposit', [
            'user_id' => $user->id,
            'amount' => $firstDeposit,
        ]);

        $user = $this->getTestUser();
        $this->assertEquals($balance + $firstDeposit, $user->balance);


        //Deposit second time
        $secondDeposit = 51.56;
        $response = $this->json('POST', 'api/deposit', [
            'user_id' => $user->id,
            'amount' => $secondDeposit,
        ]);

        $user = $this->getTestUser();
        $this->assertEquals($balance + $firstDeposit + $secondDeposit, $user->balance);

        //Third deposit
        $thirdDeposit = 23.50;
        $response = $this->json('POST', 'api/deposit', [
            'user_id' => $user->id,
            'amount' => $thirdDeposit,
        ]);

        $user = $this->getTestUser();
        $expectedFinalBalance = $balance + $firstDeposit + $secondDeposit + $thirdDeposit;

        //Add Bonus
        $expectedFinalBalance += $thirdDeposit * $user->percent_bonus / 100;

        $this->assertEquals($expectedFinalBalance, $user->balance);

    }

    /**
     * Tests that /api/deposit method has minimum allowed deposit limitation
     */
    public function test_minimum_allowed_deposit(){
        $this->withoutMiddleware();

        $this->clearTestUser();
        $user = $this->addTestUser();

        $balance = $user->balance;

        //Allowed deposit
        $firstDeposit = 5;
        $response = $this->json('POST', 'api/deposit', [
            'user_id' => $user->id,
            'amount' => $firstDeposit,
        ]);

        $user = $this->getTestUser();
        $this->assertEquals($balance + $firstDeposit, $user->balance);

        //Denied deposit
        $secondDeposit = 4;
        $response = $this->json('POST', 'api/deposit', [
            'user_id' => $user->id,
            'amount' => $secondDeposit,
        ]);

        $data = $response->json();
        $user = $this->getTestUser();

        $this->assertTrue(!empty($data['exception']));
        $this->assertEquals($balance + $firstDeposit, $user->balance);
    }

    /**
     * Tests GET /api/transactions method
     */
    public function test_get_transactions(){
        $this->withoutMiddleware();

        $this->clearTestUser();
        $user = $this->addTestUser();

        $user->transactions()->save(Transaction::create([
            'type' => Transaction::TYPE_DEPOSIT,
            'country' => 'BA',
            'date' => Carbon::now(),
            'amount' => 230,
            'user_id' => $user->id
        ]));

        $response = $this->json('GET', 'api/transactions');
        $transactions = $response->json();

        $this->assertTrue(sizeof($transactions) > 0);

        $t = $transactions[0];
        $this->assertTrue(!empty($t['country']));
        $this->assertTrue(!empty($t['date']));
        $this->assertTrue(!empty($t['amount']));
        $this->assertTrue(!empty($t['type']));
        $this->assertTrue(!empty($t['user']['name']));
        $this->assertTrue(!empty($t['user']['id']));
    }

    /**
     * Tests /api/transactions method filtered by user
     */
    public function test_get_user_transactions(){
        $this->withoutMiddleware();

        $this->clearTestUser();
        $user = $this->addTestUser();

        $t1 = Transaction::create([
            'type' => Transaction::TYPE_DEPOSIT,
            'country' => 'BA',
            'date' => Carbon::now(),
            'amount' => 230,
            'user_id' => $user->id
        ]);
        $user->transactions()->save($t1);

        $t2 = Transaction::create([
            'type' => Transaction::TYPE_WITHDRAWAL,
            'country' => 'BA',
            'date' => Carbon::yesterday(),
            'amount' => 130,
            'user_id' => $user->id
        ]);
        $user->transactions()->save($t2);

        $response = $this->json('GET', 'api/transactions', [
            'user_id' => $user->id
        ]);

        $trans= $response->json();

        $this->assertEquals(2, sizeof($trans));
        $this->assertEquals($t1->id, $trans[0]['id']);
        $this->assertEquals($t1->type, $trans[0]['type']);
        $this->assertEquals($t2->id, $trans[1]['id']);
        $this->assertEquals($t2->type, $trans[1]['type']);
    }

    /**
     * Tests /api/transactions method filtered by date
     */
    public function test_get_transactions_filtered_by_date(){
        $this->withoutMiddleware();

        $this->clearTestUser();
        Transaction::where('date', Carbon::now()->format('Y-m-d'))->delete();

        $user = $this->addTestUser();

        $t1 = Transaction::create([
            'type' => Transaction::TYPE_DEPOSIT,
            'country' => 'BA',
            'date' => Carbon::now(),
            'amount' => 230,
            'user_id' => $user->id
        ]);
        $user->transactions()->save($t1);

        $t2 = Transaction::create([
            'type' => Transaction::TYPE_WITHDRAWAL,
            'country' => 'BA',
            'date' => Carbon::yesterday(),
            'amount' => 130,
            'user_id' => $user->id
        ]);
        $user->transactions()->save($t2);

        $response = $this->json('GET', 'api/transactions', [
            'from' => Carbon::today()->format('Y-m-d'),
            'to' => Carbon::today()->format('Y-m-d'),
        ]);

        $trans= $response->json();

        $this->assertEquals(1, sizeof($trans));
        $this->assertEquals($t1->id, $trans[0]['id']);
        $this->assertEquals($t1->type, $trans[0]['type']);
    }

    /**
     * Tests /api/transaction/{id} method
     */
    public function test_get_transaction(){
        $this->withoutMiddleware();

        $this->clearTestUser();
        $user = $this->addTestUser();

        $t1 = Transaction::create([
            'type' => Transaction::TYPE_DEPOSIT,
            'country' => 'BA',
            'date' => Carbon::now(),
            'amount' => 230,
            'user_id' => $user->id
        ]);
        $user->transactions()->save($t1);

        $response = $this->json('GET', 'api/transaction/'. $t1->id);
        $data = $response->json();
        $this->assertEquals($t1->id, $data['id']);
        $this->assertEquals($t1->type, $data['type']);
        $this->assertEquals($t1->country, $data['country']);
        $this->assertEquals($t1->amount, $data['amount']);
        $this->assertEquals($t1->user_id, $data['user_id']);
    }

    /**
     * Tests /api/withdrawal method
     */
    public function test_withdrawal(){
        $this->withoutMiddleware();

        $this->clearTestUser();
        $user = $this->addTestUser();

        $balance = $user->balance;

        $withdrawalAmount = 120;
        $response = $this->json('POST', 'api/withdrawal', [
            'user_id' => $user->id,
            'amount' => $withdrawalAmount,
        ]);

        $user = $this->getTestUser();
        $this->assertEquals($balance - $withdrawalAmount, $user->balance);
    }

    /**
     * Tests that /api/withdrawal method has minimum amount limitation
     */
    public function test_minimum_withdrawal_amount(){
        $this->withoutMiddleware();

        $this->clearTestUser();
        $user = $this->addTestUser();

        $balance = $user->balance;

        //Allowed withdrawal
        $firstWithdrawal = 100;
        $response = $this->json('POST', 'api/withdrawal', [
            'user_id' => $user->id,
            'amount' => $firstWithdrawal,
        ]);

        $user = $this->getTestUser();
        $this->assertEquals($balance - $firstWithdrawal, $user->balance);

        //Denied withdrawal
        $secondWithdrawal = 99;
        $response = $this->json('POST', 'api/withdrawal', [
            'user_id' => $user->id,
            'amount' => $secondWithdrawal,
        ]);

        $data = $response->json();
        $user = $this->getTestUser();

        $this->assertTrue(!empty($data['exception']));
        $this->assertEquals($balance - $firstWithdrawal, $user->balance);
    }

    /**
     * Tests /api/reports method
     */
    public function test_report_returns_aggregated_data(){
        $this->withoutMiddleware();

        $this->clearReportTestData();
        $this->addReportTestData();

        $response = $this->json('POST', 'api/reports', [
            'from' => Carbon::yesterday()->format('Y-m-d'),
            'to' => Carbon::today()->format('Y-m-d'),
        ]);

        $response->assertJson([
            [
                "total_deposits" => 448,
                "total_withdrawals" => 0,
                "deposits" => 3,
                "withdrawals" => 0,
                "users" => 2,
                "date" => Carbon::now()->format('Y-m-d'),
                "country" => 'BA'
            ],
            [
                "total_deposits" => 0,
                "total_withdrawals" => 200,
                "deposits" => 0,
                "withdrawals" => 1,
                "users" => 1,
                "date" => Carbon::now()->format('Y-m-d'),
                "country" => 'US'
            ],
            [
                "total_deposits" => 121,
                "total_withdrawals" => 421,
                "deposits" => 1,
                "withdrawals" => 1,
                "users" => 1,
                "date" => Carbon::yesterday()->format('Y-m-d'),
                "country" => 'BA'
            ],
            [
                "total_deposits" => 0,
                "total_withdrawals" => 681,
                "deposits" => 0,
                "withdrawals" => 2,
                "users" => 2,
                "date" => Carbon::yesterday()->format('Y-m-d'),
                "country" => "US"
            ],
        ]);
    }

    /**
     * Tests /api/reports filter by dates (from, to)
     */
    public function test_report_filters_today(){
        $this->withoutMiddleware();

        $this->clearReportTestData();
        $this->addReportTestData();

        $response = $this->json('POST', 'api/reports', [
            'from' => Carbon::today()->format('Y-m-d'),
            'to' => Carbon::today()->format('Y-m-d'),
        ]);

        $response->assertJson([
            [
                "total_deposits" => 448,
                "total_withdrawals" => 0,
                "deposits" => 3,
                "withdrawals" => 0,
                "users" => 2,
                "date" => Carbon::now()->format('Y-m-d'),
                "country" => 'BA'
            ],
            [
                "total_deposits" => 0,
                "total_withdrawals" => 200,
                "deposits" => 0,
                "withdrawals" => 1,
                "users" => 1,
                "date" => Carbon::now()->format('Y-m-d'),
                "country" => 'US'
            ]
        ]);
    }

    //PRIVATE METHODS - HELPERS

    /**
     * Clears test user
     * @param string $userEmail
     */
    private function clearTestUser($userEmail = 'test@test.com'){
        $userRecord = $this->getTestUser($userEmail);
        if($userRecord){
            $userRecord->delete();
        }
    }

    /**
     * Returns test user
     * @param string $userEmail
     * @return mixed
     */
    private function getTestUser($userEmail = 'test@test.com'){
        return User::where('email', $userEmail)->first();
    }

    /**
     * Add a new test user
     * @param string $userEmail
     * @return mixed
     */
    private function addTestUser($userEmail = 'test@test.com'){
        return User::create([
            'name'=> 'Test',
            'email' => $userEmail,
            'country' => 'BA',
            'password' => 'password',
            'password2' => 'password',
            'balance' => '1000.00',
            'percent_bonus' => '10'
        ]);
    }

    /**
     * Add test data for testing reports
     */
    private function addReportTestData(){

        $user1 = $this->addTestUser('user1@example.com');
        $user2 = $this->addTestUser('user2@example.com');

        $user1->transactions()->save(Transaction::create([
            'type' => Transaction::TYPE_DEPOSIT,
            'country' => 'BA',
            'date' => Carbon::now(),
            'amount' => 230,
            'user_id' => $user1->id
        ]));

        $user1->transactions()->save(Transaction::create([
            'type' => Transaction::TYPE_DEPOSIT,
            'country' => 'BA',
            'date' => Carbon::now(),
            'amount' => 120,
            'user_id' => $user1->id
        ]));

        $user1->transactions()->save(Transaction::create([
            'type' => Transaction::TYPE_WITHDRAWAL,
            'country' => 'US',
            'date' => Carbon::now(),
            'amount' => 200,
            'user_id' => $user1->id
        ]));

        $user1->transactions()->save(Transaction::create([
            'type' => Transaction::TYPE_WITHDRAWAL,
            'country' => 'US',
            'date' => Carbon::yesterday(),
            'amount' => 160,
            'user_id' => $user1->id
        ]));

        $user2->transactions()->save(Transaction::create([
            'type' => Transaction::TYPE_DEPOSIT,
            'country' => 'BA',
            'date' => Carbon::now(),
            'amount' => 98,
            'user_id' => $user2->id
        ]));

        $user2->transactions()->save(Transaction::create([
            'type' => Transaction::TYPE_DEPOSIT,
            'country' => 'BA',
            'date' => Carbon::yesterday(),
            'amount' => 121,
            'user_id' => $user2->id
        ]));

        $user2->transactions()->save(Transaction::create([
            'type' => Transaction::TYPE_WITHDRAWAL,
            'country' => 'BA',
            'date' => Carbon::yesterday(),
            'amount' => 421,
            'user_id' => $user2->id
        ]));
        $user2->transactions()->save(Transaction::create([
            'type' => Transaction::TYPE_WITHDRAWAL,
            'country' => 'US',
            'date' => Carbon::yesterday(),
            'amount' => 521,
            'user_id' => $user2->id
        ]));
    }

    /**
     * Clear test data for reports
     */
    private function clearReportTestData(){
        $this->clearTestUser('user1@example.com');
        $this->clearTestUser('user2@example.com');

        Transaction::where('date', Carbon::yesterday()->format('Y-m-d'))->delete();
        Transaction::where('date', Carbon::now()->format('Y-m-d'))->delete();
    }
}
