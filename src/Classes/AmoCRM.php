<?php

namespace Classes;
class AmoCRM
{
    public string $subdomain = 'adela1998';
    public string $amoTokenFile = 'amotoken.txt';

    public function curlRequest($link, $accessToken, $data = []): array
    {
        $headers = [
            'Accept: application/json',
            'Authorization: Bearer '.$accessToken,
        ];
        $dataJson = json_encode($data);
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_USERAGENT, 'amoCRM-oAuth-client/1.0');
        curl_setopt($curl, CURLOPT_URL, $link);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        if ($data) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $dataJson);
        }
        $responseJson = curl_exec($curl);
        curl_close($curl);
        $response = json_decode($responseJson, true);

        return $response;
    }

    public function amoAddContact($accessToken, $formData): int
    {
        $link = 'https://'.$this->subdomain.'.amocrm.ru/api/v4/contacts';

        $contactData = array(
            [
                'name' => $formData['user_name'],
                'custom_fields_values' => [
                    [
                        'field_code' => 'PHONE',
                        'values' => [
                            [
                                'value' => $formData['user_phone'],
                                'enum_code' => 'MOB',
                            ],
                        ],
                    ],
                    [
                        'field_code' => 'EMAIL',
                        'values' => [
                            [
                                'value' => $formData['user_email'],
                                'enum_code' => 'WORK',
                            ],
                        ],
                    ],
                ],
                '_embedded' => [
                    'tags' => [
                        0 => [
                            'name' => 'авто отправка',
                        ],
                    ],
                ],
            ],
        );

        $contactResponse = $this->curlRequest($link, $accessToken, $contactData);

        return $contactResponse['_embedded']['contacts'][0]['id'];
    }


    public function amoSendLead($accessToken, $formData, $contactId = 0): void
    {
        $link = 'https://'.$this->subdomain.'.amocrm.ru/api/v4/leads';

        $leadData = [
            0 => [
                'name' => 'Заявка с сайта',
                'price' => intval($formData['lead_price']),
                '_embedded' => [
                    'contacts' => [
                        0 => [
                            'id' => $contactId,
                        ],
                    ],
                    'tags' => [
                        0 => [
                            'name' => 'авто отправка',
                        ],
                    ],
                ],
            ],
        ];

        $this->curlRequest($link, $accessToken, $leadData);
    }

    public function amoIntegration($formData): void
    {
        $amoAuth = new AmoAuthorization();
        if (!file_exists($this->amoTokenFile)) {
            $amoAuth->generatedToken();
        }

        $dataToken = file_get_contents($this->amoTokenFile);
        $dataToken = json_decode($dataToken, true);

        if ($dataToken['endTokenTime'] < time()) {
            $dataToken = $amoAuth->returnNewToken($dataToken['refresh_token']);
        }
        $newAccessToken = $dataToken['access_token'];
        $this->amoSendLead(
            $newAccessToken,
            $formData,
            $this->amoAddContact($newAccessToken, $formData)
        );
    }
}
