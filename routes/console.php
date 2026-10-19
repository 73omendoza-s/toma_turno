<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('turnos:reiniciar')->dailyAt('00:00');
