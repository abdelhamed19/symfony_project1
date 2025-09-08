<?php

namespace App\Controller;

use App\Entity\Student;
use App\Form\CreateStudentType;
use App\Services\StudentService;
use App\Services\RestHelperService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/student', name: 'student_class')]
final class StudentController extends AbstractFOSRestController
{
    public function __construct(
        private StudentService $studentService,
        private RestHelperService $restHelperService
    ) {}
    #[Route('/list', methods: ["GET"])]
    public function index(Request $request)
    {
        $students = $this->studentService->listAll($request);
        $this->restHelperService->setPagination($students);
        return $this->handleView($this->view($this->restHelperService->getResponse()));
    }
    #[Route('/show/{id}', name: 'show_student',  methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show($id)
    {
        $student = $this->studentService->showStudent($id);
        if (!$student) {
            $this->restHelperService->failed()->addMessage('Not Found')->set('status', 404);
            return $this->handleView($this->view($this->restHelperService->getResponse()));
        }
        $this->restHelperService->set('student', $student);
        return $this->handleView($this->view($this->restHelperService->getResponse()));
    }
    #[Route('/create', name: 'create_student', methods: ['POST'])]
    public function store(Request $request)
    {
        $data = $request->request->all();
        $data['image'] = $request->files->get('image');

        $student = new Student();
        $form = $this->createForm(CreateStudentType::class, $student);

        $form->submit($data);
        if (!$form->isValid() || !$form->isSubmitted()) {
            $this->restHelperService->failed()->setFormErrors($form->getErrors(true))->setData(null);
            return $this->handleView($this->view($this->restHelperService->getResponse()));
        }

        $student = $this->studentService->createStudent($data, $student);

        if ($student instanceof Student) {
            $this->restHelperService->succeeded()->addMessage('Student created successfully')->setData($student)->set('status', 201);
            return $this->handleView($this->view($this->restHelperService->getResponse()));
        }

        $this->restHelperService->failed()->addMessage($student)->setData(null);
        return $this->handleView($this->view($this->restHelperService->getResponse()));
    }
    #[Route('/delete/{id}', name: 'delete_student', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function delete($id)
    {
        $student = $this->studentService->showStudent($id);
        if (!$student) {
            $this->restHelperService->failed()->addMessage('Not Found')->set('status', 404);
            return $this->handleView($this->view($this->restHelperService->getResponse()));
        }
        $this->studentService->deleteStudent($student);
        $this->restHelperService->succeeded()->addMessage('Student deleted successfully')->setData(null);
        return $this->handleView($this->view($this->restHelperService->getResponse()));
    }
}
