<?php

use App\Http\Controllers\Api\SpaceSlotsController;
use Illuminate\Support\Facades\Route;

Route::get('/spaces/{space:slug}/slots', SpaceSlotsController::class);
