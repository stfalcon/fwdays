<?php

namespace App\Service\SonataBlock\EventBlock;

use App\Entity\Category;
use App\Entity\Event;
use App\Entity\EventBlock;
use App\Exception\RuntimeException;
use App\Repository\CategoryRepository;
use App\Repository\SponsorRepository;
use App\Traits\GrandAccessSonataBlockServiceTrait;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Block\Service\AbstractBlockService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Twig\Environment;

/**
 * PartnersEventBlockService.
 */
class PartnersEventBlockService extends AbstractBlockService
{
    use GrandAccessSonataBlockServiceTrait;

    private $partnerRepository;
    private $partnerCategoryRepository;

    /**
     * @param Environment        $twig
     * @param SponsorRepository  $partnerRepository
     * @param CategoryRepository $partnerCategoryRepository
     */
    public function __construct(Environment $twig, SponsorRepository $partnerRepository, CategoryRepository $partnerCategoryRepository)
    {
        parent::__construct($twig);

        $this->partnerRepository = $partnerRepository;
        $this->partnerCategoryRepository = $partnerCategoryRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {
        $eventBlock = $blockContext->getSetting('event_block');
        if ($eventBlock instanceof EventBlock) {
            $accessGrand = $this->accessSonataBlockService->isAccessGrand($eventBlock);
            if (!$accessGrand) {
                return new Response();
            }
            $event = $eventBlock->getEvent();
        } else {
            $event = $blockContext->getSetting('event');
        }

        if (!$event instanceof Event) {
            throw new RuntimeException();
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
