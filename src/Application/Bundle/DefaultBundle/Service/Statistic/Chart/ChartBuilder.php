<?php

namespace Application\Bundle\DefaultBundle\Service\Statistic\Chart;

use CMEN\GoogleChartsBundle\GoogleCharts\Charts\LineChart;
use Stfalcon\Bundle\EventBundle\Repository\TicketRepository;

/**
 * ChartBuilder.
 */
class ChartBuilder
{
    /** @var TicketRepository */
    private $ticketRepository;

    /**
     * @param TicketRepository $ticketRepository
     */
    public function __construct(TicketRepository $ticketRepository)
    {
        $this->ticketRepository = $ticketRepository;
    }

    public function columnChart($data)
    {
        $chart = new \CMEN\GoogleChartsBundle\GoogleCharts\Charts\Material\ColumnChart();
        $chart->getData()->setArrayToDataTable($data);
        $chart->getOptions()->getChart()
            ->setTitle('Статистика продаж')
            ->setSubtitle('За последние 30 дней');
        $chart->getOptions()
            ->setBars('vertical')
            ->setHeight(400)
            ->setWidth(1400)
//            ->setColors(['#1b9e77', '#d95f02', '#7570b3'])
            ->getVAxis()
            ->setFormat('decimal');

        return $chart;
    }

    /**
     * @param $data
     *
     * @return \CMEN\GoogleChartsBundle\GoogleCharts\Charts\CalendarChart
     */
    public function calendarChart($data)
    {
        $chart = new \CMEN\GoogleChartsBundle\GoogleCharts\Charts\CalendarChart();
        $chart->getData()->setArrayToDataTable($data);

        // рахуєм різницю в роках між першою датою графіка і останньою, щоб динамічно підлаштувати висоту канви
        $firstDate = array_keys($data)[1]; // в 0-му елементі заголовки графіка. перша дата в 1-му
        $lastDate = array_keys($data)[count($data) - 1];

        $years = (new \DateTime($lastDate))->format('Y') - (new \DateTime($firstDate))->format('Y') + 1;
        $chart->getOptions()->setHeight(200 * $years);

        $chart->getOptions()->getCalendar()->setCellSize(18);
        $chart->getOptions()->getNoDataPattern()->setBackgroundColor('#76a7fa');
        $chart->getOptions()->getNoDataPattern()->setColor('#a0c3ff');

        return $chart;
    }

    /**
     * @param array $data
     *
     * @return LineChart
     *
     * @throws \Exception
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
