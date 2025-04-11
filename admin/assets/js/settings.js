var create_twofa = new bootstrap.Modal(document.getElementById('create_twofa'), 
{
    backdrop: 'static',
    keyboard: false
});

var disable_twofa = new bootstrap.Modal(document.getElementById('disable_twofa'), 
{
    backdrop: 'static',
    keyboard: false
});

var connect_telegram = new bootstrap.Modal(document.getElementById('connect_telegram'), 
{
    backdrop: 'static',
    keyboard: false
});

var disable_telegram = new bootstrap.Modal(document.getElementById('disable_telegram'), 
{
    backdrop: 'static',
    keyboard: false
});

var table;

$(document).ready(function()
{
    getConfiguration();
    getSessions();
});

function getConfiguration()
{
    $.post('api/settings', 
    { 
        method: 'get_configuration'
    },
    function(response)
    {
        if(response.success)
        {
            // ----------------------------------------------------------------------
            // telegram settings
            if(response.telegram_enable == 1)
            {
                $("#button_enable_telegram").css("display", "none");
            }
            else
            {
                $("#button_disable_telegram").css("display", "none");
            }

            $('#notify_logins').prop('checked', response.notify_logins == 1);
            $('#notify_twofa_change').prop('checked', response.notify_twofa_change == 1);
            $('#notify_password_change').prop('checked', response.notify_password_change == 1);
            $('#notify_all_logs').prop('checked', response.notify_all_logs == 1);
            $('#notify_only_crypto_logs').prop('checked', response.notify_only_crypto_logs == 1);
            $('#notify_with_screen').prop('checked', response.notify_with_screen == 1);

            // ----------------------------------------------------------------------
            // twofa status
            if(response.twofa_status == 1)
            {
                $("#button_enable_twofa").css("display", "none");
            }
            else
            {
                $("#button_disable_twofa").css("display", "none");
            }
        }
        else
        {
            toastr.error(response.error);
        }
    }, 'json' );
}

