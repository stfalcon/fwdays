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

    /**
     * @return LineChart
     */
    public function buildLineChartForSoldTicketsDuringLastMonth()
    {
        $ticketsBoughtDuringLastMonthGroupedByDate = $this->ticketRepository->getBoughtTicketsCountForTheLastGroupedByDateForChart();
        array_unshift($ticketsBoughtDuringLastMonthGroupedByDate, ['Дата', 'Количество билетов']);
        $chart = new LineChart();
        $chart->getData()->setArrayToDataTable($ticketsBoughtDuringLastMonthGroupedByDate);

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

        return $chart;
    }
}
