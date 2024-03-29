<?php

namespace App\Repository;

use App\Entity\Transaction;
use App\Entity\User;
use App\Transaction\TransactionEnum;
use DateTimeInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * @extends ServiceEntityRepository<Transaction>
 *
 * @method Transaction|null find($id, $lockMode = null, $lockVersion = null)
 * @method Transaction|null findOneBy(array $criteria, array $orderBy = null)
 * @method Transaction[]    findAll()
 * @method Transaction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TransactionRepository extends ServiceEntityRepository
{
	
	private ?User $user;
	
	public function __construct(ManagerRegistry              $registry,
								protected CategoryRepository $categoryRepository,
								protected Security           $security)
	{
		parent::__construct($registry, Transaction::class);
		$this->user = $this->security->getUser();
	}
	
	
	public function getUserTransactionsQuery(): Query
	{
		return $this->createQueryBuilder('transaction')
			->where('transaction.user = :user')
			->orderBy('transaction.id', 'DESC')
			->setParameter('user', $this->user->getId())
			->getQuery();
	}
	
	public function getUserTransactions(array $orderBy = [], int $limit = null): array
	{
		return $this->findBy(['user' => $this->user->getId()], $orderBy, $limit);
	}
	
	
	public function getTransactionsPerPeriod(DateTimeInterface $dateStart, DateTimeInterface $dateEnd):
	string|array|int|float
	{
		return $this->createQueryBuilder('transaction')
			->andWhere('transaction.date BETWEEN :startDate AND :endDate')
			->andWhere('transaction.user = :user')
			->setParameter('startDate', $dateStart)
			->setParameter('endDate', $dateEnd)
			->setParameter('user', $this->user->getId())
			->getQuery()
			->getResult();
	}
	
	/**
	 * @throws NonUniqueResultException
	 * @throws NoResultException
	 */
	public function getTransactionSum(User $user, array $conditions = [], string $type = TransactionEnum::Expense->value):
	float|bool|int|string|null
	{
		$queryBuilder = $this->createQueryBuilder('transaction')
			->select('SUM(transaction.amount)')
			->andWhere('transaction.user = :user')
			->setParameter('user', $user);
		
		foreach ($conditions as $key => $value) {
			if (in_array($key, ['date', 'category', 'sub_category_id', 'user'])) {
				$queryBuilder->andWhere("transaction.$key = :$key")
					->setParameter($key, $value);
			}
		}
		
		if ($type) {
			$queryBuilder->andWhere('transaction.type = :type')
				->setParameter('type', $type);
		}
		
		return $queryBuilder->getQuery()
			->getSingleScalarResult() ?? 0;
	}
	
	public function getAll(): array
	{
		if ($this->user)
			return $this->findBy(['user' => $this->user->getId()]);
		return [];
	}
	
	public function getAllPerCurrentMonth(): array
	{
		$list = [];
		$month = date("m");
		foreach ($this->getAll() as $transaction) {
			$transactionDate = $transaction->getDate();
			if ($month === $transactionDate->format('m'))
				$list[$transactionDate->format('d')] = $transaction;
		}
		return $list;
	}
}


