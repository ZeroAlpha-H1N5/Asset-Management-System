$(document).ready(function() {
  Chart.register(ChartDataLabels);

  // -----    BAR CHART DASHBOARD     ----- //
  var ctx = document.getElementById('assetChart').getContext('2d');

  // Function to fetch data from PHP with ALL filters
  function fetchData(status, location, department) { 
    var url = 'dashboard_bar_chart_fetch.php?';
    var params = []; 

    if (status !== 'all') {
      params.push('status=' + encodeURIComponent(status)); 
    }
    if (location !== 'all') {
      params.push('location=' + encodeURIComponent(location));
    }
    if (department !== 'all') {
      params.push('department=' + encodeURIComponent(department)); 
    }
    url += params.join('&');

    console.log("Fetching data with URL:", url);

    $.ajax({ 
      url: url,
      type: 'GET',
      dataType: 'json',
      cache: false, 
      success: function(data) {
        if (data.error) { console.error("Backend Error:", data.error); return; }
        if (!data || !data.labels || !data.datasets) { console.error("Invalid chart data received:", data); return; } 
        updateChart(data);
      },
      error: function(jqXHR, textStatus, errorThrown) { console.log('AJAX Error:', textStatus, errorThrown, jqXHR.responseText);  }
    });
  }
  
  // Function to update the chart with new data
  function updateChart(data) {
    myChart.data.labels = data.labels;
    myChart.data.datasets = data.datasets;

    let maxStackTotal = 0;
    if (data.labels && data.labels.length > 0 && data.datasets && data.datasets.length > 0) {
      for (let i = 0; i < data.labels.length; i++) { // Loop through categories (index i)
        let currentStackTotal = 0;
        for (let j = 0; j < data.datasets.length; j++) { // Loop through datasets (statuses)
          if (data.datasets[j].data && data.datasets[j].data[i]) {
            currentStackTotal += Number(data.datasets[j].data[i]) || 0;
          } }
        if (currentStackTotal > maxStackTotal) {
          maxStackTotal = currentStackTotal;
        } } } 
    const minSuggestedMax = 5; 
    myChart.options.scales.y.suggestedMax = Math.max(maxStackTotal + Math.ceil(maxStackTotal * 0.1), minSuggestedMax);
    myChart.update();
  }

  // Initial chart setup (without initial data)
  var myChart = new Chart(ctx, {
    type: 'bar',
    data: { labels: [], datasets: [] }, 
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        x: { 
             stacked: true, // <<< Stack bars on the X axis (categories)
        },
        y: { 
             stacked: true, // <<< Stack bars on the Y axis (counts)
             beginAtZero: true,
             ticks: { stepSize: 1, precision: 0 },
             suggestedMax: 5 
        }
      },
      plugins: {
        legend: {
            display: true, 
            position: 'top', 
        },
        title: {
          display: false,
          text: 'Asset Count by Category and Status', 
          padding: { bottom: 15 }
        },
        tooltip: {
            mode: 'index',
            intersect: false 
        },
        datalabels: {
          display: true, 
          formatter: function(value, context) {
            return value > 0 ? value : '';
          },
          color: '#fff', 
          font: {
            weight: 'bold',
            size: 10 
          },
           // Option 2: Disable datalabels if too cluttered
           // display: false,
        }
      }
    }
  });

  // --- Initial Fetch and Event Listeners (remain the same) ---
  function triggerFetch() { 
    var s=$('#statusFilter').val(), 
    l=$('#locationFilter').val(), 
    d=$('#departmentFilter').val(); 
    fetchData(s,l,d); }
  
  triggerFetch();

  $('#statusFilter, #locationFilter, #departmentFilter').on('change', triggerFetch);

  // -----                            ----- //

  // -----  DONUT CHART DASHBOARD     ----- //
  var donutCtx = document.getElementById('statusDonutChart').getContext('2d');
  var statusDonutChart = new Chart(donutCtx, {
    type: 'doughnut',
    data: {
      labels: [],
      datasets: [{
        label: 'Asset Count by Status',
        data: [],
        backgroundColor: []
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'top',
        },
        title: {
          display: true,
          text: 'Asset Distribution by Status'
        }
      }
    }
  });

  // Function to fetch data for the donut chart
  function fetchDonutData() {
    $.ajax({
      url: 'dashboard_donut_chart_fetch.php',
      type: 'GET',
      dataType: 'json',
      cache: false,
      success: function(data) {
        updateDonutChart(data);
      },
      error: function(jqXHR, textStatus, errorThrown) {
        console.log('Error fetching donut data:', textStatus, errorThrown);
      }
    });
  }

  // Function to update the donut chart
  function updateDonutChart(data) {
    statusDonutChart.data.labels = data.labels;
    statusDonutChart.data.datasets[0].data = data.datasets[0].data;
    statusDonutChart.data.datasets[0].backgroundColor = data.datasets[0].backgroundColor;
    statusDonutChart.update();
  }

  // Fetch initial donut data
  fetchDonutData();

  // Fetch donut data periodically
  setInterval(fetchDonutData, 10000);
});