<?php

namespace App\Service\SonataBlock\EventBlock;

use App\Entity\Category;
use App\Entity\Event;
use App\Repository\CategoryRepository;
use App\Repository\SponsorRepository;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Block\Service\AbstractBlockService;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class PartnersEventBlockService.
 */
class PartnersEventBlockService extends AbstractBlockService
{
    private $partnerRepository;
    private $partnerCategoryRepository;

    /**
     * PartnersEventBlockService constructor.
     *
     * @param string             $name
     * @param EngineInterface    $templating
     * @param SponsorRepository  $partnerRepository
     * @param CategoryRepository $partnerCategoryRepository
     */
    public function __construct($name, EngineInterface $templating, SponsorRepository $partnerRepository, CategoryRepository $partnerCategoryRepository)
    {
        parent::__construct($name, $templating);

        $this->partnerRepository = $partnerRepository;
        $this->partnerCategoryRepository = $partnerCategoryRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {
        $event = $blockContext->getSetting('event');

        if (!$event instanceof Event) {
            throw new NotFoundHttpException();
        }

        $partners = $this->partnerRepository->getSponsorsOfEventWithCategory($event);

        $sortedPartners = [];
        foreach ($partners as $partner) {
            $partnerCategory = $this->partnerCategoryRepository->find($partner['id']);
            if ($partnerCategory instanceof Category) {
                $sortedPartners[$partnerCategory->isWideContainer()][$partnerCategory->getSortOrder()][$partnerCategory->getName()][] = $partner[0];
            }
        }

        if (isset($sortedPartners[0])) {
            krsort($sortedPartners[0]);
        }

        if (isset($sortedPartners[1])) {
            krsort($sortedPartners[1]);
        }

        return $this->renderResponse($blockContext->getTemplate(), [
            'block' => $blockContext->getBlock(),
            'settings' => $blockContext->getSettings(),
            'partners' => $sortedPartners,
        ], $response);
    }

    /**
     * {@inheritdoc}
     */
    public function configureSettings(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'template' => 'Redesign/Partner/partners.html.twig',
            'event' => null,
            'event_block' => null,
        ]);
    }
}
