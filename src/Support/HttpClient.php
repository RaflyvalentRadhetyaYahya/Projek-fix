<?php

declare(strict_types=1);

namespace App\Support;

use RuntimeException;

final class HttpClient
{
    /** @return array{0:int,1:string} */
    public static function postForm(string $url, array $data, int $timeoutSeconds = 20): array
    {
        $body = http_build_query($data);

        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $body,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
                CURLOPT_TIMEOUT => $timeoutSeconds,
            ]);
            $resp = curl_exec($ch);
            $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $err = curl_error($ch);
            curl_close($ch);
            if ($resp === false) {
                throw new RuntimeException('HTTP error: ' . $err);
            }
            return [$code, $resp];
        }

        $opts = [
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
                'content' => $body,
                'timeout' => $timeoutSeconds,
            ],
        ];
        $ctx = stream_context_create($opts);
        $resp = file_get_contents($url, false, $ctx);

        $code = 0;
        if (!empty($http_response_header[0]) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $m)) {
            $code = (int) $m[1];
        }

        return [$code, $resp === false ? '' : $resp];
    }

    /** @return array<string,mixed> */
    public static function getJson(string $url, string $bearerToken, int $timeoutSeconds = 20): array
    {
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $bearerToken],
                CURLOPT_TIMEOUT => $timeoutSeconds,
            ]);
            $resp = curl_exec($ch);
            $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $err = curl_error($ch);
            curl_close($ch);
            if ($resp === false) {
                throw new RuntimeException('HTTP error: ' . $err);
            }
            $json = json_decode($resp, true);
            if (!is_array($json)) {
                throw new RuntimeException('Invalid JSON response');
            }
            $json['_http_code'] = $code;
            return $json;
        }

        $opts = [
            'http' => [
                'method' => 'GET',
                'header' => 'Authorization: Bearer ' . $bearerToken . "\r\n",
                'timeout' => $timeoutSeconds,
            ],
        ];
        $ctx = stream_context_create($opts);
        $resp = file_get_contents($url, false, $ctx);

        $code = 0;
        if (!empty($http_response_header[0]) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $m)) {
            $code = (int) $m[1];
        }

        $json = json_decode($resp ?: '', true);
        if (!is_array($json)) {
            throw new RuntimeException('Invalid JSON response');
        }
        $json['_http_code'] = $code;
        return $json;
    }
}
