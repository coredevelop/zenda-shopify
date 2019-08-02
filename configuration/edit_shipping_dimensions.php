<?php
$productId = $_GET['id'];
$url = 'https://' . $_GET['shop'] . '/admin/bulk?resource_name=Product&id=' . $productId . '&edit=metafields.global.LENGTH%3Astring%2Cmetafields.global.WIDTH%3Astring%2Cmetafields.global.HEIGHT';
echo "<script> top.location.href='$url'</script>";