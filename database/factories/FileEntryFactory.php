<?php

use Faker\Generator as Faker;

$factory->define(\App\FileEntry::class, function (Faker $faker) {
    $word = $faker->word;
    return [
        'type' => $faker->randomElement(['dir', 'file']),
        'path' => '/tmp/' . $word,
        'timestamp' => $faker->unixTime(),
        'size' => $faker->randomNumber(),
        'dirname' => '/tmp',
        'basename' => $word,
        'extension' => '',
        'filename' => $word,
    ];
});
