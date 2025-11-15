<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Monthly Overview and Charts -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Left section: Monthly Overview -->
                        <div>
                            <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">{{ __('Monthly Overview') }}</h3>
                            <div class="grid grid-cols-1 gap-4">
                                <div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Income') }}</p>
                                    <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ number_format($monthlyIncome, 2) }} {{$currency}}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Expenses') }}</p>
                                    <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ number_format(abs($monthlyExpenses), 2) }} {{$currency}}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Right section: Income vs Expenses chart -->
                        <div>
                            <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">{{ __('Income vs Expenses') }}</h3>
                            <div id="distributionChart" class="w-full" style="min-height: 300px;"></div>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div>
                        <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">{{ __('Transaction Trends') }}</h3>
                        <div id="transactionTrendsChart" class="w-full" style="min-height: 300px;"></div>
                    </div>
                </div>
            </div>

            <!-- Insights and Tips Section -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">{{ __('Insights') }}</h3> @if (!empty($insights))
                        <div class="mb-4 space-y-2">
                            @foreach ($insights as $insight)
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-blue-500 dark:text-blue-400 mt-0.5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"> <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                    </svg>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $insight }}</p> </div>
                            @endforeach
                        </div>
                    @endif

                    <div class="mt-4">
                        <h4 class="text-md font-semibold text-gray-800 dark:text-gray-100 mb-2">{{ __('Money-Saving Tips') }}</h4> <ul class="space-y-1">
                            @foreach ($tips as $tip)
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 dark:text-green-400 mt-0.5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"> <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="text-sm text-gray-600 dark:text-gray-400">{{ $tip }}</span> </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">{{ __('Budget Tracker') }}</h3>

                    @if ($budgetGoal > 0)
                        <div classs="space-y-4">
                            <div class="mb-4">
                                <div class="flex justify-between mb-1">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Spent') }}</span>
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Goal') }}</span>
                                </div>
                                <div class="flex justify-between mb-2">
                                    <span class="text-lg font-bold text-red-600 dark:text-red-400">
                                        {{ number_format(abs($monthlyExpenses), 2) }} {{$currency}}
                                    </span>
                                    <span class="text-lg font-bold text-gray-900 dark:text-gray-100">
                                        {{ number_format($budgetGoal, 2) }} {{$currency}}
                                    </span>
                                </div>
                            </div>

                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-4 mb-4">
                                @php
                                    $progressColor = $budgetPercentage > 100 ? 'bg-red-600' : 'bg-blue-600';
                                @endphp
                                <div class="{{ $progressColor }} h-4 rounded-full" style="width: {{ min($budgetPercentage, 100) }}%"></div>
                            </div>

                            <div class="text-center">
                                @if ($remainingBudget >= 0)
                                    <p class="text-lg font-medium text-green-600 dark:text-green-400">
                                        {{ number_format($remainingBudget, 2) }} {{$currency}} {{ __('Remaining') }}
                                    </p>
                                @else
                                    <p class="text-lg font-medium text-red-600 dark:text-red-400">
                                        {{ number_format(abs($remainingBudget), 2) }} {{$currency}} {{ __('Over Budget') }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                {{ __('You have not set a budget goal.') }}
                            </p>
                            <a href="{{ route('preferences.edit') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                {{ __('Set Budget Goal') }}
                            </a>
                        </div>
                    @endif
                </div>

            </div>

            <!-- Recent Transactions -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">{{ __('Recent Transactions') }}</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('Date') }}</th>
                                    <th class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('Type') }}</th>
                                    <th class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('Category') }}</th>
                                    <th class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('Amount') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                                @forelse ($recentTransactions as $transaction)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                                            {{ $transaction->date->format('Y-m-d') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm {{ $transaction->type === 'income' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }} font-medium">
                                            {{ __(ucfirst($transaction->type)) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                                            {{ $transaction->category }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right {{ $transaction->type === 'income' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                            {{ number_format($transaction->amount, 2) }} {{$currency}}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                            {{ __('No recent transactions') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Get new daily trend data
            const distribution = @json($distributionData);
            const currency = @json($currency ?? '$');
            const dailyIncome = @json($dailyIncomeTrends);
            const dailyExpense = @json($dailyExpenseTrends);

            // Process data for Income and Expense series
            const incomeData = dailyIncome.map(t => ({
                x: new Date(t.day),  // Use 'day' from our controller query
                y: parseFloat(t.total) // Ensure numeric precision
            }));

            const expenseData = dailyExpense.map(t => ({
                x: new Date(t.day), // Use 'day' from our controller query
                y: parseFloat(t.total)
            }));

            const trendOptions = {
                chart: {
                    type: 'line',
                    height: 300,
                    toolbar: {
                        show: false
                    },
                    animations: {
                        enabled: true
                    },
                    background: 'transparent',
                    zoom: {
                        enabled: true,
                        type: 'x',
                        autoScaleYaxis: true
                    }
                },
                // Updated Series for two lines
                series: [{
                    name: 'Income',
                    data: incomeData
                }, {
                    name: 'Expenses',
                    data: expenseData
                }],
                xaxis: {
                    type: 'datetime',
                    labels: {
                        datetimeFormatter: {
                            year: 'yyyy',
                            month: "MMM 'yy",
                            day: 'dd MMM',
                            hour: 'HH:mm'
                        },
                        style: {
                            colors: '#9ca3af'
                        }
                    },
                    tooltip: { enabled: true }
                },
                yaxis: {
                    labels: {
                        formatter: function (value) {
                            return value.toFixed(2) + ' ' + currency;
                        },
                        style: {
                            colors: '#9ca3af'
                        }
                    }
                },
                tooltip: {
                    x: {
                        format: 'dd MMM yyyy'
                    },
                    y: {
                        formatter: function (value) {
                            return value.toFixed(2) + ' ' + currency;
                        }
                    }
                },
                // Updated colors for Income (Green) and Expenses (Red)
                colors: ['#22C55E', '#EF4444'],
                stroke: {
                    curve: 'smooth',
                    width: 2
                },
                theme: {
                    mode: 'dark'
                },
                grid: {
                    borderColor: '#374151'
                },
                markers: {
                    size: 4,
                    // Remove series-specific colors
                    hover: { sizeOffset: 3 }
                },
                // Add legend to distinguish lines
                legend: {
                    labels: {
                        colors: '#9ca3af'
                    }
                }
            };

            // Pie chart for income vs expenses distribution
            const distributionOptions = {
                chart: {
                    type: 'pie',
                    height: 300,
                    animations: {
                        enabled: true
                    },
                    background: 'transparent',
                },
                series: distribution.map(item => item.value),
                labels: distribution.map(item => item.label),
                colors: ['#22C55E', '#EF4444'],  // Green for income, red for expenses
                theme: {
                    mode: 'dark'
                },
                legend: {
                    position: 'bottom',
                    labels: {
                        colors: '#9ca3af',
                        formatter: function(label, opts) {
                            const val = opts.w.globals.series[opts.seriesIndex];
                            return `${label}: ${val.toFixed(2)} ${currency}`;
                        }
                    }
                },
                tooltip: {
                    y: {
                        formatter: function (value) {
                            return value.toFixed(2) + ' ' + currency;
                        }
                    }
                },
                stroke: {
                    show: true,
                    width: 1,
                    colors: 'black'  // Black outline
                },
                responsive: [{
                    breakpoint: 480,
                    options: {
                        chart: {
                            height: 250
                        }
                    }
                }]
            };
            try {
                const trendsChart = new ApexCharts(document.querySelector("#transactionTrendsChart"), trendOptions);
                const distributionChart = new ApexCharts(document.querySelector("#distributionChart"), distributionOptions);

                trendsChart.render();
                distributionChart.render();
            } catch (error) {
                console.error('Error initializing charts:', error);
            }
        });
    </script>
    @endpush
</x-app-layout>
