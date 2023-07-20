<?php

namespace Classes;

use Exception;

class AmoAuthorization
{
    public string $subdomain = 'adela1998';
    public string $client_id = '843b4c30-e7c4-415b-8508-ec20b700ad7c';
    public string $client_secret = 'by0ehOILscyIOi2qhVLdWmw6oaRjqNVvXh9QLfhhkkWc0iMsI2z7j0OrMYljrRCT';
    public string $redirect_uri = 'https://adela-ya.ru';
    public string $auth_code = 'def502009e38525558f07fef86bebd99cc5d0c68981bb5d366620764f44b96f017a8627213a95e103d3142c79a690dfc5ad67207fc03feb69d10f4da0989a57ba95d746bdedead90da380ed5413907871fa1d077a849bdb5e95367ca730f1090a129d99612c63ef3b95dd1165f60c9db0b7a4b61c30944e4098309ed25f3bc3f8c3de2034d020ab69fd211baaba28fa9d9ec2c145405876c042e61d32c72301ada183dea686778bb38290f1ba1b8a7c756c8dbbaa6d01104f82b80c5393324334ada8b5789cbbb51898aa11d135d175947fdd66646e4cc971ebe9a3f594b9ce68553fae400d4a19a00ff97181aca2620c8dea7b8a9447af3f3e1ffed2e6ac5f28926311076bb39d97e3c687f54f1f91ec98e66862ae3f42d2c0db482130ec29ac15cfcca4c21943779da17d3cf9e9ec638f8f9a3b87e5555b65b38b3277fcd963690f7d518c419466acf1ab0284730c424410c9061579108ef223c1cf300ab17f9291e6ac883a49674961e2143240b333ce4cd44185a0276b4bf00a08fd4703b1e4e35b8c05d3cb47fa1825131b19e2893d5751327213850ee184d597e21a2dcef10c764b42dd80d03fb9011daf682459c6891b3f9ac8bf2dc2831d154aa4de067b9072abb1d21df0f3a7796e1c64212ce58c49760e33e2697fa17fba7fb39d838ee3e';
    public string $amoTokenFile = 'amotoken.txt';

    public function curlRequest($link, $data = []): array
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_USERAGENT, 'amoCRM-oAuth-client/1.0');
        curl_setopt($curl, CURLOPT_URL, $link);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type:application/json']);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        $out = curl_exec($curl);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        $code = (int)$code;

        // коды возможных ошибок
        $errors = [
            400 => 'Bad request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not found',
            500 => 'Internal server error',
            502 => 'Bad gateway',
            503 => 'Service unavailable',
        ];
        try {
            if ($code < 200 || $code > 204) {
                throw new Exception($errors[$code] ?? 'Undefined error', $code);
            }
        } catch (Exception $e) {
            die('Ошибка: '.$e->getMessage().PHP_EOL.'Код ошибки: '.$e->getCode());
        }

        $response = json_decode($out, true);

        return $response;
    }

    public function generatedToken(): void
    {
        $link = 'https://'.$this->subdomain.'.amocrm.ru/oauth2/access_token';
        $data = [
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
            'grant_type' => 'authorization_code',
            'code' => $this->auth_code,
            'redirect_uri' => $this->redirect_uri,
        ];
        $tokenResponse = $this->curlRequest($link, $data);
        $arrParamsAmo = [
            'access_token' => $tokenResponse['access_token'],
            'refresh_token' => $tokenResponse['refresh_token'],
            'token_type' => $tokenResponse['token_type'],
            'expires_in' => $tokenResponse['expires_in'],
            'endTokenTime' => $tokenResponse['expires_in'] + time(),
        ];
        $arrParamsAmoJSON = json_encode($arrParamsAmo);
        $f = fopen($this->amoTokenFile, 'w');
        fwrite($f, $arrParamsAmoJSON);
        fclose($f);
    }

    public function returnNewToken($refresh_token): ?array
    {
        $link = 'https://'.$this->subdomain.'.amocrm.ru/oauth2/access_token';
        $data = [
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
            'grant_type' => 'refresh_token',
            'refresh_token' => $refresh_token,
            'redirect_uri' => $this->redirect_uri,
        ];

        $response = $this->curlRequest($link, $data);

        if ($response) {
            $response['endTokenTime'] = time() + $response['expires_in'];
            $responseJSON = json_encode($response);
            $f = fopen($this->amoTokenFile, 'w');
            fwrite($f, $responseJSON);
            fclose($f);
            $response = json_decode($responseJSON, true);

            return is_array($response) ? $response : null;
        }

        return null;
    }
}