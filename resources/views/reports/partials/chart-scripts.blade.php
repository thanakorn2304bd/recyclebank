<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
  (function () {
    const monthlyLabels = @json($monthlyChart['labels']);
    const monthlyDeposit = @json($monthlyChart['deposit']);
    const monthlyWithdraw = @json($monthlyChart['withdraw']);
    const cashflowLabels = @json($cashflowChart['labels']);
    const cashflowValues = @json($cashflowChart['values']);
    const statusLabels = @json($statusChart['labels']);
    const statusValues = @json($statusChart['values']);
    const materialLabels = @json($materialChart['labels']);
    const materialValues = @json($materialChart['values']);

    function createChart(id, config) {
      const element = document.getElementById(id);
      if (! element || typeof Chart === 'undefined') {
        return null;
      }

      return new Chart(element, config);
    }

    createChart('rbMonthlyChart', {
      type: 'bar',
      data: {
        labels: monthlyLabels,
        datasets: [
          {
            label: 'ยอดรับซื้อ',
            data: monthlyDeposit,
            backgroundColor: 'rgba(23, 169, 122, 0.82)',
            borderRadius: 8,
            maxBarThickness: 34,
          },
          {
            label: 'ยอดถอน',
            data: monthlyWithdraw,
            backgroundColor: 'rgba(245, 158, 11, 0.82)',
            borderRadius: 8,
            maxBarThickness: 34,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'bottom',
          },
        },
        scales: {
          x: {
            grid: {
              display: false,
            },
          },
          y: {
            beginAtZero: true,
            ticks: {
              callback(value) {
                return Number(value).toLocaleString('th-TH');
              },
            },
          },
        },
      },
    });

    createChart('rbMaterialChart', {
      type: 'bar',
      data: {
        labels: materialLabels,
        datasets: [
          {
            label: 'น้ำหนักรวม',
            data: materialValues,
            backgroundColor: [
              '#16a34a',
              '#0f766e',
              '#2563eb',
              '#0891b2',
              '#84cc16',
              '#10b981',
            ],
            borderRadius: 10,
            maxBarThickness: 42,
          },
        ],
      },
      options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false,
          },
        },
        scales: {
          x: {
            beginAtZero: true,
            ticks: {
              callback(value) {
                return Number(value).toLocaleString('th-TH');
              },
            },
          },
          y: {
            grid: {
              display: false,
            },
          },
        },
      },
    });

    createChart('rbStatusChart', {
      type: 'doughnut',
      data: {
        labels: statusLabels,
        datasets: [
          {
            data: statusValues,
            backgroundColor: ['#16a34a', '#f59e0b', '#64748b'],
            borderWidth: 0,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '68%',
        plugins: {
          legend: {
            position: 'bottom',
          },
        },
      },
    });

    createChart('rbCashflowChart', {
      type: 'doughnut',
      data: {
        labels: cashflowLabels,
        datasets: [
          {
            data: cashflowValues,
            backgroundColor: ['#10b981', '#f59e0b'],
            borderWidth: 0,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '68%',
        plugins: {
          legend: {
            position: 'bottom',
          },
        },
      },
    });
  })();
</script>
