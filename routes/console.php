<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('lab:about', function () {
    $this->info('Dental Laboratory Management System - University of Aleppo');
})->purpose('Display project information');
