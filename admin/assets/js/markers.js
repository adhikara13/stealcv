var table;

var create_rule_modal = new bootstrap.Modal(document.getElementById('create_rule_modal'), 
{
    backdrop: 'static',
    keyboard: false
});

var modal_create_button = document.querySelector('#modal_create_button');

$(document).ready(function()
{
    table = $('#markers_table').DataTable(
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
            url: 'api/markers', 
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
                    // url from task
                    return data.urls.replace(/,/g, '<br>');
                }
            },
            {
                data: null,
                "render": function (data, type, row) 
                {
                    var html = ``;

                    if(data.in_passwords == 1)
                    {
                        html += `<span class="mb-1 badge text-bg-primary">Passwords</span><br>`;
                    }

                    if(data.in_cookies == 1)
                    {
                        html += `<span class="mb-1 badge text-bg-info">Cookies</span>`;
                    }

                    return html;
                }
            },
            {
                data: null,
                "render": function (data, type, row) 
                {
                    return `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="${data.color}" d="M12 22q-2.075 0-3.9-.788t-3.175-2.137T2.788 15.9T2 12t.788-3.9t2.137-3.175T8.1 2.788T12 2t3.9.788t3.175 2.137T21.213 8.1T22 12t-.788 3.9t-2.137 3.175t-3.175 2.138T12 22"/></svg>`;
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
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <a class="dropdown-item" href="javascript:void(0)" onclick="DeleteRule('${data.rule_id}')">Delete</a>
                            </li>
                        </ul>
                    </div>
                    `;
                }
            },
        ]
    });
});

function ChangeStatus(rule_id, rule_active)
{
    $.post('api/markers', 
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

function createRuleModal()
{
    modal_create_button.removeEventListener('click', handleCreateRule);
    create_rule_modal.show();
    modal_create_button.addEventListener('click', handleCreateRule);
}

function handleCreateRule()
{
    var rule_name           = $('#create_rule_name').val();
    var rule_urls           = $('#create_rule_urls').val();
    var rule_in_passwords   = $('#create_rule_in_passwords').is(':checked') ? 1 : 0;
    var rule_in_cookies     = $('#create_rule_in_cookies').is(':checked') ? 1 : 0;
    var rule_color          = $('#create_rule_color').val();

    if (rule_name.trim().length > 0 & rule_urls.trim().length > 0)
    {
        $.post('api/markers', 
        { 
            method:             'create_rule',
            rule_name:          rule_name,
            rule_urls:          utf8_to_b64(rule_urls.replace(/\r?\n/g, ',')),
            rule_in_passwords:  rule_in_passwords,
            rule_in_cookies:    rule_in_cookies,
            rule_color:         rule_color
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
    $.post('api/markers', 
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

function utf8_to_b64(str) 
{
	return window.btoa(unescape(encodeURIComponent(str)));
}