<?php

namespace VMBundle\VendingMachine;

use Doctrine\ORM\EntityManager;
use VMBundle\Entity\Coin;
use VMBundle\Entity\Product;
use VMBundle\Entity\Transaction;
use VMBundle\Exception\InsufficientFundsException;
use VMBundle\Exception\NotEnoughChangeException;
use VMBundle\Exception\DenominationNotFoundException;
use VMBundle\Exception\ProductIsEmptyException;

/**
 * Class VendingMachineService
 * @package VMBundle\VendingMachineService
 */
class VendingMachineService
{

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var Transaction[]
     */
    private $outputTransactions;

    /**
     * VendingMachineService constructor.
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @return Transaction[]
     */
    public function getOutputTransactions()
    {
        return $this->outputTransactions;
    }

    /**
     * @param $denomination
     * @return Transaction
     */
    public function addTransaction($denomination)
    {
        $coins = $this->getCoins();
        if (!isset($coins[$denomination])) {
            throw new DenominationNotFoundException('Denomination not allowed.');
        }
        $transaction = new Transaction();
        $transaction->setCoinDenomination($denomination);
        $transaction->setType(Transaction::TYPE_INPUT);
        $transaction->setStatus(Transaction::STATUS_INJECTED);
        $this->em->persist($transaction);
        $this->em->flush();

        return $transaction;
    }

    /**
     * @param Product $product
     * @return Product
     * @throws \Exception
     */
    public function byProduct(Product $product)
    {
        $this->checkSumOnBalance($product);
        if ($product->getQuantity() <= 0) {
            throw  new ProductIsEmptyException('Product is empty');
        }
        $this->em->getConnection()->beginTransaction();
        try {
            $this->withdrawCoins($product);
            $product->setQuantity($product->getQuantity() - 1);

            $this->em->persist($product);
            $this->em->flush();
            $this->em->getConnection()->commit();

            return $product;

        } catch (\Exception $e) {
            $this->em->getConnection()->rollback();
            $this->em->close();
            throw $e;
        }
    }

    /**
     * @return array|Transaction[]
     */
    public function returnCoins()
    {
        $transactions = $this->em->getRepository("VMBundle:Transaction")->getInjectedTransactions();

        foreach ($transactions as $transaction) {
            $transaction->setStatus(Transaction::STATUS_RETURNED);
            $this->em->persist($transaction);
        }

        $this->em->flush();

        return $transactions;
    }

    /**
     * @return array|Transaction[]
     */
    public function getBalance()
    {
        return $this->em->getRepository("VMBundle:Transaction")->getInjectedTransactions();
    }

    /**
     * @return array|Transaction[]
     */
    public function getProducts()
    {
        return $this->em->getRepository("VMBundle:Product")->findAll();
    }

    /**
     * @param Product $product
     * @return bool
     */
    private function checkSumOnBalance(Product $product)
    {
        $balance = $this->em->getRepository("VMBundle:Transaction")
            ->getSumOfTransaction(Transaction::TYPE_INPUT, Transaction::STATUS_INJECTED);
        if ($product->getCost() > $balance) {
            throw new InsufficientFundsException('Not enough coins.');
        }

        return true;
    }

    /**
     * @param $product Product
     */
    private function withdrawCoins(Product $product)
    {
        $committedSum = 0;
        $transactions = $this->em->getRepository("VMBundle:Transaction")->getInjectedTransactions();
        $coins = $this->getCoins();
        foreach ($transactions as $transaction) {
            $coin = $coins[$transaction->getCoinDenomination()];
            $coin->setQuantity($coin->getQuantity() + 1);
            $this->em->persist($coin);
            $transaction->setStatus(Transaction::STATUS_CONFIRMED);
            $this->em->persist($transaction);
            $committedSum += $transaction->getCoinDenomination();
        }

        $this->setOddMoney($product, $committedSum, $coins);

        $this->em->flush();
    }

    /**
     * @return Coin[]
     */
    private function getCoins()
    {
        $coins = [];
        $coinEntities = $this->em->getRepository('VMBundle:Coin')->findBy([], ['denomination' => 'DESC']);
        /**
         * @var $coin Coin
         */
        foreach ($coinEntities as $coin) {
            $coins[$coin->getDenomination()] = $coin;
        }

        return $coins;
    }

    /**
     * @param Product $product
     * @param $committedSum int
     * @param $coins Coin[]
     */
    private function setOddMoney(Product $product, $committedSum, $coins)
    {
        $oddMoneySum = $committedSum - $product->getCost();
        foreach ($coins as $coin) {
            $count = intdiv($oddMoneySum, $coin->getDenomination());
            if ($count) {
                $oddMoneySum -= $this->addOutputTransactions($count, $coin);
            }
            if ($oddMoneySum == 0) {
                break;
            }
        }
        if ($oddMoneySum != 0) {
            throw new NotEnoughChangeException('Not enough change!');
        }
    }

    /**
     * @param $count int
     * @param $coin Coin
     * @return int
     */
    private function addOutputTransactions($count, $coin)
    {
        $sum = 0;
        for ($count; $count != 0; $count--) {
            if ($coin->getQuantity() > 0 ){
                $transaction = new Transaction();
                $transaction->setStatus(Transaction::STATUS_CONFIRMED);
                $transaction->setType(Transaction::TYPE_OUTPUT);
                $transaction->setCoinDenomination($coin->getDenomination());
                $coin->setQuantity($coin->getQuantity() - 1);
                $sum += $transaction->getCoinDenomination();
                $this->outputTransactions[] = $transaction;
                $this->em->persist($transaction);
                $this->em->persist($coin);
            }
        }
        $this->em->flush();

        return $sum;
    }
}