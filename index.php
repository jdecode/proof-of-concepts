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
    <script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.8.0/dist/alpine.min.js" defer></script>


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
    <!--
  This example requires Tailwind CSS v2.0+ 
  
  This example requires some changes to your config:
  
  ```
  // tailwind.config.js
  module.exports = {
    // ...
    plugins: [
      // ...
      require('@tailwindcss/forms'),
    ]
  }
  ```
-->
    <div class="fixed inset-0 overflow-hidden">
        <div class="absolute inset-0 overflow-hidden">
            <section class="absolute inset-y-0 pl-16 max-w-full right-0 flex transform transition ease-in-out duration-500 sm:duration-700 translate-x-full" aria-labelledby="slide-over-heading">
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
                    <form class="h-full divide-y divide-gray-200 flex flex-col bg-white shadow-xl">
                        <div class="flex-1 h-0 overflow-y-auto">
                            <div class="py-6 px-4 bg-indigo-700 sm:px-6">
                                <div class="flex items-center justify-between">
                                    <h2 id="slide-over-heading" class="text-lg font-medium text-white">
                                        Settings
                                    </h2>
                                    <div class="ml-3 h-7 flex items-center">
                                        <button class="bg-indigo-700 rounded-md text-indigo-200 hover:text-white focus:outline-none focus:ring-2 focus:ring-white">
                                            <span class="sr-only">Close panel</span>
                                            <!-- Heroicon name: x -->
                                            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                <div class="mt-1">
                                    <p class="text-sm text-indigo-300">
                                        Info!
                                    </p>
                                </div>
                            </div>
                            <div class="flex-1 flex flex-col justify-between">
                                <div class="px-4 divide-y divide-gray-200 sm:px-6">
                                    <div class="space-y-6 pt-6 pb-5">
                                        <div>
                                            <label for="project_name" class="block text-sm font-medium text-gray-900">
                                                Days
                                            </label>
                                            <div class="mt-1">
                                                <input type="text" name="project_name" id="project_name" class="block w-full shadow-sm sm:text-sm focus:ring-indigo-500 focus:border-indigo-500 border-gray-300 rounded-md">
                                            </div>
                                        </div>
                                        <div>
                                            <label for="description" class="block text-sm font-medium text-gray-900">
                                                Description
                                            </label>
                                            <div class="mt-1">
                                                <textarea id="description" name="description" rows="4" class="block w-full shadow-sm sm:text-sm focus:ring-indigo-500 focus:border-indigo-500 border-gray-300 rounded-md"></textarea>
                                            </div>
                                        </div>
                                        <div>
                                            <h3 class="text-sm font-medium text-gray-900">
                                                More details
                                            </h3>
                                            <div class="mt-2">
                                                <div class="flex space-x-2">

                                                </div>
                                            </div>
                                        </div>
                                        <fieldset>
                                            ??
                                        </fieldset>
                                    </div>
                                    <div class="pt-4 pb-6">
                                        <div class="flex text-sm">
                                            <a href="#" class="group inline-flex items-center font-medium text-indigo-600 hover:text-indigo-900">
                                                <!-- Heroicon name: link -->
                                                <svg class="h-5 w-5 text-indigo-500 group-hover:text-indigo-900" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                    <path fill-rule="evenodd" d="M12.586 4.586a2 2 0 112.828 2.828l-3 3a2 2 0 01-2.828 0 1 1 0 00-1.414 1.414 4 4 0 005.656 0l3-3a4 4 0 00-5.656-5.656l-1.5 1.5a1 1 0 101.414 1.414l1.5-1.5zm-5 5a2 2 0 012.828 0 1 1 0 101.414-1.414 4 4 0 00-5.656 0l-3 3a4 4 0 105.656 5.656l1.5-1.5a1 1 0 10-1.414-1.414l-1.5 1.5a2 2 0 11-2.828-2.828l3-3z" clip-rule="evenodd" />
                                                </svg>
                                            </a>
                                        </div>
                                        <div class="mt-4 flex text-sm">
                                            <a href="#" class="group inline-flex items-center text-gray-500 hover:text-gray-900">
                                                <!-- Heroicon name: question-mark-circle -->
                                                <svg class="h-5 w-5 text-gray-400 group-hover:text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                                                </svg>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="flex-shrink-0 px-4 py-4 flex justify-end">
                            <button type="button" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Cancel
                            </button>
                            <button type="submit" class="ml-4 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Update
                            </button>
                        </div>
                    </form>
                </div>
            </section>
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