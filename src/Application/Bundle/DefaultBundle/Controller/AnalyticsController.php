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
     * Shows the dynamics of daily ticket sales
     *
     * @param Event $event
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function showDailyDynamicsAction(Event $event)
    {
        $analyticsService = $this->get('app.analytics.service');

        // daily statistics
        $dailyData = $analyticsService->getDailyTicketsSoldData($event);
        array_unshift($dailyData, [
            ['label' => 'Date', 'type' => 'date'],
            ['label' => 'Tickets sold number', 'type' => 'number'],
        ]);

        $chart = $this->container->get('app.chart.service')->calendarChart($dailyData);

        // summary statistics
        $summary = $analyticsService
            ->getSummaryTicketsSoldData($event);

        return $this->render('ApplicationDefaultBundle:Analytics:daily_dynamics.html.twig', [
            'event' => $event, 'chart' => $chart, 'summary' => $summary,
        ]);
    }

    /**
     * Sales dynamics compared to past conferences (in weeks)
     *
     * @param Event $event
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function showComparisonWithPreviousEventsAction(Event $event)
    {
        $analyticsService = $this->get('app.analytics.service');
        $data = $analyticsService->getDataForCompareTicketSales($event);

        return $this->render('ApplicationDefaultBundle:Analytics:comparison_with_previous_events.html.twig', [
            'event' => $event, 'data' => $data,
        ]);
    }

}
