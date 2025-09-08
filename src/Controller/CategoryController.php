<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Traits\ResponseTrait;
use App\Form\UpdateSortOrderType;
use App\Services\RestHelperService;
use App\Services\CategoryService;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/categories')]
final class CategoryController extends AbstractFOSRestController
{
    use ResponseTrait;

    public function __construct(
        private CategoryService $categoryService,
        private EntityManagerInterface $entityManager,
        private RestHelperService $rest
    ) {}
    #[Route('/index', name: 'all_categories', methods: ['GET'])]
    public function index(Request $request)
    {
        $data = $this->categoryService->listAll($request);
        $this->rest->setPagination($data);
        return $this->handleView($this->view($this->rest->getResponse()));
    }
    #[Route('/create', name: 'create_category', methods: ['POST'])]
    public function store(Request $request)
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);

        // form-data -> $request->request->all()
        // raw/json -> json_decode($request->getContent(), true)

        $data = $request->request->all();
        $data['imageFile'] = $request->files->get('imageFile');
        $form->submit($data);

        if ($form->isSubmitted() && $form->isValid()) {
            $category = $this->categoryService->storeCategory($category);
            if ($category) {
                $this->rest->succeeded()->addMessage('Category created successfully')->setData($category)->set('status', 201);
                return $this->handleView($this->view($this->rest->getResponse()));
            }
            $this->rest->failed()->addMessage('Faild to create');
            return $this->handleView($this->view($this->rest->getResponse()));
        }

        $this->rest->failed()->setFormErrors($form->getErrors(true))->setData(null);
        return $this->handleView($this->view($this->rest->getResponse()));
    }

    #[Route('/show/{id}', name: 'show_category', methods: ['GET'])]
    public function show($id)
    {
        $data = $this->categoryService->showCategory($id);
        if (!$data) {
            $this->rest->failed()->addMessage('Failed to retrieve category');
            return $this->handleView($this->view($this->rest->getResponse()));
        }
        $this->rest->setData($data);
        return $this->handleView($this->view($this->rest->getResponse()));
    }
    #[Route('/update/{id}', name: 'update_category', methods: ['PUT'])]
    public function update(Request $request, $id)
    {
        $category = $this->entityManager->getRepository(Category::class)->find($id);

        if (!$category) {
            return $this->rest->failed()->addMessage('Category not found');
        }

        $data = json_decode($request->getContent(), true);
        $form = $this->createForm(CategoryType::class, $category);
        $form->submit($data);

        if ($form->isValid() && $form->isSubmitted()) {
            $category = $this->categoryService->updateCategory($category);
            if ($category) {
                $this->rest->succeeded()->addMessage('Category updated successfully')->setData($category)->set('status', 200);
                return $this->handleView($this->view($this->rest->getResponse()));
            }
            $this->rest->failed()->addMessage('Failed to update category');
            return $this->handleView($this->view($this->rest->getResponse()));
        }
        return $this->handleView($this->view($this->rest->failed()->setFormErrors($form->getErrors(true))->setData(null)));
    }

    #[Route('/delete/{id}', name: 'delete_category', methods: ['DELETE'])]
    public function destroy(int $id)
    {
        $result = $this->categoryService->deleteCategory($id, $this->getParameter('app.upload_dir'));
        if ($result === null) {
            $this->rest->failed()->addMessage('Failed to delete category');
            return $this->handleView($this->view($this->rest->getResponse()));
        } else {
            $this->rest->succeeded()->addMessage('Category deleted successfully')->set('status', 200);
            return $this->handleView($this->view($this->rest->getResponse()));
        }
    }

    #[Route('/update-sort-order/{id}', name: 'update_category_sort_order', methods: ['PUT'])]
    public function updateSortOrder(Request $request, $id)
    {
        $category = $this->entityManager->getRepository(Category::class)->find($id);
        if (!$category) {
            $this->rest->failed()->addMessage('Failed to update sort order');
            return $this->handleView($this->view($this->rest->getResponse()));
        }

        $form = $this->createForm(UpdateSortOrderType::class, $category);

        $form->submit(json_decode($request->getContent(), true), false);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();
            $this->rest->set('data', null);
            return $this->handleView($this->view($this->rest->getResponse()));
        }

        $this->rest->failed()->setFormErrors($form->getErrors(true))->setData(null);
        return $this->handleView($this->view($this->rest->getResponse()));
    }
}
