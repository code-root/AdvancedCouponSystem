//
// MULTIPLE RADIALBARS
//
import ApexCharts from 'apexcharts';

var colors = ["#313a46,#f9c45c,#465dff,#6ac75a"];
var dataColors = $("#multiple-radialbar").data("colors");
if (dataColors) {
    colors = dataColors.split(",");
}
var options = {
    chart: {
        height: 368,
        type: "radialBar",
    },
    plotOptions: {
        circle: {
            dataLabels: {
                showOn: "hover",
            },
        },
        radialBar: {
            track: {
                margin: 20,
                background: "rgba(170,184,197, 0.2)",
            },
            hollow: {
                size: "5%",
            },
            dataLabels: {
                name: {
                    show: true,
                },
                value: {
                    show: true,
                },
            },
        },
    },

    stroke: {
        lineCap: "round",
    },

    legend: {
        show: true,
        showForSingleSeries: false,
        showForNullSeries: true,
        showForZeroSeries: true,
        position: "bottom",
        horizontalAlign: "center",
        floating: false,
        fontSize: "14px",
        fontFamily: "Helvetica, Arial",
        fontWeight: 400,
        formatter: undefined,
        inverseOrder: false,
        width: undefined,
        height: undefined,
        tooltipHoverFormatter: undefined,
        customLegendItems: [],
        offsetX: 0,
        offsetY: 0,
        labels: {
            colors: undefined,
            useSeriesColors: false,
        },
    },

    colors: colors,
    series: [44, 60, 70, 80],
    labels: ["Patient Visit", "Patient Care", "Endoscopic", "Operations"],
    responsive: [
        {
            breakpoint: 380,
            options: {
                chart: {
                    height: 210,
                },
            },
        },
    ],
};

var chart = new ApexCharts(
    document.querySelector("#multiple-radialbar"),
    options
);

chart.render();
