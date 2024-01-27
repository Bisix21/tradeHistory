<?php

namespace App\Controller\Api;
use App\Transaction\Repository\TransactionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('IS_AUTHENTICATED_FULLY')]
#[Route('/api/transaction')]
class TransactionApiController extends AbstractController
{
    #[Route('/', name: 'app_api_transactionApi', methods: ['GET'])]
    public function transactions(TransactionRepository $transactionRepository): JsonResponse
    {
        $data = $transactionRepository->getAllUserCurrentTransactionsQuery('serqdrago@gmail.com');
        return new JsonResponse(['content' => $data]);
    }

    #[Route('/get/{id}', name: 'app_api_transactionApi_get', methods: ['GET'])]
    public function get(int $id, TransactionRepository $transactionRepository): JsonResponse
    {
        $data = $transactionRepository->findBy(['id' => $id]);
        return new JsonResponse(['content' => $data]);
    }

    #[Route('/edit', name: 'app_api_transactionApi_edit', methods: ['GET'])]
    public function edit()
    {

    }
}