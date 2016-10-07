<?php

namespace CartRabbit\Helper;

use Carbon\Carbon;
use CartRabbit\Models\Settings;
use CommerceGuys\Intl\Currency\CurrencyRepository;
use CommerceGuys\Pricing\Price;

class Dashboard
{
    public static function dailyChart($amounts, $type = 'day')
    {
        switch ($type) {
            case 'day':
                $chart = 'day';
                break;
            case 'monthly':
                $chart = 'monthly';
                break;
            default:
                $chart = 'day';
                break;
        }
        $year = Carbon::today()->format('Y');
        $month = Carbon::today()->format('m');

        $chart_data = array_get(array_get(array_get($amounts, $year, []), $month, []), $chart, []);

        $currency = Settings::get('currency', false);

        //day chart axis.
        $month_total = array_get(array_get(array_get($amounts, $year, []), 'month', []), $month, []);
        $chart_title = 'Daily Revenue';
        $currencyRepository = new CurrencyRepository();
        $currency = $currencyRepository->get($currency);
        // FOR GETTING SYMBOL ONLY
        $firstPrice = new Price('99.99', $currency);

        $symbol = $firstPrice->getCurrency()->getSymbol();

        $month = is_array($month) ? array_first($month) : $month;

        $month_total = is_array($month_total) ? array_first($month_total) : $month;

        $script = '
        google.load("visualization", "1", {packages:["corechart"]});
        google.setOnLoadCallback(drawChart);

       function drawChart() {
        var daychart = google.visualization.arrayToDataTable([
            ["' . $month . '","' . $month_total . '"],';
        $daily_title = 'Daily Report';
        /**
         * array of items.
         */
        if (!empty($chart_data)) {

            foreach ($chart_data as $day => $total) {

                //day charts properties.

                $script .= ' ["' . $day . '",' . $total . '],';
            }


            $script .= ']);

		//day chart options.
		var dayoptions = {
		title:"' . $daily_title . '",
		pointSize: 6,
		height:300,
		backgroundColor: "#F7F7F7",
		curveType: "function",
        pointSize: 10,
		colors: ["#9ACAE6", "#E674B9", "#D0278E","#D0278E","#e49307", "#D0278E"],
		vAxis:{
				title:"' . $chart_title . '",
				titleTextStyle:{color:"#444444"},
				baselineColor: "#ffffff",
				format:"' . $symbol . ' #",
				viewWindowMode: "explicit",
				viewWindow:{ min: 0 }
			},

		};

		//day line chart.

		var daycharts = new google.visualization.LineChart(document.getElementById("daily_report"));
		daycharts.draw(daychart, dayoptions);

		}
		';

            return $script;
        }
    }

}

