<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class HttpRequestService
{
    public const BASE_GPSWOX_URL = 'http://154.38.188.87/api/';
    public const API_GPSWOX_TOKEN = '$2y$10$tdPnvRrLH9YWm5sg7dCKre8DcredKhXqOx8CEmug33F6i50ERkgWG';

    public static function makeRequest(string $method, string $endpoint, array $data = [])
    {
        $response = Http::{$method}($endpoint, $data);

        if ($response->successful()) {
            return $response->json();
        }

        throw new \Exception('Error en la solicitud HTTP: ' . $response->body());
    }
}
