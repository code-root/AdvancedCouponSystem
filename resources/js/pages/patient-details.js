//
// SIMPLE DONUT CHART
//
import ApexCharts from 'apexcharts';


var colors = ["#39afd1", "#ffbc00"];
var dataColors = $("#simple-donut").data("colors");
if (dataColors) {
  colors = dataColors.split(",");
}
var options = {
  chart: {
    height: 262,
    type: "donut",
  },
  series: [4, 7],
  legend: {
    show: true,
    position: "bottom",
    horizontalAlign: "center",
    verticalAlign: "middle",
    floating: false,
    fontSize: "14px",
    offsetX: 0,
    offsetY: 7,
  },
  labels: ["Analysis 4", "Visits 7"],
  colors: colors,
  responsive: [
    {
      breakpoint: 600,
      options: {
        chart: {
          height: 240,
        },
        legend: {
          show: false,
        },
      },
    },
  ],
};

var chart = new ApexCharts(document.querySelector("#simple-donut"), options);

chart.render();

var dataColors = $("#booked-revenue-chart").data("colors");
if (dataColors) {
  colors = dataColors.split(",");
}
var options4 = {
  chart: {
    type: "bar",
    height: 200,
    sparkline: {
      enabled: true,
    },
  },
  plotOptions: {
    bar: {
      horizontal: false,
      columnWidth: "60%",
      borderRadius: 4,
    },
  },
  colors: colors,
  series: [
    {
      data: [2, 3, 2, 7, 4, 2, 3],
    },
  ],
  xaxis: {
    categories: ["S", "M", "T", "W", "T", "F" , "S"],
    labels: {
      style: {
        colors: colors,
        fontSize: "14px",
      },
    },
  },
  legend: {
    offsetY: 7,
  },
  grid: {
    row: {
      colors: ["transparent", "transparent"], // takes an array which will be repeated on columns
      opacity: 0.2,
    },
    borderColor: "#f1f3fa",
  },
};
new ApexCharts(
  document.querySelector("#booked-revenue-chart"),
  options4
).render();
