<?php

use Faker\Generator as Faker;

$factory->define(\App\TorrentEntry::class, function (Faker $faker) {
    $filename = $faker->word;
    return [
        'torrent_id' => $faker->unique()->randomNumber(),
        'name' => $filename,
        'size' => $faker->randomNumber(),
        'percent' => 100,
        'path' => Storage::disk('torrents')->getDriver()->getAdapter()->applyPathPrefix($filename),
        'eta' => $this->faker->randomNumber(),
        'copy_failed' => false,
    ];
});
