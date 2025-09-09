<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use App\Services\ArticleService;
use App\Services\RestHelperService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Symfony\Component\HttpFoundation\Response;

#[Route('/api/atricles')]
final class AtricleController extends AbstractFOSRestController
{
    public function __construct(
        private EntityManagerInterface $em,
        private ArticleService $articleService,
        private RestHelperService $rest
    ) {}

    #[Route('/list', name: 'list_atricle',  methods: ['GET'])]
    public function index(Request $request)
    {
        $articles = $this->articleService->listAll($request);
        $this->rest->setPagination($articles);
        return $this->handleView($this->view($this->rest->getResponse(), Response::HTTP_OK));
    }

    #[Route('/store', name: 'store_atricle',  methods: ['POST'])]
    public function store(Request $request)
    {
        $data = $request->request->all();
        $article = new Article();

        $form = $this->createForm(ArticleType::class, $article);
        $form->submit($data);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($article);
            $this->em->flush();
            $this->rest
                ->succeeded()
                ->setData($article);
            return $this->handleView($this->view($this->rest->getResponse(), Response::HTTP_CREATED));
        }
        $this->rest
            ->failed()
            ->setFormErrors($form->getErrors(true))
            ->setData(null);
        return $this->handleView($this->view($this->rest->getResponse(), Response::HTTP_BAD_REQUEST));
    }

    #[Route('/show/{id}', name: 'show_atricle',  methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(Article $article)
    {
        $this->rest->set('article', $article);
        return $this->handleView($this->view($this->rest->getResponse(), Response::HTTP_OK));
    }

    #[Route('/update/{id}', name: 'update_atricle',  methods: ['PUT'], requirements: ['id' => '\d+'])]
    public function update(Request $request, Article $article)
    {
        $data = $request->request->all();

        $form = $this->createForm(ArticleType::class, $article);
        $form->submit($data);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();
            $this->rest
                ->succeeded()
                ->addMessage('Article updated successfully')
                ->setData($article);
            return $this->handleView($this->view($this->rest->getResponse(), Response::HTTP_OK));
        }
        $this->rest
            ->failed()
            ->setFormErrors($form->getErrors(true))
            ->setData(null);
        return $this->handleView($this->view($this->rest->getResponse(), Response::HTTP_BAD_REQUEST));
    }

    #[Route('/delete/{id}', name: 'delete_atricle',  methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function delete(Article $article)
    {
        try {
            $this->em->remove($article);
            $this->em->flush();

            $this
                ->rest
                ->succeeded()
                ->setData($article);
            return $this->handleView($this->view($this->rest->getResponse(), Response::HTTP_OK));
        } catch (ForeignKeyConstraintViolationException $e) {
            $this->rest
                ->failed()
                ->addMessage('Unable to delete the article');
        }
    }
}
