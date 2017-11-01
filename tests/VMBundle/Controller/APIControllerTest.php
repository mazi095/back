<?php

namespace Tests\VMBundle\Controller;

use JMS\Serializer\Serializer;
use Tests\WebTestCase;
use Symfony\Component\HttpKernel\Client;
use VMBundle\Entity\Product;
use VMBundle\Entity\Transaction;
use VMBundle\VendingMachine\DTO\BuyProductResponse;

/**
 * Class APIControllerTest
 * @package Tests\VMBundle\Controller
 */
class APIControllerTest extends WebTestCase
{
    /** @var Client */
    private $client;


    /**
     * @var Serializer
     */
    private $serializer;

    /**
     *
     */
    public function setUp()
    {
        self::loadOrmFixtures(['Coin.yml', 'Transaction.yml', 'Product.yml']);
        static::bootKernel();
        $this->client = static::createClient();
        $this->serializer =  self::$kernel->getContainer()->get('jms_serializer');
    }

    /**
     *
     */
    public function testAddCoinAction()
    {
        $this->client->request('POST', '/add_coin/5');

        $response = $this->client->getResponse();
        $this->assertTrue($response->isSuccessful());

        $data = json_decode($response->getContent());
        $transaction =  $this->serializer->deserialize(
            $data,
            'VMBundle\Entity\Transaction',
            'json'
        );
        $this->assertInstanceOf(Transaction::class,$transaction);
    }

    /**
     *
     */
    public function testBuyProductAction()
    {
        $this->client->request('GET', '/buy_product/1');

        $response = $this->client->getResponse();
        $this->assertTrue($response->isSuccessful());

        $data = json_decode($response->getContent());
        $byProductResponse =  $this->serializer->deserialize(
            $data,
            'VMBundle\VendingMachine\DTO\BuyProductResponse',
            'json'
        );

        $this->assertInstanceOf(BuyProductResponse::class, $byProductResponse);
        $this->assertInstanceOf(Product::class, $byProductResponse->getProduct());
        $this->assertNotEmpty($byProductResponse->getOddMoney());

        foreach ($byProductResponse->getOddMoney() as $transaction) {
            $this->assertInstanceOf(Transaction::class, $transaction);
            $this->assertEquals(Transaction::TYPE_OUTPUT, $transaction->getType());
            $this->assertEquals(Transaction::STATUS_CONFIRMED, $transaction->getStatus());
        }

    }

    public function testReturnCoinAction()
    {
        $this->client->request('GET', '/return_coin/');

        $response = $this->client->getResponse();
        $this->assertTrue($response->isSuccessful());

        $data = json_decode($response->getContent());
        $transactions =  $this->serializer->deserialize(
            $data,
            'array<VMBundle\Entity\Transaction>',
            'json'
        );

        $this->assertNotEmpty($transactions);

        foreach ( $transactions as $transaction){
            $this->assertInstanceOf(Transaction::class, $transaction);
            $this->assertEquals(Transaction::STATUS_RETURNED, $transaction->getStatus());
            $this->assertEquals(Transaction::TYPE_INPUT, $transaction->getType());
        }

    }

    public function testGetBalanceAction()
    {
        $this->client->request('GET', '/balance/');

        $response = $this->client->getResponse();
        $this->assertTrue($response->isSuccessful());

        $data = json_decode($response->getContent());
        $transactions =  $this->serializer->deserialize(
            $data,
            'array<VMBundle\Entity\Transaction>',
            'json'
        );

        $this->assertNotEmpty($transactions);

        foreach ( $transactions as $transaction){
            $this->assertInstanceOf(Transaction::class, $transaction);
            $this->assertEquals(Transaction::STATUS_INJECTED, $transaction->getStatus());
            $this->assertEquals(Transaction::TYPE_INPUT, $transaction->getType());
        }
    }

    public function testGetProductListAction()
    {
        $this->client->request('GET', '/product_list/');

        $response = $this->client->getResponse();
        $this->assertTrue($response->isSuccessful());

        $data = json_decode($response->getContent());
        $products=  $this->serializer->deserialize(
            $data,
            'array<VMBundle\Entity\Product>',
            'json'
        );

        $this->assertNotEmpty($products);

        foreach ($products as $product){
            $this->assertInstanceOf(Product::class, $product);
        }
    }

}
