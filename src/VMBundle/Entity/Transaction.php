<?php

namespace VMBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * Transaction
 *
 * @ORM\Table(name="transaction")
 * @ORM\Entity(repositoryClass="VMBundle\Repository\TransactionRepository")
 * @ORM\HasLifecycleCallbacks
 * @JMS\ExclusionPolicy("none")
 */
class Transaction
{
    const STATUS_INJECTED = 'injected';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_RETURNED = 'returned';

    const TYPE_INPUT = 'input';
    const TYPE_OUTPUT = 'output';

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
     * @ORM\Column(name="type", type="string", columnDefinition="ENUM('input', 'output')")
     */
    private $type;

    /**
     * @var string
     * 
     * @JMS\Type("string")
     *
     * @ORM\Column(name="status", type="string", columnDefinition="ENUM('injected', 'confirmed', 'returned')")
     */
    private $status;

    /**
     * @var int
     * 
     * @JMS\Type("integer")
     *
     * @ORM\Column(name="coin_denomination", type="integer")
     */
    private $coinDenomination;

    /**
     * @var \DateTime
     * 
     * @JMS\Type("DateTime<'Y-m-d h:i:s'>")
     *
     * @ORM\Column(name="created", type="datetime")
     */
    private $created;

    /**
     * @var \DateTime
     *
     * @JMS\Type("DateTime<'Y-m-d h:i:s'>")
     *
     * @ORM\Column(name="updated", type="datetime")
     */
    private $updated;


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
     * Set type
     *
     * @param string $type
     *
     * @return Transaction
     */
    public function setType($type)
    {
        if (!in_array($type, array(self::TYPE_INPUT, self::TYPE_OUTPUT))) {
            throw new \InvalidArgumentException("Invalid type");
        }
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set status
     *
     * @param string $status
     *
     * @return Transaction
     */
    public function setStatus($status)
    {
        if (!in_array($status, array(self::STATUS_INJECTED, self::STATUS_CONFIRMED, self::STATUS_RETURNED))) {
            throw new \InvalidArgumentException("Invalid status");
        }

        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set coinDenomination
     *
     * @param integer $coinDenomination
     *
     * @return Transaction
     */
    public function setCoinDenomination($coinDenomination)
    {
        $this->coinDenomination = $coinDenomination;

        return $this;
    }

    /**
     * Get coinDenomination
     *
     * @return int
     */
    public function getCoinDenomination()
    {
        return $this->coinDenomination;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return Transaction
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set updated
     *
     * @param \DateTime $updated
     *
     * @return Transaction
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated
     *
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /** @ORM\PrePersist */
    public function doStuffOnPrePersist()
    {
        $this->created = new \DateTime();
        $this->updated = new \DateTime();
    }

    /** @ORM\PreUpdate */
    public function doStuffOnPreUpdate()
    {
        $this->updated = new \DateTime();
    }
}

