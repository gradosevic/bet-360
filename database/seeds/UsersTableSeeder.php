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
        factory(App\Models\User::class, 50)->create()->each(function ($u) {
            $u->transactions()->save(factory(App\Models\Transaction::class)->make());
            $u->transactions()->save(factory(App\Models\Transaction::class)->make());
            $u->transactions()->save(factory(App\Models\Transaction::class)->make());
            $u->transactions()->save(factory(App\Models\Transaction::class)->make());
            $u->transactions()->save(factory(App\Models\Transaction::class)->make());
        });
        $demoUser = factory(App\Models\User::class)->create();
        $demoUser->name = 'Demo User';
        $demoUser->email = 'demo@demo.com';
        $demoUser->password = Hash::make('demo');
        $demoUser->save();
    }
}
