<?php
require_once 'global.php';

class Service
{
    const XML_TEST_USER = 'Myndshft';
    const XML_TEST_PASS = 'bGX!spWp';
    const LIVE_ACCOUNT = 0;

    protected $_isTestAPI = true;

    protected $_accessToken = '';

    /**
     * 1 kilogram (kg) is equal to 2.2046 pounds
     */
    const KG_TO_POUND = 2.20462262;

    /**
     * 1 m3 is equal to 61 023.7441 in3
     */
    const M2_TO_IN3 = 61023.7441;

    /**
     * 1 cm3 is equal to 0.0610237441 in3
     */
    const CM3_TO_IN3 = 0.0610237441;

    protected $_apiUser;
    protected $_apiPass;
    protected $_storeSetting;

    public function __construct()
    {
        global $storeSetting;
        $this->_storeSetting = $storeSetting;
        if ($storeSetting['live_account'] == self::LIVE_ACCOUNT){
            $this->setIsTestAPI(false);
        }
        $this->setConfigPass();
        $this->setConfigUser();
    }

    /**
     * @return bool
     */
    public function isTestAPI()
    {
        return $this->_isTestAPI;
    }

    /**
     * @param bool $isTestAPI
     */
    public function setIsTestAPI($isTestAPI)
    {
        $this->_isTestAPI = $isTestAPI;
    }

    /**
     * API Test Url
     *
     * @var string
     */
    protected $_testApiUrl = 'https://uat2-api.zenda.global/v1/';

    /**
     * API Production Url
     *
     * @var string
     */
    protected $_productionApiUrl = 'https://prd-api.zenda.global/v1/';

    public function getConfigShippingName()
    {
        if ($this->_storeSetting && isset($this->_storeSetting['live_account'])) {
            return $this->_storeSetting['live_method_title'];
        }

        return 'Zenda Shipping';
    }

    public function setStoreConfig($storeSetting)
    {
        $this->_storeSetting = $storeSetting;
    }

    public function setConfigUser()
    {
        if (!$this->_isTestAPI && $this->_storeSetting && isset($this->_storeSetting['user_name'])) {
            $this->_apiUser = $this->_storeSetting['user_name'];
        } else {
            $this->_apiUser = self::XML_TEST_USER;
        }

        return $this;
    }

    public function setConfigPass()
    {
        if (!$this->_isTestAPI && $this->_storeSetting && isset($this->_storeSetting['user_password'])) {
            $this->_apiPass = $this->_storeSetting['user_password'];
        } else {
            $this->_apiPass = self::XML_TEST_PASS;
        }

        return $this;
    }

    public function getConfigUser()
    {
        if (!$this->_apiUser) {
            $this->_apiUser = self::XML_TEST_USER;
        }

        return $this->_apiUser;
    }

    public function getConfigPass()
    {
        if (!$this->_apiPass) {
            $this->_apiPass = self::XML_TEST_PASS;
        }

        return $this->_apiPass;
    }


    public function getBaseUrlAPI()
    {
        if ($this->_isTestAPI) {
            return $this->_testApiUrl;
        }

        return $this->_productionApiUrl;
    }

    /**
     * User authorization URL
     *
     * @return string
     */
    public function getAuthorizationUrl()
    {
        return $this->getBaseUrlAPI() . 'token';
    }


    /**
     * URL for retrieving shipping cost (no tax and duty)
     *
     * @return string
     */
    public function getShippingCostUrl()
    {
        return $this->getBaseUrlAPI() . 'quotes/shipments';
    }

    /**
     * URL for retrieving tax and duty for the shopping cart
     *
     * @return string
     */
    public function getCartTaxAndDutyUrl()
    {
        return $this->getBaseUrlAPI() . 'quotes/baskets';
    }

