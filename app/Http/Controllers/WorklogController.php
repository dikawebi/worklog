<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WorklogController extends Controller
{
    public function __invoke(Request $request, ?string $date = null): View
    {
        $selectedDate = $date
            ? CarbonImmutable::createFromFormat('!Y-m-d', $date)
            : today()->toImmutable();

        abort_if($selectedDate === false, 404);

        return view('worklogs.index', [
            'selectedDate' => $selectedDate,
        ]);
    }
}
