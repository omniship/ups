<?php

namespace Omniship\Ups;

use FluidXml\FluidXml;

abstract class Api
{
    # Temporarly here
    public $username;
    public $password;
    public $accessKey;
    #---

    public function getAccess()
    {
        $xml = new FluidXml('AccessRequest');

        $xml->addChild('AccessLicenseNumber', $this->accessKey);
        $xml->addChild('UserId', $this->username);
        $xml->addChild('Password', $this->password);

        return $xml->xml();
    }

    public function execute()
    {
        $data = $this->getAccess().$this->getData();

        #var_dump($data);die;

        $url = 'https://wwwcie.ups.com/ups.app/xml/'.$this->getEndpoint();

        $payload = [
            'body' => $data,
            'headers' => [
                'Accept-Charset' => 'UTF-8',
                'Content-type' => 'application/xml; charset=utf-8',
            ],
            'http_errors' => true,
        ];

        $response = (new \GuzzleHttp\Client)->post($url, $payload);

        $body = (string) $response->getBody();

        if ($response->getStatusCode() === 200) {
            $xml = new \SimpleXMLElement($body);

            if (isset($xml->Response) && isset($xml->Response->ResponseStatusCode)) {
                if ($xml->Response->ResponseStatusCode == 1) {
                    $sxml = simplexml_load_string($body);
                    $json = json_decode(json_encode($sxml), true);
                    return $this->getResponse($json);
                } elseif ($xml->Response->ResponseStatusCode == 0) {
                    $error = $xml->Response->Error;

                    //throw new InvalidResponseException('Failure: ' . $error->ErrorDescription . ' (' . $error->ErrorCode . ')');
                    throw new \Exception('Failure: ' . $error->ErrorDescription . ' (' . $error->ErrorCode . ')');
                }
            } else {
                //throw new InvalidResponseException('Failure: response is in an unexpected format.');
                throw new \Exception('Failure: response is in an unexpected format.');
            }
        }
    }

    abstract protected function getData();

    abstract protected function getResponse(array $data);
}
