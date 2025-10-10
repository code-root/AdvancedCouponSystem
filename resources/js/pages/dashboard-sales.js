/**
 * Theme: Osen - Responsive Bootstrap 5 Admin Dashboard
 * Author: Coderthemes
 * Module/App: Sales Dashboard
 */

//
// REVENUE AREA CHART
//
///

import $ from 'jquery'

window.jQuery = window.$ = $

import ApexCharts from 'apexcharts';

var colors = ["#727cf5", "#0acf97", "#fa5c7c", "#ffbc00"];
var dataColors = $("#revenue-chart").data('colors');
if (dataColors) {
    colors = dataColors.split(",");
}

var options = {
    series: [
    {
        name: "Total Income",
        type: "bar",
        data: [
            89.25, 98.58, 68.74, 108.87, 77.54, 84.03, 51.24, 28.57, 92.57, 42.36, 88.51, 36.57,
        ],
    },{
        name: "Total Expenses",
        type: "bar",
        data: [
            22.25, 24.58, 36.74, 22.87, 19.54, 25.03, 29.24, 10.57, 24.57, 35.36, 20.51, 17.57,
        ],
    },{
        name: "Investments",
        type: "area",
        data: [34, 65, 46, 68, 49, 61, 42, 44, 78, 52, 63, 67],
    },
    {
        name: "Savings",
        type: "line",
        data: [8, 12, 7, 17, 21, 11, 5, 9, 7, 29, 12, 35],
    },
    ],
    chart: {
        height: 300,
        type: "line",
        toolbar: {
            show: false,
        },
    },
    stroke: {
        dashArray: [0, 0, 0, 8],
        width: [0, 0, 2, 2],
        curve: 'smooth'
    },
    fill: {
        opacity: [1, 1, 0.1, 1],
        type: ['gradient', 'solid', 'solid', 'solid'],
        gradient: {
            type: "vertical",
            //   shadeIntensity: 1,
            inverseColors: false,
            opacityFrom: 0.5,
            opacityTo: 0,
            stops: [0, 70]
        },
    },
    markers: {
        size: [0, 0, 0, 0],
        strokeWidth: 2,
        hover: {
            size: 4,
        },
    },
    xaxis: {
        categories: [
            "Jan",
            "Feb",
            "Mar",
            "Apr",
            "May",
            "Jun",
            "Jul",
            "Aug",
            "Sep",
            "Oct",
            "Nov",
            "Dec",
        ],
        axisTicks: {
            show: false,
        },
        axisBorder: {
            show: false,
        },
    },
    yaxis: {
        stepSize: 25,
        min: 0,
        labels: {
            formatter: function (val) {
                return val + "k";
            },
            offsetX: -15
        },
        axisBorder: {
            show: false,
        }
    },
    grid: {
        show: true,
        xaxis: {
            lines: {
                show: false,
            },
        },
        yaxis: {
            lines: {
                show: true,
            },
        },
        padding: {
            top: 0,
            right: -15,
            bottom: 15,
            left: -15,
        },
    },
    legend: {
        show: true,
        horizontalAlign: "center",
        offsetX: 0,
        offsetY: -5,
        markers: {
            width: 9,
            height: 9,
            radius: 6,
        },
        itemMargin: {
            horizontal: 10,
            vertical: 0,
        },
    },
    plotOptions: {
        bar: {
            columnWidth: "50%",
            barHeight: "70%",
            borderRadius: 3,
        },
    },
    colors: colors,
    tooltip: {
        shared: true,
        y: [{
            formatter: function (y) {
                if (typeof y !== "undefined") {
                    return "$" + y.toFixed(2) + "k";
                }
                return y;
            },
        },
        {
            formatter: function (y) {
                if (typeof y !== "undefined") {
                    return "$" + y.toFixed(2) + "k";
                }
                return y;
            },
        },
        {
            formatter: function (y) {
                if (typeof y !== "undefined") {
                    return "$" + y.toFixed(2) + "k";
                }
                return y;
            },
        },
        {
            formatter: function (y) {
                if (typeof y !== "undefined") {
                    return "$" + y.toFixed(2) + "k";
                }
                return y;
            },
        },
        ],
    },
}

var chart = new ApexCharts(
    document.querySelector("#revenue-chart"),
    options
);

chart.render();


//
// MULTIPLE RADIALBARS
//

var colors = ["#6C757D", "#FFBC00", "#727CF5", "#0ACF97"];
var dataColors = $("#multiple-radialbar").data('colors');
if (dataColors) {
    colors = dataColors.split(",");
}
var options = {
    chart: {
        height: 330,
        type: 'radialBar',
    },
    plotOptions: {
        circle: {
            dataLabels: {
                showOn: 'hover'
            }
        },
        radialBar: {
            track: {
                margin: "17%",
                background: "rgba(170,184,197, 0.2)"
            },
            hollow: {
                size: '1%',
            },
            dataLabels: {
                name: {
                    show: false,
                },
                value: {
                    show: false,
                }
            }
        }
    },
    stroke: {
        lineCap: 'round'
    },
    colors: colors,
    series: [44, 55, 67, 22],
    labels: ['Completed', 'In Progress', 'Yet to Start', 'Cancelled'],
    responsive: [{
        breakpoint: 380,
        options: {
            chart: {
                height: 260,
            }
        }
    }]
}
var chart = new ApexCharts(
    document.querySelector("#multiple-radialbar"),
    options
);
chart.render();
