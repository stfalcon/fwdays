<?php

namespace Stfalcon\Bundle\EventBundle\Entity\AbstractClass;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Stfalcon\Bundle\EventBundle\Entity\AbstractClass\AbstractPage;

/**
 * @ORM\MappedSuperclass
 */
abstract class AbstractNews extends AbstractPage
{
    /**
     * @var text $preview
     * @Gedmo\Translatable(fallback=true)
     * @ORM\Column(name="preview", type="text")
     */
    protected $preview;

    /**
     * @var datetime $created_at
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected $created_at;

    /**
     * Set preview
     *
     * @param text $preview
     */
    public function setPreview($preview)
    {
        $this->preview = $preview;
    }

    /**
     * Get preview
     *
     * @return text
     */
    public function getPreview()
    {
        return $this->preview;
    }


    /**
     * Set created_at
     *
     * @param datetime $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->created_at = $createdAt;
    }

    /**
     * Get created_at
     *
     * @return datetime
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }
}