    public function authenticate()
    {
        $curl = curl_init();
        $dataPost = array('username' => $this->getConfigUser(), 'password' => $this->getConfigPass());
        $dataPost = json_encode($dataPost);

        curl_setopt_array($curl, array(
            CURLOPT_URL            => $this->getAuthorizationUrl(),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => "",
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => "POST",
            CURLOPT_POSTFIELDS     => $dataPost,
            CURLOPT_HTTPHEADER     => array(
                "Content-Type: application/json",
                "cache-control: no-cache"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return "cURL Error #:" . $err;
        } else {
            $response = json_decode($response, true);
            $this->_accessToken = $response['access_token'];
        }

        return $this;
    }

    public function getShippingPrice($originDetails, $destinationDetails, $currencyCode, $packageWeight, $packageVolume)
    {
        $curl = curl_init();
        $data = [
            'serviceLevel' => '',
            'origin'       => [
                'postalCode'  => $originDetails['postalCode'],
                'countryCode' => $originDetails['countryCode']
            ],
            'destination'  => [
                'postalCode'  => $destinationDetails['postalCode'],
                'countryCode' => $destinationDetails['countryCode']
            ],
            'currencyCode' => $currencyCode,
            'parcel'       => [
                'metrics' => [
                    [
                        'metricType'  => 'WEIGHT',
                        'metricValue' => $packageWeight,
                        'metricUnit'  => 'LB'
                    ],
                    [
                        'metricType'  => 'VOLUME',
                        'metricValue' => $packageVolume,
                        'metricUnit'  => 'IN3'
                    ]
                ]
            ]
        ];
        $payload = json_encode($data);

        curl_setopt_array($curl, array(
            CURLOPT_URL            => $this->getShippingCostUrl(),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => "",
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => "POST",
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => array(
                "Content-Type: application/json",
                "cache-control: no-cache",
                "token: " . $this->_accessToken
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return "cURL Error #:" . $err;
        } else {
            return json_decode($response, true);
        }
    }

    public function getTaxAndDuty(
        $shippingPrice,
        $sourceCountry,
        $destinationCountry,
        $currentCartTotal,
        $cartCurrencyCode,
        $products
    ) {
        $data = [
            'sourceCountry'      => $sourceCountry,
            'shippingPrice'      => $shippingPrice,
            'destinationCountry' => $destinationCountry,
            'currentCartValue'   => $currentCartTotal,
            'cartCurrencyCode'   => $cartCurrencyCode,
            'products'           => $products
        ];

        $payload = json_encode($data);

        $ch = curl_init($this->getCartTaxAndDutyUrl());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($payload),
                "token: " . $this->_accessToken
            )
        );

        $result = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);
        if ($err) {
            return "cURL Error #:" . $err;
        } else {
            return json_decode($result, true);
        }
    }

    public function getMaxValidDimension($originDetails, $destinationDetails, $currencyCode)
    {
        $data = $this->getShippingPrice($originDetails, $destinationDetails, $currencyCode, 1, 1);
        if (isset($data[0])) {
            foreach ($data[0]['maxValidDimension'] as $dimension) {
                if (isset($dimension['metricType']) && isset($dimension['metricValue']) && isset($dimension['metricUnit'])) {
                    if ($dimension['metricType'] == "WEIGHT") {
                        $maxValidWeight = $this->convertWeight($dimension['metricValue'], $dimension['metricUnit']);
                    }
                    if ($dimension['metricType'] == "VOLUME") {
                        $maxValidVolume = $this->convertVolume($dimension['metricValue'], $dimension['metricUnit']);
                    }
                }
            }
            return [
                'weight' => $maxValidWeight,
                'volume' => $maxValidVolume
            ];
        }

        return false;
    }

    public function convertVolume($volume, $unit)
    {
        switch (strtoupper($unit)) {
            case 'IN3':
                break;
            case 'M3':
                $volume = $volume * self::M2_TO_IN3;
                break;
            case 'CM3':
                $volume = $volume * self::CM3_TO_IN3;
                break;
        }

        return $volume;
    }

    public function convertWeight($weight, $unit)
    {
        switch (strtoupper($unit)) {
            case 'LB':
                break;
            case 'KG':
                $weight = $weight * self::KG_TO_POUND;
                break;
        }

        return $weight;
    }

    public function _getShippingPrice($originDetails, $destinationDetails, $currencyCode, $items)
    {
        $totalShippingPrice = 0;
        $maxDimension = $this->getMaxValidDimension($originDetails, $destinationDetails, $currencyCode);
        $packages = $this->composerPackage($items, $maxDimension['weight'], $maxDimension['volume']);
        if (!empty($packages)) {
            foreach ($packages as $key => $package) {
                // Sum up the shipping price
                $totalShippingPrice += $this->getShippingPrice(
                    $originDetails, $destinationDetails, $currencyCode,
                    (float)$package['weight'],
                    (float)$package['volume']
                );
            }
        }

        return $totalShippingPrice;
    }

    public function composerPackage($allItems, $maxweight, $maxvolume)
    {

        $items = [];
        foreach ($allItems as $item) {
            $itemWeight = $item['weight'];
            $itemVolume = $item['volume'];

            if (
                $itemWeight >= $maxweight ||
                $itemVolume >= $maxvolume
            ) {
                $error = "Sorry, one of the items is overweight or oversized. Maximum package weight allowed is $maxweight and volume is $maxvolume";
                return [];
            }

            for ($i = 0; $i < $item['quantity']; $i++) {
                $items[] = [
                    'weight' => $itemWeight,
                    'volume' => $itemVolume
                ];
            }
        }

        $parcels = [];
        $numberOfItems = count($items);
        for ($i = 0; $i < $numberOfItems; $i++) {
            $parcelWeight = $items[$i]['weight'];
            $parcelVolume = $items[$i]['volume'];
            for ($j = $i + 1; $j < $numberOfItems; $j++) {
                if (
                    ($parcelWeight + $items[$j]['weight'] > $maxweight) ||
                    ($parcelVolume + $items[$j]['volume'] > $maxvolume)
                ) {
                    break;
                }
                $parcelWeight += $items[$j]['weight'];
                $parcelVolume += $items[$j]['volume'];
            }
            $i = $j - 1;
            $parcels[] = [
                'weight' => $parcelWeight,
                'volume' => $parcelVolume
            ];
        }

        return $parcels;
    }

    public function getProductMetafield($storeSetting, $storeData, $productId)
    {
        $apikey = $storeSetting['api_key'];
        $password = $storeSetting['api_password'];
        $url = $storeData['domain'];
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL            => "https://{$url}/admin/api/2019-07/products/{$productId}/metafields.json",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => "",
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => "GET",
            CURLOPT_POSTFIELDS     => "",
            CURLOPT_HTTPHEADER     => array(
                "cache-control: no-cache"
            ),
        ));
        curl_setopt($curl, CURLOPT_USERPWD, $apikey . ":" . $password);
        $response = curl_exec($curl);

        curl_close($curl);

        return $response;
    }

    public function logMessage($dataLog)
    {
        file_put_contents('carrier.log', print_r(array($dataLog), true), FILE_APPEND);
    }
}