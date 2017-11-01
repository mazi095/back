<?php

namespace VMBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Coin
 *
 * @ORM\Table(name="coin")
 * @ORM\Entity(repositoryClass="VMBundle\Repository\CoinRepository")
 */
class Coin
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="denomination", type="integer", unique=true)
     */
    private $denomination;

    /**
     * @var int
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
     * Set denomination
     *
     * @param integer $denomination
     *
     * @return Coin
     */
    public function setDenomination($denomination)
    {
        $this->denomination = $denomination;

        return $this;
    }

    /**
     * Get denomination
     *
     * @return int
     */
    public function getDenomination()
    {
        return $this->denomination;
    }

    /**
     * Set quantity
     *
     * @param integer $quantity
     *
     * @return Coin
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * Get quantity
     *
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }
}

