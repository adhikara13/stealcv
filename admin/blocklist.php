<?php

include "../config.php";
include_once "app/functions.php";
include_once "app/managers/AccountManager.php";

session_start();

$link = ConnectDB();

$accountManager = new AccountManager($link);
$accountManager->CheckAuth();

$is_admin = ($_SESSION['user_group'] === 'Administrator') ? 1 : 0;

if(!$is_admin)
{
    header("Location: dashboard");
    exit();
}

$page_name = "Blocklist";

$is_admin = ($_SESSION['user_group'] === 'Administrator') ? 1 : 0;

include_once 'app/pages/header.php';

?> 
<div class="body-wrapper">
    <div class="container-fluid">
        
        <div class="card card-body py-3">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-space-between">
                        <h4 class="mb-4 mb-sm-0 card-title">Blocklist</h4>
                        <nav aria-label="breadcrumb" class="ms-auto">
                            <a href="javascript:void(0)" id="btn-add-contact" class="btn btn-xs btn-primary align-items-center" onclick="createRuleModal();">Create Rule</a>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
        
        <table id="blocklist_table" class="table text-nowrap customize-table mb-0 align-middle">
            <thead class="text-dark fs-4">
                <tr>
                    <th>
                        <h6 class="fs-4 fw-semibold mb-0">ID</h6>
                    </th>
                    <th>
                        <h6 class="fs-4 fw-semibold mb-0">Type</h6>
                    </th>
                    <th>
                        <h6 class="fs-4 fw-semibold mb-0">Value</h6>
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

<!-- Modal -->
<div class="modal fade" id="create_rule_modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-2">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header d-flex align-items-center">
                <h4 class="modal-title" id="myLargeModalLabel">Create Block Rule</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body">
                <div class="card">
                    <div class="card-body">
                        <div class="form-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <div class="form-group mb-4">
                                            <label for="create_rule_type">Block Type</label>
                                            <select class="form-select" id="create_rule_type">
                                                <option value="ip">ip</option>
                                                <option value="mask">mask</option>
                                                <option value="hwid">hwid</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                                
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <div class="form-group">
                                            <textarea class="form-control" rows="3" placeholder="Enter IP, mask or HWID" id="create_rule_value"></textarea>
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

<!-- blocklist worker -->
<script src="assets/js/blocklist.js"></script>
</body>
</html>