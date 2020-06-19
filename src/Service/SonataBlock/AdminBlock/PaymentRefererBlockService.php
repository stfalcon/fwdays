<?php

namespace App\Service\SonataBlock\AdminBlock;

use App\Entity\Payment;
use App\Entity\User;
use App\Repository\Referer\RefererRepository;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Block\Service\AbstractBlockService;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * PaymentRefererBlockService.
 */
class PaymentRefererBlockService extends AbstractBlockService
{
    /** @var RefererRepository */
    private $refererRepository;

    /**
     * @param string            $name
     * @param EngineInterface   $templating
     * @param RefererRepository $refererRepository
     */
    public function __construct($name, EngineInterface $templating, RefererRepository $refererRepository)
    {
        parent::__construct($name, $templating);

        $this->refererRepository = $refererRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {
        $payment = $blockContext->getSetting('payment');
        if (!$payment instanceof Payment) {
            throw new \RuntimeException(\sprintf('Object of class %s is not instance of %s', \get_class($payment), Payment::class));
        }

        $referrers = [];
        $user = $payment->getUser();

        if ($user instanceof User) {
            $referrers = $this->refererRepository->findAllByUserBeforeDate($user, $payment->getUpdatedAt());
        }

        return $this->renderResponse($blockContext->getTemplate(), [
            'block' => $blockContext->getBlock(),
            'referrers' => $referrers,
        ], $response);
    }

    /**
     * {@inheritdoc}
     */
    public function configureSettings(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'template' => 'Admin/referrers.html.twig',
            'payment' => null,
        ]);
    }
}
