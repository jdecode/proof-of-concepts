<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Tacenda POC</title>
    <link href="https://unpkg.com/tailwindcss@^2/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js"></script>



</head>
<body>
<div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
    <div class="w-9/12">
        <div class="container">
            <?php
            $data['records'] = [];
            $data['target'] = $_GET['target'] ?? 1000;
            $data['days'] = $_GET['days'] ?? 60;
            $data['target_deviation'] = $_GET['target_deviation'] ?? 10;
            $data['daily_target'] = round($data['target']/$data['days'], 2);
            $data['deviate'] = round(($data['daily_target'] / 1), 2);
            $data['start_timestamp'] = mktime(0, 0, 0, 7, 1, 2020); // July 1, 2020
            $data['index'] = 1;
            $days = $data['days'];
            while($days > 0) {
                $data['records'][$data['index']] = [
                    'date' => date('M d, Y', $data['start_timestamp']),
                    'opportunities_created' => rand($data['daily_target'] - $data['deviate'], $data['daily_target'] + ($data['deviate']))
                ];
                $data['records'][$data['index']]['cumulative_opportunities'] =
                    $data['records'][$data['index']]['opportunities_created'] +
                    ($data['records'][$data['index']-1]['cumulative_opportunities'] ?? 0);

                $data['records'][$data['index']]['daily_target'] = $data['daily_target'];
                $data['records'][$data['index']]['cumulative_target'] =
                    $data['records'][$data['index']]['daily_target'] +
                    ($data['records'][$data['index']-1]['cumulative_target'] ?? 0);

                $data['records'][$data['index']]['percentage_behind_target'] = round(
                        (
                            $data['records'][$data['index']]['cumulative_target']
                            /
                            $data['records'][$data['index']]['cumulative_opportunities']
                        ) * 100,
                        2)
                    - 100;

                $data['records'][$data['index']]['send_email'] = ($data['records'][$data['index']]['percentage_behind_target'] >= $data['target_deviation'])
                    ? 'Yes-'
                    : (
                    ($data['records'][$data['index']]['percentage_behind_target'] < -$data['target_deviation'])
                        ? 'Yes+'
                        : 'No'
                    );
                $data['start_timestamp'] += 86400;
                $data['index']++;
                $days--;
            }
            ?>




            <!--
            Right side-bar (hidden by default)
            -->
            <div class="fixed inset-0 overflow-hidden hidden">
                <div class="absolute inset-0 overflow-hidden">
                    <section id="settings" class="translate-x-full absolute inset-y-0 pl-16 max-w-full right-0 flex transform transition ease-in-out duration-500 sm:duration-700" aria-labelledby="slide-over-heading">
                        <!--
                          Slide-over panel, show/hide based on slide-over state.

                          Entering: "transform transition ease-in-out duration-500 sm:duration-700"
                            From: "translate-x-full"
                            To: "translate-x-0"
                          Leaving: "transform transition ease-in-out duration-500 sm:duration-700"
                            From: "translate-x-0"
                            To: "translate-x-full"
                        -->
                        <div class="w-screen max-w-md">
                        </div>
                    </section>
                </div>
            </div>

        </div>
    </div>
    <div class="w-3/12">

    </div>

    <!-- 3 column wrapper -->
    <div class="flex-grow w-full max-w-7xl mx-auto xl:px-8 lg:flex">
        <!-- Left sidebar & main wrapper -->
        <div class="flex-1 min-w-0 bg-white xl:flex">
            <div class="border-b border-gray-200 xl:border-b-0 xl:flex-shrink-0 xl:w-64 xl:border-r xl:border-gray-200 bg-white">
                <div class="h-full pl-4 pr-6 py-6 sm:pl-6 lg:pl-8 xl:pl-0">
                    <!-- Start left column area -->
                    <div class="h-full relative" style="min-height: 12rem;">
                        <div class="border-2 border-gray-200 border-dashed rounded-lg">
                            <canvas class="border" id="chart" width="800" height="500"></canvas>
                        </div>
                    </div>
                    <!-- End left column area -->
                </div>
            </div>

            <div class="bg-white lg:min-w-0 lg:flex-1">
                <div class="h-full py-6 px-4 sm:px-6 lg:px-8">
                    <!-- Start main area-->
                    <div class="relative h-full" style="min-height: 36rem;">
                        <div class="border-1 border-gray-200 border-dashed rounded-lg">
                            <div class="flex flex-col">
                                <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                                    <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                                        <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                                            <table class="min-w-full divide-y divide-gray-200">
                                                <thead class="bg-gray-50">
                                                <tr>
                                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Date
                                                    </th>
                                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Today<br /> Created / Target
                                                    </th>
                                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Cumulative<br /> Created / Target
                                                    </th>
                                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" title="Negative %age : Over achieved">
                                                        Behind by (%)
                                                        <div class="w-4">
                                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                            </svg>
                                                        </div>
                                                    </th>
                                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" title="Yes- : Target not met, Yes+ : Target over-achieved, No : Within safe range">
                                                        <span class="">Alert?</span>
                                                        <div class="w-4">
                                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                            </svg>
                                                        </div>
                                                    </th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <?php
                                                $js_data = [
                                                    'labels' => []
                                                ];
                                                $js_cumulative_opp = [];
                                                $js_cumulative_target = [];

                                                $js_cumulative_opp_color = [];
                                                $js_cumulative_target_color = [];
                                                foreach ($data['records'] as $day_index => $record) {
                                                    ?>
                                                    <tr class="bg-white">
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                            <?=$record['date']?>
                                                        </td>
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                            <?=$record['opportunities_created']?> / <?=$record['daily_target']?>
                                                        </td>
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                            <?=$record['cumulative_opportunities']?> / <?=$record['cumulative_target']?>
                                                        </td>
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                            <?=round($record['percentage_behind_target'], 2)?>
                                                        </td>
                                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                            <a href="#" class="text-indigo-600 hover:text-indigo-900">
                                                                <?=$record['send_email']?>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                    <?php
                                                    array_push($js_data['labels'], $record['date']);

                                                    array_push($js_cumulative_opp, $record['cumulative_opportunities']);
                                                    array_push($js_cumulative_target, round($record['cumulative_target']));

                                                    $opp_color = $record['send_email'] == 'No'
                                                        ? 'rgba(255, 99, 132, 0.2)'
                                                        : (
                                                        $record['send_email'] == 'Yes-'
                                                            ? 'rgba(255, 0, 0, 1)'
                                                            : 'rgba(0, 255, 0, 1)'
                                                        );
                                                    array_push($js_cumulative_opp_color, $opp_color);

                                                    array_push($js_cumulative_target_color, 'rgba(153, 102, 255, 0.1');


                                                }

                                                $js_data['datasets'][] = [
                                                    'label' => 'Cumulative Opportunities',
                                                    'data' => $js_cumulative_opp,
                                                    'backgroundColor' => $js_cumulative_opp_color,
                                                    'borderColor' => $js_cumulative_opp_color,
                                                    'fill' => false,
                                                    'borderWidth' => 1
                                                ];
                                                $js_data['datasets'][] = [
                                                    'label' => 'Cumulative Target',
                                                    'data' => $js_cumulative_target,
                                                    'backgroundColor' => $js_cumulative_target_color,
                                                    'borderColor' => $js_cumulative_target_color,
                                                    'fill' => false,
                                                    'borderWidth' => 1
                                                ];
                                                ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End main area -->
                </div>
            </div>
        </div>

        <div class="bg-gray-50 pr-4 sm:pr-6 lg:pr-8 lg:flex-shrink-0 lg:border-l lg:border-gray-200 xl:pr-0">
            <div class="h-full pl-6 py-6 lg:w-80">
                <!-- Start right column area -->
                <div class="h-full relative" style="min-height: 16rem;">
                    <div class="border-2 border-gray-200 border-dashed rounded-lg">
                        <form class="h-full divide-y divide-gray-200 flex flex-col bg-white shadow-xl">
                            <div class="flex-1 h-0 overflow-y-auto">
                                <div class="py-6 px-4 bg-indigo-700 sm:px-6">
                                    <div class="flex items-center justify-between">
                                        <h2 id="slide-over-heading" class="text-lg font-medium text-white">
                                            Settings
                                        </h2>
                                    </div>
                                </div>
                                <div class="flex-1 flex flex-col justify-between">
                                    <div class="px-4 divide-y divide-gray-200 sm:px-6">
                                        <div class="space-y-6 pt-6 pb-5">
                                            <div>
                                                <label for="target" class="block text-sm font-medium text-gray-700">Target</label>
                                                <div class="mt-1">
                                                    <input value="<?=$data['target'] ?>" type="number" name="target" id="target" class="shadow-sm focus:ring-indigo-500 focus:border-gray-300 block w-full sm:text-sm border-gray-300 rounded-md p-2" placeholder="Enter a numeric value">
                                                </div>
                                            </div>
                                            <div class="">
                                                <label for="kpi" class="block text-sm font-medium text-gray-700">
                                                    KPI
                                                </label>
                                                <select id="kpi" name="kpi" class="mt-1 block w-full pl-2 pr-10 px-2 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                                    <option value="1" selected>Opportunities Created</option>
                                                    <option value="2" disabled>Calls made [disabled]</option>
                                                    <option value="3" disabled>Avg. Sales [disabled]</option>
                                                    <option value="4" disabled>Some other KPI [disabled]</option>
                                                </select>
                                            </div>
                                            <div class="">
                                                <label for="days" class="block text-sm font-medium text-gray-700">
                                                    Period (days)
                                                </label>
                                                <select id="days" name="days" class="mt-1 block w-full pl-2 pr-10 px-2 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                                    <option value="1" <?php echo $data['days'] == 1 ? 'selected' : '' ?>>Everyday (1 day)</option>
                                                    <option value="5" <?php echo $data['days'] == 5 ? 'selected' : '' ?>>Week (5 days)</option>
                                                    <option value="22" <?php echo $data['days'] == 22 ? 'selected' : '' ?>>Month (22 days)</option>
                                                    <option value="60" <?php echo $data['days'] == 60 ? 'selected' : '' ?>>Quarter (60 days)</option>
                                                </select>
                                            </div>
                                            <div class="">
                                                <label for="alert" class="block text-sm font-medium text-gray-700">
                                                    Alert frequency
                                                </label>
                                                <select id="alert" name="alert" class="mt-1 block w-full pl-2 pr-10 px-2 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                                    <option value="1" selected>Daily</option>
                                                    <option value="5" disabled>Weekly [disabled]</option>
                                                    <option value="22" disabled>Monthly [disabled]</option>
                                                    <option value="60" disabled>Quarterly [disabled]</option>
                                                </select>
                                            </div>
                                            <div class="">
                                                <label for="target_deviation" class="block text-sm font-medium text-gray-700">
                                                    Target deviation
                                                </label>
                                                <select id="target_deviation" name="target_deviation" class="mt-1 block w-full pl-2 pr-10 px-2 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                                    <option value="5" <?php echo $data['target_deviation'] == 5 ? 'selected' : '' ?>>5</option>
                                                    <option value="10" <?php echo $data['target_deviation'] == 10 ? 'selected' : '' ?>>10</option>
                                                    <option value="15" <?php echo $data['target_deviation'] == 15 ? 'selected' : '' ?>>15</option>
                                                    <option value="20" <?php echo $data['target_deviation'] == 20 ? 'selected' : '' ?>>20</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="flex-shrink-0 px-4 py-1 flex justify-end">
                                <button type="submit" class="ml-4 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Update
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
                <!-- End right column area -->
            </div>
        </div>
    </div>

    <script>
        let ctx = document.getElementById('chart').getContext('2d');
        let _data = JSON.parse('<?php echo json_encode($js_data) ?>');
        let myLineChart = new Chart(ctx, {
            type: 'line',
            data: _data,
            options: {
                scales: {
                    yAxes: [{
                        stacked: false
                    }]
                }
            }
        });
    </script>

</div>

</body>
</html>