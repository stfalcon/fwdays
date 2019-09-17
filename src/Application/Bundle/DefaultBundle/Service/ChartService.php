<?php

namespace Application\Bundle\DefaultBundle\Service;

use CMEN\GoogleChartsBundle\GoogleCharts\Charts\CalendarChart;
use CMEN\GoogleChartsBundle\GoogleCharts\Charts\LineChart;

/**
 * Service for initialization and configuration charts.
 */
class ChartService
{
    /**
     * Initialize the calendar chart object.
     *
     * @param array $data
     *
     * @return CalendarChart
     */
    public function calendarChart($data)
    {
        $chart = new CalendarChart();
        $chart->getData()->setArrayToDataTable($data);

        // рахуєм різницю в роках між першою датою графіка і останньою, щоб динамічно підлаштувати висоту канви
        $firstDate = array_keys($data)[1]; // в 0-му елементі заголовки графіка. перша дата в 1-му
        $lastDate = array_keys($data)[\count($data) - 1];

        $firstYear = (int) (new \DateTime($firstDate))->format('Y');
        $lastYear = (int) (new \DateTime($lastDate))->format('Y');
        $years = $lastYear - $firstYear + 1;
        $chart->getOptions()->setHeight(200 * $years);

        $chart->getOptions()->getCalendar()->setCellSize(18);
        $chart->getOptions()->getNoDataPattern()->setBackgroundColor('#76a7fa');
        $chart->getOptions()->getNoDataPattern()->setColor('#a0c3ff');

        return $chart;
    }

    /**
     * Initialize the line chart object.
     *
     * @param array $data
     *
     * @return LineChart
     */
    public function lineChart($data)
    {
        $chart = new LineChart();
        $chart->getData()->setArrayToDataTable($data);

        $chart->getOptions()->setTitle('Продажа билетов за прошедший месяц');
        $chart->getOptions()->setHeight(600);
        $chart->getOptions()->setWidth(800);
        $chart->getOptions()->getTitleTextStyle()->setBold(true);
        $chart->getOptions()->getTitleTextStyle()->setColor('#009900');
        $chart->getOptions()->getTitleTextStyle()->setItalic(true);
        $chart->getOptions()->getTitleTextStyle()->setFontName('Arial');
        $chart->getOptions()->getTitleTextStyle()->setFontSize(20);
        $chart->getOptions()->getHAxis()->getTextStyle()->setFontSize(12);
        $chart->getOptions()->getVAxis()->getTextStyle()->setFontSize(12);
        $chart->getOptions()->setCurveType('function');

        return $chart;
    }
}
