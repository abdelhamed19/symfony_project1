<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Form\CategoryImageType;
use App\Form\UpdateSortOrderType;
use App\Services\CategoryService;
use App\Services\RestHelperService;
use Doctrine\ORM\EntityManagerInterface;
use App\Job\Message\CategoryDeleteMessage;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Messenger\MessageBusInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;

#[Route('/api/categories')]
final class CategoryController extends AbstractFOSRestController
{

    public function __construct(
        private CategoryService $categoryService,
        private EntityManagerInterface $em,
        private RestHelperService $rest
    ) {}

    #[Route('/index', name: 'all_categories', methods: ['GET'])]
    /**
     * @OA\Tag(name="Categories")
     * @OA\Parameter(ref="#/components/parameters/locale")
     * @Security(name="Bearer")
     */
    public function index(Request $request)
    {
        $page = $request->query->getInt('page', 1);
        $data = $this->categoryService->listAll($page);

        $this->rest
            ->succeeded()
            ->setPagination($data);

        return $this->handleView($this->view($this->rest->getResponse(), Response::HTTP_OK));
    }

    #[Route('/create', name: 'create_category', methods: ['POST'])]
    /**
     * @OA\Tag(name="Categories")
     * @OA\Parameter(ref="#/components/parameters/locale")
     * @Security(name="Bearer")
     */
    public function createWithoutImage(Request $request): Response
    {
        $category = new Category();

        $form = $this->createForm(CategoryType::class, $category);

        $form->submit($request->request->all());

        if ($form->isSubmitted() && $form->isValid()) {

            $maxSortOrder = $this->categoryService->findMaxSortOrder();
            $category->setSortOrder($maxSortOrder + 1);

            $this->em->persist($category);

            $this->em->flush();

            $this->rest
                ->succeeded()
                ->setData($category);

            return $this->handleView($this->view($this->rest->getResponse(), Response::HTTP_CREATED));
        }

        $this->rest
            ->failed()
            ->setFormErrors($form->getErrors(true))
            ->setData(null);
        return $this->handleView($this->view($this->rest->getResponse(), Response::HTTP_BAD_REQUEST));
    }

    #[Route('/create/with/image', name: 'create_category_with_image', methods: ['POST'])]
    /**
     * @OA\Tag(name="Categories")
     * @OA\Parameter(ref="#/components/parameters/locale")
     * @OA\RequestBody(
     *      @OA\MediaType(
     *           mediaType="multipart/form-data",
     *           @OA\Schema(type="object",
     *              @OA\Property(property="document", type="string"),
     *              @OA\Property(property="file", type="string", format="binary")
     *           )
     *      )
     *   )
     * )
     * @Security(name="Bearer")
     */
    public function createWithImage(Request $request)
    {
        $dataString = $request->request->get('data');
        $file = $request->files->get('imageFile');
        $data = json_decode($dataString, true) ?? [];

        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category, [
            'file_required' => true
        ]);

        // form-data -> $request->request->all()
        // raw/json -> json_decode($request->getContent(), true)

        $form->submit([
            ...$data,
            'imageFile' => $file
        ]);

        if ($form->isSubmitted() && $form->isValid()) {
            $maxSortOrder = $this->categoryService->findMaxSortOrder();
            $category->setSortOrder($maxSortOrder + 1);

            $this->em->persist($category);
            $this->em->flush();

            $this->categoryService->uploadImage($category, $file);

            $this->rest
                ->succeeded()
                ->addMessage('Category created successfully')
                ->setData($category)
                ->set('status', 201);

            return $this->handleView($this->view($this->rest->getResponse(), Response::HTTP_CREATED));
        }

