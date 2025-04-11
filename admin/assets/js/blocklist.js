var table;

/**
 * datatables
 *
 * 
 *
 * @return none
 */
$(document).ready(function() 
{
    table = $('#blocklist_table').DataTable(
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

        ajax:
        {
            url: 'api/blocklist', 
            type: 'POST',

            data: function (d) 
            {
                d.method                    = "get_blocklist";
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
                    switch(data.type)
                    {
                        case "ip":
                            return `<span class="mb-1 badge text-bg-primary">IP</span>`;
                            break;

                        case "mask":
                            return `<span class="mb-1 badge text-bg-secondary">Mask</span>`;
                            break;

                        case "hwid":
                            return `<span class="mb-1 badge text-bg-info">HWID</span>`;
                            break;
                    }
                }
            },
            {
                data: null,
                "render": function (data, type, row) 
                {
                    return `<h6 class="fs-4 fw-semibold mb-0">${data.value}</h6>`;
                }
            },
            {
                data: null,
                "render": function (data, type, row) 
                {
                    return `<button type="button" class="btn bg-danger-subtle text-danger" onclick="deleteRule('${data.id}')" >Delete</button>`;
                }
            }
        ]
    });
});

/**
 * createRuleModal
 *
 * 
 *
 * @return none
 */
function createRuleModal()
{
    var create_rule_modal = new bootstrap.Modal(document.getElementById('create_rule_modal'), 
    {
        backdrop: 'static',
        keyboard: false
    });

    create_rule_modal.show();

    var modal_create_button = document.querySelector('#modal_create_button');

    modal_create_button.addEventListener('click', function () 
    {
        $.post('api/blocklist', 
        { 
            method: 'create_rule',
            rule_type: document.getElementById("create_rule_type").value,
            rule_value: document.getElementById("create_rule_value").value
        },
        function(response) 
        {
            if (response.success)
            {
                table.draw();
                create_rule_modal.hide();

                toastr.info(response.success);
            }
            else
            {
                toastr.warning(response.error);
            }
        }, 'json');
    }, { once: true });
}

/**
 * deleteRule
 *
 * 
 *
 * @return none
 */
function deleteRule(rule_id)
{
    $.post('api/blocklist', 
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