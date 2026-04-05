<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
  (function () {
    const reportCharts = [];
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

      const chart = new Chart(element, config);
      reportCharts.push(chart);

      return chart;
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

    document.querySelectorAll('[data-report-visibility-controls]').forEach(function (control) {
      const inputs = Array.from(control.querySelectorAll('input[data-target]'));
      const rows = Array.from(document.querySelectorAll('[data-report-section-row]'));
      const selectAllButton = control.querySelector('[data-select-all]');
      const storageKey = control.dataset.storageKey;

      function saveSelection() {
        if (! storageKey) {
          return;
        }

        try {
          const selected = inputs
            .filter(function (input) {
              return input.checked;
            })
            .map(function (input) {
              return input.dataset.target;
            });

          localStorage.setItem(storageKey, JSON.stringify(selected));
        } catch (error) {
          // Ignore storage failures in private mode or restricted browsers.
        }
      }

      function syncPill(input) {
        const pill = input.closest('.rb-section-toggle-pill');
        if (pill) {
          pill.classList.toggle('is-active', input.checked);
        }
      }

      function syncRows() {
        rows.forEach(function (row) {
          const sections = Array.from(row.querySelectorAll('[data-report-section]'));
          const visibleSections = sections.filter(function (section) {
            return ! section.hidden;
          });
          const hasVisibleSection = sections.some(function (section) {
            return ! section.hidden;
          });

          sections.forEach(function (section) {
            section.classList.toggle('rb-report-row-single', visibleSections.length === 1 && ! section.hidden);
          });

          row.hidden = ! hasVisibleSection;
        });
      }

      function resizeCharts() {
        window.requestAnimationFrame(function () {
          reportCharts.forEach(function (chart) {
            if (chart) {
              chart.resize();
            }
          });
        });
      }

      function applySelection(shouldPersist) {
        inputs.forEach(function (input) {
          const target = input.dataset.target;
          const sections = document.querySelectorAll('[data-report-section="' + target + '"]');

          syncPill(input);
          sections.forEach(function (section) {
            section.hidden = ! input.checked;
          });
        });

        syncRows();

        if (shouldPersist) {
          saveSelection();
        }

        resizeCharts();
      }

      if (storageKey) {
        try {
          const storedValue = localStorage.getItem(storageKey);
          const selectedTargets = storedValue ? JSON.parse(storedValue) : null;

          if (Array.isArray(selectedTargets)) {
            inputs.forEach(function (input) {
              input.checked = selectedTargets.includes(input.dataset.target);
            });
          }
        } catch (error) {
          // Ignore malformed storage data and fall back to default checked state.
        }
      }

      inputs.forEach(function (input) {
        input.addEventListener('change', function () {
          applySelection(true);
        });
      });

      if (selectAllButton) {
        selectAllButton.addEventListener('click', function () {
          inputs.forEach(function (input) {
            input.checked = true;
          });

          applySelection(true);
        });
      }

      applySelection(false);
    });
  })();
</script>
