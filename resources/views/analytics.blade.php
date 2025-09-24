<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WhatsApp Analytics Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
</head>
<body class="bg-gray-900 text-white h-screen flex flex-col">
    <!-- Navigation -->
    <nav class="bg-gray-800 py-4 px-6 sticky top-0 z-50 border-b border-gray-700">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <div class="flex items-center space-x-2">
                <i class="fab fa-spotify text-green-500 text-2xl"></i>
                <span class="text-xl font-bold">WhatsApp<span class="text-green-500">Spotify</span></span>
            </div>
            <div class="flex items-center space-x-4">
                <a href="/" class="text-gray-300 hover:text-white px-3 py-2 rounded-md font-medium transition duration-300">
                    Home
                </a>
                <a href="/dashboard" class="text-gray-300 hover:text-white px-3 py-2 rounded-md font-medium transition duration-300">
                    Dashboard
                </a>
                <a href="/analytics" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-full font-medium transition duration-300">
                    Analytics
                </a>
                <a href="/spotify-playlists" class="text-gray-300 hover:text-white px-3 py-2 rounded-md font-medium transition duration-300">
                    My Playlists
                </a>
                <a href="/chat" class="text-gray-300 hover:text-white px-3 py-2 rounded-md font-medium transition duration-300">
                    Chat
                </a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto flex-1 p-6">
        <h1 class="text-3xl font-bold mb-6 flex items-center">
            <i class="fas fa-chart-line text-green-500 mr-3"></i>
            WhatsApp Analytics Dashboard
        </h1>
        
        <!-- Date Range Selector -->
        <div class="mb-6 p-4 bg-gray-800 rounded-lg shadow-lg">
            <label for="days" class="block text-sm font-medium mb-2">Time Range</label>
            <select id="days" class="bg-gray-700 text-white border border-gray-600 rounded p-2 focus:ring-2 focus:ring-green-500 focus:outline-none">
                <option value="7">Last 7 days</option>
                <option value="30" selected>Last 30 days</option>
                <option value="90">Last 90 days</option>
                <option value="180">Last 180 days</option>
                <option value="365">Last year</option>
            </select>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-gradient-to-r from-blue-600 to-blue-800 p-6 rounded-lg shadow-lg">
                <div class="flex items-center">
                    <div class="p-3 bg-blue-500 rounded-full mr-4">
                        <i class="fas fa-comments text-xl"></i>
                    </div>
                    <div>
                        <p class="text-gray-300">Total Messages</p>
                        <p class="text-2xl font-bold">{{ $dailyVolume->sum('count') }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-gradient-to-r from-green-600 to-green-800 p-6 rounded-lg shadow-lg">
                <div class="flex items-center">
                    <div class="p-3 bg-green-500 rounded-full mr-4">
                        <i class="fas fa-paper-plane text-xl"></i>
                    </div>
                    <div>
                        <p class="text-gray-300">Messages Sent</p>
                        <p class="text-2xl font-bold">{{ $dailyVolume->sum('sent_count') }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-gradient-to-r from-purple-600 to-purple-800 p-6 rounded-lg shadow-lg">
                <div class="flex items-center">
                    <div class="p-3 bg-purple-500 rounded-full mr-4">
                        <i class="fas fa-inbox text-xl"></i>
                    </div>
                    <div>
                        <p class="text-gray-300">Messages Received</p>
                        <p class="text-2xl font-bold">{{ $dailyVolume->sum('received_count') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Most Contacted Users -->
            <div class="bg-gray-800 p-6 rounded-lg shadow-lg">
                <h2 class="text-xl font-semibold mb-4 flex items-center">
                    <i class="fas fa-users text-green-500 mr-2"></i>
                    Most Contacted Users
                </h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="border-b border-gray-700">
                                <th class="text-left py-2">User</th>
                                <th class="text-left py-2">Messages</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($mostContactedUsers as $user)
                            <tr class="border-b border-gray-700 hover:bg-gray-750">
                                <td class="py-2">{{ $user->name ?? $user->phone }}</td>
                                <td class="py-2">{{ $user->message_count }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="2" class="py-2 text-center text-gray-500">No data available</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Peak Messaging Hours -->
            <div class="bg-gray-800 p-6 rounded-lg shadow-lg">
                <h2 class="text-xl font-semibold mb-4 flex items-center">
                    <i class="fas fa-clock text-yellow-500 mr-2"></i>
                    Peak Messaging Hours
                </h2>
                <div id="hoursChart" class="h-72"></div>
            </div>
        </div>

        <!-- More Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Messages by Day of Week -->
            <div class="bg-gray-800 p-6 rounded-lg shadow-lg">
                <h2 class="text-xl font-semibold mb-4 flex items-center">
                    <i class="fas fa-calendar-day text-blue-500 mr-2"></i>
                    Messages by Day of Week
                </h2>
                <div id="daysChart" class="h-72"></div>
            </div>

            <!-- Daily Message Volume -->
            <div class="bg-gray-800 p-6 rounded-lg shadow-lg">
                <h2 class="text-xl font-semibold mb-4 flex items-center">
                    <i class="fas fa-chart-line text-purple-500 mr-2"></i>
                    Daily Message Volume
                </h2>
                <div id="volumeChart" class="h-72"></div>
            </div>
        </div>

        <!-- Additional Analytics -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Top Active Time Periods -->
            <div class="bg-gray-800 p-6 rounded-lg shadow-lg">
                <h2 class="text-xl font-semibold mb-4 flex items-center">
                    <i class="fas fa-bolt text-orange-500 mr-2"></i>
                    Top Active Time Periods
                </h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="border-b border-gray-700">
                                <th class="text-left py-2">Day</th>
                                <th class="text-left py-2">Hour</th>
                                <th class="text-left py-2">Messages</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($activeTimePeriods as $period)
                            <tr class="border-b border-gray-700 hover:bg-gray-750">
                                <td class="py-2">{{ $period->day_of_week == 1 ? 'Sunday' : ($period->day_of_week == 2 ? 'Monday' : ($period->day_of_week == 3 ? 'Tuesday' : ($period->day_of_week == 4 ? 'Wednesday' : ($period->day_of_week == 5 ? 'Thursday' : ($period->day_of_week == 6 ? 'Friday' : ($period->day_of_week == 7 ? 'Saturday' : 'Unknown')))))) }}</td>
                                <td class="py-2">{{ $period->hour }}:00</td>
                                <td class="py-2">{{ $period->count }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="py-2 text-center text-gray-500">No data available</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Message Type Distribution -->
            <div class="bg-gray-800 p-6 rounded-lg shadow-lg">
                <h2 class="text-xl font-semibold mb-4 flex items-center">
                    <i class="fas fa-chart-pie text-teal-500 mr-2"></i>
                    Message Type Distribution
                </h2>
                <div id="typesChart" class="h-72"></div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('days').addEventListener('change', function() {
            const days = this.value;
            window.location.href = `/analytics?days=${days}`;
        });

        // Function to get day name
        function getDayName(dayIndex) {
            const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            return days[dayIndex - 1] || 'Unknown';
        }

        // Enhanced Hours Chart with ApexCharts
        const hoursOptions = {
            chart: {
                type: 'bar',
                height: 300,
                background: '#1f2937',
                toolbar: {
                    show: true
                }
            },
            plotOptions: {
                bar: {
                    borderRadius: 8,
                    dataLabels: {
                        position: 'top',
                    },
                }
            },
            dataLabels: {
                enabled: true,
                offsetX: -6,
                style: {
                    fontSize: '12px',
                    colors: ['#fff']
                }
            },
            series: [{
                name: 'Messages',
                data: @json($peakHours->pluck('count'))
            }],
            xaxis: {
                categories: @json($peakHours->pluck('hour_label')),
                position: 'bottom',
                axisBorder: {
                    show: false
                },
                axisTicks: {
                    show: false
                },
                labels: {
                    style: {
                        colors: '#d1d5db'
                    }
                }
            },
            yaxis: {
                axisBorder: {
                    show: false
                },
                labels: {
                    style: {
                        colors: '#d1d5db'
                    }
                }
            },
            tooltip: {
                theme: 'dark'
            },
            grid: {
                borderColor: '#374151',
            },
            colors: ['#10b981']
        };

        const hoursChart = new ApexCharts(document.querySelector("#hoursChart"), hoursOptions);
        hoursChart.render();

        // Days Chart
        const daysOptions = {
            chart: {
                type: 'area',
                height: 300,
                background: '#1f2937',
                toolbar: {
                    show: true
                }
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                curve: 'smooth',
                width: 3
            },
            series: [{
                name: 'Messages',
                data: @json($peakDays->pluck('count'))
            }],
            xaxis: {
                categories: @json($peakDays->pluck('day_name')),
                labels: {
                    style: {
                        colors: '#d1d5db'
                    }
                }
            },
            yaxis: {
                labels: {
                    style: {
                        colors: '#d1d5db'
                    }
                }
            },
            tooltip: {
                theme: 'dark'
            },
            grid: {
                borderColor: '#374151',
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shade: 'dark',
                    gradientToColors: ['#10b981'],
                    shadeIntensity: 1,
                    type: 'horizontal',
                    opacityFrom: 0.8,
                    opacityTo: 0.2,
                    stops: [0, 100, 100, 100]
                },
            },
            colors: ['#3b82f6']
        };

        const daysChart = new ApexCharts(document.querySelector("#daysChart"), daysOptions);
        daysChart.render();

        // Volume Chart
        const volumeOptions = {
            chart: {
                type: 'line',
                height: 300,
                background: '#1f2937',
                toolbar: {
                    show: true
                }
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                curve: 'smooth',
                width: 3
            },
            series: [
                {
                    name: 'Total',
                    data: @json($dailyVolume->pluck('count'))
                },
                {
                    name: 'Sent',
                    data: @json($dailyVolume->pluck('sent_count'))
                },
                {
                    name: 'Received',
                    data: @json($dailyVolume->pluck('received_count'))
                }
            ],
            xaxis: {
                categories: @json($dailyVolume->pluck('date')),
                labels: {
                    style: {
                        colors: '#d1d5db'
                    }
                }
            },
            yaxis: {
                labels: {
                    style: {
                        colors: '#d1d5db'
                    }
                }
            },
            tooltip: {
                theme: 'dark'
            },
            grid: {
                borderColor: '#374151',
            },
            colors: ['#3b82f6', '#ef4444', '#10b981'],
            markers: {
                size: 5,
                colors: ['#3b82f6', '#ef4444', '#10b981'],
                strokeWidth: 2,
            }
        };

        const volumeChart = new ApexCharts(document.querySelector("#volumeChart"), volumeOptions);
        volumeChart.render();

        // Message Types Chart
        const typesOptions = {
            chart: {
                type: 'donut',
                height: 300,
                background: '#1f2937',
                toolbar: {
                    show: true
                }
            },
            series: @json($messageTypes->pluck('count')),
            labels: @json($messageTypes->pluck('type_display')),
            tooltip: {
                theme: 'dark'
            },
            plotOptions: {
                pie: {
                    donut: {
                        labels: {
                            show: true,
                            name: {
                                show: true,
                                fontSize: '14px',
                                color: '#d1d5db',
                                offsetY: -5
                            },
                            value: {
                                show: true,
                                fontSize: '14px',
                                color: '#d1d5db',
                                offsetY: 5
                            },
                            total: {
                                show: true,
                                label: 'Total',
                                color: '#d1d5db',
                            }
                        }
                    }
                }
            },
            colors: ['#3b82f6', '#10b981', '#ef4444', '#8b5cf6', '#f59e0b'],
            legend: {
                position: 'bottom',
                labels: {
                    colors: '#d1d5db'
                }
            }
        };

        const typesChart = new ApexCharts(document.querySelector("#typesChart"), typesOptions);
        typesChart.render();
    </script>
</body>
</html>