function getSessions()
{
    table = $('#sessions_table').DataTable(
    {
        dom: '<"top"i>rt<"bottom"lp><"clear">', 
        paging: true,
        pagingType: "full_numbers",
        processing: true,
        serverSide: true,
        ordering: false,
        searching: false,
        pageLength: 10,
        lengthMenu: [ [10, 25, 50, 100, 200], [10, 25, 50, 100, 200] ],
        autoWidth: false,

        ajax:
        {
            url: 'api/settings', 
            type: 'POST',

            data: function (d) 
            {
                d.method                    = "get_sessions";
                delete d.columns;
                delete d.search;
            }
        },

        columns: 
        [
            {
                data: null,
                "render": function (data, type, row) 
                {
                    var browser_html = '<div class="d-flex align-items-center">';

                    switch(data.browser)
                    {
                        case "Microsoft Edge":
                            browser_html += `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="M13.817 21.835q-.16.005-.317.005c-1.073 0-2.374-.62-3.42-1.758A6.75 6.75 0 0 1 8.3 15.5c0-1.418.518-2.565 1.201-3.406C9.558 14.58 11.86 17.7 16.5 17.7c1.678 0 2.717-.452 3.28-.697c.208-.09.35-.153.436-.153c.184 0 .284.1.284.3c0 .187-.101.321-.426.752l-.073.098a10 10 0 0 1-6.184 3.835m-3.115.081C5.792 21.28 2 17.084 2 12c0-1.28.74-2.329 1.897-3.08C5.058 8.164 6.587 7.75 8 7.75c2.276 0 3.635.765 4.428 1.647q.075.084.144.169A2.5 2.5 0 0 0 12 9.5h-.004a2.5 2.5 0 0 0-1.2.309a5 5 0 0 0-.236.117a6 6 0 0 0-1.51 1.168A6.35 6.35 0 0 0 7.3 15.5c0 2.137.855 3.965 2.044 5.258c.414.45.874.841 1.358 1.159m3.15-8.32c.266-.28.648-.684.648-1.596c0-.86-.338-2.171-1.328-3.272C12.165 7.61 10.524 6.75 8 6.75c-1.587 0-3.308.46-4.647 1.33q-.422.274-.79.605A10 10 0 0 1 12 2c5.523 0 10 4 10 8.5c0 2.8-2.2 4.85-5 4.85c-2 0-3.4-.65-3.4-1.35c0-.14.11-.254.252-.404"/></svg>`;
                            break;
        
                        case "Opera":
                            browser_html += `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="M8.71 6.365c-1.107 1.305-1.822 3.236-1.872 5.4v.47c.051 2.165.765 4.093 1.872 5.4c1.434 1.862 3.566 3.044 5.95 3.044a7.2 7.2 0 0 0 4.005-1.226a9.94 9.94 0 0 1-7.139 2.535A10 10 0 0 1 2.001 12c0-5.524 4.477-10 10-10h.038a9.97 9.97 0 0 1 6.627 2.546a7.24 7.24 0 0 0-4.008-1.226c-2.382 0-4.514 1.183-5.95 3.045zM22.001 12a9.97 9.97 0 0 1-3.335 7.454c-2.565 1.25-4.955.376-5.747-.17c2.52-.554 4.423-3.6 4.423-7.284c0-3.685-1.903-6.73-4.423-7.283c.791-.545 3.182-1.42 5.747-.171A9.97 9.97 0 0 1 22.001 12"/></svg>`;
                            break;
        
                        case "Google Chrome":
                            browser_html += `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="M9.827 21.763C5.35 20.771 2 16.777 2 12c0-1.822.487-3.53 1.339-5.002l4.283 7.419a5 5 0 0 0 4.976 2.548zM12 22l4.287-7.425A5 5 0 0 0 17 12a4.98 4.98 0 0 0-1-3h5.542A10 10 0 0 1 22 12c0 5.523-4.477 10-10 10m2.572-8.455a3 3 0 0 1-5.17-.045l-.029-.05a3 3 0 1 1 5.225.05zm-9.94-8.306A9.97 9.97 0 0 1 12 2a10 10 0 0 1 8.662 5H12a5 5 0 0 0-4.599 3.034z"/></svg>`;
                            break;
        
                        case "Safari":
                            browser_html += `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="m16.701 6.8l-6.114 3.786L6.802 16.7l-.104-.104l-1.415 1.414l.708.707l1.414-1.414L7.3 17.2l6.114-3.785L17.2 7.3l.104.104L18.72 5.99l-.708-.708l-1.414 1.415zm-4.7 15.2c-5.523 0-10-4.477-10-10s4.477-10 10-10s10 4.477 10 10s-4.477 10-10 10m-.5-19v2h1V3zm0 16v2h1v-2zM8.095 3.876l.765 1.848l.924-.383l-.765-1.847zm6.123 14.783l.765 1.847l.924-.382l-.765-1.848zm.765-15.165l-.765 1.847l.924.383l.765-1.848zM8.86 18.276l-.765 1.848l.924.382l.765-1.848zM21.001 11.5h-2v1h2zm-16 0h-2v1h2zm15.458 3.616l-1.835-.795l-.397.918l1.835.794zM5.775 8.76L3.94 7.967l-.397.918l1.835.794zm14.35-.667l-1.848.765l.383.924l1.847-.765zM5.342 14.217l-1.847.765l.382.924l1.848-.765zM18.72 18.01l-1.415-1.414l-.707.707l1.414 1.415zM7.404 6.697L5.99 5.282l-.708.708l1.415 1.414zm3.908 4.615l3.611-2.235l-2.235 3.61z"/></svg>`;
                            break;
        
                        case "Mozilla Firefox":
                            browser_html += `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="M21.283 8.26c-.436-1.047-1.317-2.178-2.01-2.535c.48.939.893 2.003 1.017 3.057c-1.133-2.823-3.054-3.962-4.622-6.44a8 8 0 0 1-.545-1.013c-2.228 1.305-3.151 3.589-3.388 5.042a5.3 5.3 0 0 0-1.985.507a.26.26 0 0 0-.127.318a.254.254 0 0 0 .341.147A4.9 4.9 0 0 1 12 6.879c1.805-.013 3.518.99 4.416 2.558c-.535-.375-1.493-.746-2.415-.586c3.602 1.801 2.635 8.004-2.357 7.77c-2.014-.083-3.945-1.65-4.126-3.73c0 0 .462-1.723 3.31-1.723c.309 0 1.189-.86 1.205-1.109c-.004-.081-1.747-.775-2.426-1.444c-.302-.298-.594-.618-.948-.856a4.64 4.64 0 0 1-.028-2.448c-1.03.469-1.834 1.21-2.416 1.863c-.397-.503-.37-2.162-.347-2.508c-.873.465-1.642 1.274-2.248 2.137c-1.029 1.458-1.622 3.37-1.622 5.182C1.997 17.515 6.468 22 12 22c4.954 0 9.081-3.597 9.887-8.32c.241-1.823.107-3.71-.604-5.42"/></svg>`;
                            break;
        
                        case "Internet Explorer":
                            browser_html += `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="M20.645 8.586c-.17-.711-.441-1.448-.774-2.021c-.771-1.329-1.464-2.237-3.177-3.32S13.077 2 12.171 2c-2.415 0-4.211.86-5.525 1.887C3.345 6.47 3.001 11 3.001 11s1.221-2.045 3.54-3.526C7.944 6.579 9.942 6 11.569 6c4.317 0 4.432 4 4.432 4h-7c0-2 1-3 1-3s-5 2-5 7.044c0 .487-.003 1.372.248 2.283c.232.843.7 1.705 1.132 2.353c1.221 1.832 3.045 2.614 3.916 2.904c.996.332 2.029.416 3.01.416c2.72 0 4.877-.886 5.694-1.275v-4.172c-.758.454-2.679 1.447-5 1.447c-5 0-5-4-5-4h12v-2.49s-.039-1.593-.356-2.924"/></svg>`;
                            break;
                    }

                    browser_html += `
                    <div class="ms-3">
                        <div class="user-meta-info">
                            <h6 class="user-name mb-0">${data.browser}</h6>
                            <span class="user-work fs-3">${data.version}</span>
                        </div>
                    </div>
                    </div>
                    `;

                    return browser_html;
                }
            },
            {
                data: null,
                "render": function (data, type, row) 
                {
                    var status_html = '';
    
                    switch(data.active)
                    {
                        case "0":
                            status_html = '<span class="mb-1 badge text-bg-warning">Expired</span>';
                            break;

                        case "1":
                            status_html = '<span class="mb-1 badge text-bg-primary">Active</span>';
                            break;
                    }

                    return status_html;
                }
            },
            {
                data: null,
                "render": function (data, type, row) 
                {
                    const updated_date = formatTimeAgo(new Date(data.created_at));

                    return `<h6 class="fw-semibold mb-0">${data.created_at}</h6> <i>${updated_date}</i>`;
                }
            },
            {
                data: null,
                "render": function (data, type, row) 
                {
                    return `<svg xmlns="http://www.w3.org/2000/svg" style="cursor: pointer;" onclick="DeleteSession('${data.id}')" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="M7 21q-.825 0-1.412-.587T5 19V6H4V4h5V3h6v1h5v2h-1v13q0 .825-.587 1.413T17 21zm2-4h2V8H9zm4 0h2V8h-2z"/></svg>`;
                }
            }
        ]

    });

}

