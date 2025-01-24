<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogRequestResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // if (!auth()->check()) {
        //     return $next($request);
        // }
        // $data = $request->all();

        // // If logging an authentication request, mask the password in the log
        // if ($request->isMethod('post') && (isset($data['pin']) || isset($data['password']))) {
        //     if (isset($data['password'])) {
        //         $data['password'] = 'REDACTED';
        //     }
        //     if (isset($data['pin'])) {
        //         $data['pin'] = 'REDACTED';
        //     }
        // }

        // $user = auth()->user();
        // // Prepare the log entry
        // $logEntry = [
        //     'user_id' => $user->id,
        //     'method' => $request->method(),
        //     'url' => $request->fullUrl(),
        //     'request_headers' => $request->headers->all(),
        //     'request_body' => $data,
        //     'request_ip_address' => $request->ip(),
        //     '_account_type' => $user->current_role
        // ];

        // // Continue processing the request
        $response = $next($request);
        // $eResponse = $response->getContent();
        // if (!is_array($eResponse)) {
        //     $eResponse = json_decode($eResponse, true);
        //     if(isset($eResponse['data']) && isset($eResponse['data']['app_secret'])) {
        //         $eResponse['data']['app_secret'] = "REDACTED";
        //     }
        // }

        // // Add response data to the log entry
        // $logEntry['response_status'] = $response->status();
        // $logEntry['response_headers'] = $response->headers->all();
        // $logEntry['response_body'] = $eResponse;
        // $logEntry['request_origin'] = "call2fix";

        // // Save log to database
        // \App\Models\ApiLog::create($logEntry);

        return $response;
    }
}
