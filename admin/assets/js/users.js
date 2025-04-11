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

let boundEditRule = null;

var create_user_modal = new bootstrap.Modal(document.getElementById('create_user_modal'), 
{
    backdrop: 'static',
    keyboard: false
});

var modal_session_list = new bootstrap.Modal(document.getElementById('modal_session_list'), 
{
    backdrop: 'static',
    keyboard: false
});

var edit_user_modal = new bootstrap.Modal(document.getElementById('edit_user_modal'), 
{
    backdrop: 'static',
    keyboard: false
});

var modal_create_button = document.querySelector('#modal_create_button');
var modal_edit_button = document.querySelector('#modal_edit_button');

$(document).ready(function()
{
    table = $('#users_table').DataTable(
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
            url: 'api/users', 
            type: 'POST',

            data: function (d) 
            {
                d.method                    = "get_users";
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
                    return `${data.id}`;
                }
            },
            {
                data: null,
                "render": function (data, type, row) 
                {
                    var html = `
                    <div class="d-flex align-items-center">
                        <img src="avatar?seed=${data.seed}" alt="avatar" class="rounded-circle" width="35" style="background: white;">
                        <div class="ms-3">
                            <div class="user-meta-info">
                                <h6 class="user-name mb-0">${data.username}</h6>
                                <span class="user-work fs-3">${data.user_group}</span>
                            </div>
                        </div>
                    </div>`;

                    return html;
                }
            },
            {
                data: null,
                "render": function (data, type, row) 
                {
                    var html = ``;

                    switch(data.twofa)
                    {
                        case "0":
                            return `<span class="badge bg-danger-subtle text-danger">No</span>`;
                            break;

                        case "1":
                            return `<span class="badge bg-primary-subtle text-primary">Yes</span>`;
                            break;
                    }

                    return html;
                }
            },
            {
                data: null,
                "render": function (data, type, row) 
                {
                    var html = ``;

                    if (data.builds)
                    {
                        let builds = data.builds.split(",");

                        for (let build of builds) 
                        {
                            html += `<span class="mb-1 badge text-bg-danger" title="Build: ${build}">${build}</span><br>`;
                        }
                    }
                    else
                    {
                        html += `<span class="mb-1 badge text-bg-primary" title="">All Builds</span><br>`;
                    }

                    return html;
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
                    if(data.last_login != null)
                    {
                        const updated_date = formatTimeAgo(new Date(data.last_login));
                        return `<h6 class="fw-semibold mb-0">${data.last_login}</h6> <i>${updated_date}</i>`;
                    }
                    else
                    {
                        return `<h6 class="fw-semibold mb-0">Never</h6>`;
                    }
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
                    <div class="btn-group">
                        <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">Actions</button>
                            
                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton" style="">
                            <li>
                                <a class="dropdown-item" href="javascript:void(0)" onclick="ViewSessions('${data.id}', true);" >View Sessions</a>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <a class="dropdown-item" href="javascript:void(0)" onclick="ChangeStatus('${data.id}', '${data.active == 1 ? '0' : '1'}')">${data.active == 1 ? 'Disable' : 'Enable'}</a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="javascript:void(0)" onclick="EditUser('${data.id}');" >Edit</a>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <a class="dropdown-item" href="javascript:void(0)" onclick="DeleteUser('${data.id}')">Delete</a>
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

function DeleteUser(user_id)
{
    $.post('api/users', 
    { 
        method: 'delete_user',
        user_id: user_id
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

function ChangeStatus(user_id, user_status)
{
    $.post('api/users', 
    { 
        method: 'change_status',
        user_id: user_id,
        user_status: user_status
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

function createUserModal()
{
    modal_create_button.removeEventListener('click', handleCreateUser);

    $.post('api/builder', 
    { 
        method: 'get_builds'
    },
    function(response)
    {
        $('#create_user_builds').select2(
        {
            dropdownParent: $('#create_user_modal'),
            data: response,
            searchInputPlaceholder: 'Search options',
            placeholder: "Builds (blank to access all builds)",			
        });

        create_user_modal.show();
        modal_create_button.addEventListener('click', handleCreateUser);
    }, 'json');
}

function handleCreateUser()
{
    var username            = $('#create_user_username').val();
    var password            = $('#create_user_password').val();
    var confirm_password    = $('#create_user_confirm_password').val();
    var user_builds         = utf8_to_b64($('#create_user_builds').val());
    var role                = $('#create_user_role').val();

    if (username.trim().length > 0 & password.trim().length > 0 & confirm_password.trim().length > 0)
    {
        if(password === confirm_password)
        {
            $.post('api/users', 
            { 
                method:             'create_user',
                username:           username,
                password:           password,
                user_builds:        user_builds,
                role:               role
            },
            function(response) 
            {
                if (response.success)
                {
                    table.draw();
                    create_user_modal.hide();
        
                    toastr.info(response.success);
                    modal_create_button.removeEventListener('click', handleCreateUser);
                }
                else
                {
                    toastr.warning(response.error);
                }
            }, 'json');
        }
        else
        {
            toastr.error("Error: Passwords Do Not Match");
        }
    }
    else
    {
        toastr.error("Error: Enter Name, Masks and Max Size");
    }
}

function ViewSessions(user_id, open_modal)
{
    $.post('api/users', 
    { 
        method: 'get_sessions',
        user_id: user_id
    },
    function(response) 
    {
        if(response.success)
        {
            var tbody = $("#sessionsTable tbody");
            tbody.empty();

            response.data.forEach(function(item) 
            {
                var row = $("<tr></tr>");

                row.append($("<td></td>").text(item.id));
                row.append($("<td></td>").text(item.session_id));
                row.append($("<td></td>").text(item.user_agent));
                
                var statusHtml = '';

                switch(item.active)
                {
                    case "0":
                        statusHtml = '<span class="mb-1 badge text-bg-warning">Expired</span>';
                        break;
                    case "1":
                        statusHtml = '<span class="mb-1 badge text-bg-primary">Active</span>';
                        break;
                }

                row.append($("<td></td>").html(statusHtml));
                row.append($("<td></td>").text(item.created_at));
                row.append($("<td></td>").text(item.last_activity));

                var actions = `<svg xmlns="http://www.w3.org/2000/svg" style="cursor: pointer;" onclick="DeleteSession('${user_id}', '${item.id}')" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="M7 21q-.825 0-1.412-.587T5 19V6H4V4h5V3h6v1h5v2h-1v13q0 .825-.587 1.413T17 21zm2-4h2V8H9zm4 0h2V8h-2z"/></svg>`;
                    
                row.append($("<td></td>").html(actions));
                tbody.append(row);
            });

            if(open_modal)
            {
                modal_session_list.show();
            }
        }
        else
        {
            toastr.error(response.error);
        }
    }, 'json' );
}

function DeleteSession(user_id, session_id)
{
    $.post('api/users', 
    { 
        method: 'delete_session',
        session_id: session_id
    },
    function(response) 
    {
        if (response.success)
        {
            ViewSessions(user_id, false);
            toastr.info(response.success);
        }
        else
        {
            toastr.warning(response.error);
        }
    }, 'json');
}

function EditUser(user_id)
{
    modal_edit_button.removeEventListener('click', handleEditUser);

    $.post('api/users', 
    { 
        method: 'get_user',
        user_id: user_id
    },
    function(response)
    {
        if (response.success) 
        {
            document.getElementById("modal_edit_user_header").innerText = `Edit User #${user_id} (${response.data.username})`;

            document.getElementById("edit_user_username").value = response.data.username;
            document.getElementById("edit_user_id").value = user_id;

            $('#edit_user_role').val(response.data.role);

            $.post('api/builder', 
            { 
                method: 'get_builds'
            },
            function(respbuilds)
            {
                $('#edit_user_builds').select2(
                {
                    dropdownParent: $('#edit_user_modal'),
                    data: respbuilds,
                    searchInputPlaceholder: 'Search options',
                    placeholder: "Builds (blank to access all builds)",			
                });

                if(response.data.builds)
                {
                    var buildsArray = response.data.builds.split(",");
                    $('#edit_user_builds').val(buildsArray).trigger("change");
                }

                modal_edit_button.addEventListener('click', handleEditUser);
                edit_user_modal.show();
            }, 'json');
        }
        else
        {
            toastr.error(response.error);
        }
    }, 'json');
}

function handleEditUser() 
{
    var user_id             = $('#edit_user_id').val();
    var username            = $('#edit_user_username').val();
    var password            = $('#edit_user_password').val();
    var confirm_password    = $('#edit_user_confirm_password').val();
    var user_builds         = utf8_to_b64($('#edit_user_builds').val());
    var role                = $('#edit_user_role').val();
    var twofa_disable       = $('#edit_user_twofa_disable').is(':checked') ? '1' : '0';

    if (password.trim().length > 0 || confirm_password.trim().length > 0) 
    {
        if (password !== confirm_password) 
        {
            toastr.error("Error: Passwords Do Not Match");
            return;
        }
    }

    var postData = 
    { 
        method:        'edit_user',
        user_id:       user_id,
        username:      username,
        user_builds:   user_builds,
        role:          role,
        twofa_disable: twofa_disable
    };

    if (password.trim().length > 0) 
    {
        postData.password = password;
    }
    
    $.post('api/users', postData, function(response) 
    {
        if (response.success) 
        {
            table.draw();
            edit_user_modal.hide();
            toastr.info(response.success);
            modal_edit_button.removeEventListener('click', handleEditUser);
        } 
        else 
        {
            toastr.warning(response.error);
        }
    }, 'json');
}