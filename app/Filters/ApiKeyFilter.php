<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Filtro de autenticación por API Key.
 * Acepta el header X-API-Key para acceso programático (OpenClaw, webhooks, etc.)
 * Funciona como alternativa al filtro auth basado en sesión.
 */
class ApiKeyFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $apiKey = $request->getHeaderLine('X-API-Key');

        if (empty($apiKey)) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON(['success' => false, 'error' => 'API Key requerida (header X-API-Key)']);
        }

        $validKey = env('APP_API_KEY', '');

        if (empty($validKey) || !hash_equals($validKey, $apiKey)) {
            return service('response')
                ->setStatusCode(403)
                ->setJSON(['success' => false, 'error' => 'API Key invalida']);
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
