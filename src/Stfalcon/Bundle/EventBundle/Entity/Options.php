<?php

namespace Stfalcon\Bundle\EventBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Stfalcon\Bundle\EventBundle\Entity\Options
 *
 * @ORM\Table(name="options")
 * @ORM\Entity(repositoryClass="Stfalcon\Bundle\EventBundle\Repository\OptionsRepository")
 */
class Options
{
    /**
     * @var integer $id
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string $optionName
     * @ORM\Column(name="option_name", type="string", nullable=false)
     */
    private $name;

    /**
     * @var string $optionMean
     * @ORM\Column(name="option_mean", type="string", nullable=false)
     */
    private $mean;

    /**
     * @var string $description
     * @ORM\Column(name="description", type="string", nullable=true)
     */
    private $description;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getMean()
    {
        return $this->mean;
    }

    /**
     * @param $mean
     * @return $this
     */
    public function setMean($mean)
    {
        $this->mean = $mean;

        return $this;
    }

    /**
     * @param $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }
}