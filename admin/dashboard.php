<?php

include "../config.php";
include_once "app/functions.php";
include_once "app/managers/AccountManager.php";

session_start();

$link = ConnectDB();

$accountManager = new AccountManager($link);
$accountManager->CheckAuth();

$is_admin = ($_SESSION['user_group'] === 'Administrator') ? 1 : 0;

$page_name = "Dashboard";

include_once 'app/pages/header.php';

?>
<style>
      #chart {
      width: 100%;
      height: 200px;
    }

    /* Нижняя часть карточки (доп. данные) */
    .card-footer {
      display: flex;
      justify-content: space-between;
      margin-top: 20px;
    }
    .footer-item {
      display: flex;
      flex-direction: column;
      font-size: 14px;
    }
    .footer-item .label {
      color: #7d7c83;
    }
    .footer-item .value {
      font-weight: bold;
      color: #2f2e41;
      font-size: 16px;
    }
</style>

<div class="body-wrapper">
    <div class="container-fluid">
        
        <div class="row">
            
            <div class="col-7">
                <div class="card bg-primary-subtle">
                    <div class="card-body">
                        <div class="hstack align-items-center gap-3 mb-4">
                            <div>
                                <p class="mb-1 text-dark-light">Logs Last 7 days</p>
                                <h4 class="mb-0 fw-bolder" id="total_logs_two_days">?</h5>
                            </div>
                        </div>
                        <div style="height: 122px;">
                            <div id="chart-last-48-hours"></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div id="disk_usage_block" class="col-lg-2 col-xxl-5 col-2" >
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title fw-semibold">Disk Usage</h5>
                        <p class="card-subtitle mb-0 lh-base">Disk usage statistics</p>
                        
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="vstack gap-9 mt-2">
                                    <div class="hstack align-items-center gap-3">
                                        <div class="d-flex align-items-center justify-content-center round-48 rounded bg-danger-subtle flex-shrink-0">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" fill-rule="evenodd" d="M4.172 3.172C3 4.343 3 6.229 3 10v4c0 3.771 0 5.657 1.172 6.828S7.229 22 11 22h2c3.771 0 5.657 0 6.828-1.172S21 17.771 21 14v-4c0-3.771 0-5.657-1.172-6.828S16.771 2 13 2h-1v2h1.5c.471 0 .707 0 .854.146c.146.147.146.383.146.854s0 .707-.146.854C14.207 6 13.97 6 13.5 6H12v2h1.5c.471 0 .707 0 .854.146c.146.147.146.383.146.854s0 .707-.146.854C14.207 10 13.97 10 13.5 10H13c-.471 0-.707 0-.854-.146C12 9.707 12 9.47 12 9V8h-1.5c-.471 0-.707 0-.854-.146C9.5 7.707 9.5 7.47 9.5 7s0-.707.146-.854C9.793 6 10.03 6 10.5 6H12V4h-1.5c-.471 0-.707 0-.854-.146C9.5 3.707 9.5 3.47 9.5 3v-.997c-2.794.02-4.324.164-5.328 1.169M9.5 12.875V13a2.5 2.5 0 0 0 5 0v-.125a.875.875 0 0 0-.875-.875h-3.25a.875.875 0 0 0-.875.875" clip-rule="evenodd"/></svg>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 text-nowrap" id="used_space">? KB</h6>
                                            <span>Used Space</span>
                                        </div>
                                    </div>
                                    <div class="hstack align-items-center gap-3">
                                        <div class="d-flex align-items-center justify-content-center round-48 rounded bg-primary-subtle">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" d="M18 10h-5"/><path d="M2 6.95c0-.883 0-1.324.07-1.692A4 4 0 0 1 5.257 2.07C5.626 2 6.068 2 6.95 2c.386 0 .58 0 .766.017a4 4 0 0 1 2.18.904c.144.119.28.255.554.529L11 4c.816.816 1.224 1.224 1.712 1.495a4 4 0 0 0 .848.352C14.098 6 14.675 6 15.828 6h.374c2.632 0 3.949 0 4.804.77q.119.105.224.224c.77.855.77 2.172.77 4.804V14c0 3.771 0 5.657-1.172 6.828S17.771 22 14 22h-4c-3.771 0-5.657 0-6.828-1.172S2 17.771 2 14z"/></g></svg>
                                        </div>
                                        <div>
                                            <h6 class="mb-0" id="free_space">? KB</h6>
                                            <span>Free Space</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-center mt-sm-n7">
                                    <canvas id="diskUsageChart" style="max-width: 140px; max-height: 140px;"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="build_block" class="col-lg-5">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <span class="d-flex align-items-center" id="last_compile">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="1.5"><path d="M17 15h-5m-5-5l.234.195c1.282 1.068 1.923 1.602 1.923 2.305s-.64 1.237-1.923 2.305L7 15"/><path d="M22 12c0 4.714 0 7.071-1.465 8.535C19.072 22 16.714 22 12 22s-7.071 0-8.536-1.465C2 19.072 2 16.714 2 12s0-7.071 1.464-8.536C4.93 2 7.286 2 12 2s7.071 0 8.535 1.464c.974.974 1.3 2.343 1.41 4.536"/></g></svg>
                                &nbsp;&nbsp;Compiled: ?</span>
                        </div>
                        <h4 class="card-title">Download build</h4>
                        <p class="mb-0 card-subtitle" id="build_version">Actual build version: v2.0.0</p>

                        <br>
                        <p class="mb-0 card-subtitle" id="build_password">Password for archive:</p>
                        
                        <br>
                        <div class="text-end">
                        <button class="btn btn-primary" id="download_button">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24">
                                            <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 18a3.5 3.5 0 0 0 0-7h-1A5 4.5 0 0 0 7 9a4.6 4.4 0 0 0-2.1 8.4M12 13v9m-3-3l3 3l3-3"/>
                                        </svg>&nbsp;&nbsp;Download
                                    </button>
                        </div>

                        
                    </div>
                </div>
            </div>

        </div>
        
        <div class="row">
            <div class="col-8">
                <table id="countries_table" class="table text-nowrap mb-0 align-middle">
                    <thead class="text-dark fs-4">
                        <tr>
                            <th>
                                <h6 class="fs-4 fw-semibold mb-0">Country</h6>
                            </th>
                            <th>
                                <h6 class="fs-4 fw-semibold mb-0">Logs Count</h6>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>

            <div class="col-lg-4 d-flex align-items-stretch">
                <div class="card w-100">
                    <div class="card-body">
                        <h4 class="card-title fw-semibold">Statitics</h4>
                        <p class="card-subtitle mb-7">For all logs</p>
                        <div class="position-relative">

                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <div class="d-flex">
                                    <div class="p-8 bg-warning-subtle rounded-2 d-flex align-items-center justify-content-center me-6">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m3 17l9 5l9-5v-3l-9 5l-9-5v-3l9 5l9-5V8l-9 5l-9-5l9-5l5.418 3.01"/></svg>
                                    </div>
                                    <div>
                                        <h6 class="mb-1 fs-4 fw-semibold">Total Logs</h6>
                                        <p class="fs-3 mb-0">for all time</p>
                                    </div>
                                </div>
                                <h6 class="mb-0 fw-semibold" id="logs_count">?</h6>
                            </div>

                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <div class="d-flex">
                                    <div class="p-8 bg-primary-subtle rounded-2 d-flex align-items-center justify-content-center me-6">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="M7 14q-.825 0-1.412-.587T5 12t.588-1.412T7 10t1.413.588T9 12t-.587 1.413T7 14m0 4q-2.5 0-4.25-1.75T1 12t1.75-4.25T7 6q1.675 0 3.038.825T12.2 9H21l3 3l-4.5 4.5l-2-1.5l-2 1.5l-2.125-1.5H12.2q-.8 1.35-2.162 2.175T7 18m0-2q1.4 0 2.463-.85T10.875 13H14l1.45 1.025L17.5 12.5l1.775 1.375L21.15 12l-1-1h-9.275q-.35-1.3-1.412-2.15T7 8Q5.35 8 4.175 9.175T3 12t1.175 2.825T7 16"/></svg>
                                    </div>
                                    <div>
                                        <h6 class="mb-1 fs-4 fw-semibold">Passwords</h6>
                                        <p class="fs-3 mb-0">Total password in logs</p>
                                    </div>
                                </div>
                                <h6 class="mb-0 fw-semibold" id="passwords_count" >?</h6>
                            </div>
                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <div class="d-flex">
                                    <div class="p-8 bg-success-subtle rounded-2 d-flex align-items-center justify-content-center me-6">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="M12 22q-2.075 0-3.9-.788t-3.175-2.137T2.788 15.9T2 12q0-1.875.725-3.675T4.75 5.112t3.125-2.275t4-.862q.525 0 1.075.05t1.125.175q-.225 1.125.15 2.125t1.125 1.662t1.788.913t2.137-.125q-.65 1.475.188 2.825T21.95 11q.025.275.038.512t.012.513q0 2.05-.788 3.862t-2.137 3.175t-3.175 2.15T12 22m-1.5-12q.625 0 1.063-.437T12 8.5t-.437-1.062T10.5 7t-1.062.438T9 8.5t.438 1.063T10.5 10m-2 5q.625 0 1.063-.437T10 13.5t-.437-1.062T8.5 12t-1.062.438T7 13.5t.438 1.063T8.5 15m6.5 1q.425 0 .713-.288T16 15t-.288-.712T15 14t-.712.288T14 15t.288.713T15 16m-3 4q3.05 0 5.413-2.1T20 12.55q-1.25-.55-1.963-1.5t-.962-2.125q-1.925-.275-3.3-1.65t-1.7-3.3q-2-.05-3.512.725T6.037 6.688T4.512 9.325T4 12q0 3.325 2.338 5.663T12 20m0-8.1"/></svg>
                                    </div>
                                    <div>
                                        <h6 class="mb-1 fs-4 fw-semibold">Cookies</h6>
                                        <p class="fs-3 mb-0">Total cookies in logs</p>
                                    </div>
                                </div>
                                <h6 class="mb-0 fw-semibold" id="cookies_count">?</h6>
                            </div>
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div class="d-flex">
                                    <div class="p-8 bg-danger-subtle rounded-2 d-flex align-items-center justify-content-center me-6">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="M5 19V5zm0 2q-.825 0-1.412-.587T3 19V5q0-.825.588-1.412T5 3h14q.825 0 1.413.588T21 5v2.5h-2V5H5v14h14v-2.5h2V19q0 .825-.587 1.413T19 21zm8-4q-.825 0-1.412-.587T11 15V9q0-.825.588-1.412T13 7h7q.825 0 1.413.588T22 9v6q0 .825-.587 1.413T20 17zm7-2V9h-7v6zm-4-1.5q.625 0 1.063-.437T17.5 12t-.437-1.062T16 10.5t-1.062.438T14.5 12t.438 1.063T16 13.5"/></svg>
                                    </div>
                                    <div>
                                        <h6 class="mb-1 fs-4 fw-semibold">Wallet Files</h6>
                                        <p class="fs-3 mb-0">Total wallet files in logs</p>
                                    </div>
                                </div>
                                <h6 class="mb-0 fw-semibold" id="wallets_count">?</h6>
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
<div class="dark-transparent sidebartoggler"></div>
    
<?php include_once 'app/pages/footer.php'; ?>

<script src="assets/libs/chart.js/chart.js"></script>
 
<script>var server_time = "<?php echo (new DateTime())->format('Y-m-d H:i:s'); ?>";</script>

<?php include_once 'app/pages/footer.php'; ?>

<!-- ApexCharts -->
<script src="assets/libs/apexcharts/apexcharts.min.js"></script>

<script src="assets/js/dashboard.js"></script>

</body>
</html>