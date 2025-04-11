var table;

$(document).ready(function()
{
    table = $('#versions_table').DataTable(
    {
        dom: '<"top"i>rt<"bottom"lp><"clear">', 
        paging: true,
        pagingType: "full_numbers",
        processing: true,
        serverSide: true,
        ordering: false,
        searching: false,
        pageLength: 10,
        lengthMenu: [ [10, 25, 50, 100], [10, 25, 50, 100] ],
        autoWidth: false,

        ajax:
        {
            url: 'api/admin', 
            type: 'POST',

            data: function (d) 
            {
                d.method                    = "get_versions";
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
                    return `<h6 class="fs-4 fw-semibold mb-0">${data.version}</h6>`;
                }
            },
            {
                data: null,
                "render": function (data, type, row) 
                {
                    var html = '';

                    for(const change of data.changes)
                    {
                        html += `<label class="form-label"><span class="mb-1 badge text-bg-primary align-middle">${change.type}</span>&nbsp;&nbsp;${change.description}</label><br>`;
                    }

                    return html;
                }
            },
            {
                data: null,
                "render": function (data, type, row) 
                {
                    return `<h6 class="fs-4 fw-semibold mb-0">${data.created_at}</h6>`;
                }
            }
        ]
    });
});

function UploadUpdate()
{
    var update_file = document.getElementById('update_file');

    if (update_file.files.length === 0) 
    {
        toastr.warning("Select update file for upload.");
        return;
    }

    var file = update_file.files[0];

    var formData = new FormData();
    formData.append('method', 'install_update');
    formData.append('file', file);

    $.ajax(
    {
        url: "api/admin",
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,
        success: function(response)
        {
            if (typeof response === "string")
            {
                try 
                {
                    response = JSON.parse(response);
                } 
                catch(e){}
            }

            if(response.status === "success")
            {
                table.draw();
                toastr.success(response.message);
            } 
            else 
            {
                toastr.error(response.message);
            }
        },
        error: function(xhr, status, error) 
        {
            toastr.error("Select update file for upload.");
         }

    });
}

function SaveTelegram()
{
    var telegram_token          = $('#telegram_token').val();
    var telegram_chat_ids       = $('#telegram_chat_ids').val();

    $.post('api/admin',
    { 
        method: 'save_telegram_creds',
        telegram_token: telegram_token,
        telegram_chat_ids: telegram_chat_ids
    },
    function(response) 
    {
        if (response.status)
        {
            toastr.info(response.message);
        }
        else
        {
            toastr.warning(response.error);
        }
    }, 'json');
}

function getTelegramCreds()
{
    $.post('api/admin', 
    { 
        method: 'get_telegram'
    },
    function(response)
    {
        if(response.success)
        {
            $('#telegram_token').val(response.telegram_token);
            $('#telegram_chat_ids').val(response.telegram_chat_ids);
            $('#telegram_message').val(b64_to_utf8(response.telegram_message));
        }
        else
        {
            toastr.warning(response.message);   
        }
    }, 'json' );
}

function SaveMessageTemplate()
{
    var telegram_message          = $('#telegram_message').val();

    $.post('api/admin',
    { 
        method: 'save_message',
        telegram_message: utf8_to_b64(telegram_message)
    },
    function(response) 
    {
        if (response.status)
        {
            toastr.info(response.message);
        }
        else
        {
            toastr.warning(response.error);
        }
    }, 'json');
}

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

function b64_to_utf8(str) 
{
    return decodeURIComponent(
      Array.from(window.atob(str))
        .map(c => '%' + c.charCodeAt(0).toString(16).padStart(2, '0'))
        .join('')
    );
  }