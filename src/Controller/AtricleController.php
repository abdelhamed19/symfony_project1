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

    #[Route('/list', name: 'list_articles',  methods: ['GET'])]
    /**
     * @OA\Tag(name="Articles")
     * @OA\Parameter(ref="#/components/parameters/locale")
     * @Security(name="Bearer")
     */
    public function index(Request $request)
    {
        $articles = $this->articleService->listAll($request);
        $this->rest->setPagination($articles);
        return $this->handleView($this->view($this->rest->getResponse(), Response::HTTP_OK));
    }

    #[Route('/store', name: 'store_article',  methods: ['POST'])]
    /**
     * @OA\Tag(name="Articles")
     * @OA\Parameter(ref="#/components/parameters/locale")
     * @Security(name="Bearer")
     */
    public function store(Request $request)
    {
        $dataString = $request->request->get('data');
        $file = $request->files->get('image');
        $data = json_decode($dataString, true) ?? [];
        $article = new Article();

        $form = $this->createForm(ArticleType::class, $article);
        $form->submit([
            ...$data,
            'image' => $file
        ]);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($article);
            $this->em->flush();

            $this->articleService->uploadImage($article, $file);
            
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

    #[Route('/show/{id}', name: 'show_article',  methods: ['GET'], requirements: ['id' => '\d+'])]
    /**
     * @OA\Tag(name="Articles")
     * @OA\Parameter(ref="#/components/parameters/locale")
     * @Security(name="Bearer")
     */
    public function show(Article $article)
    {
        $this->rest->set('article', $article);
        return $this->handleView($this->view($this->rest->getResponse(), Response::HTTP_OK));
    }

    #[Route('/update/{id}', name: 'update_article',  methods: ['PUT'], requirements: ['id' => '\d+'])]
    /**
     * @OA\Tag(name="Articles")
     * @OA\Parameter(ref="#/components/parameters/locale")
     * @Security(name="Bearer")
     */
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

    #[Route('/delete/{id}', name: 'delete_article',  methods: ['DELETE'], requirements: ['id' => '\d+'])]
    /**
     * @OA\Tag(name="Articles")
     * @OA\Parameter(ref="#/components/parameters/locale")
     * @Security(name="Bearer")
     */
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
