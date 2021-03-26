<?php

declare(strict_types=1);

namespace App\Model;

/**
 * DownloadTicketData.
 */
class DownloadTicketData
{
    /** @var string|null */
    private $caption;
    /** @var string|null */
    private $class;
    /** @var string|null */
    private $url;

    /**
     * @param string|null $caption
     * @param string|null $class
     * @param string|null $url
     */
    public function __construct(?string $caption, ?string $class, ?string $url)
    {
        $this->caption = $caption;
        $this->class = $class;
        $this->url = $url;
    }

    /**
     * @return null[]|string[]
     */
    public function getTwigDate(): array
    {
        return [
            'caption' => $this->getCaption(),
            'class' => $this->getClass(),
            'url' => $this->getUrl(),
        ];
    }

    /**
     * @return string|null
     */
    public function getCaption(): ?string
    {
        return $this->caption;
    }

    /**
     * @return string|null
     */
    public function getClass(): ?string
    {
        return $this->class;
    }

    /**
     * @return string|null
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }
}
