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
<div class="container">
    <?php
        $data['records'] = [];
        $data['target'] = 1000;
        $data['days'] = 50;
        $data['target_deviation'] = 10;
        $data['daily_target'] = round($data['target']/$data['days'], 2);
        $data['deviate'] = round(($data['daily_target'] / 1), 2);
        $data['target_deviation'] = 10;
        $data['start_timestamp'] = mktime(0, 0, 0, 7, 1, 2020); // July 1, 2020
        $data['index'] = 1;
        while($data['days'] > 0) {
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

            $data['records'][$data['index']]['send_email'] = ($data['records'][$data['index']]['percentage_behind_target'] >= 10)
                ? 'Yes-'
                : (
                    ($data['records'][$data['index']]['percentage_behind_target'] < -10)
                    ? 'Yes+'
                    : 'No'
                );
            $data['start_timestamp'] += 86400;
            $data['index']++;
            $data['days']--;
        }
    ?>
    <div class="p-10">
        <canvas class="border" id="chart" width="800" height="400"></canvas>
    </div>

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
                                Opportunities Created
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Cumulative Opportunities
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Target
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Cumulative Target
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
                    <?=$record['opportunities_created']?>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <?=$record['cumulative_opportunities']?>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <?=$record['daily_target']?>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <?=$record['cumulative_target']?>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <?=$record['percentage_behind_target']?>
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
    <script>
        var ctx = document.getElementById('chart').getContext('2d');
        var _data = JSON.parse('<?php echo json_encode($js_data) ?>');
        var myLineChart = new Chart(ctx, {
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