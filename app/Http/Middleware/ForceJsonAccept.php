<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Forces every API request to negotiate JSON.
 *
 * Without this, framework error handlers (validation, auth, 404, 500) honor
 * the client's Accept header and may render HTML for clients that did not
 * explicitly request JSON.
 */
final class ForceJsonAccept
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $request->headers->set('Accept', 'application/json');

        return $next($request);
    }
}
