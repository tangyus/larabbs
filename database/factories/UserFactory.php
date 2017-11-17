<?php

use Faker\Generator as Faker;
use Carbon\Carbon;

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

$factory->define(App\Models\User::class, function (Faker $faker) {
    static $password;
<<<<<<< HEAD
	$now = Carbon::now()->toDateTimeString();
=======
    $now = Carbon::now()->toDateTimeString();
>>>>>>> 9224be3d6ef0cbc310ebe6f1e13dc43844f74dae

    return [
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'password' => $password ?: $password = bcrypt('secret'),
        'remember_token' => str_random(10),
<<<<<<< HEAD
		'introduction' => $faker->sentence(),
		'created_at' => $now,
		'updated_at' => $now,
=======
        'introduction' => $faker->sentence(),
        'created_at' => $now,
        'updated_at' => $now,
>>>>>>> 9224be3d6ef0cbc310ebe6f1e13dc43844f74dae
    ];
});
