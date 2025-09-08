<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Category;
use App\Form\ArticleType;
use App\Traits\ResponseTrait;
use App\Services\ArticleService;
use App\Services\RestHelperService;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/atricles')]
final class AtricleController extends AbstractFOSRestController
{
    use ResponseTrait;
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ArticleService $articleService,
        private RestHelperService $rest
    ) {}

    #[Route('/list', name: 'list_atricle',  methods: ['GET'])]
    public function index(Request $request)
    {
        $articles = $this->articleService->listAll($request);
        $this->rest->setPagination($articles);
        return $this->handleView($this->view($this->rest->getResponse()));
    }

    #[Route('/store', name: 'store_atricle',  methods: ['POST'])]
    public function store(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $article = new Article();

        $form = $this->createForm(ArticleType::class, $article);
        $form->submit($data);

        if ($form->isSubmitted() && $form->isValid()) {
            $article = $this->articleService->storeArticle($data, $article);
            if ($article == true) {
                $this->rest->succeeded()->addMessage('Article created successfully')->setData($article)->set('status', 201);
                return $this->handleView($this->view($this->rest->getResponse()));
            } else {
                $this->rest->failed()->addMessage($article)->set('status', 400);
                return $this->handleView($this->view($this->rest->getResponse()));
            }
        }
        $this->rest->failed()->setFormErrors($form->getErrors(true))->setData(null);
        return $this->handleView($this->view($this->rest->getResponse()));
    }
    #[Route('/show/{id}', name: 'show_atricle',  methods: ['GET'])]
    public function show($id)
    {
        $article = $this->articleService->showArticle($id);
        if (!$article) {
            $this->rest->failed()->addMessage('Not Found')->set('status', 404);
            return $this->handleView($this->view($this->rest->getResponse()));
        }
        $this->rest->set('article', $article);

        return $this->handleView($this->view($this->rest->getResponse()));
    }
    #[Route('/update/{id}', name: 'update_atricle',  methods: ['PUT'])]
    public function update(Request $request, $id)
    {
        $data = json_decode($request->getContent(), true);

        $article = $this->entityManager->getRepository(Article::class)->find($id);
        $category = $this->entityManager->getRepository(Category::class)->find((int)$data['category'] ?? '');

        if (!$category || !$article) {
            return $this->rest->failed()->addMessage('Invalid category or article')->set('status', 400);
        }

        $form = $this->createForm(ArticleType::class, $article);
        $form->submit($data);

        if ($form->isSubmitted() && $form->isValid()) {
            $article = $this->articleService->updateArticle();
            if ($article == true) {
                $this->rest->succeeded()->addMessage('Article updated successfully')->setData($article);
                return $this->handleView($this->view($this->rest->getResponse()));
            }
            $this->rest->failed()->addMessage('Error updating article')->set('status', 500);
            return $this->handleView($this->view($this->rest->getResponse()));
        }
        $this->rest->failed()->setFormErrors($form->getErrors(true))->setData(null);
        return $this->handleView($this->view($this->rest->getResponse()));
    }
    #[Route('/delete/{id}', name: 'delete_atricle',  methods: ['DELETE'])]
    public function delete($id)
    {
        $result = $this->articleService->deleteArticle($id);
        if ($result === true) {
            $this->rest->succeeded()->addMessage('Article deleted successfully')->set('status', 200);
            return $this->handleView($this->view($this->rest->getResponse()));
        }
        $this->rest->failed()->addMessage($result)->set('status', 500);
        return $this->handleView($this->view($this->rest->getResponse()));
    }
}
