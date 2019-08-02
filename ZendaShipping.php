<?php
require_once __DIR__ . '/Service.php';

class ZendaShipping
{
    const XML_SHIPPING_CODE = 'zenda';

    /**@var Service */
    protected $_apiService;

    protected $_storeSetting;
    protected $_storeData;
    protected $_currencySymbols;

    protected $_data = array();

    protected $_currency = 'US';
    protected $_sourceCountry;
    protected $_destinationCountry;
    protected $_products;
    protected $_cartPrice = 0;
    protected $_items = array();

    public function __construct()
    {
        global $storeSetting, $storeData, $currencySymbols;
        $this->_storeSetting = $storeSetting;
        $this->_storeData = $storeData;
        $this->_currencySymbols = $currencySymbols;
        $this->_apiService = new Service();
        $this->_data = json_decode(file_get_contents("php://input"), true);
    }

    public function collectRates()
    {
        if ($this->isApplicableRequest()) {
            if ($this->getOriginDetails() && $this->getDestinationDetails()) {
                $maxDimension = $this->getApiService()->authenticate()->getMaxValidDimension(
                    $this->getOriginDetails(),
                    $this->getDestinationDetails(),
                    $this->getCartCurrencyCode()
                );

                $totalShippingPrice = 0.00;
                $storeSetting = $this->_storeSetting;
                if (@$storeSetting['enable_flat_rate']){
                    $totalShippingPrice = (float)$storeSetting['flat_rate'];
                }else{
                    $packages = $this->_apiService->composerPackage(
                        $this->getDataItems(),
                        $maxDimension['weight'],
                        $maxDimension['volume']
                    );

                    if (!empty($packages)) {
                        foreach ($packages as $key => $package) {
                            $totalShippingPrice += $this->getShippingPrice($package);
                        }
                    }
                }

                $rate = $this->getRate(
                    $totalShippingPrice,
                    $this->getTotalTax($totalShippingPrice)
                );

                return $rate ? ['rates' => [$rate]] : [];
            }
        }

        return [];
    }

    protected function getRate($totalShippingPrice, $totalTaxAndDuty)
    {
        $rawData = $this->getData();
        $currencySymbols = $this->_currencySymbols;

        return array(
            [
                'service_name' => $this->getApiService()->getConfigShippingName(),
                'service_code' => self::XML_SHIPPING_CODE,
                'total_price'  => ($totalShippingPrice + $totalTaxAndDuty) * 100,
                'description'  => $currencySymbols[$rawData['rate']['currency']] . $totalShippingPrice . ' shipping + ' . $currencySymbols[$rawData['rate']['currency']] . $totalTaxAndDuty . ' prepaid tax and duty',
                'currency'     => $rawData['rate']['currency']
            ]
        );
    }

    protected function getTotalTax($totalShippingPrice)
    {
        $cartPrice = $this->_cartPrice * 0.01;

        $taxRespon = $this->getApiService()->getTaxAndDuty($totalShippingPrice,
            $this->getOriginDetails()['countryCode'],
            $this->getDestinationDetails()['countryCode'], $cartPrice, $this->getCartCurrencyCode(),
            $this->getProducts());
        $totalTaxAndDuty = 0.0;
        if (isset($taxRespon['response'][0]['totalTax'])
            && isset($taxRespon['response'][0]['totalDuty'])
        ) {
            $totalTax = $taxRespon['response'][0]['totalTax'];
            $totalDuty = $taxRespon['response'][0]['totalDuty'];
            $totalTaxAndDuty = $totalTax + $totalDuty;
        } elseif (isset($taxRespon['alerts'][0]['code'])
            && isset($taxRespon['alerts'][0]['message'])
        ) {
            $msg = $taxRespon['alerts'][0]['message'];
            if (isset($taxRespon['commodityException'][0]['exceptionMessage'])) {
                $msg .= ': ' . $taxRespon['commodityException'][0]['exceptionMessage'];
            }
            throw new \Exception($msg);
        }


        return $totalTaxAndDuty;
    }

    protected function getShippingPrice($package)
    {
        $shippingPrice = 0.00;
        try {
            // Sum up the shipping price
            $shippingItemsData = $this->getApiService()->getShippingPrice(
                $this->getOriginDetails(),
                $this->getDestinationDetails(),
                $this->getCartCurrencyCode(),
                (float)$package['weight'],
                (float)$package['volume']
            );
            if (isset($shippingItemsData[0]) && isset($shippingItemsData[0]['cost']['value'])) {
                $shippingPrice = (float)$shippingItemsData[0]['cost']['value'];
            } elseif (isset($shippingItemsData['alerts'][0]['code'])
                && isset($shippingItemsData['alerts'][0]['message'])
            ) {
                $msg = $shippingItemsData['alerts'][0]['message'];
                $this->logMessage($msg);
            }
        } catch (\Exception $e) {
            $this->logMessage($e);
        }

        return $shippingPrice;
    }

    protected function getDataItems()
    {
        if (!count($this->_items) && count($this->getData())) {
            $this->getProducts();
        }
        return $this->_items;
    }

