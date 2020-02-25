<?php

declare(strict_types=1);

namespace App\Model;

/**
 * TranslatedMail.
 */
class TranslatedMail
{
    /**
     * @var int|null
     */
    private $id;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string|null
     */
    protected $text;

    /**
     * @var array
     */
    protected $events;

    /**
     * @param int|null    $id
     * @param string      $title
     * @param string|null $text
     * @param array       $events
     */
    public function __construct(?int $id, string $title, ?string $text, array $events)
    {
        $this->id = $id;
        $this->title = $title;
        $this->text = $text;
        $this->events = $events;
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @return array
     */
    public function getEvents(): array
    {
        return $this->events;
    }
}
