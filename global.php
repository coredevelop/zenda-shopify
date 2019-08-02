<?php
require_once "conf.php";
if(MODE == 'Production'){
    error_reporting(0);
}else{
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

session_start();
require_once('Database.php');
require_once('ShopifyApi.php');

$db = new Database();
$shopifyApi = new ShopifyApi();
$storeTable = ShopifyApi::STORE_TABLE;
$storeData = [];
if (isset($_GET['store'])) {
    $storeData = $shopifyApi->getStoreById($_GET['store']);
} else {
    $storeData = $shopifyApi->getCurrentStoreId("all");
}
$storeSetting = $shopifyApi->getSettingStore($storeData, 'all');
$currencySymbols = [
    'EUR' => '€',
    'GBP' => '£',
    'USD' => '$',
    'INR' => '₹',
    'JPY' => '¥',
    'NZD' => '$'
];
$currency = isset($currencySymbols[$storeData['currency']]) ? $currencySymbols[$storeData['currency']] : $storeData['currency'];


$storeId = $storeData['store_id'];
$baseUrl = BASE_URL . "/supplier_dropship_shopify/";

function logMessage($dataLog)
{
    file_put_contents('../carrier.log', print_r(array($dataLog), true), FILE_APPEND);
}

function is_valid_request($query_params, $shared_secret)
{
    if (!isset($query_params['timestamp'])) return false;

    $seconds_in_a_day = 24 * 60 * 60;
    $older_than_a_day = $query_params['timestamp'] < (time() - $seconds_in_a_day);
    if ($older_than_a_day) return false;

    $hmac = $query_params['hmac'];
    unset($query_params['signature'], $query_params['hmac']);

    foreach ($query_params as $key=>$val) $params[] = "$key=$val";
    sort($params);

    return (hash_hmac('sha256', implode('&', $params), $shared_secret) === $hmac);
}