function formatTimeAgo(startDate) 
{
    const currentDate = new Date(server_time);
    const timeDifference = currentDate - startDate;

    if (timeDifference < 0) 
    {
        return "Date is in the future";
    }

    const seconds = Math.floor(timeDifference / 1000);
    const minutes = Math.floor(seconds / 60);
    const hours = Math.floor(minutes / 60);
    const days = Math.floor(hours / 24);
    const months = Math.floor(days / 30);

    let result = [];

    if (months > 0) {
        result.push(`${months}m`);
    }

    const remainingDays = days % 30;
    if (remainingDays > 0) {
        result.push(`${remainingDays}d`);
    }

    const remainingHours = hours % 24;
    if (remainingHours > 0) {
        result.push(`${remainingHours}h`);
    }

    const remainingMinutes = minutes % 60;
    if (remainingMinutes > 0) {
        result.push(`${remainingMinutes}m`);
    }

    const remainingSeconds = seconds % 60;
    if (remainingSeconds > 0) {
        result.push(`${remainingSeconds}s`);
    }

    if (result.length === 0) {
        return "just now";
    }

    return result.join(" ") + " ago";
}

function DeleteSession(session_id)
{
    $.post('api/settings', 
    { 
        method: 'delete_session',
        session_id: session_id
    },
    function(response) 
    {
        if (response.success)
        {
            table.draw();
            toastr.info(response.success);
        }
        else
        {
            toastr.warning(response.error);
        }
    }, 'json');
}

function ChangePassword()
{
    var old_password        = $('#edit_old_password').val();
    var password            = $('#edit_new_password').val();
    var confirm_password    = $('#edit_new_password_repeat').val();

    if (old_password.trim().length > 0 || password.trim().length > 0 || confirm_password.trim().length > 0) 
    {
        if (password !== confirm_password)
        {
            toastr.error("Error: Passwords Do Not Match");
            return;
        }

        $.post('api/settings',
        { 
            method: 'change_password',
            old_password: old_password,
            new_password: password
        },
        function(response) 
        {
            if (response.success)
            {
                toastr.info(response.success);
            }
            else
            {
                toastr.warning(response.error);
            }
        }, 'json');
    }
    else
    {
        toastr.error("Error: Enter old password, new password and confirm");
    }
}

