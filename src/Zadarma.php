<?php

namespace Otsaplin\ZadarmaApi;

use GuzzleHttp\Client;

class Zadarma
{

    const URL = 'https://api.zadarma.com';

    private $key;
    private $secret;
    private $format = 'json';

    public function __construct($key = '', $secret = '')
    {
        $this->key = $key;
        $this->secret = $secret;
    }

    public function doMethod($method, $params = [], $requestType = 'get')
    {
        if (!is_array($params)) {
            throw new Exception('Query params must be an array.');
        }

        $type = strtoupper($requestType);
        if (!in_array($type, array('GET', 'POST', 'PUT', 'DELETE'))) {
            $type = 'GET';
        }

        if ($this->format != 'json')
            $params['format'] = $this->format;

        $auth = $this->buildHeader($method, $params);

        $client = new Client([
            'base_uri' => static::URL
        ]);

        try {
            $response = $client->request($type, $method, [
                'headers' => [
                    'Authorization' => $auth
                ],
                'query' => $params
            ]);
        } catch (\Exception $e) {
            return false;
        }

        $result = $response->getBody()->getContents();

        if ($this->format == 'json')
            $result = json_decode ($result, true);

        return $result;
    }

    private function buildHeader($method, $params)
    {
        ksort($params);
        $paramsString = http_build_query($params, null, '&', PHP_QUERY_RFC1738);
        $signature = base64_encode(hash_hmac('sha1', $method . $paramsString . md5($paramsString), $this->secret));
        return array($this->key . ':' . $signature);
    }

    public function getFormat()
    {
        return $this->format;
    }

    public function setFormat($format)
    {
        $this->format = $format;
    }

}
