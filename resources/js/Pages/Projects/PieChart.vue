<template>
    <div class="chart-container">
      <canvas :id="chartId"></canvas>
    </div>
  </template>
  
  <script>
  import { Chart, ArcElement, Tooltip, Legend, PieController } from "chart.js";
  
  export default {
    props: {
      progress: {
        type: Number,
        required: true,
      },
      chartId: {
        type: String,
        required: true,
      },
    },
    mounted() {
      this.registerChart();
      this.renderChart();
    },
    methods: {
      registerChart() {
        Chart.register(ArcElement, Tooltip, Legend, PieController);
      },
      renderChart() {
        const ctx = document.getElementById(this.chartId).getContext("2d");
        new Chart(ctx, {
          type: "doughnut",
          data: {
            //labels: ["Completed", "Remaining"],
            datasets: [
              {
                data: [this.progress, 100 - this.progress],
                backgroundColor: ["#16325B", "#bee9f4"], // Custom colors
                borderWidth: 0,
              },
            ],
          },
          options: {
            responsive: true,
            maintainAspectRatio: false, // Allows the chart to fill the container
            plugins: {
              tooltip: {
                callbacks: {
                  label: (context) => `${context.label}: ${context.raw}%`,
                },
              },
              legend: {
                position: "bottom",
              },
            },
          },
        });
      },
    },
  };
  </script>
  
  <style scoped>
  .chart-container {
    width: 100%; /* Adjust the width as needed */
    max-width: 100px; /* Maximum width of the chart container */
    height: 100px; /* Adjust the height as needed */
    margin: 0 auto;
  }
  canvas {
    display: block;
  }
  </style>
  