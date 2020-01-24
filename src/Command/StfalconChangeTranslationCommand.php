<?php

namespace App\Command;

use App\Repository\CategoryRepository;
use App\Repository\EventPageRepository;
use App\Repository\EventRepository;
use App\Repository\PageRepository;
use App\Repository\SpeakerRepository;
use App\Repository\SponsorRepository;
use App\Traits\EntityManagerTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * StfalconChangeTranslationCommand.
 */
class StfalconChangeTranslationCommand extends Command
{
    use EntityManagerTrait;

    private $speakerRepository;
    private $eventPageRepository;
    private $eventRepository;
    private $categoryRepository;
    private $sponsorRepository;
    private $pageRepository;

    /**
     * @param SpeakerRepository   $speakerRepository
     * @param EventPageRepository $eventPageRepository
     * @param EventRepository     $eventRepository
     * @param CategoryRepository  $categoryRepository
     * @param SponsorRepository   $sponsorRepository
     * @param PageRepository      $pageRepository
     */
    public function __construct(SpeakerRepository $speakerRepository, EventPageRepository $eventPageRepository, EventRepository $eventRepository, CategoryRepository $categoryRepository, SponsorRepository $sponsorRepository, PageRepository $pageRepository)
    {
        parent::__construct();

        $this->speakerRepository = $speakerRepository;
        $this->eventPageRepository = $eventPageRepository;
        $this->eventRepository = $eventRepository;
        $this->categoryRepository = $categoryRepository;
        $this->sponsorRepository = $sponsorRepository;
        $this->pageRepository = $pageRepository;
    }

    /**
     * Set options.
     */
    protected function configure(): void
    {
        $this
            ->setName('stfalcon:change_translation')
            ->setDescription('Change default translation to uk');
    }

    /** {@inheritdoc} */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $repositories = [];
        $repositories[] = $this->speakerRepository->findAll();
        $repositories[] = $this->eventPageRepository->findAll();
        $repositories[] = $this->eventRepository->findAll();
        $repositories[] = $this->categoryRepository->findAll();
        $repositories[] = $this->sponsorRepository->findAll();
        $repositories[] = $this->pageRepository->findAll();
        foreach ($repositories as $entities) {
            foreach ($entities as $entity) {
                $translations = $entity->getTranslations();
                foreach ($translations as $translation) {
                    if ('uk' === $translation->getLocale()) {
                        $field = $translation->getField();
                        $action = 'set'.ucfirst($field);
                        $content = $translation->getContent();
                        if (null !== $content && \is_callable([$entity, $action])) {
                            $entity->$action($content);
                        }
                    }
                }
            }
        }

        $this->em->flush();

        return 0;
    }
}
