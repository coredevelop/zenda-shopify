<?php
require_once __DIR__ . '/ZendaShipping.php';
$zendaShipping = new ZendaShipping();
$rates = $zendaShipping->collectRates();
if ($rates) {
    echo json_encode($rates);
}