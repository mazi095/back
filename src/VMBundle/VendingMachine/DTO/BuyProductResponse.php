<?php
namespace VMBundle\VendingMachine\DTO;

use VMBundle\Entity\Product;
use VMBundle\Entity\Transaction;
use JMS\Serializer\Annotation as JMS;

/**
 * Class BuyProductResponse
 * @package VMBundle\VendingMachine\DTO
 * @JMS\ExclusionPolicy("none")
 */
class BuyProductResponse
{
    /**
     * @JMS\Type("VMBundle\Entity\Product")
     * @var Product
     */
    private $product;

    /**
     * @var Transaction []
     * @JMS\Type("array<VMBundle\Entity\Transaction>")
     */
    private $oddMoney;

    /**
     * BuyProductResponse constructor.
     * @param Product $product
     * @param Transaction[] $oddMoney
     */
    public function __construct(Product $product, array $oddMoney)
    {
        $this->product = $product;
        $this->oddMoney = $oddMoney;
    }

    /**
     * @return Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @return Transaction[]
     */
    public function getOddMoney()
    {
        return $this->oddMoney;
    }


}