function OpenTwofaModal()
{
    $.post('api/settings', 
    { 
        method: 'get_twofa'
    },
    function(response)
    {
        $('#qr_code_block').html(response.qr);
        $('#qr_secret').text(response.secret.replace(/(.{4})/g, '$1 ').trim());

        create_twofa.show();
    }, 'json');
}

function ConfirmTwofaCode()
{
    var onetime_code = $('#qr_onetime_code').val();

    if (onetime_code.trim().length > 0)
    {
        $.post('api/settings',
        { 
            method: 'save_twofa',
            onetime_code: onetime_code
        },
        function(response) 
        {
            if (response.success)
            {
                toastr.info(response.success);
                create_twofa.hide();

                $("#button_enable_twofa").css("display", "none");
                $("#button_disable_twofa").css("display", "block");
            }
            else
            {
                toastr.warning(response.error);
            }
        }, 'json');
    }
    else
    {
        toastr.error("Error: Enter onetime confirm code");
    }
}

function DisableTwofaModal()
{
    disable_twofa.show();
}

function DisableTwofa()
{
    var onetime_code = $('#qr_onetime_code_disable').val();

    if (onetime_code.trim().length > 0)
    {
        $.post('api/settings',
        { 
            method: 'disable_twofa',
            onetime_code: onetime_code
        },
        function(response) 
        {
            if (response.success)
            {
                toastr.info(response.success);
                disable_twofa.hide();
    
                $("#button_enable_twofa").css("display", "block");
                $("#button_disable_twofa").css("display", "none");
            }
            else
            {
                toastr.warning(response.error);
            }
        }, 'json');
    }
    else
    {
        toastr.error("Error: Enter onetime confirm code");
    }
}

function ChangeTelegramSettings()
{
    var notify_logins               = $('#notify_logins').is(':checked') ? 1 : 0;
    var notify_twofa_change         = $('#notify_twofa_change').is(':checked') ? 1 : 0;
    var notify_password_change      = $('#notify_password_change').is(':checked') ? 1 : 0;
    var notify_all_logs             = $('#notify_all_logs').is(':checked') ? 1 : 0;
    var notify_only_crypto_logs     = $('#notify_only_crypto_logs').is(':checked') ? 1 : 0;
    var notify_with_screen          = $('#notify_with_screen').is(':checked') ? 1 : 0;

    $.post('api/settings',
    { 
        method:                     'change_notifications',
        notify_logins:              notify_logins,
        notify_twofa_change:        notify_twofa_change,
        notify_password_change:     notify_password_change,
        notify_all_logs:            notify_all_logs,
        notify_only_crypto_logs:    notify_only_crypto_logs,
        notify_with_screen:         notify_with_screen
    },
    function(response) 
    {
        if (response.success)
        {
            toastr.info(response.success);
        }
        else
        {
            toastr.warning(response.error);
        }
    }, 'json');
}

function ConnectTelegram()
{
    $.post('api/settings',
    {
        method: 'create_token'
    },
    function(response) 
    {
        if (response.success)
        {
            window.open(response.link, '_blank');
            $("#link_telegram").attr("href", response.link);
            connect_telegram.show();

            checkTelegramToken();
        }
        else
        {
            toastr.warning(response.error);
        }
    }, 'json');
}

function checkTelegramToken()
{
    $.post('api/settings', 
    {
        method: 'check_telegram'
    }, 
    function(response) 
    {
        if (response.success)
        {
            if (response.status === 'success') 
            {
                $("#button_enable_telegram").css("display", "none");
                $("#button_disable_telegram").css("display", "block");
        
                connect_telegram.hide();
                toastr.success('Telegram account has been successfully linked!');
            }
            else
            {
                setTimeout(checkTelegramToken, 1000);
            }
        }
        else
        {
            setTimeout(checkTelegramToken, 1000);
        }
    }, 'json');
}

function DisconnectTelegram()
{
    disable_telegram.show();
}

function DisableTelegram()
{
    $.post('api/settings',
    { 
        method: 'disable_telegram',
    },
    function(response) 
    {
        if (response.success)
        {
            $("#button_enable_telegram").css("display", "block");
            $("#button_disable_telegram").css("display", "none");

            toastr.info(response.success);
            disable_telegram.hide();
        }
        else
        {
            toastr.warning(response.error);
        }
    }, 'json');
}