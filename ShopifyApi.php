<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/conf.php';

use phpish\shopify;

class ShopifyApi
{
    const STORE_TABLE = 'store';
    const SETTING_TABLE = 'setting';

    /** @var Database */
    protected $_db;

    function __construct()
    {
        $this->_db = new Database();
    }

    /**
     * Function call api shopify
     *
     * @param        $method_uri
     * @param string $query
     * @param string $payload
     * @param array  $response_headers
     * @param array  $request_headers_override
     * @param array  $curl_opts_override
     *
     * @return mixed
     */
    public function shopify(
        $method_uri,
        $query = '',
        $payload = '',
        &$response_headers = array(),
        $request_headers_override = array(),
        $curl_opts_override = array()
    ) {

        $shopify = shopify\client($_SESSION['shop'], SHOPIFY_APP_API_KEY, $_SESSION['oauth_token']);
        return $shopify($method_uri, $query, $payload, $response_headers, $request_headers_override,
            $curl_opts_override);
    }

    /**
     *
     * Get Setting store
     *
     * @param $storeData
     * @param $data
     *
     * @return bool|mixed
     */
    public function getSettingStore($storeData, $data)
    {
        if (is_array($storeData) && isset($storeData['store_id'])) {
            $storeId = $storeData['store_id'];
            $storeSettingExist = $this->_db->select(self::SETTING_TABLE, '*', null, "`store_id`='$storeId'");
            if (isset($storeSettingExist[0]['store_id'])) {
                $storeId = $storeSettingExist[0]['store_id'];
                if ($data == "all") {
                    return $storeSettingExist[0];
                } else {
                    return $storeId;
                }
            }
        }

        return false;
    }

    /**
     * Get store by id
     *
     * @param $id
     *
     * @return bool
     */
    public function getStoreById($id)
    {
        try {
            $storeExist = $this->_db->select(self::STORE_TABLE, '*', null, "`store_id`='" . $id . "'");
            if ($storeExist) {
                return $storeExist[0];
            } else {
                $storeExist = $this->_db->select(self::STORE_TABLE, '*', null, "`domain`='$id'");
                if ($storeExist) {
                    return $storeExist[0];
                } else {
                    false;
                }
            }
        } catch (\Exception $e) {

        }

        return false;
    }

    /**
     * Get current Store
     *
     * @param string $data
     *
     * @return bool
     */
    public function getCurrentStoreId($data = "")
    {
        $shopData = $this->getShopData();
        if (isset($shopData['domain'])) {
            $domain = $shopData['domain'];
            $storeExist = $this->_db->select(self::STORE_TABLE, '*', null, "`domain`='$domain'");
            //echo "<pre>";print_r($storeExist);die;
            if (!$storeExist) {
                $storeId = $this->_db->insert(self::STORE_TABLE, $shopData);
                $fields = [
                    'carrier_service' => [
                        "name"              => "Zenda",
                        "callback_url"      => BASE_URL . 'carier.php?store=' . $storeId,
                        "service_discovery" => true
                    ]
                ];
                $fields = json_encode($fields);
                $this->createCarrier($domain, $fields);
                return $storeId;
            }
            if (isset($storeExist[0]['store_id'])) {
                $storeId = $storeExist[0]['store_id'];
                unset($shopData['enabled_presentment_currencies']);
                $this->_db->update(self::STORE_TABLE, $shopData, "`store_id`='$storeId'");
                $storeExist = $this->_db->select(self::STORE_TABLE, '*', null, "`domain`='$domain'");
                if ($data == "all") {
                    return $storeExist[0];
                } else {
                    return $storeId;
                }
            }
        }
        return false;
    }

    public function createCarrier($domain, $fields)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL            => "https://{$domain}/admin/api/2019-04/carrier_services.json",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => "",
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => "POST",
            CURLOPT_POSTFIELDS     => $fields,
            CURLOPT_HTTPHEADER     => array(
                "Content-Type: application/json; charset=utf-8",
                "X-Shopify-Access-Token: {$_SESSION['oauth_token']}",
                "cache-control: no-cache"
            ),
        ));

        $response = curl_exec($curl);
        file_put_contents('../carrier.log', print_r(array($domain, $response), true), FILE_APPEND);
        curl_close($curl);
    }

    /**
     * Get shop data from shopify store
     *
     * @return mixed
     */
    public function getShopData()
    {
        return $this->shopify('GET /admin/shop.json');
    }
}
