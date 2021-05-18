<?php

namespace App\Service\SonataBlock\AdminBlock;

use App\Entity\User;
use App\Entity\UserWithDateActionInterface;
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
        $entity = $blockContext->getSetting('entity');
        if (!$entity instanceof UserWithDateActionInterface) {
            throw new \RuntimeException(\sprintf('Object of class %s is not instance of %s', \get_class($entity), UserWithDateActionInterface::class));
        }

        $referrers = [];
        $user = $entity->getUser();

        if ($user instanceof User) {
            $referrers = $this->refererRepository->findAllByUserBeforeDate($user, $entity->getActionDate());
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
            'entity' => null,
        ]);
    }
}
