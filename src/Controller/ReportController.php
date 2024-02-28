<?php

namespace App\Controller;

use App\Entity\User;
use App\Enum\TransactionEnum;
use App\Form\ReportPeriodType;
use App\Service\ChartService;
use App\Service\TransactionService;
use App\Trait\TransactionTrait;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('IS_AUTHENTICATED_FULLY')]
class ReportController extends AbstractController
{
	public function __construct(
		protected TransactionService $transactionService,
		protected ChartService       $chartService,
	)
	{
	}
	
	use TransactionTrait;
	
	/**
	 * @throws NonUniqueResultException
	 * @throws NoResultException
	 */
	#[Route('/report', name: 'app_report', methods: ['GET', 'POST'])]
	public function index(#[CurrentUser] ?User $user, Request $request):
	Response
	{
		$form = $this->createForm(ReportPeriodType::class);
		$form->handleRequest($request);
		$chart = $this->chartService->reportChart($user);
		
		if ($form->isSubmitted() && $form->isValid()) {
			$formDate = $form->getData();
			
			$start = $formDate['dateFrom'];
			$end = $formDate['dateEnd'];
			
			
			$transactions = $this->transactionService->getTransactionsPerPeriod($user, $start, $end);
			$income = $this->transactionService->getSum($transactions, TransactionEnum::Income->value);
			$expense = $this->transactionService->getSum($transactions, TransactionEnum::Expense->value);
			$data = $this->transactionService->groupTransactionsByCategory($transactions);
			
			return $this->render('report/index.html.twig', [
				'income' => $income,
				'expense' => $expense,
				'form' => $form,
				'transactionsList' => $data,
				'chart' => $chart
			]);
		}
		return $this->render('report/index.html.twig', [
			'income' => null,
			'expense' => null,
			'form' => $form,
			'transactionsList' => null,
			'chart' => $chart
		]);
	}
	
}