        $this->rest
            ->failed()
            ->setFormErrors($form->getErrors(true))
            ->setData(null);
        return $this->handleView($this->view($this->rest->getResponse(), Response::HTTP_BAD_REQUEST));
    }

    #[Route('/upload/image/{id}', name: 'upload_category_image', methods: ['POST'], requirements: ['id' => '\d+'])]
    /**
     * @OA\Tag(name="Categories")
     * @OA\Parameter(ref="#/components/parameters/locale")
     * @OA\RequestBody(
     *      @OA\MediaType(
     *           mediaType="multipart/form-data",
     *           @OA\Schema(type="object",
     *              @OA\Property(property="file", type="string", format="binary")
     *           )
     *      )
     *   )
     * )
     * @Security(name="Bearer")
     */
    public function uploadImage(Request $request, Category $category)
    {
        $form = $this->createForm(CategoryImageType::class);
        $file = $request->files->get('imageFile');
        $form->submit(['imageFile' => $file]);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->categoryService->uploadImage($category, $file);
            $this->rest->succeeded()->setData($category);
            return $this->handleView($this->view($this->rest->getResponse(), Response::HTTP_CREATED));
        }

        $this->rest
            ->failed()
            ->setFormErrors($form->getErrors(true))
            ->setData(null);
        return $this->handleView($this->view($this->rest->getResponse(), Response::HTTP_BAD_REQUEST));
    }

    #[Route('/show/{id}', name: 'show_category', methods: ['GET'], requirements: ['id' => '\d+'])]
    /**
     * @OA\Tag(name="Categories")
     * @OA\Parameter(ref="#/components/parameters/locale")
     * @Security(name="Bearer")
     */
    public function show(Category $category)
    {
        $this->rest->setData($category);
        return $this->handleView($this->view($this->rest->getResponse(), Response::HTTP_OK));
    }

    #[Route('/update/{id}', name: 'update_category', methods: ['PUT'], requirements: ['id' => '\d+'])]
    /**
     * @OA\Tag(name="Categories")
     * @OA\Parameter(ref="#/components/parameters/locale")
     * @OA\RequestBody(
     *      description="Edit Category",
     *      @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(ref="#/components/schemas/categoryForm"),
     *          @OA\Examples(example="categoryForm", ref="#/components/examples/categoryForm")
     *      )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Returns the Category",
     *     @OA\JsonContent(
     *        @OA\Property(property="success", type="boolean"),
     *        @OA\Property(property="data", type="object", ref="#/components/schemas/category"),
     *        @OA\Examples(example="category", ref="#/components/examples/category")
     *     )
     * )
     * @Security(name="Bearer")
     */
    public function update(Request $request, Category $category)
    {
        $data = $request->request->all();
        $form = $this->createForm(CategoryType::class, $category);
        $form->submit($data);

        if ($form->isValid() && $form->isSubmitted()) {
            try {
                $this->em->flush();
                $this->rest
                    ->succeeded()
                    ->addMessage('Category updated successfully')
                    ->setData($category);
                return $this->handleView($this->view($this->rest->getResponse(), Response::HTTP_OK));
            } catch (\Exception $e) {
                $this->rest
                    ->failed()
                    ->addMessage($e->getMessage());
                return $this->handleView($this->view($this->rest->getResponse(), Response::HTTP_BAD_REQUEST));
            }
        }
        $this->rest
            ->failed()
            ->setFormErrors($form->getErrors(true))
            ->setData(null);
        return $this->handleView($this->view($this->rest->getResponse(), Response::HTTP_BAD_REQUEST));
    }

    #[Route('/update/patch/{id}', name: 'update_category_patch', methods: ['PATCH'], requirements: ['id' => '\d+'])]
    /**
     * @OA\Tag(name="Categories")
     * @OA\Parameter(ref="#/components/parameters/locale")
     * @OA\RequestBody(
     *      description="Edit Category",
     *      @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(ref="#/components/schemas/categoryForm"),
     *          @OA\Examples(example="categoryForm", ref="#/components/examples/categoryForm")
     *      )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Returns the Category",
     *     @OA\JsonContent(
     *        @OA\Property(property="success", type="boolean"),
     *        @OA\Property(property="data", type="object", ref="#/components/schemas/category"),
     *        @OA\Examples(example="category", ref="#/components/examples/category")
     *     )
     * )
     * @Security(name="Bearer")
     */
    public function patchUpdate(Request $request, Category $category): Response
    {
        $form = $this->createForm(CategoryType::class, $category, ['edit' => true]);
        $form->submit($request->request->all(), false);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();

            $this
                ->rest
                ->succeeded()
                ->addMessage('Category updated successfully')
                ->setData($category)
                ->set('status', 200);
            return $this->handleView($this->view($this->rest->getResponse(), Response::HTTP_OK));
        }
        $this->rest
            ->failed()
            ->setFormErrors($form->getErrors(true))
            ->setData(null);
        return $this->handleView($this->view($this->rest->getResponse(), Response::HTTP_BAD_REQUEST));
    }

    #[Route('/delete/{id}', name: 'delete_category', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    /**
     * @OA\Tag(name="Categories")
     * @OA\Parameter(ref="#/components/parameters/locale")
     * @Security(name="Bearer")
     */
    public function delete(Category $category, MessageBusInterface $bus)
    {
        try {
            $category->setDeleted(1);
            $this->em->flush();

            $bus->dispatch(new CategoryDeleteMessage($category->getId()));

            $this->rest
                ->succeeded()
                ->setData($category);
        } catch (ForeignKeyConstraintViolationException $e) {
            $this->rest
                ->failed()
                ->addMessage('Unable to delete the category');
        }
    }

    #[Route('/remove-image/{id}', name: 'remove_category_image', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    /**
     * @OA\Tag(name="Categories")
     * @OA\Parameter(ref="#/components/parameters/locale")
     * @Security(name="Bearer")
     */
    public function removeImage(Category $category)
    {
        $this->categoryService->removeImage($category);
        $this->rest->succeeded()->setData($category);
        return $this->handleView($this->view($this->rest->getResponse()));
    }

    #[Route('/update-sort-order/{id}', name: 'update_category_sort_order', methods: ['PUT'], requirements: ['id' => '\d+'])]
    public function updateSortOrder(Request $request, Category $category)
    {
        $form = $this->createForm(UpdateSortOrderType::class, $category);

        $form->submit(json_decode($request->getContent(), true), false);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();
            $this->rest->succeeded();
            return $this->handleView($this->view($this->rest->getResponse()));
        }

        $this->rest
            ->failed()
            ->setFormErrors($form->getErrors(true))
            ->setData(null);

        return $this->handleView($this->view($this->rest->getResponse()));
    }
}
