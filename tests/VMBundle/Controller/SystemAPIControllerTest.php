<?php

namespace VMBundle\Tests\Controller;

use Tests\WebTestCase;
use VMBundle\Entity\Coin;

class SystemAPIControllerTest extends WebTestCase
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
        static::bootKernel();
        $this->client = static::createClient();
        $this->serializer =  self::$kernel->getContainer()->get('jms_serializer');
    }

    public function testVMCoinList()
    {
        self::loadOrmFixtures(['Coin.yml']);

        $this->client->request('GET', '/system/vm_coin_list/');

        $response = $this->client->getResponse();
        $this->assertTrue($response->isSuccessful());

        $coinList =  $this->serializer->deserialize(
            $response->getContent(),
            'VMBundle\Entity\Coin',
            'json'
        );

        foreach ($coinList as $coin){
            $this->assertInstanceOf(Coin::class,$coin);
        }
    }

}
