<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Category;
use App\Form\ArticleType;
use App\Form\CategoryType;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Form\PropertySearchType;
use App\Form\CategorySearchType;
use App\Form\PriceSearchType;
use App\Entity\PropertySearch;
use App\Entity\CategorySearch;
use App\Entity\PriceSearch;


final class IndexController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    // #[Route('/', name: 'app_index_index', methods: ['GET'])]
    // public function index(ArticleRepository $articleRepository): Response
    // {
    //     return $this->render('index/index.html.twig', [
    //         'articles' => $articleRepository->findAll(),
    //     ]);
    // }

    #[Route('/new', name: 'app_index_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($article);
            $entityManager->flush();

            return $this->redirectToRoute('app_index_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('index/new.html.twig', [
            'article' => $article,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_index_show', methods: ['GET'])]
    public function show(Article $article): Response
    {
        return $this->render('index/show.html.twig', [
            'article' => $article,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_index_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Article $article, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_index_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('index/edit.html.twig', [
            'article' => $article,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_index_delete', methods: ['POST'])]
    public function delete(Request $request, Article $article, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $article->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($article);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_index_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/category/newCat', name: 'new_category', methods: ['GET', 'POST'])]
    public function newCategory(Request $request): Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($category);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_index_index');
        }

        return $this->render('category/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/', name: 'app_index_index')]
    public function home(Request $request): Response
    {
        $propertySearch = new PropertySearch();
        $form = $this->createForm(PropertySearchType::class, $propertySearch);
        $form->handleRequest($request);

        $articles = [];
        if ($form->isSubmitted() && $form->isValid()) {
            $nom = $propertySearch->getNom();
            $articles =
                $this->entityManager->getRepository(Article::class)->findBy(['nom' => $nom]);
        } else {
            $articles = $this->entityManager->getRepository(Article::class)->findAll();
        }

        return $this->render('index/index.html.twig', [
            'articles' => $articles,
            'form' => $form->createView()
        ]);
    }

    #[Route('/article/search/category', name: 'article_search_category')]
    public function searchByCategory(Request $request): Response
    {
        $categorySearch = new CategorySearch();
        $form = $this->createForm(CategorySearchType::class, $categorySearch);
        $form->handleRequest($request);
        $articles = [];
        if ($form->isSubmitted() && $form->isValid()) {
            $category = $categorySearch->getCategory();
            if ($category) {
                $articles =
                    $this->entityManager->getRepository(Article::class)->findBy(['category' =>
                    $category]);
            }
        }
        return $this->render('index/searchCategory.html.twig', [
            'articles' => $articles,
            'form' => $form->createView()
        ]);
    }

    #[Route('/article/search/price', name: 'article_search_price')]
    public function searchByPrice(Request $request): Response
    {
        $priceSearch = new PriceSearch();
        $form = $this->createForm(PriceSearchType::class, $priceSearch);
        $form->handleRequest($request);
        $articles = [];
        if ($form->isSubmitted() && $form->isValid()) {
            $minPrice = $priceSearch->getMinPrice();
            $maxPrice = $priceSearch->getMaxPrice();

            $articles = $this->entityManager->getRepository(Article::class)->findAll();
            $filteredArticles = [];

            foreach ($articles as $article) {
                $prix = $article->getPrix();
                $passesMin = $minPrice === null || $prix >= $minPrice;
                $passesMax = $maxPrice === null || $prix <= $maxPrice;
                if ($passesMin && $passesMax) {
                    $filteredArticles[] = $article;
                }
            }
            $articles = $filteredArticles;
        }
        return $this->render('index/searchPrice.html.twig', [
            'articles' => $articles,
            'form' => $form->createView()
        ]);
    }
}
