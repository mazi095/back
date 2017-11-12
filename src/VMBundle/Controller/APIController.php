<?php

namespace VMBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use VMBundle\Entity\Product;
use VMBundle\Exception\VMLogicException;
use VMBundle\VendingMachine\DTO\BuyProductResponse;

class APIController extends Controller
{
    /**
     * @Route("/add_coin/{denomination}", defaults={"_format": "json"})
     * @Method({"GET"})
     */
    public function addCoinAction($denomination)
    {
        $serializer = $this->get("jms_serializer");
        $service = $this->get("vm.vending_machine_service");
        try{
            $transaction = $service->addTransaction($denomination);
        }catch (VMLogicException $e){

            return new JsonResponse($e->getMessage(),$e->getCode());
        }
        $response = new JsonResponse();

        return $response->setContent($serializer->serialize($transaction, 'json'));
    }

    /**
     * @Route("/buy_product/{id}", defaults={"_format": "json"})
     * @ParamConverter("product", class="VMBundle:Product")
     */
    public function buyProductAction(Product $product)
    {
        $serializer = $this->get("jms_serializer");
        $service = $this->get("vm.vending_machine_service");
        try{
            $product = $service->byProduct($product);
            $oddMoney = $service->getOutputTransactions();
            $byProductResponse = new BuyProductResponse($product, $oddMoney);
        }catch (VMLogicException $e){

            return new JsonResponse($e->getMessage(),$e->getCode());
        }
        $response = new JsonResponse();

        return $response->setContent($serializer->serialize($byProductResponse, 'json'));
    }

    /**
     * @Route("/return_coin/", defaults={"_format": "json"})
     * @Method({"GET"})
     */
    public function returnCoinAction()
    {
        $serializer = $this->get("jms_serializer");
        $service = $this->get("vm.vending_machine_service");
        $transactions = $service->returnCoins();
        $response = new JsonResponse();

        return $response->setContent($serializer->serialize($transactions, 'json'));
    }

    /**
     * @Route("/balance/", defaults={"_format": "json"})
     * @Method({"GET"})
     */
    public function getBalanceAction()
    {
        $serializer = $this->get("jms_serializer");
        $service = $this->get("vm.vending_machine_service");
        $transactions = $service->getBalance();
        $response = new JsonResponse();

        return $response->setContent($serializer->serialize($transactions, 'json'));
    }

    /**
     * @Route("/product_list/", defaults={"_format": "json"})
     * @Method({"GET"})
     */
    public function getProductListAction()
    {
        $serializer = $this->get("jms_serializer");
        $service = $this->get("vm.vending_machine_service");
        $transactions = $service->getProducts();
        $response = new JsonResponse();

        return $response->setContent($serializer->serialize($transactions, 'json'));
    }
}
