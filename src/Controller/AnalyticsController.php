<?php

namespace App\Controller;

use App\Entity\Event;
use App\Service\AnalyticsService;
use App\Service\ChartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

/**
 * AnalyticsController.
 */
class AnalyticsController extends AbstractController
{
    /**
     * Shows the dynamics of daily ticket sales.
     *
     * @param Event $event
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function showDailyDynamicsAction(Event $event)
    {
        $analyticsService = $this->get(AnalyticsService::class);
        // summary statistics
        $summary = $analyticsService
            ->getSummaryTicketsSoldData($event);
        $chart = null;
        // daily statistics
        $dailyData = $analyticsService->getDailyTicketsSoldData($event);
        if (!empty($dailyData)) {
            array_unshift($dailyData, [
                ['label' => 'Date', 'type' => 'date'],
                ['label' => 'Tickets sold number', 'type' => 'number'],
            ]);

            $chart = $this->container->get(ChartService::class)->calendarChart($dailyData);
        }

        return $this->render(':Analytics:daily_dynamics.html.twig', [
            'event' => $event, 'chart' => $chart, 'summary' => $summary,
        ]);
    }

    /**
     * Sales dynamics compared to past conferences (in weeks).
     *
     * @param Event $event
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function showComparisonWithPreviousEventsAction(Event $event)
    {
        $analyticsService = $this->get(AnalyticsService::class);
        $data = $analyticsService->getDataForCompareTicketSales($event);

        return $this->render(':Analytics:comparison_with_previous_events.html.twig', [
            'event' => $event, 'data' => $data,
        ]);
    }
}
