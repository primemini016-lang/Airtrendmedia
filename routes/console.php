<?php

use Illuminate\Support\Facades\Artisan;

/*
 | Console routes intentionally kept lightweight for shared hosting.
 | Add scheduled/closure commands here without requiring a Node build step.
 */

Artisan::command('airtrendmedia:health', function () {
    $this->info('Airtrendmedia application is healthy.');
})->purpose('Verify that the application console is bootable.');
