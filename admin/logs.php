<?php

include "../config.php";
include_once "app/functions.php";
include_once "app/managers/AccountManager.php";

session_start();

$link = ConnectDB();

$accountManager = new AccountManager($link);
$accountManager->CheckAuth();

$page_name = "Logs";

$is_admin = ($_SESSION['user_group'] === 'Administrator') ? 1 : 0;

include_once 'app/pages/header.php';

?>
<style>
table.dataTable {
    table-layout: fixed;
    width: 100%;
}
td.long-text {
  white-space: normal !important;
  overflow-wrap: break-word !important;
  word-break: break-all !important;
  box-sizing: border-box;
}
</style>
            <div class="body-wrapper">
                <div class="container-fluid" style="max-width: 1280px;">
                    
                    <div id="warning_created_downloads" class="card bg-primary-gt text-white overflow-hidden shadow-none" style="display: none;">
                        <div class="card-body position-relative z-1">
                            <div class="row justify-content-between align-items-center">
                                <div class="col-sm-12">
                                    <h5 class="fw-semibold mb-9 fs-5 text-white">Warning!</h5>
                                    <p class="mb-9 opacity-75">you have created one or more background downloads of logs with server-side archive generation. with a large volume of logs, the archives created by the server for uploading can take up a large disk space.</p>
                                    <p class="mb-9 opacity-75">check list of background downloads and delete ones you have already downloaded.</p>
                                    <button type="button" class="btn btn-danger" onclick="checkDownloadsModal(true);">Check</button>
                                </div>
                                <div class="col-sm-5"></div>
                            </div>
                        </div>
                    </div>

                    <div class="row">

                        <div class="col-lg-3 col-md-6">
                            <div class="card overflow-hidden">
                                <div class="d-flex flex-row">
                                    <div class="p-3">
                                        <h3 class="text-info mb-0 fs-6" id="logs_count">?</h3>
                                        <span>Created Logs</span>
                                    </div>
                                </div>
                            </div>
                        </div>
   
                        <div class="col-lg-3 col-md-6">
                            <div class="card overflow-hidden">
                                <div class="d-flex flex-row">
                                    <div class="p-3">
                                        <h3 class="text-primary mb-0 fs-6" id="unique_logs_percent">?</h3>
                                        <span>Unique Logs</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-3 col-md-6">
                            <div class="card overflow-hidden">
                                <div class="d-flex flex-row">
                                    <div class="p-3">
                                        <h3 class="text-warning mb-0 fs-6" id="fully_logs_count">?</h3>
                                        <span>Fully Uploaded</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6">
                            <div class="card overflow-hidden">
                                <div class="d-flex flex-row">
                                    <div class="p-3">
                                        <h3 class="text-danger mb-0 fs-6" id="passwords_count">?</h3>
                                        <span>Total Passwords</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title mb-3">Search</h4>
                            
                                <div class="form-body">
                                    <div class="row">

                                        <div class="col-md-4">
                                            <div class="mb-3">
                                            <select id="parse_builds" multiple="multiple" class="form-control" ></select>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <input type="text" id="parse_passwords" class="form-control" placeholder="passwords..." />
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <input type="text" id="parse_cookies" class="form-control" placeholder="cookies..." />
                                            </div>
                                        </div>

                                    </div>

                                    <div class="row">

                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <input type="text" id="parse_ip" class="form-control" placeholder="ip..." />
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <select id="parse_marker" multiple="multiple" class="form-control" ></select>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <input type="text" id="parse_system" class="form-control" placeholder="system..." />
                                            </div>
                                        </div>

                                    </div>

                                    <div class="row">

                                        <div class="col-md-8">
                                            <div class="mb-3">
                                                <select id="parse_countries" multiple="multiple" class="select2 form-control" ></select>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <input type="text" class="form-control" placeholder="wallets..." id="parse_wallets" />
                                            </div>
                                        </div>

                                    </div>

                                    <div class="row">

                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <input type="text" class="form-control" id="parse_date" name="daterange" value="" />
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <input type="text" class="form-control" placeholder="note..." id="parse_note" />
                                            </div>
                                        </div>

                                        <div class="col-md-4 d-flex align-items-center">
                                            <div class="mb-3">
                                                <div class="form-check form-check-inline" style="margin-right: 0.5rem;">
                                                    <input class="form-check-input" type="checkbox" id="parse_steam">
                                                    <label class="form-check-label" for="parse_steam">
                                                        <img src="assets/images/summary/soft/Steam.png" height="20" title="Steam">
                                                    </label>
                                                </div>

                                                <div class="form-check form-check-inline" style="margin-right: 0.5rem;">
                                                    <input class="form-check-input" type="checkbox" id="parse_tox">
                                                    <label class="form-check-label" for="parse_tox">
                                                        <img src="assets/images/summary/soft/Tox.png" height="20" title="Tox">
                                                    </label>
                                                </div>

                                                <div class="form-check form-check-inline" style="margin-right: 0.5rem;">
                                                    <input class="form-check-input" type="checkbox" id="parse_outlook">
                                                    <label class="form-check-label" for="parse_outlook">
                                                        <img src="assets/images/summary/soft/Outlook.png" height="20" title="Outlook">
                                                    </label>
                                                </div>

                                                <div class="form-check form-check-inline" style="margin-right: 0.5rem;">
                                                    <input class="form-check-input" type="checkbox" id="parse_discord">
                                                    <label class="form-check-label" for="parse_discord">
                                                        <img src="assets/images/summary/soft/Discord.png" height="20" title="Discord">
                                                    </label>
                                                </div>

                                                <div class="form-check form-check-inline" style="margin-right: 0.5rem;">
                                                    <input class="form-check-input" type="checkbox" id="parse_telegram">
                                                    <label class="form-check-label" for="parse_telegram">
                                                        <img src="assets/images/summary/soft/Telegram.png" height="20" title="Telegram">
                                                    </label>
                                                </div>

                                                <div class="form-check form-check-inline" style="margin-right: 0.5rem;">
                                                    <input class="form-check-input" type="checkbox" id="parse_pidgin">
                                                    <label class="form-check-label" for="parse_pidgin">
                                                        <img src="assets/images/summary/soft/Pidgin.png" height="20" title="Pidgin">
                                                    </label>
                                                </div>

                                            </div>
                                        </div>

                                    </div>

                                    <div class="row">

                                        <div class="col-md-8 d-flex align-items-center">
                                            <div class="mb-3">
                                                <div class="form-check form-check-inline" style="margin-right: 0.5rem;">
                                                    <input class="form-check-input" type="checkbox" id="parse_no_empty">
                                                    <label class="form-check-label" for="parse_no_empty">No Empty</label>
                                                </div>

                                                <div class="form-check form-check-inline" style="margin-right: 0.5rem;">
                                                    <input class="form-check-input" type="checkbox" id="parse_repeated">
                                                    <label class="form-check-label" for="parse_repeated">Unique</label>
                                                </div>

                                                <div class="form-check form-check-inline" style="margin-right: 0.5rem;">
                                                    <input class="form-check-input" type="checkbox" id="parse_with_wallets">
                                                    <label class="form-check-label" for="parse_with_wallets">With Wallets</label>
                                                </div>

                                                <div class="form-check form-check-inline" style="margin-right: 0.5rem;">
                                                    <input class="form-check-input" type="checkbox" id="parse_with_mnemonic">
                                                    <label class="form-check-label" for="parse_with_mnemonic">With Mnemonic</label>
                                                </div>

                                                <div class="form-check form-check-inline" style="margin-right: 0.5rem;">
                                                    <input class="form-check-input" type="checkbox" id="parse_favorites">
                                                    <label class="form-check-label" for="parse_favorites">In Favorites</label>
                                                </div>

                                            </div>
                                        </div>

                                        <div class="col-md-4 d-flex align-items-center">
                                            <div class="mb-3">
                                                <div class="form-check form-check-inline" style="margin-right: 0.5rem;">
                                                    <input class="form-check-input" type="checkbox" id="parse_no_download">
                                                    <label class="form-check-label" for="parse_no_download">No Download</label>
                                                </div>

                                                <div class="form-check form-check-inline" style="margin-right: 0.5rem;">
                                                    <input class="form-check-input" type="checkbox" id="parse_download">
                                                    <label class="form-check-label" for="parse_download">Downloaded</label>
                                                </div>
                                            </div>
                                        </div>

                                    </div>

                                    <div class="row">

                                        <div class="col-md-8 d-flex align-items-center">
                                            <div class="mb-3">
                                                <div class="form-check form-check-inline" style="margin-right: 0.5rem;">
                                                    <input class="form-check-input" type="checkbox" id="select_all" value="option1">
                                                    <label class="form-check-label" for="select_all"">Select All</label>
                                                </div>

                                                <span class="text-black-darker" style="margin-right:8px;">with selected: </span>
                                                <button type="button" class="btn btn-sm btn-danger" onclick="DeleteSelected();">Delete</button>&nbsp;&nbsp;&nbsp;
                                                <button type="button" class="btn btn-sm btn-primary" onclick="DownloadSelected();">Download</button>
                                            </div>
                                        </div>

                                        <div class="col-md-4 d-flex justify-content-end gap-6">
                                            <button id="search_button" type="submit" class="btn btn-primary">Search</button>
                                        </div>
                                    </div>

                                </div>
                        </div>
                    </div>

                    <table id="logs_table" class="table text-nowrap customize-table mb-0 align-middle">
                        <thead class="text-dark fs-4">
                            <tr>
                                <th>
                                    <h6 class="fs-4 fw-semibold mb-0">ID</h6>
                                </th>
                                <th>
                                    <h6 class="fs-4 fw-semibold mb-0">Summary</h6>
                                </th>
                                <th>
                                    <h6 class="fs-4 fw-semibold mb-0">Network</h6>
                                </th>
                                <th>
                                    <h6 class="fs-4 fw-semibold mb-0">Date</h6>
                                </th>
                                <th>
                                    <h6 class="fs-4 fw-semibold mb-0">Note</h6>
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
                    <br>
                </div>
            </div>
        </div>

        <!-- Modal Passwords -->
        <div class="modal fade" id="modal_passwords" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-2">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header d-flex align-items-center">
                        <h4 class="modal-title" id="passwords_modal_header">Passwords at log #?</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    
                    <div class="modal-body-passwords">


                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn bg-danger-subtle text-danger waves-effect text-start" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Screenshot -->
        <div class="modal fade" id="modal_screenshot" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-2">
            <div class="modal-dialog modal-fullscreen">
                <div class="modal-content">
                    <div class="modal-header d-flex align-items-center">
                        <h4 class="modal-title" id="screenshot_modal_header">Screenshot at log #?</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    
                    <div class="modal-body-screenshot">


                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn bg-danger-subtle text-danger waves-effect text-start" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Log Info -->
        <div class="modal fade" id="modal_about" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-2">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header d-flex align-items-center">
                        <h4 class="modal-title" id="about_modal_header">About Log #?</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    
                    <div class="modal-body-about">

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn bg-danger-subtle text-danger waves-effect text-start" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Delete Logs -->
        <div id="modal_delete_logs" class="modal fade" tabindex="-1" aria-labelledby="danger-header-modalLabel" style="display: none;" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable modal-lg">
                <div class="modal-content">
                    <div class="modal-header modal-colored-header bg-danger text-white d-flex align-items-center">
                        <h4 class="modal-title text-white" id="myModalLabel">Delete Logs</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body-delete-logs"></div>
                    <div class="modal-footer">
                        <button type="button" class="btn bg-light" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn bg-danger-subtle text-danger ">Delete</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Delete One Log -->
        <div class="modal fade" id="modal_delete_log" tabindex="-1" aria-labelledby="mySmallModalLabel" style="display: none;" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable modal-lg">
                <div class="modal-content">
                    <div class="modal-header modal-colored-header bg-danger text-white">
                        <h4 class="modal-title-delete-log" id="modal_delete_log_header"></h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body-delete-log">

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn bg-light" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn bg-danger-subtle text-danger ">Delete</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Download Logs -->
        <div class="modal fade" id="modal_download_logs" tabindex="-1" aria-labelledby="mySmallModalLabel" style="display: none;" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable modal-lg">
                <div class="modal-content">
                    <div class="modal-header modal-colored-header bg-dark">
                        <h4 class="modal-title text-white" id="modal_download_logs_header">Logs Downloader</h4>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body-download-logs">
                        <div class="card w-100">
                            <div class="card-body d-flex align-items-center">
                                <div id="download_spinner" class="spinner-border" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <strong id="download_status" style="margin-left: 10px;"></strong>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn bg-light" data-bs-dismiss="modal">Close</button>
                        <a id="modal_download_link" href="#" class="btn btn-primary" style="display:none;">Download</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Downloads List -->
        <div class="modal fade" id="modal_downloads" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-2">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header d-flex align-items-center">
                        <h4 class="modal-title" id="downloads_modal_header">Downloads List</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    
                    <div class="modal-body-downloads">
                        <table  id="downloadsTable" class="table text-nowrap mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th><h6 class="fs-4 fw-semibold mb-0">ID</h6></th>
                                    <th><h6 class="fs-4 fw-semibold mb-0">Token</h6></th>
                                    <th><h6 class="fs-4 fw-semibold mb-0">Status</h6></th>
                                    <th><h6 class="fs-4 fw-semibold mb-0">Logs Count</h6></th>
                                    <th><h6 class="fs-4 fw-semibold mb-0">Created</h6></th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
    
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn bg-danger-subtle text-danger waves-effect text-start" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Downloads List -->
        <div class="modal fade" id="block_log_modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-2">
            <div class="modal-dialog modal-dialog-scrollable modal-lg">
                <div class="modal-content">
                    <div class="modal-header modal-colored-header bg-danger text-white">
                        <h4 class="modal-title text-white" id="myLargeModalLabel">Block Log HWID and IP</h4>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    
                    <div class="modal-body">

                        <div class="card">
                            <div class="card-body">
                                <h4>Are you sure you want to lock these parameters?</h4>

                                <table class="table text-nowrap mb-0 align-middle">
                                    <thead class="text-dark fs-4">
                                        <tr>
                                            <th>
                                                <h6 class="fs-4 fw-semibold mb-0">IP</h6>
                                            </th>
                                            <th>
                                                <h6 class="fs-4 fw-semibold mb-0">HWID</h6>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <h6 class="fs-4 fw-semibold mb-0" id="block_rule_ip">?</h6>
                                            </td>
                                            <td>
                                                <h6 class="fs-4 fw-semibold mb-0" id="block_rule_hwid">?</h6>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <a id="modal_block_button" href="#" class="btn btn-danger" >Block</a>
                            <button type="button" class="btn bg-light-subtle text-dark  waves-effect text-start" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Mnemonic List -->
        <div class="modal fade" id="modal_mnemonic" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-2">
            <div class="modal-dialog modal-fullscreen">
                <div class="modal-content">
                    <div class="modal-header d-flex align-items-center">
                        <h4 class="modal-title" id="mnemonic_modal_header">Mnemonic List from log #?</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                        
                    <div class="modal-body-mnemonic">

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn bg-danger-subtle text-danger waves-effect text-start" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

    <div class="dark-transparent sidebartoggler"></div>

    <script>var server_time = "<?php echo (new DateTime())->format('Y-m-d H:i:s'); ?>";</script>
    
    <?php include_once 'app/pages/footer.php'; ?>

    <script src="assets/libs/filesaver/FileSaver.min.js"></script>

    <!-- logs worker -->
    <script src="assets/js/logs.js"></script>

    <script src="assets/libs/moment/moment.min.js"></script>
    <script src="assets/libs/daterangepicker/daterangepicker.js"></script>
    <script src="assets/libs/daterangepicker/daterangepicker-data.js"></script>
</body>
</html>