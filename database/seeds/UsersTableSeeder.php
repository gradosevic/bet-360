<?php

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(App\User::class, 50)->create()->each(function ($u) {
            $u->transactions()->save(factory(App\Transaction::class)->make());
            $u->transactions()->save(factory(App\Transaction::class)->make());
            $u->transactions()->save(factory(App\Transaction::class)->make());
            $u->transactions()->save(factory(App\Transaction::class)->make());
            $u->transactions()->save(factory(App\Transaction::class)->make());
        });
        $demoUser = factory(App\User::class)->create();
        $demoUser->name = 'Demo User';
        $demoUser->email = 'demo@demo.com';
        $demoUser->password = Hash::make('demo');
        $demoUser->save();
    }
}
