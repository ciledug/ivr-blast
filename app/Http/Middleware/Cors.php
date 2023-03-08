<?php

namespace App\Http\Middleware;

use Closure;

class Cors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // $allowedOrigins = ['http://localhost', 'example1.com', 'example2.com'];
        // $origin = $_SERVER['HTTP_ORIGIN'];
        
        // if(isset($_SERVER['HTTP_ORIGIN']) && in_array($origin, $allowedOrigins)){
            
            $response = $next($request);

            $response->headers->set('Access-Control-Allow-Credentials', 'true');
            $response->headers->set('Access-Control-Allow-Origin', '*');
            $response->headers->set('Access-Control-Allow-Methods', 'POST, GET');
            $response->headers->set('Access-Control-Allow-Headers', 'Origin, Accept, Content-Type, X-Requested-With, Authorization');

            return $response;    
       // }
    }
}
