<?php require_once('../global.php');
try {
    if ($storeData && isset($storeData['store_id'])) {
        if ($storeSetting) {
            $data = $storeSetting;
            $data['store_id'] = $storeData['store_id'];
            $data['enable'] = $db->getRequestParam('enable');
            $data['method_name'] = $db->getRequestParam('method_name');
            $data['enable_flat_rate'] = $db->getRequestParam('enable_flat_rate');
            $data['tooltip_label'] = $db->getRequestParam('tooltip_label');
            $data['tooltip_content'] = $db->getRequestParam('tooltip_content');
            $data['user_name'] = $db->getRequestParam('user_name');
            $data['user_password'] = $db->getRequestParam('user_password');
            $data['live_account'] = $db->getRequestParam('live_account');
            $data['live_method_title'] = $db->getRequestParam('live_method_title');
            $data['ship_to_applicable_countries'] = $db->getRequestParam('ship_to_applicable_countries');
            $data['ship_to_specific_countries'] = implode(",", $db->getRequestParam('ship_to_specific_countries'));
            $data['debug'] = $db->getRequestParam('debug');
            $data['sort_order'] = $db->getRequestParam('sort_order');
            $data['weight_unit'] = $db->getRequestParam('sort_order');
            $data['flat_rate'] = $db->getRequestParam('flat_rate');
            $data['api_key'] = $db->getRequestParam('api_key');
            $data['api_password'] = $db->getRequestParam('api_password');
            $db->update('setting', $data, 'id = ' . $data['id']);
        } else {
            $data = [
                'store_id'                     => $storeData['store_id'],
                'enable'                       => $db->getRequestParam('enable'),
                'method_name'                  => $db->getRequestParam('method_name'),
                'enable_flat_rate'             => $db->getRequestParam('enable_flat_rate'),
                'tooltip_label'                => $db->getRequestParam('tooltip_label'),
                'tooltip_content'              => $db->getRequestParam('tooltip_content'),
                'user_name'                    => $db->getRequestParam('user_name'),
                'user_password'                => $db->getRequestParam('user_password'),
                'live_account'                 => $db->getRequestParam('live_account'),
                'live_method_title'            => $db->getRequestParam('live_method_title'),
                'ship_to_applicable_countries' => $db->getRequestParam('ship_to_applicable_countries'),
                'ship_to_specific_countries'   => $db->getRequestParam('ship_to_specific_countries'),
                'debug'                        => $db->getRequestParam('debug'),
                'sort_order'                   => $db->getRequestParam('sort_order'),
                'weight_unit'                  => $db->getRequestParam('weight_unit'),
                'flat_rate'                    => $db->getRequestParam('flat_rate'),
                'api_key'                      => $db->getRequestParam('api_key'),
                'api_password'                 => $db->getRequestParam('api_password')
            ];
            $db->insert('setting', $data);
        }
    }
} catch (\Exception $e) {
    logMessage($e->getMessage());
}
try {
    $backUrl = 'https://' . $storeData['domain'] . '/admin/apps';
} catch (\Exception $e) {
    logMessage($e->getMessage());
    $backUrl = 'index.php';
}

header('Location: ' . $backUrl);
exit;
