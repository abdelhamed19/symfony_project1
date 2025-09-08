<?php

namespace App\Controller;

use App\Entity\Course;
use App\Services\CourseService;
use App\Services\RestHelperService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('api/course', name: 'app_course')]
final class CourseController extends AbstractFOSRestController
{
    public function __construct(
        private CourseService $courseService,
        private RestHelperService $restHelperService
    ) {}
    #[Route('/list', methods: ["GET"])]
    public function index(Request $request)
    {
        $courses = $this->courseService->listAll($request);
        $this->restHelperService->setPagination($courses);
        return $this->handleView($this->view($this->restHelperService->getResponse()));
    }
    #[Route('/show/{id}', name: 'show_course',  methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show($id)
    {
        $course = $this->courseService->showCourse($id);
        if (!$course) {
            $this->restHelperService->failed()->addMessage('Not Found')->set('status', 404);
            return $this->handleView($this->view($this->restHelperService->getResponse()));
        }
        $this->restHelperService->set('course', $course);
        return $this->handleView($this->view($this->restHelperService->getResponse()));
    }
    #[Route('/create', name: 'create_course', methods: ['POST'])]
    public function store(Request $request)
    {
        $data = $request->request->all();
        $course = new Course();

        $form = $this->createForm(\App\Form\CreateCourseType::class, $course);
        $form->submit($data);
        if (!$form->isValid() || !$form->isSubmitted()) {
            $this->restHelperService->failed()->setFormErrors($form->getErrors(true))->setData(null);
            return $this->handleView($this->view($this->restHelperService->getResponse()));
        }
        $course = $this->courseService->createCourse($course);
        if ($course instanceof Course) {
            $this->restHelperService->succeeded()->addMessage('Course created successfully')->setData($course)->set('status', 201);
            return $this->handleView($this->view($this->restHelperService->getResponse()));
        }
        $this->restHelperService->failed()->addMessage('Failed to create')->setData(null);
        return $this->handleView($this->view($this->restHelperService->getResponse()));
    }
}
