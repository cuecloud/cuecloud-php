<?php
namespace CueCloud\API;

/**
 * HTTP functions via curl
 * @package CueCloud\API
 */
class Http
{

    /**
     * API End Point
     * @var string
     */
    private static $endPoint;

    /**
     * Set the end point to be used for the requests
     *
     * @param string $endPoint
     *
     * @return null
     */
    public static function setEndPoint($endPoint)
    {
        self::$endPoint = $endPoint;
    }

    /**
     * Perform a cURL request and decode the JSON response.
     * Can throw and exception if the request can't be successfully executed.
     *
     * @param Client $client   The client making the request, so we can extract
     *                         the API Key and Password
     * @param string $resource The API resource/path to request
     * @param string $method   The HTTP verb to use (GET|POST|PUT|DELETE)
     * @param array  $data     Collection of data to be sent with the request
     *
     * @return string Decoded JSON object
     */
    public static function request($client, $resource, $method, $data = array())
    {
        $curl = curl_init();

        $url = self::$endPoint . $resource;

        if (sizeof($data)) {
            if ($method == 'POST') {
                curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
            } elseif ($method == 'GET') {
                $url .= '?' . http_build_query($data);
            }
        }

        if ($method == 'POST' && sizeof($data)) {
            $body = json_encode($data);
        } else {
            $body = '';
        }
        $nonce = (int)(microtime(true) * 1e6);
        $message = $nonce . $url . $body;
        $signature = hash_hmac('sha256', $message, $client->getApiPass());

        $headers = array(
            'Access-Key:'           . $client->getApiKey()
            , 'Access-Nonce:'       . $nonce
            , 'Content-Type:'       . 'application/json'
            , 'Access-Signature:'   . $signature
        );

        curl_setopt($curl, CURLOPT_URL, $url);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HEADER, 1);

        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($curl);
        if ($response === false) {
            throw new \Exception('No response from curl_exec in ' . __METHOD__);
        }
        $headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $responseBody = substr($response, $headerSize);
        curl_close($curl);

        return json_decode($responseBody);
    }
}
