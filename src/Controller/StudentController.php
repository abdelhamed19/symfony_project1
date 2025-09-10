<?php

namespace App\Controller;

use App\Entity\Student;
use App\Form\StudentImageType;
use App\Form\CreateStudentType;
use App\Services\StudentService;
use App\Services\RestHelperService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;

#[Route('/api/student', name: 'student_class')]
final class StudentController extends AbstractFOSRestController
{
    public function __construct(
        private StudentService $studentService,
        private RestHelperService $rest,
        private EntityManagerInterface $em
    ) {}

    #[Route('/list', methods: ["GET"])]
    /**
     * @OA\Tag(name="Students")
     * @OA\Parameter(ref="#/components/parameters/locale")
     * @Security(name="Bearer")
     */
    public function index(Request $request)
    {
        $page = $request->query->getInt('page', 1);
        $students = $this->studentService->listAll($page);
        $this->rest->setPagination($students);
        return $this->handleView($this->view($this->rest->getResponse(), Response::HTTP_OK));
    }

    #[Route('/show/{id}', name: 'show_student',  methods: ['GET'], requirements: ['id' => '\d+'])]
    /**
     * @OA\Tag(name="Students")
     * @OA\Parameter(ref="#/components/parameters/locale")
     * @Security(name="Bearer")
     */
    public function show(Student $student)
    {
        $this->rest->set('student', $student);
        return $this->handleView($this->view($this->rest->getResponse(), Response::HTTP_OK));
    }

    #[Route('/create/with/image', name: 'create_student', methods: ['POST'])]
    /**
     * @OA\Tag(name="Students")
     * @OA\Parameter(ref="#/components/parameters/locale")
     * @OA\RequestBody(
     *      @OA\MediaType(
     *           mediaType="multipart/form-data",
     *           @OA\Schema(type="object",
     *              @OA\Property(property="data", type="string"),
     *              @OA\Property(property="image", type="string", format="binary")
     *           )
     *      )
     *   )
     * )
     * @Security(name="Bearer")
     */
    public function createWithImage(Request $request)
    {
        $dataString = $request->request->get('data');
        $file = $request->files->get('image');
        $data = json_decode($dataString, true) ?? [];

        $student = new Student();
        $form = $this->createForm(CreateStudentType::class, $student);

        $form->submit([
            ...$data,
            'image' => $file
        ]);

        if ($form->isValid() && $form->isSubmitted()) {

            $this->em->persist($student);
            $this->em->flush();

            $this->studentService->uploadImage($student, $file);

            $this->rest
                ->succeeded()
                ->setData($student);
            return $this->handleView($this->view($this->rest->getResponse(), Response::HTTP_CREATED));
        }

        $this->rest->failed()->setFormErrors($form->getErrors(true));
        return $this->handleView($this->view($this->rest->getResponse(), Response::HTTP_BAD_REQUEST));
    }

    #[Route('/create/without/image', name: 'create_student_without_image', methods: ['POST'])]
    /**
     * @OA\Tag(name="Students")
     * @OA\Parameter(ref="#/components/parameters/locale")
     * @Security(name="Bearer")
     */
    public function createWithoutImage(Request $request)
    {
        $data = $request->request->all();

        $student = new Student();
        $form = $this->createForm(CreateStudentType::class, $student, [
            'file_required' => false
        ]);

        $form->submit($data);

        if ($form->isValid() && $form->isSubmitted()) {
            $this->em->persist($student);
            $this->em->flush();

            $this->rest
                ->succeeded()
                ->setData($student);
            return $this->handleView($this->view($this->rest->getResponse(), Response::HTTP_CREATED));
        }

        $this->rest->failed()->setFormErrors($form->getErrors(true));
        return $this->handleView($this->view($this->rest->getResponse(), Response::HTTP_BAD_REQUEST));
    }

    #[Route('/delete/{id}', name: 'delete_student', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    /**
     * @OA\Tag(name="Students")
     * @OA\Parameter(ref="#/components/parameters/locale")
     * @Security(name="Bearer")
     */
    public function delete(Student $student)
    {
        try {
            $this->em->remove($student);
            $this->em->flush();

            $this->rest->succeeded()->addMessage('Student deleted successfully');
            return $this->handleView($this->view($this->rest->getResponse(), Response::HTTP_OK));
        } catch (ForeignKeyConstraintViolationException $e) {
            $this->rest->failed()->addMessage($e->getMessage());
            return $this->handleView($this->view($this->rest->getResponse(), Response::HTTP_BAD_REQUEST));
        }
    }

    #[Route('/update/{id}', name: 'update_student', methods: ['PUT'], requirements: ['id' => '\d+'])]
    /**
     * @OA\Tag(name="Students")
     * @OA\Parameter(ref="#/components/parameters/locale")
     * @Security(name="Bearer")
     */
    public function update(Request $request, Student $student)
    {
        $data = $request->request->all();

        $form = $this->createForm(CreateStudentType::class, $student, [
            'file_required' => false
        ]);

        $form->submit($data);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();

            $this->rest->succeeded()->addMessage('Student updated successfully')->setData($student);
            return $this->handleView($this->view($this->rest->getResponse()));
        }

        $this->rest->failed()->setFormErrors($form->getErrors(true));
        return $this->handleView($this->view($this->rest->getResponse(), Response::HTTP_BAD_REQUEST));
    }

    #[Route('/remove-image/{id}', name: 'remove_student_image', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    /**
     * @OA\Tag(name="Students")
     * @OA\Parameter(ref="#/components/parameters/locale")
     * @Security(name="Bearer")
     */
    public function removeImage(Student $student)
    {
        $this->studentService->removeImage($student);
        $this->rest->succeeded()->setData($student);
        return $this->handleView($this->view($this->rest->getResponse()));
    }

    #[Route('/upload/image/{id}', name: 'upload_student_image', methods: ['POST'], requirements: ['id' => '\d+'])]
    /**
     * @OA\Tag(name="Students")
     * @OA\Parameter(ref="#/components/parameters/locale")
     * @OA\RequestBody(
     *      @OA\MediaType(
     *           mediaType="multipart/form-data",
     *           @OA\Schema(type="object",
     *              @OA\Property(property="image", type="string", format="binary")
     *           )
     *      )
     *   )
     * )
     * @Security(name="Bearer")
     */
    public function uploadImage(Request $request, Student $student)
    {
        $form = $this->createForm(StudentImageType::class, $student);
        $file = $request->files->get('image');
        $form->submit(['image' => $file]);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->studentService->uploadImage($student, $file);
            $this->rest->succeeded()->setData($student);
            return $this->handleView($this->view($this->rest->getResponse(), Response::HTTP_CREATED));
        }

        $this->rest
            ->failed()
            ->setFormErrors($form->getErrors(true));
        return $this->handleView($this->view($this->rest->getResponse(), Response::HTTP_BAD_REQUEST));
    }
}
