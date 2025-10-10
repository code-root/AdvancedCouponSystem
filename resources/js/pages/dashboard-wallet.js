/**
 * Theme: Osen - Responsive Bootstrap 5 Admin Dashboard
 * Author: Coderthemes
 * Module/App: Dashboard Analytics
 */

import ApexCharts from 'apexcharts';

var colors = ["#727cf5", "#0acf97", "#fa5c7c", "#ffbc00"];
var dataColors = $("#revenue-chart").data('colors');
if (dataColors) {
    colors = dataColors.split(",");
}
var options1 = {
    chart: {
        type: 'area',
        height: 50,
        sparkline: {
            enabled: true
        }
    },
    series: [{
        data: [25, 28, 32, 38, 43, 55, 60, 48, 42, 51, 35]
    }],
    stroke: {
        width: 2,
        curve: 'smooth'
    },
    markers: {
        size: 0
    },
    colors: colors,
    tooltip: {
        fixed: {
            enabled: false
        },
        x: {
            show: false
        },
        y: {
            title: {
                formatter: function (seriesName) {
                    return ''
                }
            }
        },
        marker: {
            show: false
        }
    },
    fill: {
        opacity: [1],
        type: ['gradient'],
        gradient: {
            type: "vertical",
            //   shadeIntensity: 1,
            inverseColors: false,
            opacityFrom: 0.5,
            opacityTo: 0,
            stops: [0, 100]
        },
    },
}

new ApexCharts(document.querySelector("#revenue-chart"), options1).render();

//
// currency-cny-chart
//
var colors = ["#727cf5", "#0acf97", "#fa5c7c", "#ffbc00"];
var dataColors = $("#expenses-chart").data('colors');
if (dataColors) {
    colors = dataColors.split(",");
}
var options4 = {
    chart: {
        type: 'bar',
        height: 60,
        sparkline: {
            enabled: true
        }
    },
    plotOptions: {
        bar: {
            horizontal: false,
            columnWidth: '60%',
            borderRadius: 4,
        },
    },
    colors: colors,
    series: [{
        data: [47, 45, 74, 14, 56, 74, 14, 11, 7, 39, 82]
    }],
    labels: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11],
    xaxis: {
        crosshairs: {
            width: 1
        },
    },
    tooltip: {
        fixed: {
            enabled: false
        },
        x: {
            show: false
        },
        y: {
            title: {
                formatter: function (seriesName) {
                    return ''
                }
            }
        },
        marker: {
            show: false
        }
    }
}

new ApexCharts(document.querySelector("#expenses-chart"), options4).render();

var colors = ["#727cf5", "#0acf97", "#fa5c7c", "#ffbc00"];
var dataColors = $("#balance-overview").data('colors');
if (dataColors) {
    colors = dataColors.split(",");
}

var options = {
    series: [{
        name: "Total Income",
        type: "bar",
        data: [
            89.25, 98.58, 68.74, 108.87, 77.54, 84.03, 51.24, 28.57, 92.57, 42.36, 88.51, 36.57,
        ],
    }, {
        name: "Total Expense",
        type: "area",
        data: [34, 65, 46, 68, 49, 61, 42, 44, 78, 52, 63, 67],
    }, {
        name: "Investment",
        type: "bar",
        data: [8, 12, 7, 17, 21, 11, 5, 9, 7, 29, 12, 35],
    }],
    chart: {
        height: 375,
        type: "line",
        toolbar: {
            show: false,
        },
    },
    stroke: {
        dashArray: [0, 6, 0],
        width: [0, 2, 0],
        curve: 'smooth'
    },
    fill: {
        opacity: [1, 0.1, 1]
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
        min: 0,
        labels: {
            formatter: function (val) {
                return val + "k";
            },
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
            right: -2,
            bottom: 15,
            left: 10,
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
            columnWidth: "30%",
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
    document.querySelector("#balance-overview"),
    options
);


chart.render();