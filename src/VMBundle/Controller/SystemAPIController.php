<?php

namespace VMBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 *
 */
define( '_ORM_FIXTURES_PATH',__DIR__ . '/../../../tests/Fixtures/ORM/ForExample');

/**
 * Class SystemAPIController
 * @package VMBundle\Controller
 */
class SystemAPIController extends Controller
{

    /**
     * @Route("/vm_coin_list/", defaults={"_format": "json"})
     * @Method({"GET"})
     */
    public function VMCoinListAction()
    {
        $serializer = $this->get("jms_serializer");
        $em = $this->get("doctrine");
        $coins = $em->getRepository("VMBundle:Coin")->findAll();
        $response = new JsonResponse();

        return $response->setContent($serializer->serialize($coins, 'json'));
    }
    
    /**
     * !!Work only in dev environment!!
     * 
     * @Route("/reset_data/", defaults={"_format": "json"})
     * @Method({"GET"})
     */
    public function resetDataAction()
    {
        $this->loadORMFixtures(['Coin.yml','Product.yml']);
        return new JsonResponse(true);
    }

    /**
     * !!Work only in dev environment!!
     *
     * @Route("/rob_vm/", defaults={"_format": "json"})
     * @Method({"GET"})
     */
    public function robVM()
    {
        $this->loadORMFixtures(['CoinIsEmpty.yml','Product.yml']);
        return new JsonResponse(true);
    }

    /**
     * !!Work only in dev environment!!
     * @param $fixtures
     */
    private function  loadORMFixtures($fixtures)
    {
        $kernel = $this->get('kernel');
        $this->get('hautelook_alice.doctrine.executor.fixtures_executor')
            ->execute(
                $this->get('doctrine.orm.default_entity_manager'),
                $this->get('hautelook_alice.doctrine.orm.loader_generator')->generate(
                    $this->get('hautelook_alice.fixtures.loader'),
                    $this->get('hautelook_alice.alice.fixtures.loader'),
                    $kernel->getBundles(),
                    $kernel->getEnvironment()
                ),
                $this->get('hautelook_alice.doctrine.orm.fixtures_finder')->resolveFixtures(
                    $kernel,
                    array_map(
                        function (string $fixture): string {
                            return sprintf(
                                '%s/%s',
                                rtrim(_ORM_FIXTURES_PATH, '/'),
                                ltrim($fixture, '/')
                            );
                        },
                        $fixtures
                    )
                ),
                false,                   // If true, data will not be purged
                function ($message) { }, // Can be used for logging, if needed
                true                     // If true, truncates instead of deleting
            );
    }
}
