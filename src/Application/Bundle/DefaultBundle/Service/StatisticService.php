<?php

namespace Application\Bundle\DefaultBundle\Service;

use Stfalcon\Bundle\EventBundle\Entity\Event;
use Stfalcon\Bundle\EventBundle\Entity\Payment;
use Doctrine\ORM\EntityManager;

class StatisticService
{
    /** @var EntityManager */
    protected $em;

    /**
     * @param EntityManager         $em
     */
    public function __construct($em)
    {
        $this->em = $em;
    }

    /**
     * Get data for daily statistics of tickets sold
     * @param Event $event
     *
     * @return array
     * @throws \Exception
     */
    public function getDataForDailyStatisticsOfTicketsSold(Event $event)
    {
        // get createdAt of the first event ticket
        $qb = $this->em->createQueryBuilder();
        $qb->select('t.createdAt')
            ->from('Stfalcon\Bundle\EventBundle\Entity\Ticket', 't')
            ->where($qb->expr()->eq('t.event', ':event'))
            ->setParameters([
                'event' => $event,
            ])
            ->orderBy('t.createdAt', 'ASC')
            ->setMaxResults(1);

        $dateFrom = new \DateTime($qb
            ->getQuery()
            ->getSingleScalarResult()
        );

        $dateTo = new \DateTime('now');

        $qb = $this->em->createQueryBuilder();
        $qb->select('DATE(p.updatedAt) as date_of_sale, COUNT(t.id) as tickets_sold_number')
            ->from('Stfalcon\Bundle\EventBundle\Entity\Ticket', 't')
            ->join('t.payment', 'p')
            ->where($qb->expr()->eq('t.event', ':event'))
            ->andWhere($qb->expr()->gte('p.updatedAt', ':date_from'))
            ->andWhere($qb->expr()->lte('p.updatedAt', ':date_to'))
            ->andWhere($qb->expr()->eq('p.status', ':status'))
            ->andWhere('p.amount > 0') // тільки реально продані квитки
            ->setParameters([
                'event' => $event,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'status' => Payment::STATUS_PAID,
            ])
            ->addGroupBy('date_of_sale')
        ;

        $results = $qb
            ->getQuery()
            ->getResult()
        ;

        // fill the possible gap in sequence of dates
        $period = new \DatePeriod($dateFrom, new \DateInterval('P1D'), $dateTo);
        $formattedResult = [];
        foreach ($period as $date) {
            $key = $date->format('Y-m-d');
            $formattedResult[$key][0] = $date;
            $formattedResult[$key][1] = null;
        }

        // merge real data with array of prepared results
        foreach ($results as $result) {
            $formattedResult[$result['date_of_sale']][1] = (int) $result['tickets_sold_number'];
        }

        return $formattedResult;
    }


    /**
     * Get data for total statistics of tickets sold
     * @param Event|null $event
     *
     * @return array
     * @throws \Exception
     */
    public function getDataForTotalStatisticsOfTicketsSold(Event $event)
    {
        $qbTicketsSold = $this->em->createQueryBuilder();
        $qbTicketsSold->select('COUNT(t.id) as tickets_sold_number, SUM(t.amount) as tickets_amount')
            ->from('Stfalcon\Bundle\EventBundle\Entity\Ticket', 't')
            ->join('t.payment', 'p')
            ->andWhere($qbTicketsSold->expr()->eq('t.event', ':event'))
            ->andWhere($qbTicketsSold->expr()->eq('p.status', ':status'))
            ->setParameters([
                'event' => $event,
                'status' => Payment::STATUS_PAID,
            ])
        ;

        $qbFreeTickets = clone $qbTicketsSold;

        $qbTicketsSold->andWhere('p.amount > 0');
        $results = $qbTicketsSold->getQuery()->getSingleResult();

        $qbFreeTickets->andWhere('p.amount = 0'); //free_tickets_number
        $resultsFreeTickets = $qbFreeTickets->getQuery()->getSingleResult();

        $results['free_tickets_number'] = $resultsFreeTickets['tickets_sold_number'];
        $results['total_tickets_number'] = $results['free_tickets_number'] + $results['tickets_sold_number'];

        return $results;
    }
}
