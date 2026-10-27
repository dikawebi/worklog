<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ProjectShowController extends Controller
{
    public function __invoke(Project $project): View
    {
        Gate::authorize('view', $project);

        return view('projects.show', compact('project'));
    }
}
