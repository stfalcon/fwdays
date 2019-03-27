<?php

declare(strict_types=1);

namespace Application\Bundle\DefaultBundle\DataFixtures;

use Symfony\Component\Filesystem\Filesystem;

/**
 * PrepareFileStorage.
 */
class PrepareFileStorage
{
    private $tmpDirectory;
    private $remoteFilesystems;
    /** @var Filesystem */
    protected $filesystem;

    /**
     * @param string             $environment
     * @param array|Filesystem[] $remoteFilesystems
     */
    public function __construct(string $environment, array $remoteFilesystems)
    {
        $this->tmpDirectory = \implode(\DIRECTORY_SEPARATOR, [\sys_get_temp_dir(), 'upload', $environment, 'files']);
        $this->remoteFilesystems = $remoteFilesystems;
    }

    /**
     * @param Filesystem $filesystem
     *
     * @required
     */
    public function setFilesystem(Filesystem $filesystem): void
    {
        $this->filesystem = $filesystem;
    }

    /**
     *  Clear storeg for fixture files.
     */
    public function clearStorage(): void
    {
        foreach ($this->remoteFilesystems as $filesystem) {
            $contents = $filesystem->listContents('/', true);
            foreach ($contents as $contentItem) {
                if ('file' !== $contentItem['type']) {
                    continue;
                }
                $filesystem->delete($contentItem['path']);
            }
        }
    }
}
