<?php $resourceBaseUrl = BASE_URL . 'configuration/'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Zenda Shipping Configuration</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" href="<?= $resourceBaseUrl ?>assets/images/images.jpg" type="image/x-icon"/>

    <link
        href="http://fonts.googleapis.com/css?family=Source+Sans+Pro:200,200i,300,300i,400,400i,600,600i,700,700i,900,900i"
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="<?= $resourceBaseUrl ?>assets/bootstrap/css/bootstrap.min.css">
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="<?= $resourceBaseUrl ?>assets/bootstrap/css/bootstrap-select.min.css">
    <link rel="stylesheet" type="text/css" href="<?= $resourceBaseUrl ?>assets/bootstrap/css/bootstrap-multiselect.css">
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="<?= $resourceBaseUrl ?>fonts/font-awesome-4.7.0/css/font-awesome.min.css">
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="<?= $resourceBaseUrl ?>assets/css-hamburgers/hamburgers.min.css">
    <!--===============================================================================================-->
    <link href="<?= $resourceBaseUrl ?>assets/css/styles.css" rel="stylesheet">
    <!--===============================================================================================-->
    <!-- To Load App Inside Shopify Admin -->
    <style type="text/css">
        form#creator-form {
            display: inline-block;
            width: 100%;
            margin: 20px 0;
        }

        .or_class {
            display: block;
            width: 100%;
        }

        .supplier-input {
            float: left;
        }

        input#supplier {
            width: 400px;
            margin-right: 10px;
        }

        label.error {
            color: red;
        }

        .closebtn {
            margin-left: 15px;
            color: white;
            font-weight: bold;
            float: right;
            font-size: 22px;
            line-height: 20px;
            cursor: pointer;
            transition: 0.3s;
        }

        .closebtn:hover {
            color: black;
        }
    </style>
    <script src="<?= $resourceBaseUrl ?>assets/jquery/jquery-3.2.1.min.js"></script>
    <script src="<?= $resourceBaseUrl ?>assets/jquery/jquery.validate.min.js"></script>
    <script src="<?= $resourceBaseUrl ?>assets/jquery/additional-methods.js"></script>
    <script src="<?= $resourceBaseUrl ?>assets/bootstrap/js/popper.js"></script>
    <script src="<?= $resourceBaseUrl ?>assets/bootstrap/js/bootstrap.js"></script>
    <script src="<?= $resourceBaseUrl ?>assets/bootstrap/js/bootstrap-select.min.js"></script>
    <script src="<?= $resourceBaseUrl ?>assets/bootstrap/js/bootstrap-multiselect.js"></script>
    <script src="<?= $resourceBaseUrl ?>assets/tilt/tilt.jquery.min.js"></script>
</head>
<body class="shopify-app-layout main-section">
<header>
    <div class="left">
        <?php if ($_SERVER['REQUEST_URI'] == '/supplier_dropship_shopify/'): ?>
            <a class="register_carrier btn-success btn-xs btn btn-primary"
               href="<?php echo 'https://' . $storeData['domain'] . '/admin/apps' ?>">Back</a>
        <?php else: ?>
            <button type="button" class="register_carrier btn-success btn-xs btn btn-primary">Back</button>
        <?php endif; ?>
    </div>
    <div class="right"><strong>Shop Name :</strong><span><?php echo $storeData['name'] ?></span></div>
</header>
<div class="main-container container" style="margin-top: 1em;">
    <div class="alert alert-danger" style="display: none;">
        <span class="closebtn" onclick="this.parentElement.style.display='none';">&times;</span>
        <div class="error-message"></div>
    </div>
    <div class="alert alert-success" style="display: none;">
        <span class="closebtn" onclick="this.parentElement.style.display='none';">&times;</span>
        <div class="success-message"></div>
    </div>
    <div class="row">
        <div class="title"><h1>Zenda Shipping Configuration</h1></div>
    </div>