    protected function getProducts()
    {
        $storeSetting = $this->_storeSetting;
        $storeData = $this->_storeData;

        if (!$this->_products && count($this->_data) && isset($this->_data['rate']['items'])) {
            $items = $this->getItems();
            $products = [];
            $cartPrice = 0;
            $dataItems = [];
            foreach ($items as $item) {
                $productId = $item['product_id'];
                $item['weight'] = $this->_apiService->convertWeight($item['grams'] * 0.001, 'KG');
                $metaFields = $this->_apiService->getProductMetafield($storeSetting, $storeData, $productId);
                $metaFields = json_decode($metaFields, true);
                $length = $width = $height = 0;
                if (@$metaFields['metafields']) {
                    foreach ($metaFields['metafields'] as $meta) {
                        switch ($meta['key']) {
                            case 'LENGTH' :
                                $length = (int)$meta['value'];
                                break;
                            case 'WIDTH' :
                                $width = (int)$meta['value'];
                                break;
                            case 'HEIGHT' :
                                $height = (int)$meta['value'];
                                break;
                            default :
                                break;
                        }
                    }
                }
                $item['volume'] = $width * $height * $length;
                $cartPrice += $item['price'];
                $products[] = [
                    'SKUCode'     => $item['sku'],
                    'description' => $item['name'],
                    'value'       => $item['price'] * 0.01,
                    'qty'         => $item['quantity']
                ];
                $dataItems[] = $item;
            }

            $this->_products = $products;
            $this->_cartPrice = $cartPrice;
            $this->_items = $dataItems;
        }

        return $this->_products;
    }

    protected function getItems()
    {
        if (count($this->_data) && isset($this->_data['rate']['items'])) {
            return $this->_data['rate']['items'];
        }

        return false;
    }

    protected function getSourceCountry()
    {
        $rawData = $this->getData();
        if (count($rawData) && !$this->_sourceCountry) {
            $this->_sourceCountry = $rawData['rate']['origin']['country'];
        }

        return $this->_sourceCountry;
    }

    protected function getDestinationCountry()
    {
        $rawData = $this->getData();
        if (count($rawData) && !$this->_destinationCountry) {
            $this->_destinationCountry = $rawData['rate']['destination']['country'];
        }

        return $this->_sourceCountry;

    }

    protected function getCartCurrencyCode()
    {
        $data = $this->getData();
        if (count($data)) {
            $this->_currency = $data['rate']['currency'];
        }
        return $this->_currency;
    }

    protected function getOriginDetails()
    {
        $countries = $this->getCountries();
        $rawData = $this->getData();
        if (isset($rawData['rate']['origin']['postal_code']) && isset($rawData['rate']['origin']['country']) && isset($countries[$rawData['rate']['origin']['country']])) {
            return [
                'postalCode'  => $rawData['rate']['origin']['postal_code'],
                'countryCode' => $countries[$rawData['rate']['origin']['country']]
            ];
        }

        return false;
    }

    protected function getDestinationDetails()
    {
        $countries = $this->getCountries();
        $rawData = $this->getData();
        if (isset($rawData['rate']['destination']['postal_code']) && isset($rawData['rate']['destination']['country']) && isset($countries[$rawData['rate']['destination']['country']])) {
            return [
                'postalCode'  => $rawData['rate']['destination']['postal_code'],
                'countryCode' => $countries[$rawData['rate']['destination']['country']]
            ];
        }

        return false;
    }

    protected function getCountries()
    {
        $countries = [];
        try {
            $countryList = file_get_contents('country_let3.json');
            $countryList = json_decode($countryList, true);
            foreach ($countryList as $country) {
                try {
                    $countries[trim($country['let2'])] = $country['let3'];
                } catch (\Exception $e) {
                    $this->logMessage($e);
                }
            }
        } catch (\Exception $e) {
            $this->logMessage($e);
        }

        return $countries;
    }

    protected function isApplicableRequest()
    {
        $storeSetting = $this->_storeSetting;
        $rawData = $this->getData();
        if (count($rawData)) {
            $countries = [];
            if (isset($storeSetting['ship_to_specific_countries'])) {
                $countries = explode(',', $storeSetting['ship_to_specific_countries']);
            }
            if (!$storeSetting['enable']) {
                return false;
            }
            if ($storeSetting['ship_to_applicable_countries'] && $countries && !in_array($rawData['rate']['destination']['country'],
                    $countries)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return mixed
     */
    public function getStoreSetting()
    {
        return $this->_storeSetting;
    }

    /**
     * @param mixed $storeSetting
     *
     * @return ZendaShipping
     */
    public function setStoreSetting($storeSetting)
    {
        $this->_storeSetting = $storeSetting;
        return $this;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->_data;
    }

    /**
     * @param array $data
     *
     * @return ZendaShipping
     */
    public function setData($data)
    {
        $this->_data = $data;
        return $this;
    }

    /**
     * @return Service
     */
    public function getApiService()
    {
        return $this->_apiService;
    }

    public function logMessage($dataLog)
    {
        if (!$this->_apiService) {
            file_put_contents('carrier.log', print_r(array($dataLog), true), FILE_APPEND);
        } else {
            $this->_apiService->logMessage($dataLog);
        }
    }
}