<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;

Route::view('/{any}', 'app')->where('any', '.*');

