<?php

namespace VMBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;


/**
 * Product
 *
 * @ORM\Table(name="product")
 * @ORM\Entity(repositoryClass="VMBundle\Repository\ProductRepository")
 */
class Product
{
    /**
     * @var int
     *
     * @JMS\Type("integer")
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @JMS\Type("string")
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @var int
     *
     * @JMS\Type("integer")
     *
     * @ORM\Column(name="cost", type="integer")
     */
    private $cost;


    /**
     * @var int
     *
     * @JMS\Type("integer")
     *
     * @ORM\Column(name="quantity", type="integer")
     */
    private $quantity;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return Product
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set cost
     *
     * @param integer $cost
     *
     * @return Product
     */
    public function setCost($cost)
    {
        $this->cost = $cost;

        return $this;
    }

    /**
     * Get cost
     *
     * @return int
     */
    public function getCost()
    {
        return $this->cost;
    }

    /**
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param int $quantity
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
    }

}

