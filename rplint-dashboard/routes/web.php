<?php

use App\Imports\StudentsImport;
use Illuminate\Support\Facades\Route;
use Maatwebsite\Excel\Facades\Excel;

Route::get('/', function () {
    return redirect()->route('filament.admin.pages.dashboard');
});
