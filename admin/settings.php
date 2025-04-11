<?php

include "../config.php";
include_once "app/functions.php";
include_once "app/managers/AccountManager.php";

session_start();

$link = ConnectDB();

$accountManager = new AccountManager($link);
$accountManager->CheckAuth();

$page_name = "Account Settings";

$is_admin = ($_SESSION['user_group'] === 'Administrator') ? 1 : 0;

include_once 'app/pages/header.php';

?>
<style>
/* QR & Key Container */
.qr-section {
    display: flex;
    align-items: center;
    gap: 16px;
}
.qr-image {
    width: 180px;
    height: 180px;
    border: 1px solid #e1e3e6;
    background-color: white;
    border-radius: 4px;
}
.key-block {
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.key-title {
    font-size: 14px;
    color: #6b6b6b;
}
.key-value {
    font-weight: bold;
    font-size: 16px;
    letter-spacing: 1px;
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
                    <button class="nav-link position-relative rounded-0 d-flex align-items-center justify-content-center bg-transparent fs-3 py-3 active" id="pills-account-tab" data-bs-toggle="pill" data-bs-target="#pills-account" type="button" role="tab" aria-controls="pills-account" aria-selected="true">
                        <svg class="me-2 fs-6" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="9" r="3"/><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" d="M17.97 20c-.16-2.892-1.045-5-5.97-5s-5.81 2.108-5.97 5"/></g></svg>
                        <span class="d-none d-md-block">Account</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link position-relative rounded-0 d-flex align-items-center justify-content-center bg-transparent fs-3 py-3" id="pills-security-tab" data-bs-toggle="pill" data-bs-target="#pills-security" type="button" role="tab" aria-controls="pills-security" aria-selected="false" tabindex="-1">
                        <svg class="me-2 fs-6" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="1.5"><path d="M2 16c0-2.828 0-4.243.879-5.121C3.757 10 5.172 10 8 10h8c2.828 0 4.243 0 5.121.879C22 11.757 22 13.172 22 16s0 4.243-.879 5.121C20.243 22 18.828 22 16 22H8c-2.828 0-4.243 0-5.121-.879C2 20.243 2 18.828 2 16Z"/><path stroke-linecap="round" d="M12 14v4m-6-8V8a6 6 0 1 1 12 0v2"/></g></svg>
                        <span class="d-none d-md-block">Security</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link position-relative rounded-0 d-flex align-items-center justify-content-center bg-transparent fs-3 py-3" id="pills-security-tab" data-bs-toggle="pill" data-bs-target="#pills-notifications" type="button" role="tab" aria-controls="pills-security" aria-selected="false" tabindex="-1">
                        <svg class="me-2 fs-6" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="M17.094 7.146c.593-.215.888-.292 1.05-.32q.002.08-.002.122c-.232 2.444-1.251 8.457-1.775 11.255c-.122.655-.216.967-.85.595c-.416-.245-.792-.553-1.196-.817c-1.325-.869-3.221-2.162-3.065-2.084c-1.304-.86-.758-1.386-.03-2.088c.117-.113.24-.231.36-.356c.054-.056.317-.3.687-.645c1.188-1.104 3.484-3.239 3.542-3.486c.01-.04.018-.192-.071-.271c-.09-.08-.223-.053-.318-.031q-.203.046-6.474 4.279q-.918.63-1.664.614l.005.003c-.655-.231-1.308-.43-1.964-.63a66 66 0 0 1-1.3-.405l-.308-.098c4.527-1.972 7.542-3.27 9.053-3.899c2.194-.913 3.496-1.438 4.32-1.738m2.423-1.928a1.8 1.8 0 0 0-.726-.346c-.2-.048-.39-.063-.533-.06c-.477.008-.988.143-1.846.454c-.875.318-2.219.862-4.406 1.771Q9.691 8 2.804 11.001c-.404.161-.773.344-1.065.56c-.27.201-.647.56-.716 1.11c-.052.416.069.8.315 1.103c.214.263.488.423.697.524c.31.15.728.281 1.095.396c.573.18 1.144.363 1.719.539c1.778.544 3.242.992 4.852 2.054c1.181.778 2.34 1.59 3.523 2.366c.432.283.835.608 1.28.87c.488.285 1.106.546 1.86.477c1.138-.105 1.73-1.152 1.97-2.43c.521-2.79 1.557-8.886 1.8-11.432a3.8 3.8 0 0 0-.037-.885a1.66 1.66 0 0 0-.58-1.035"/></svg>
                        <span class="d-none d-md-block">Notifications</span>
                    </button>
                </li>
            </ul>
            
            <div class="card-body">
                <div class="tab-content" id="pills-tabContent">
                    <div class="tab-pane fade active show" id="pills-account" role="tabpanel" aria-labelledby="pills-account-tab" tabindex="0">
                        <div class="row justify-content-center"> 
                            <div class="col-lg-8 d-flex align-items-stretch">
                                <div class="card w-100 border position-relative overflow-hidden">
                                    <div class="card-body p-4">
                                        <h4 class="card-title">Change Password</h4>
                                        <p class="card-subtitle mb-4">To change your password please confirm here</p>
                                        <div class="mb-3">
                                            <label for="edit_old_password" class="form-label">Current Password</label>
                                            <input type="password" class="form-control" id="edit_old_password" >
                                        </div>
                                        <div class="mb-3">
                                            <label for="edit_new_password" class="form-label">New Password</label>
                                            <input type="password" class="form-control" id="edit_new_password" >
                                        </div>
                                        <div>
                                            <label for="edit_new_password_repeat" class="form-label">Confirm Password</label>
                                            <input type="password" class="form-control" id="edit_new_password_repeat" >
                                        </div>
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="d-flex align-items-center justify-content-end mt-4 gap-6">
                                                    <button class="btn btn-primary" onclick="ChangePassword()">Save</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="tab-pane fade" id="pills-security" role="tabpanel" aria-labelledby="pills-security-tab" tabindex="0">
                        <div class="row">

                            <div class="col-lg-8 d-flex align-items-stretch">
                                <div class="card w-100 border position-relative overflow-hidden">
                                    <div class="card-body p-4">
                                        <h4 class="card-title">Session List</h4>
                                        <p class="card-subtitle mb-4">Displays each login session with details on last recorded activity.</p>

                                        <table id="sessions_table" class="table text-nowrap customize-table mb-0 align-middle" >
                                            <thead class="text-dark fs-4">
                                                <tr>
                                                    <th>
                                                        <h6 class="fs-4 fw-semibold mb-0">Browser</h6>
                                                    </th>
                                                    <th>
                                                        <h6 class="fs-4 fw-semibold mb-0">Status</h6>
                                                    </th>
                                                    <th>
                                                        <h6 class="fs-4 fw-semibold mb-0">Created</h6>
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

                            <div class="col-lg-4">
                                <div class="card border shadow-none">
                                    <div class="card-body p-4">
                                        <h4 class="card-title mb-3">Two-factor Authentication</h4>
                                        <div class="d-flex align-items-center justify-content-between pb-7">
                                            <p class="card-subtitle mb-0">Enables two-factor authentication (2FA), adding an extra security layer by requiring a second verification step during login.</p>
                                        </div>
                                        <div class="d-flex align-items-center justify-content-between py-3 border-top">
                                            <div>
                                                <h5 class="fs-4 fw-semibold mb-0">Authentication App</h5>
                                                <p class="mb-0">Google auth app like</p>
                                            </div>
                                            <button id="button_enable_twofa" class="btn btn-primary" onclick="OpenTwofaModal()">Turn On</button>
                                            <button id="button_disable_twofa" class="btn btn-danger" onclick="DisableTwofaModal()">Disable</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="tab-pane fade" id="pills-notifications" role="tabpanel" aria-labelledby="pills-notifications-tab" tabindex="0">
                        <div class="row"> 
                            <div class="col-lg-8 d-flex align-items-stretch">
                                <div class="card w-100 border position-relative overflow-hidden">
                                    <div class="card-body p-4">
                                        <h4 class="card-title">Notification Preferences</h4>
                                        <p class="card-subtitle mb-4">Select the notificaitons ou would like to receive via Telegram.</p>

                                        <div>
                                            <h5 class="fs-4 fw-semibold mb-4">Account Details</h5>

                                            <div class="d-flex align-items-center justify-content-between mb-4">
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="text-bg-light rounded-1 p-6 d-flex align-items-center justify-content-center">
                                                        <svg class="text-dark d-block fs-7" xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24"><path fill="currentColor" d="M4 15h2v5h12V4H6v5H4V3a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1v18a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1zm6-4V8l5 4l-5 4v-3H2v-2z"/></svg>
                                                    </div>
                                                    <div>
                                                    <h5 class="fs-4 fw-semibold">Successful login</h5>
                                                    <p class="mb-0">Receive notifications about successful account login</p>
                                                    </div>
                                                </div>
                                                <div class="form-check form-switch mb-0">
                                                    <input class="form-check-input" type="checkbox" role="switch" id="notify_logins">
                                                </div>
                                            </div>

                                            <div class="d-flex align-items-center justify-content-between mb-4">
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="text-bg-light rounded-1 p-6 d-flex align-items-center justify-content-center">
                                                        <svg class="text-dark d-block fs-7" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" fill-rule="evenodd" d="M9.944 1.25h5.59c1.139 0 2.04 0 2.766.062c.743.063 1.37.195 1.94.499a4.75 4.75 0 0 1 1.95 1.95c.303.569.435 1.196.498 1.939c.062.725.062 1.627.062 2.766V8.5a.75.75 0 0 1-1.5 0c0-1.18 0-2.018-.056-2.673c-.055-.646-.16-1.044-.328-1.359a3.25 3.25 0 0 0-1.334-1.334c-.315-.168-.713-.273-1.359-.328c-.655-.055-1.493-.056-2.673-.056H10c-1.907 0-3.261.002-4.29.14c-1.005.135-1.585.389-2.008.812S3.025 4.705 2.89 5.71c-.138 1.029-.14 2.383-.14 4.29v1q-.001.687.004 1.25H11a.75.75 0 0 1 0 1.5H2.807q.018.234.046.442c.099.734.28 1.122.556 1.399c.277.277.665.457 1.4.556c.754.101 1.756.103 3.191.103h3a.75.75 0 0 1 .75.75v5a.75.75 0 0 1-.75.75H8a.75.75 0 0 1 0-1.5h2.25v-3.5H7.945c-1.367 0-2.47 0-3.337-.116c-.9-.122-1.658-.38-2.26-.982s-.86-1.36-.981-2.26c-.049-.36-.077-.762-.094-1.206a.75.75 0 0 1-.01-.327c-.013-.541-.013-1.142-.013-1.804V9.944c0-1.838 0-3.294.153-4.433c.158-1.172.49-2.121 1.238-2.87c.749-.748 1.698-1.08 2.87-1.238c1.14-.153 2.595-.153 4.433-.153m8.004 9h.104c.899 0 1.648 0 2.242.08c.628.084 1.195.27 1.65.726c.456.455.642 1.022.726 1.65c.08.594.08 1.344.08 2.242v3.104c0 .899 0 1.648-.08 2.242c-.084.628-.27 1.195-.726 1.65c-.455.456-1.022.642-1.65.726c-.594.08-1.343.08-2.242.08h-.104c-.899 0-1.648 0-2.242-.08c-.628-.084-1.195-.27-1.65-.726c-.456-.455-.642-1.022-.726-1.65c-.08-.594-.08-1.343-.08-2.242v-3.104c0-.899 0-1.648.08-2.242c.084-.628.27-1.195.726-1.65c.455-.456 1.022-.642 1.65-.726c.594-.08 1.343-.08 2.242-.08m-2.043 1.566c-.461.063-.659.17-.789.3s-.237.328-.3.79c-.064.482-.066 1.13-.066 2.094v3c0 .964.002 1.612.066 2.095c.063.461.17.659.3.789s.328.237.79.3c.482.064 1.13.066 2.094.066s1.612-.002 2.095-.067c.461-.062.659-.169.789-.3s.237-.327.3-.788c.064-.483.066-1.131.066-2.095v-3c0-.964-.002-1.612-.067-2.095c-.062-.461-.169-.659-.3-.789s-.327-.237-.788-.3c-.483-.064-1.131-.066-2.095-.066s-1.612.002-2.095.066M16.25 20a.75.75 0 0 1 .75-.75h2a.75.75 0 0 1 0 1.5h-2a.75.75 0 0 1-.75-.75" clip-rule="evenodd"/></svg>
                                                    </div>
                                                    <div>
                                                    <h5 class="fs-4 fw-semibold">Changing two-factor authentication settings</h5>
                                                    <p class="mb-0">Receive notifications when two-factor authentication is enabled or disabled</p>
                                                    </div>
                                                </div>
                                                <div class="form-check form-switch mb-0">
                                                    <input class="form-check-input" type="checkbox" role="switch" id="notify_twofa_change">
                                                </div>
                                            </div>

                                            <div class="d-flex align-items-center justify-content-between mb-4">
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="text-bg-light rounded-1 p-6 d-flex align-items-center justify-content-center">
                                                        <svg class="text-dark d-block fs-7" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><g fill="none"><path stroke="currentColor" stroke-linecap="round" stroke-width="1.5" d="M12 4h-2C6.229 4 4.343 4 3.172 5.172S2 8.229 2 12s0 5.657 1.172 6.828S6.229 20 10 20h2m3-16c3.114.01 4.765.108 5.828 1.172C22 6.343 22 8.229 22 12s0 5.657-1.172 6.828C19.765 19.892 18.114 19.99 15 20"/><path fill="currentColor" d="M9 12a1 1 0 1 1-2 0a1 1 0 0 1 2 0m4 0a1 1 0 1 1-2 0a1 1 0 0 1 2 0"/><path stroke="currentColor" stroke-linecap="round" stroke-width="1.5" d="M15 2v20"/></g></svg>
                                                    </div>
                                                    <div>
                                                    <h5 class="fs-4 fw-semibold">Password change</h5>
                                                    <p class="mb-0">Notify about password change</p>
                                                    </div>
                                                </div>
                                                <div class="form-check form-switch mb-0">
                                                    <input class="form-check-input" type="checkbox" role="switch" id="notify_password_change">
                                                </div>
                                            </div>

                                            <div class="d-flex border-top pt-4 mt-4"></div>

                                            <h5 class="fs-4 fw-semibold mb-4">Logs Details</h5>

                                            <div class="d-flex align-items-center justify-content-between mb-4">
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="text-bg-light rounded-1 p-6 d-flex align-items-center justify-content-center">
                                                        <svg class="text-dark d-block fs-7" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="M2 20V4h8l2 2h10v14zm2-2h16V8h-8.825l-2-2H4zm0 0V6zm10-2h2v-2h2v-2h-2v-2h-2v2h-2v2h2z"/></svg>
                                                    </div>
                                                    <div>
                                                    <h5 class="fs-4 fw-semibold">Notify Incoming Logs</h5>
                                                    <p class="mb-0">Notify about logs received on the server</p>
                                                    </div>
                                                </div>
                                                <div class="form-check form-switch mb-0">
                                                    <input class="form-check-input" type="checkbox" role="switch" id="notify_all_logs">
                                                </div>
                                            </div>

                                            <div class="d-flex align-items-center justify-content-between mb-4">
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="text-bg-light rounded-1 p-6 d-flex align-items-center justify-content-center">
                                                        <svg class="text-dark d-block fs-7" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="M9 21v-2H6v-2h2V7H6V5h3V3h2v2h2V3h2v2.125q1.3.35 2.15 1.413T18 9q0 .725-.25 1.388t-.7 1.187q.875.525 1.413 1.425T19 15q0 1.65-1.175 2.825T15 19v2h-2v-2h-2v2zm1-10h4q.825 0 1.413-.587T16 9t-.587-1.412T14 7h-4zm0 6h5q.825 0 1.413-.587T17 15t-.587-1.412T15 13h-5z"/></svg>
                                                    </div>
                                                    <div>
                                                    <h5 class="fs-4 fw-semibold">Only crypto logs</h5>
                                                    <p class="mb-0">Notify only about crypto logs</p>
                                                    </div>
                                                </div>
                                                <div class="form-check form-switch mb-0">
                                                    <input class="form-check-input" type="checkbox" role="switch" id="notify_only_crypto_logs">
                                                </div>
                                            </div>

                                            <div class="d-flex align-items-center justify-content-between mb-4">
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="text-bg-light rounded-1 p-6 d-flex align-items-center justify-content-center">
                                                        <svg class="text-dark d-block fs-7" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="M5 21q-.825 0-1.412-.587T3 19V5q0-.825.588-1.412T5 3h14q.825 0 1.413.588T21 5v14q0 .825-.587 1.413T19 21zm0-2h14V5H5zm1-2h12l-3.75-5l-3 4L9 13zm-1 2V5z"/></svg>
                                                    </div>
                                                    <div>
                                                    <h5 class="fs-4 fw-semibold">Send Screenshot</h5>
                                                    <p class="mb-0">Send a screenshot in message</p>
                                                    </div>
                                                </div>
                                                <div class="form-check form-switch mb-0">
                                                    <input class="form-check-input" type="checkbox" role="switch" id="notify_with_screen">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="d-flex align-items-center justify-content-end mt-4 gap-6">
                                                    <button class="btn btn-primary" onclick="ChangeTelegramSettings()">Save</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-4">
                                <div class="card border shadow-none">
                                    <div class="card-body p-4" style="margin-bottom: -14px;">
                                        <h4 class="card-title mb-3">Linking your Telegram account</h4>
                                        <div class="d-flex align-items-center justify-content-between pb-7">
                                            <p class="card-subtitle mb-0">To receive notifications, you need to link your Telegram account.</p>
                                        </div>
                                        <div class="d-flex align-items-center justify-content-between py-3 border-top">
                                            <button id="button_enable_telegram" class="btn btn-primary" onclick="ConnectTelegram()">Connect</button>
                                            <button id="button_disable_telegram" class="btn btn-danger" onclick="DisconnectTelegram()">Disable</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
</div>
</div>

<!-- Modal Enable 2FA -->
<div class="modal fade" id="create_twofa" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-2">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header d-flex align-items-center">
                <h4 class="modal-title" id="modal_create_rule_header">Two-factor Authentication</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body">
                <div class="card">
                    <div>
                    
                        <div class="form-body">
                            <div class="row">
                                <h4>Step 1</h4>
                                <p>Install an authentication app to generate codes, such as Google Authenticator or Authy for <a href="https://apps.apple.com/us/app/google-authenticator/id388497605" >iOS</a> or <a href="https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2">Android</a>.</p>

                                <div class="d-flex border-top pt-4 mt-4"></div>

                                <h4>Step 2</h4>
                                <p>Scan the QR code or manually enter the secret key in your authentication app.</p>

                                <div class="qr-section">
                                    <div id="qr_code_block" class="qr-image"></div>
                                    <div class="key-block">
                                        <div class="key-title">Your Key</div>
                                        <div class="key-value" id="qr_secret">???? ???? ???? ????</div>
                                    </div>
                                </div>

                                <div class="d-flex border-top pt-4 mt-4"></div>

                                <h4>Step 3</h4>
                                <p>Enter the 6-digit confirmation code from your authentication app.</p>

                                <div class="col-md-7">
                                    <div class="input-group mb-3">
                                        <input id="qr_onetime_code" type="number" class="form-control">
                                        <button class="btn btn-primary" type="button" onclick="ConfirmTwofaCode()">Confirm</button>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>        
</div>

<!-- Modal Disable 2FA -->
<div class="modal fade" id="disable_twofa" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content modal-filled bg-danger">
            <div class="modal-header d-flex align-items-center">
                <h4 class="modal-title text-white" id="myModalLabel">Disable 2FA</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h5 class="mt-0 text-white">Are you sure you want to disable two-factor authentication?</h5>
                <p class="text-white">Enter the 6-digit confirmation code from your authentication app.</p>

                <div class="col-md-12">
                    <div class="input-group mb-3">
                        <input id="qr_onetime_code_disable" type="text" class="form-control">
                        <button class="btn btn-primary" type="button" onclick="DisableTwofa()">Disable</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Connect Telegram -->
<div class="modal fade" id="connect_telegram" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content modal-filled bg-success-subtle">
            <div class="modal-body p-4">
                <div class="text-center text-dark">
                    <svg class="fs-7" fill="#24A1DE" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M20 12a8 8 0 1 1-16 0a8 8 0 0 1 16 0m-8 10c5.523 0 10-4.477 10-10S17.523 2 12 2S2 6.477 2 12s4.477 10 10 10m.358-12.618q-1.458.607-5.831 2.513q-.711.282-.744.552c-.038.304.343.424.862.587l.218.07c.51.166 1.198.36 1.555.368q.486.01 1.084-.4q4.086-2.76 4.218-2.789c.063-.014.149-.032.207.02c.059.052.053.15.047.177c-.038.161-1.534 1.552-2.308 2.271q-.344.324-.683.653c-.474.457-.83.8.02 1.36c.861.568 1.73 1.134 2.57 1.733c.414.296.786.56 1.246.519c.267-.025.543-.276.683-1.026c.332-1.77.983-5.608 1.133-7.19a1.8 1.8 0 0 0-.017-.393a.42.42 0 0 0-.142-.27c-.12-.098-.305-.118-.387-.117c-.376.007-.953.207-3.73 1.362"/></svg>
                    <h4 class="mt-2">Connecting...</h4>
                    <br>
                    <div id="download_spinner" class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <br>
                    <br>
                    <p>If nothing happens, press button</p>
                    <a id="link_telegram" href="" target="_blank" class="btn btn-light my-2 text-success">Open manually</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Disable Telegram -->
<div class="modal fade" id="disable_telegram" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header modal-colored-header bg-danger text-white">
                <h4 class="modal-title text-white" id="danger-header-modalLabel">Disable Telegram</h4>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h5>Are you sure you want to unlink telegram account?</h5>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <button type="button" onclick="DisableTelegram()" class="btn bg-danger-subtle text-danger">Disable</button>
            </div>
        </div>
    </div>
</div>

<div class="dark-transparent sidebartoggler"></div>

<script>var server_time = "<?php echo (new DateTime())->format('Y-m-d H:i:s'); ?>";</script>

<?php include_once 'app/pages/footer.php'; ?>

<script src="assets/js/settings.js"></script>
</body>
</html>