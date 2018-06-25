<?php

use Faker\Generator as Faker;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/
function rand_balance($min, $max, $decimals = 0) {
    $scale = pow(10, $decimals);
    return mt_rand($min * $scale, $max * $scale) / $scale;
}

$factory->define(App\User::class, function (Faker $faker) {

    return [
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'password' => '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', // secret
        'remember_token' => str_random(10),
        'percent_bonus' => rand(5, 20),
        'country' => 'us',
        'balance' => rand_balance(10, 100, 2)
    ];
});


$factory->define(App\Transaction::class, function (Faker $faker) {
    return [
        'type' => array_rand([\App\Transaction::TYPE_DEPOSIT, \App\Transaction::TYPE_WITHDRAWAL]),
        'country' => 'us',
        'amount' => rand_balance(10, 100, 2)
    ];
});