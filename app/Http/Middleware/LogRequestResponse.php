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
        if (!auth()->check()) {
            return $next($request);
        }
        $data = $request->all();

        // If logging an authentication request, mask the password in the log
        if ($request->isMethod('post') && (isset($data['pin']) || isset($data['password']))) {
            if (isset($data['password'])) {
                $data['password'] = 'REDACTED';
            }
            if (isset($data['pin'])) {
                $data['pin'] = 'REDACTED';
            }
        }

        // Array of allowed IPs
        $allowed_ips = [
            '20.203.67.34',
            '20.203.67.147',
            '20.203.71.158',
            '20.203.71.174',
            '20.233.81.131',
            '20.233.81.143',
            '20.233.82.7',
            '20.233.82.100',
            '20.233.82.192',
            '20.233.83.5',
            '20.233.83.47',
            '20.233.84.193',
            '20.233.84.237',
            '20.233.85.15',
            '20.233.85.25',
            '20.233.85.60',
            '20.233.85.175',
            '20.233.85.191',
            '20.233.85.226',
            '20.233.85.230',
            '20.233.85.234',
            '20.233.85.242',
            '20.233.85.247',
            '20.233.86.16',
            '20.74.192.1'
        ];

        $client_ip = $request->ip();

        // Check if the client's IP is in the allowed IPs array
        if (in_array($client_ip, $allowed_ips)) {
            $origin = "Yativo dashboard";
        } else {
            $origin = "Yativo API";
        }

        // Prepare the log entry
        $logEntry = [
            'user_id' => auth()->id(),
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'request_headers' => $request->headers->all(),
            'request_body' => $data,
            'request_ip_address' => $client_ip,
        ];

        // Continue processing the request
        $response = $next($request);
        $eResponse = $response->getContent();
        if (!is_array($eResponse)) {
            $eResponse = json_decode($eResponse, true);
            if(isset($eResponse['data']) && isset($eResponse['data']['app_secret'])) {
                $eResponse['data']['app_secret'] = "REDACTED";
            }
        }

        // Add response data to the log entry
        $logEntry['response_status'] = $response->status();
        $logEntry['response_headers'] = $response->headers->all();
        $logEntry['response_body'] = $eResponse;
        $logEntry['request_origin'] = $origin;

        // Save log to database
        \App\Models\ApiLog::create($logEntry);

        return $response;
    }
}
