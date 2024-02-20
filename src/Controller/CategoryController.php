<?php

namespace App\Controller;

use App\Category\Entity\Category;
use App\Category\Repository\CategoryRepository;
use App\Entity\User;
use App\Form\CategoryType;
use App\Security\Access;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('IS_AUTHENTICATED_FULLY')]
#[Route('/category')]
class CategoryController extends AbstractController
{
	public function __construct(private readonly Access $access)
	{
	}
	
	#[Route('/', name: 'app_category_index', methods: ['GET'])]
	public function index(#[CurrentUser] ?User $user, CategoryRepository $categoryRepository): Response
	{
		return $this->render('category/index.html.twig', [
			'categories' => $categoryRepository->getAll($user),
		]);
	}
	
	#[Route('/new', name: 'app_category_new', methods: ['GET', 'POST'])]
	public function new(#[CurrentUser] ?User $user, Request $request, EntityManagerInterface $entityManager): Response
	{
		$category = new Category();
		$form = $this->createForm(CategoryType::class, $category);
		$form->handleRequest($request);
		
		if ($form->isSubmitted() && $form->isValid()) {
			$category->setUser($user);
			$entityManager->persist($category);
			$entityManager->flush();
			
			return $this->redirectToRoute('app_category_index', [], Response::HTTP_SEE_OTHER);
		}
		
		return $this->render('category/new.html.twig', [
			'category' => $category,
			'form' => $form,
		]);
	}
	
	#[Route('/{id}/edit', name: 'app_category_edit', methods: ['GET', 'POST'])]
	public function edit(#[CurrentUser] ?User $user, Request $request, Category $category, EntityManagerInterface $entityManager):
	Response
	{
		$this->access->accessDenied($category, $user);
		$form = $this->createForm(CategoryType::class, $category);
		$form->handleRequest($request);
		
		if ($form->isSubmitted() && $form->isValid()) {
			$entityManager->flush();
			
			return $this->redirectToRoute('app_category_index', [], Response::HTTP_SEE_OTHER);
		}
		
		return $this->render('category/edit.html.twig', [
			'category' => $category,
			'form' => $form,
		]);
	}
	
	#[Route('/{id}', name: 'app_category_delete', methods: ['POST'])]
	public function delete(#[CurrentUser] ?User $user, Request $request, Category $category, EntityManagerInterface $entityManager):
	Response
	{
		$this->access->accessDenied($category, $user);
		if ($this->isCsrfTokenValid('delete' . $category->getId(), $request->request->get('_token'))) {
			$entityManager->remove($category);
			$entityManager->flush();
		}
		
		return $this->redirectToRoute('app_category_index', [], Response::HTTP_SEE_OTHER);
	}
}
