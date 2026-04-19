<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('campuslearn:orders:auto-close')->everyFiveMinutes();
Schedule::command('campuslearn:billing:recurring')->dailyAt('02:00');
Schedule::command('campuslearn:billing:penalty')->dailyAt('03:00');
Schedule::command('campuslearn:health:evaluate-circuit')->everyMinute();
Schedule::command('campuslearn:backups:record-metadata')->dailyAt('04:00');
