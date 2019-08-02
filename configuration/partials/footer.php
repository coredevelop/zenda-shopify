<div id="modal_confirm" class="modal hide fade">
    <div class="modal-body">
        Are you sure?
    </div>
    <div class="modal-footer">
        <button type="button" data-dismiss="modal" class="btn btn-primary" id="modal_delete">Delete</button>
        <button type="button" data-dismiss="modal" class="btn">Cancel</button>
    </div>
</div>
</div>

<script>
    $(document).ajaxStart(function () {
        showLoader();
    }).ajaxStop(function () {
        hideLoader();
    });

    function showLoader() {
        $('.loader').show();
        $('#overlay').show();
    }

    function hideLoader() {
        $('.loader').hide();
        $('#overlay').hide();
    }

</script>
<div id="overlay">
    <div class="loader"></div>
</div>
<style>
    #overlay {
        position: fixed;
        top: 0px;
        left: 0px;
        width: 100%;
        height: 100%;
        background: black;
        filter: alpha(opacity=60);
        opacity: 0.6;
        -moz-opacity: 0.8;
    }

    p.display_name, input#supplier_name {
        float: left;
        margin-right: 10px;
    }

    .loader {
        border: 16px solid #f3f3f3;
        border-radius: 50%;
        border-top: 16px solid #3498db;
        width: 120px;
        height: 120px;
        -webkit-animation: spin 2s linear infinite; /* Safari */
        animation: spin 2s linear infinite;
        position: fixed;
        left: 0;
        right: 0;
        margin: auto;
        top: 40%;
        transform: translateY(-40%);
        filter: alpha(opacity=100);
        opacity: 1;
        -moz-opacity: 1;
    }

    /* Safari */
    @-webkit-keyframes spin {
        0% {
            -webkit-transform: rotate(0deg);
        }
        100% {
            -webkit-transform: rotate(360deg);
        }
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }
        100% {
            transform: rotate(360deg);
        }
    }
</style>

<!--===============================================================================================-->
<script>

    $('.js-tilt').tilt({
        scale: 1.1
    })
    $(document).ready(function () {
        $('#overlay').delay(500).fadeOut(500);
        $('.loader').delay(500).fadeOut(500);
    });
    window.onload = function () {
        $('#overlay').delay(500).fadeOut(500);
        $('.loader').delay(500).fadeOut(500);
    };
</script>
