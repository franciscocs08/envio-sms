<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Str;

class OpsSmsService
{
    private const BASE_URL = 'https://apisms.opsmovil.com';

    private function modoSimulacion(): bool
    {
        return config('app.ops_simulacion', false);
    }

    private function client(string $token): Client
    {
        return new Client([
            'base_uri' => self::BASE_URL,
            'timeout'  => 15,
            'verify'   => false,
            'headers'  => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
            ],
        ]);
    }

    /**
     * Enviar un SMS individual.
     * Retorna ['success' => bool, 'ops_sms_id' => string|null, 'error' => string|null]
     */
    public function send(string $to, string $message, string $token, string $from): array
    {
        if ($this->modoSimulacion()) {
            // Simula fallo en ~10% de los envíos para probar el flujo de errores
            if (rand(1, 10) === 1) {
                return [
                    'success'    => false,
                    'ops_sms_id' => null,
                    'error'      => '[SIMULACIÓN] Error de envío simulado',
                ];
            }

            return [
                'success'    => true,
                'ops_sms_id' => 'SIM-' . strtoupper(Str::random(8)) . '-' . $to,
                'error'      => null,
            ];
        }

        try {
            $response = $this->client($token)->post('/send_sms', [
                'json' => [
                    'from'    => $from,
                    'to'      => $to,
                    'message' => $message,
                ],
            ]);

            $body = json_decode($response->getBody()->getContents(), true);

            return [
                'success'    => true,
                'ops_sms_id' => $body['id'] ?? null,
                'error'      => null,
            ];
        } catch (RequestException $e) {
            return [
                'success'    => false,
                'ops_sms_id' => null,
                'error'      => $this->extractError($e),
            ];
        }
    }

    /**
     * Resumen de SMS enviados por rango de fechas.
     */
    public function resumen(string $fechaInicio, string $fechaFin, string $token): array
    {
        if ($this->modoSimulacion()) {
            return [
                'success' => true,
                'data'    => [
                    [
                        'destino' => '987654321',
                        'estado'  => 'Entregado',
                        'fecha'   => now()->toRfc2822String(),
                        'hora'    => now()->format('H:i'),
                        'mensaje' => '[SIMULACIÓN] Mensaje de prueba',
                        'smsId'   => 'SIM-' . strtoupper(Str::random(8)),
                    ],
                ],
                'error' => null,
            ];
        }

        try {
            $response = $this->client($token)->post('/resumenSmsEnviados', [
                'json' => [
                    'fechaInicio' => $fechaInicio,
                    'fechaFin'    => $fechaFin,
                ],
            ]);

            $body = json_decode($response->getBody()->getContents(), true);

            if (isset($body['estado']) && $body['estado'] === 'success') {
                return ['success' => true, 'data' => $body['mensaje'] ?? [], 'error' => null];
            }

            return ['success' => false, 'data' => [], 'error' => $body['mensaje'] ?? 'Error desconocido'];
        } catch (RequestException $e) {
            return ['success' => false, 'data' => [], 'error' => $this->extractError($e)];
        }
    }

    /**
     * Buscar estados de SMS por IDs (batch).
     */
    public function buscarPorIds(array $ids, string $token): array
    {
        if ($this->modoSimulacion()) {
            $data = array_map(function ($id) {
                return [
                    'destino' => '987654321',
                    'estado'  => 'Entregado',
                    'fecha'   => now()->toRfc2822String(),
                    'hora'    => now()->format('H:i'),
                    'mensaje' => '[SIMULACIÓN]',
                    'smsId'   => $id,
                ];
            }, $ids);

            return ['success' => true, 'data' => $data, 'error' => null];
        }

        try {
            $response = $this->client($token)->post('/buscarSmsID', [
                'json' => ['idSMS' => $ids],
            ]);

            $body = json_decode($response->getBody()->getContents(), true);

            if (isset($body['estado']) && $body['estado'] === 'success') {
                return ['success' => true, 'data' => $body['mensaje'] ?? [], 'error' => null];
            }

            return ['success' => false, 'data' => [], 'error' => $body['mensaje'] ?? 'Error desconocido'];
        } catch (RequestException $e) {
            return ['success' => false, 'data' => [], 'error' => $this->extractError($e)];
        }
    }

    /**
     * Mensajes recibidos en casilla (últimas 24h).
     */
    public function casilla(string $token): array
    {
        if ($this->modoSimulacion()) {
            return [
                'success' => true,
                'data'    => [
                    [
                        'fecha'  => now()->format('Y-m-d H:i:s'),
                        'origen' => '56974000000',
                        'texto'  => '[SIMULACIÓN] Respuesta de prueba',
                    ],
                ],
                'error' => null,
            ];
        }

        try {
            $response = $this->client($token)->get('/casilla');
            $body = json_decode($response->getBody()->getContents(), true);

            if (isset($body['estado']) && $body['estado'] === 'correcto') {
                return ['success' => true, 'data' => $body['sms'] ?? [], 'error' => null];
            }

            return ['success' => false, 'data' => [], 'error' => $body['mensaje'] ?? 'Error desconocido'];
        } catch (RequestException $e) {
            return ['success' => false, 'data' => [], 'error' => $this->extractError($e)];
        }
    }

    private function extractError(RequestException $e): string
    {
        if ($e->hasResponse()) {
            $body = json_decode($e->getResponse()->getBody()->getContents(), true);
            return $body['mensaje'] ?? $body['message'] ?? $e->getMessage();
        }
        return $e->getMessage();
    }
}
