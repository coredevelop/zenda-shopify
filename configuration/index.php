<?php
require_once('../global.php');
include_once 'partials/header.php';
$countries = [];
if (isset($storeSetting['ship_to_specific_countries'])) {
    $countries = explode(',', $storeSetting['ship_to_specific_countries']);
}
$countryList = file_get_contents('../country_let3.json');
$countryList = json_decode($countryList, true);
?>
<div class="container">
    <div class="row">
        <style>
            td input {
                width: 100%;
            }
        </style>
        <form method="post" action="save_setting.php">
            <div class="col-sm-6">
                <fieldset>
                    <legend class="scheduler-border">General</legend>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label col-form-label-sm" for="enable">Enable</label>
                        <div class="col-sm-8">
                            <select class="form-control form-control-sm" id="enable" name="enable">
                                <option value="0"
                                    <?php if ($storeSetting && isset($storeSetting['enable']) && $storeSetting['enable'] == '0'): ?>
                                        selected
                                    <?php endif; ?>
                                >
                                    No
                                </option>
                                <option value="1"
                                    <?php if ($storeSetting && isset($storeSetting['enable']) && $storeSetting['enable'] == '1'): ?>
                                        selected
                                    <?php endif; ?>
                                >
                                    Yes
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label col-form-label-sm" for="method_name">Method Name</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control form-control-sm" id="method_name"
                                <?php if ($storeSetting && isset($storeSetting['method_name'])): ?>
                                    value="<?= $storeSetting['method_name'] ?>"
                                <?php endif; ?>
                                   placeholder="Method Name" name="method_name">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label col-form-label-sm" for="enable_flat_rate">Enable Flat
                            Rate</label>
                        <div class="col-sm-8">
                            <select class="form-control" id="enable_flat_rate" name="enable_flat_rate">
                                <option value="0"
                                    <?php if ($storeSetting && isset($storeSetting['enable_flat_rate']) && $storeSetting['enable_flat_rate'] == '0'): ?>
                                        selected
                                    <?php endif; ?>
                                >No
                                </option>
                                <option value="1"
                                    <?php if ($storeSetting && isset($storeSetting['enable_flat_rate']) && $storeSetting['enable_flat_rate'] == '1'): ?>
                                        selected
                                    <?php endif; ?>
                                >Yes
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label col-form-label-sm" for="flat_rate">Flat Rate Amount</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control form-control-sm" id="flat_rate"
                                <?php if ($storeSetting && isset($storeSetting['flat_rate'])): ?>
                                    value="<?= $storeSetting['flat_rate'] ?>"
                                <?php endif; ?>
                                   placeholder="Flat Rate Amount" name="flat_rate">
                        </div>
                    </div>
                    <div class="form-group row" style="display: none;">
                        <label class="col-sm-4 col-form-label col-form-label-sm" for="tooltip_label">Tooltip
                            Label</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control form-control-sm" id="tooltip_label"
                                <?php if ($storeSetting && isset($storeSetting['tooltip_label'])): ?>
                                    value="<?= $storeSetting['tooltip_label'] ?>"
                                <?php endif; ?>
                                   placeholder="Tooltip Label" name="tooltip_label">
                        </div>
                    </div>
                    <div class="form-group row" style="display: none;">
                        <label class="col-sm-4 col-form-label col-form-label-sm" for="tooltip_content">Tooltip
                            Content</label>
                        <div class="col-sm-8">
                            <textarea class="form-control form-control-sm" id="tooltip_content"
                                      placeholder="Tooltip Content" name="tooltip_content">
                                <?php if ($storeSetting && isset($storeSetting['tooltip_content'])): ?>
                                    <?= $storeSetting['tooltip_content'] ?>
                                <?php endif; ?>
                            </textarea>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label col-form-label-sm" for="user_name">Username</label>
                        <div class="col-sm-8">
                            <input type="password" class="form-control form-control-sm"
                                <?php if ($storeSetting && isset($storeSetting['user_name'])): ?>
                                    value="<?= $storeSetting['user_name'] ?>"
                                <?php endif; ?>
                                   placeholder="Username"
                                   id="user_name" name="user_name">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label col-form-label-sm" for="user_password">Password</label>
                        <div class="col-sm-8">
                            <input type="password" class="form-control form-control-sm"
                                <?php if ($storeSetting && isset($storeSetting['user_password'])): ?>
                                    value="<?= $storeSetting['user_password'] ?>"
                                <?php endif; ?>
                                   placeholder="Password"
                                   id="user_password" name="user_password">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label col-form-label-sm" for="live_account">Live Account</label>
                        <div class="col-sm-8">
                            <select class="form-control form-control-sm" id="live_account" name="live_account">
                                <option value="1"
                                    <?php if ($storeSetting && isset($storeSetting['live_account']) && $storeSetting['live_account'] == '1'): ?>
                                        selected
                                    <?php endif; ?>
                                >
                                    No
                                </option>
                                <option value="0"
                                    <?php if ($storeSetting && isset($storeSetting['live_account']) && $storeSetting['live_account'] == '0'): ?>
                                        selected
                                    <?php endif; ?>
                                >
                                    Yes
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label col-form-label-sm" for="live_method_title">Title</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control form-control-sm" id="live_method_title"
                                <?php if ($storeSetting && isset($storeSetting['live_method_title'])): ?>
                                    value="<?= $storeSetting['live_method_title'] ?>"
                                <?php endif; ?>
                                   placeholder="Title" name="live_method_title">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label col-form-label-sm" for="ship_to_applicable_countries">Ship
                            to Applicable Countries</label>
                        <div class="col-sm-8">
                            <select class="form-control" id="ship_to_applicable_countries"
                                    name="ship_to_applicable_countries">
                                <option value="0"
                                    <?php if ($storeSetting && isset($storeSetting['ship_to_applicable_countries']) && $storeSetting['ship_to_applicable_countries'] == '0'): ?>
                                        selected
                                    <?php endif; ?>
                                >All Allowed Countries
                                </option>
                                <option value="1"
                                    <?php if ($storeSetting && isset($storeSetting['ship_to_applicable_countries']) && $storeSetting['ship_to_applicable_countries'] == '1'): ?>
                                        selected
                                    <?php endif; ?>
                                >Specific Countries
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row" id="countries">
                        <label class="col-sm-4 col-form-label col-form-label-sm" for="ship_to_specific_countries">Ship
                            to Specific Countries</label>
                        <div class="col-sm-8">
                            <select multiple="multiple" class="form-control" id="ship_to_specific_countries"
                                    name="ship_to_specific_countries[]">
                                <?php foreach ($countryList as $country): ?>
                                    <option value="<?= $country['let2'] ?>" <?php echo in_array($country['let2'],
                                        $countries) ? 'selected' : 0; ?>><?= $country['name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row" style="display: none;">
                        <label class="col-sm-4 col-form-label col-form-label-sm" for="debug">Debug</label>
                        <div class="col-sm-8">
                            <select class="form-control form-control-sm" id="debug" name="debug">
                                <option value="0"
                                    <?php if ($storeSetting && isset($storeSetting['debug']) && $storeSetting['debug'] == '0'): ?>
                                        selected
                                    <?php endif; ?>
                                >No
                                </option>
                                <option value="1"
                                    <?php if ($storeSetting && isset($storeSetting['debug']) && $storeSetting['debug'] == '1'): ?>
                                        selected
                                    <?php endif; ?>
                                >Yes
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row" style="display: none;">
                        <label class="col-sm-4 col-form-label col-form-label-sm" for="sort_order">Sort Order</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control form-control-sm"
                                <?php if ($storeSetting && isset($storeSetting['sort_order'])): ?>
                                    value="<?= $storeSetting['sort_order'] ?>"
                                <?php endif; ?>
                                   id="sort_order" placeholder="sort order" name="sort_order">
                        </div>
                    </div>
                    <div class="form-group row" style="display: none;">
                        <label class="col-sm-4 col-form-label col-form-label-sm" for="sort_order">Weight Unit</label>
                        <div class="col-sm-8">
                            <option value="lbs"
                                <?php if ($storeSetting && isset($storeSetting['weight_unit']) && $storeSetting['weight_unit'] == 'lbs'): ?>
                                    selected
                                <?php endif; ?>
                            >lbs
                            </option>
                            <option value="kgs"
                                <?php if ($storeSetting && isset($storeSetting['weight_unit']) && $storeSetting['weight_unit'] == '1'): ?>
                                    selected
                                <?php endif; ?>
                            >kgs
                            </option>
                        </div>
                    </div>
                </fieldset>
            </div>
            <div class="col-sm-6">
                <fieldset>
                    <legend class="scheduler-border">Shopify API Info</legend>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label col-form-label-sm" for="api_key">API Key</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control form-control-sm" id="api_key"
                                <?php if ($storeSetting && isset($storeSetting['api_key'])): ?>
                                    value="<?= $storeSetting['api_key'] ?>"
                                <?php endif; ?>
                                   placeholder="APi Key" name="api_key">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label col-form-label-sm" for="api_password">API Password</label>
                        <div class="col-sm-8">
                            <input type="password" class="form-control form-control-sm"
                                <?php if ($storeSetting && isset($storeSetting['api_password'])): ?>
                                    value="<?= $storeSetting['api_password'] ?>"
                                <?php endif; ?>
                                   id="api_password" placeholder="Api Password" name="api_password">
                        </div>
                    </div>
                </fieldset>
            </div>
            <div class="col-sm-12">
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>
<script type="text/javascript">

    $(document).ready(function () {
        var allow_all_countries = $('#ship_to_applicable_countries').val();
        if (allow_all_countries === '0') {
            $('#countries').hide();
        }

        $('#ship_to_applicable_countries').on('change', function () {
            debugger;
            if ($(this).val() === '0') {
                $('#countries').hide();
            } else {
                $('#countries').show();
            }
        });
    });
</script>
<?php include_once 'partials/footer.php'; ?>
