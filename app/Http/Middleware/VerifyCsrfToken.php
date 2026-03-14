<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        'get_employee_data',
        'get_employee_schedule',
        'get_personnel_action_details',
        'delete_personnel_action',
        'delete_incident_report',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {
        // Log CSRF token verification attempts
        if ($request->method() !== 'GET' && $request->method() !== 'HEAD' && $request->method() !== 'OPTIONS') {
            \Log::debug('CSRF Token Verification', [
                'method' => $request->method(),
                'path' => $request->path(),
                'token_in_header' => $request->header('X-CSRF-TOKEN') ? 'present' : 'missing',
                'token_in_body' => $request->input('_token') ? 'present' : 'missing',
            ]);
        }

        return parent::handle($request, $next);
    }
}
