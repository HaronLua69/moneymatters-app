<div class="space-y-8">

    {{-- ── All-Time Savings Card ───────────────────────────────────────────────── --}}
    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-6">
        <p class="text-sm font-medium text-blue-600 dark:text-blue-400 uppercase tracking-wide">Current All-Time Savings</p>
        <p class="text-4xl font-bold text-blue-700 dark:text-blue-300 mt-2">₱{{ number_format($savings, 2) }}</p>
        <p class="text-xs text-blue-500 dark:text-blue-400 mt-2">
            Initial ₱{{ number_format(auth()->user()->initial_balance ?? 0, 2) }}
            + Income
            − Expense
        </p>
    </div>

    {{-- ── Charts Row ──────────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Savings Trend Line Chart --}}
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl p-6">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Savings Trend — Last 6 Months</h3>
            <div class="relative h-64">
                <canvas id="savings-trend-chart"></canvas>
            </div>
        </div>

        {{-- Monthly Income vs Expense Bar Chart --}}
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl p-6">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Monthly Income vs Expense — Last 6 Months</h3>
            <div class="relative h-64">
                <canvas id="monthly-bar-chart"></canvas>
            </div>
        </div>

    </div>

</div>

@script
<script>
    (function () {
        const trendData = @json($trendData);
        const barData   = @json($barData);

        function initCharts() {
            window.renderChart('savings-trend-chart', {
                type: 'line',
                data: {
                    labels: trendData.labels,
                    datasets: [{
                        label: 'Savings (₱)',
                        data: trendData.data,
                        borderColor: 'rgb(59,130,246)',
                        backgroundColor: 'rgba(59,130,246,0.1)',
                        fill: true,
                        tension: 0.4,
                        pointRadius: 5,
                        pointHoverRadius: 7,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: {
                            ticks: {
                                callback: v => '₱' + v.toLocaleString()
                            }
                        }
                    }
                }
            });

            window.renderChart('monthly-bar-chart', {
                type: 'bar',
                data: {
                    labels: barData.labels,
                    datasets: [
                        {
                            label: 'Income',
                            data: barData.income,
                            backgroundColor: 'rgba(34,197,94,0.7)',
                            borderColor: 'rgb(22,163,74)',
                            borderWidth: 1,
                            borderRadius: 4,
                        },
                        {
                            label: 'Expense',
                            data: barData.expense,
                            backgroundColor: 'rgba(239,68,68,0.7)',
                            borderColor: 'rgb(220,38,38)',
                            borderWidth: 1,
                            borderRadius: 4,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top' }
                    },
                    scales: {
                        y: {
                            ticks: {
                                callback: v => '₱' + v.toLocaleString()
                            }
                        }
                    }
                }
            });
        }

        // Init on first render
        initCharts();

        // Re-init when Livewire navigates back to this page
        document.addEventListener('charts:reinit', initCharts);
    })();
</script>
@endscript
