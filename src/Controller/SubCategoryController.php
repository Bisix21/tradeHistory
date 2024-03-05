<?php

namespace App\Controller;

use App\Entity\SubCategory;
use App\Entity\User;
use App\Form\SubCategoryType;
use App\Repository\SubCategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/sub/category')]
class SubCategoryController extends AbstractController
{
	#[Route('/get/{id}', name: 'app_sub_category_index', methods: ['GET'])]
	public function index(int $id, SubCategoryRepository $subCategoryRepository, SerializerInterface  $serializer):	Response
	{
		$subCategories = $subCategoryRepository->getAll($id);
		foreach ($subCategories as $subCategory) {
			$category = $subCategory->getCategory();
			$category->setUser(null);
		}
//		dd($subCategories);
		$subCategories = $serializer->serialize($subCategories, 'json');
		return $this->json($subCategories, Response::HTTP_OK);
	}
	
	#[Route('/new', name: 'app_sub_category_new', methods: ['GET', 'POST'])]
	public function new(Request $request, EntityManagerInterface $entityManager): Response
	{
		$subCategory = new SubCategory();
		$form = $this->createForm(SubCategoryType::class, $subCategory);
		$form->handleRequest($request);
		
		if ($form->isSubmitted() && $form->isValid()) {
			$entityManager->persist($subCategory);
			$entityManager->flush();
			
			return $this->redirectToRoute('app_sub_category_index', [], Response::HTTP_SEE_OTHER);
		}
		
		return $this->render('sub_category/new.html.twig', [
			'sub_category' => $subCategory,
			'form' => $form,
		]);
	}
	
	#[Route('/{id}', name: 'app_sub_category_show', methods: ['GET'])]
	public function show(SubCategory $subCategory): Response
	{
		return $this->render('sub_category/show.html.twig', [
			'sub_category' => $subCategory,
		]);
	}
	
	#[Route('/{id}/edit', name: 'app_sub_category_edit', methods: ['GET', 'POST'])]
	public function edit(Request $request, SubCategory $subCategory, EntityManagerInterface $entityManager): Response
	{
		$form = $this->createForm(SubCategoryType::class, $subCategory);
		$form->handleRequest($request);
		
		if ($form->isSubmitted() && $form->isValid()) {
			$entityManager->flush();
			
			return $this->redirectToRoute('app_sub_category_index', [], Response::HTTP_SEE_OTHER);
		}
		
		return $this->render('sub_category/edit.html.twig', [
			'sub_category' => $subCategory,
			'form' => $form,
		]);
	}
	
	#[Route('/{id}', name: 'app_sub_category_delete', methods: ['POST'])]
	public function delete(Request $request, SubCategory $subCategory, EntityManagerInterface $entityManager): Response
	{
		if ($this->isCsrfTokenValid('delete' . $subCategory->getId(), $request->request->get('_token'))) {
			$entityManager->remove($subCategory);
			$entityManager->flush();
		}
		
		return $this->redirectToRoute('app_sub_category_index', [], Response::HTTP_SEE_OTHER);
	}
}
