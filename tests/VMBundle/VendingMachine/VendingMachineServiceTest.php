<?php

namespace Tests\VMBundle\VendingMachine;


use Tests\WebTestCase;
use VMBundle\Entity\Product;
use VMBundle\Entity\Transaction;
use VMBundle\VendingMachine\VendingMachineService;

/**
 * Class VendingMachineServiceTest
 * @package Tests\VMBundle\VendingMachine
 */
class VendingMachineServiceTest extends WebTestCase
{

    /**
     * @var VendingMachineService
     */
    private $vendingMachineService;

    /**
     *
     */
    protected function setUp()
    {
        static::bootKernel();
        $this->vendingMachineService = self::$kernel->getContainer()->get('vm.vending_machine_service');
    }

    /**
     * @dataProvider getDenominationVariants
     */
    public function testAddTransaction($denomination)
    {
        self::loadOrmFixtures(['Coin.yml']);
        $transaction = $this->vendingMachineService->addTransaction($denomination);
        $this->assertInstanceOf(Transaction::class, $transaction, 'Not Transaction Entity');
        $this->assertEquals($transaction->getStatus(), Transaction::STATUS_INJECTED);
        $this->assertEquals($transaction->getType(), Transaction::TYPE_INPUT);
        $this->assertEquals($transaction->getCoinDenomination(), $denomination);
    }

    /**
     * @return array
     */
    public function getDenominationVariants()
    {
        return [
            [1],
            [2],
            [5],
            [10],
        ];
    }

    /**
     * @test
     * @expectedException \VMBundle\Exception\DenominationNotFoundException
     */
    public function invalidDenominationToAddTansaction()
    {
        self::loadOrmFixtures(['Coin.yml']);
        $transaction = $this->vendingMachineService->addTransaction(100);
    }

    /**
     *
     */
    public function testBuyProduct()
    {
        self::loadOrmFixtures(['Coin.yml','Transaction.yml','Product.yml']);
        $em = self::$kernel->getContainer()->get('doctrine.orm.default_entity_manager');
        $testProductTitle = 'Кофе';
        /**
         * @var $product Product
         */
        $product = $em->getRepository('VMBundle:Product')->findOneBy(['title' => $testProductTitle]);
        $this->assertInstanceOf(Product::class, $product);
        $this->assertEquals($product->getTitle(), $testProductTitle);
        $productQuantity = $product->getQuantity();

        $sumOfInjectedTransactions = $em->getRepository('VMBundle:Transaction')->getSumOfTransaction(
            Transaction::TYPE_INPUT,
            Transaction::STATUS_INJECTED
        );

        $product = $this->vendingMachineService->byProduct($product);
        $this->assertInstanceOf(Product::class, $product);
        $this->assertEquals($product->getQuantity(), --$productQuantity);

        $outputTransactions = $this->vendingMachineService->getOutputTransactions();
        $oddMoney = 0;
        foreach ($outputTransactions as $transaction){
            $this->assertInstanceOf(Transaction::class, $transaction);
            $this->assertEquals($transaction->getStatus(), Transaction::STATUS_CONFIRMED);
            $this->assertEquals($transaction->getType(), Transaction::TYPE_OUTPUT);
            $oddMoney += $transaction->getCoinDenomination();
        }
        $this->assertEquals($oddMoney, $sumOfInjectedTransactions - $product->getCost());
    }

    /**
     * @test
     * @expectedException \VMBundle\Exception\ProductIsEmptyException
     */
    public function tryToBuyAbsentProduct()
    {
        self::loadOrmFixtures(['Coin.yml','Transaction.yml','Product.yml']);
        $em = self::$kernel->getContainer()->get('doctrine.orm.default_entity_manager');
        /**
         * @var $product Product
         */
        $product = $em->getRepository('VMBundle:Product')->findOneBy(['title' => 'Товар отсутствует']);
        $this->assertInstanceOf(Product::class, $product);
        $this->assertEquals($product->getQuantity(), 0);
        $product = $this->vendingMachineService->byProduct($product);
    }

    /**
     * @test
     * @expectedException \VMBundle\Exception\InsufficientFundsException
     */
    public function NotEnoughMoneyToProduct()
    {
        self::loadOrmFixtures(['Coin.yml','Transaction.yml','Product.yml']);
        $em = self::$kernel->getContainer()->get('doctrine.orm.default_entity_manager');
        /**
         * @var $product Product
         */
        $product = $em->getRepository('VMBundle:Product')->findOneBy(['title' => 'Дорогой товар']);
        $this->assertInstanceOf(Product::class, $product);
        $product = $this->vendingMachineService->byProduct($product);
    }


    /**
     * @test
     * @expectedException \VMBundle\Exception\NotEnoughChangeException
     */
    public function NotEnoughChangeInVM()
    {
        self::loadOrmFixtures(['AbsentCoin.yml','TransactionTwoTenDenominationCoin.yml','Product.yml']);
        $em = self::$kernel->getContainer()->get('doctrine.orm.default_entity_manager');
        /**
         * @var $product Product
         */
        $product = $em->getRepository('VMBundle:Product')->findOneBy(['title' => 'Кофе']);
        $this->assertInstanceOf(Product::class, $product);
        $product = $this->vendingMachineService->byProduct($product);
    }

    public function testReturnCoins()
    {
        self::loadOrmFixtures(['AbsentCoin.yml','TransactionTwoTenDenominationCoin.yml']);
        $em = self::$kernel->getContainer()->get('doctrine.orm.default_entity_manager');

        $injectedTransactions = $em->getRepository("VMBundle:Transaction")->getInjectedTransactions();

        $this->assertNotEmpty($injectedTransactions);
        foreach ($injectedTransactions as $injectedTransaction){
            $this->assertEquals(Transaction::STATUS_INJECTED, $injectedTransaction->getStatus());
            $this->assertEquals(Transaction::TYPE_INPUT, $injectedTransaction->getType());
        }

        $returnedTransactions = $this->vendingMachineService->returnCoins();

        foreach ($returnedTransactions as $returnedTransaction){
            $this->assertEquals(Transaction::STATUS_RETURNED, $returnedTransaction->getStatus());
        }
    }

    public function testGetBalance()
    {
        self::loadOrmFixtures(['TransactionTwoTenDenominationCoin.yml']);

        $injectedTransactions  = $this->vendingMachineService->getBalance();

        $this->assertNotEmpty($injectedTransactions);

        $balance = 0;

        foreach ($injectedTransactions as $injectedTransaction){
            $this->assertEquals(Transaction::STATUS_INJECTED, $injectedTransaction->getStatus());
            $this->assertEquals(Transaction::TYPE_INPUT, $injectedTransaction->getType());
            $balance += $injectedTransaction->getCoinDenomination();
        }

        $this->assertEquals(20, $balance, "20 ruble must be on balance.");
    }

    public function testGetProduct()
    {
        self::loadOrmFixtures(['Product.yml']);

        $products = $this->vendingMachineService->getProducts();

        $this->assertNotEmpty($products);

        foreach ($products as $product){
            $this->assertInstanceOf(Product::class, $product);
        }
    }
}