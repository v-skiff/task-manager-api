<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Task;
use Faker\Generator as Faker;

$factory->define(Task::class, function (Faker $faker) {

    $created = $faker->dateTimeBetween('-30 days', '-1 day');
    $statuses = ["View", "In Progress", "Done"];

    return [
        'title' => $faker->realText(rand(10, 40)),
        'description' => $faker->realText(rand(15, 65)),
        'status' => $statuses[rand(0, 2)],
        'user_id' => rand(1, 25),
        'created_at' => $created,
        'updated_at' => $created,

    ];
});
