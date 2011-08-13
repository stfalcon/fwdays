<?php

namespace Stfalcon\Bundle\PageBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Stfalcon\Bundle\PageBundle\Entity\Page
 *
 * @ORM\Table(name="pages")
 * @ORM\Entity(repositoryClass="Stfalcon\Bundle\PageBundle\Entity\PageRepository")
 */
class Page extends BasePage
{
}