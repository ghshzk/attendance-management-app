<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class MailVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $session_data = session()->get('unauthenticated_user') ?? null;

        if($session_data){
            return redirect()->route('verification.notice');
        }

        return $next($request);
    }
}
