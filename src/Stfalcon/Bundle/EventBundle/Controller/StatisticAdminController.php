<?php

namespace Stfalcon\Bundle\EventBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Backend StatisticAdminController
 *
 */
class StatisticAdminController extends Controller
{
    /**
     * Show Statistic
     *
     * @param Request $request Request
     *
     * @return Response
     *
     * @Method({"GET", "POST"})
     */
    public function showStatisticAction(Request $request)
    {
        $repo = $this   ->getDoctrine()
            ->getManager()
            ->getRepository('ApplicationUserBundle:User');

        //сколько людей отказалось предоставлять свои данные партнерам
        $qb = $repo->getCountBaseQueryBuilder();
        $qb->where('u.allowShareContacts = :allowShareContacts');
        $qb->setParameter('allowShareContacts', 0);
        $countRefusedProvideData = $qb->getQuery()->getSingleScalarResult();

        //сколько согласилось
        $qb = $repo->getCountBaseQueryBuilder();
        $qb->where('u.allowShareContacts = :allowShareContacts');
        $qb->setParameter('allowShareContacts', 1);
        $countAgreedProvideData = $qb->getQuery()->getSingleScalarResult();

        //сколько еще не ответило
        $qb = $repo->getCountBaseQueryBuilder();
        $qb->where($qb->expr()->isNull('u.allowShareContacts'));
        $countNotAnswered = $qb->getQuery()->getSingleScalarResult();

        //сколько было переходов
        $qb = $repo->getCountBaseQueryBuilder();
        $qb->where($qb->expr()->isNotNull('u.userReferral'));
        $countUseReferralProgram = $qb->getQuery()->getSingleScalarResult();


        return $this->render('@StfalconEvent/Statistic/statistic.html.twig', [
            'admin_pool'  => $this->container->get('sonata.admin.pool'),
            'data'        => [
                'countRefusedProvideData' => $countRefusedProvideData,
                'countAgreedProvideData'  => $countAgreedProvideData,
                'countNotAnswered'        => $countNotAnswered,
                'countUseReferralProgram' => $countUseReferralProgram
            ]
        ]);
    }
}
