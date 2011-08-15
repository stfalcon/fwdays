<?php

namespace Stfalcon\Bundle\NewsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Stfalcon\Bundle\NewsBundle\Entity\News
 *
 * @ORM\Table(name="news")
 * @ORM\Entity(repositoryClass="Stfalcon\Bundle\NewsBundle\Entity\NewsRepository")
 */
class News extends BaseNews
{
}