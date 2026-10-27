<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplySettings
{
    /**
     * Apply database-backed settings to the current HTTP request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $settings = Setting::query()->first();
        } catch (\Throwable) {
            return $next($request);
        }

        if ($settings) {
            config([
                'app.name' => $settings->app_name,
                'app.timezone' => $settings->timezone,
                'mail.from.address' => $settings->mail_from_address ?: config('mail.from.address'),
                'mail.from.name' => $settings->mail_from_name ?: config('mail.from.name'),
            ]);

            date_default_timezone_set($settings->timezone);
        }

        return $next($request);
    }
}
