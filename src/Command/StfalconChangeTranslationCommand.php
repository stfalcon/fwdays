<?php

namespace App\Command;

use App\Entity\Category;
use App\Entity\Event;
use App\Entity\EventPage;
use App\Entity\Page;
use App\Entity\Speaker;
use App\Entity\Sponsor;
use App\Traits\EntityManagerTrait;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * StfalconMailerCommand.
 */
class StfalconChangeTranslationCommand extends ContainerAwareCommand
{
    use EntityManagerTrait;

    /**
     * Set options.
     */
    protected function configure(): void
    {
        $this
            ->setName('stfalcon:change_translation')
            ->setDescription('Change default translation to uk');
    }

    /**
     * Execute command.
     *
     * @param InputInterface  $input  Input
     * @param OutputInterface $output Output
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $repositories = [];
        $repositories[] = $this->em->getRepository(Speaker::class)->findAll();
        $repositories[] = $this->em->getRepository(EventPage::class)->findAll();
        $repositories[] = $this->em->getRepository(Event::class)->findAll();
        $repositories[] = $this->em->getRepository(Category::class)->findAll();
        $repositories[] = $this->em->getRepository(Sponsor::class)->findAll();
        $repositories[] = $this->em->getRepository(Page::class)->findAll();
        foreach ($repositories as $repository) {
            foreach ($repository as $entity) {
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
    }
}
