<?php

namespace Delfosti\Massive\Services;

date_default_timezone_set('America/Lima');

class ApiService
{
    private $domain;
    private $token;

    public function __construct(string $domain = null, $token = false)
    {
        $this->domain = $domain;
        $this->token = $token;
    }

    public function get($prefix = 'api', $entity_type, $entity_bundle, $action = null, $param_headers = null, $filters = [])
    {
        // Headers
        $headers = [];
        if (!empty($param_headers)) {
            foreach ($param_headers as $key => $item) {
                if (is_array($item)) {
                    $headers[] = $key . ': ' . current($item);
                } else {
                    $headers[] = $key . ': ' . $item;
                }
            }
        } else {
            // Set token in headers
            if ($this->token) {
                $request_headers = apache_request_headers();
                if (!empty($request_headers['Authorization'])) {
                    $headers[] = "Authorization: {$request_headers['Authorization']}";
                }
            }
            if (!empty($request_headers['Structure'])) {
                $headers[] = "Structure: {$request_headers['Structure']}";
            }
            $headers[] = "Content-Type: application/json";
        }

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $this->getUrl($prefix, $entity_type, $entity_bundle, $action, $filters),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_HTTPHEADER => $headers,
        ]);

        $response = curl_exec($curl);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        return [
            'code' => $code,
            'data' => json_decode($response),
        ];
    }

    public function getUrl($prefix, $entity_type, $entity_bundle, $action = null, $filters = [])
    {
        $output = $prefix ? "{$this->domain}/{$prefix}" : "{$this->domain}";
        $output .= "/{$entity_type}";
        if ($entity_bundle) {
            $output .= "/{$entity_bundle}";
        }
        if ($action) {
            $output .= "/{$action}";
        }
        $output = str_replace('_', '-', $output);
        if (!empty($filters)) {
            return $output .= '?' . http_build_query($filters);
        }

        return $output;
    }
}
