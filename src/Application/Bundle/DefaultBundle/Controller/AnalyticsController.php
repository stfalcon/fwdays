<?php

namespace Application\Bundle\DefaultBundle\Controller;

use Stfalcon\Bundle\EventBundle\Entity\Event;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;

/**
 * AnalyticsController.
 */
class AnalyticsController extends Controller
{
    /**
     * Show general statistics for event.
     *
     * @param Event $event
     *
     * @Template("@ApplicationDefault/Analytics/generalStatistics.html.twig")
     *
     * @return array
     */
    public function generalStatisticsAction(Event $event)
    {
        $statisticService = $this->get('app.statistic.service');

        // подобова статистика для графіка календаря
        $dailyData = $statisticService
            ->getDataForDailyStatisticsOfTicketsSold($event);
        array_unshift(
            $dailyData,
            [['label' => 'Date', 'type' => 'date'], ['label' => 'Tickets sold number', 'type' => 'number']]
        );

        $chart = $this->container->get('app.statistic.chart_builder')->calendarChart($dailyData);

        // загальна статистика
        $totalData = $statisticService
            ->getDataForTotalStatisticsOfTicketsSold($event);

        return array('event' => $event, 'chart' => $chart, 'total' => $totalData);
    }

    /**
     * Sales dynamics compared to past conferences (in weeks).
     *
     * @param Event $event
     *
     * @return Response
     */
    public function forecastedSalesAction(Event $event)
    {
        $statisticService = $this->get('app.statistic.service');
        $data = $statisticService->getDataForForecastingTicketsSales($event);

        return $this->render(
            '@ApplicationDefault/Analytics/forecastedSales.html.twig',
            [
                'event' => $event,
                'data' => $data,
            ]
        );
    }
}
