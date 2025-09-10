<?php

namespace App\Controller;

use App\Entity\Course;
use App\Form\CreateCourseType;
use App\Services\CourseService;
use App\Services\RestHelperService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;

#[Route('api/course', name: 'app_course')]
final class CourseController extends AbstractFOSRestController
{
    public function __construct(
        private CourseService $courseService,
        private RestHelperService $rest,
        private EntityManagerInterface $em
    ) {}

    #[Route('/list', methods: ["GET"])]
    /**
     * @OA\Tag(name="Courses")
     * @OA\Parameter(ref="#/components/parameters/locale")
     * @Security(name="Bearer")
     */
    public function index(Request $request)
    {
        $page = $request->query->getInt('page', 1);
        $courses = $this->courseService->listAll($page);
        $this->rest->setPagination($courses);
        return $this->handleView($this->view($this->rest->getResponse(), Response::HTTP_OK));
    }

    #[Route('/show/{id}', name: 'show_course',  methods: ['GET'], requirements: ['id' => '\d+'])]
    /**
     * @OA\Tag(name="Courses")
     * @OA\Parameter(ref="#/components/parameters/locale")
     * @Security(name="Bearer")
     */
    public function show(Course $course)
    {
        $this->rest->set('course', $course);
        return $this->handleView($this->view($this->rest->getResponse(), Response::HTTP_OK));
    }

    #[Route('/create', name: 'create_course', methods: ['POST'])]
    /**
     * @OA\Tag(name="Courses")
     * @OA\Parameter(ref="#/components/parameters/locale")
     * @Security(name="Bearer")
     */
    public function store(Request $request)
    {
        $data = $request->request->all();
        $course = new Course();

        $form = $this->createForm(CreateCourseType::class, $course);
        $form->submit($data);

        if ($form->isValid() && $form->isSubmitted()) {

            $this->em->persist($course);
            $this->em->flush();

            $this->rest
                ->succeeded()
                ->addMessage('Course created successfully')
                ->setData($course);

            return $this->handleView($this->view($this->rest->getResponse(), Response::HTTP_OK));
        }

        $this->rest->failed()->setFormErrors($form->getErrors(true));
        return $this->handleView($this->view($this->rest->getResponse(), Response::HTTP_BAD_REQUEST));
    }

    #[Route('/delete/{id}', name: 'delete_course', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    /**
     * @OA\Tag(name="Courses")
     * @OA\Parameter(ref="#/components/parameters/locale")
     * @Security(name="Bearer")
     */
    public function delete(Course $course)
    {
        try {
            $this->em->remove($course);
            $this->em->flush();

            $this->rest->succeeded()->addMessage('Course deleted successfully');
            return $this->handleView($this->view($this->rest->getResponse(), Response::HTTP_OK));
        } catch (ForeignKeyConstraintViolationException $e) {
            $this->rest->failed()->addMessage($e->getMessage());
            return $this->handleView($this->view($this->rest->getResponse(), Response::HTTP_BAD_REQUEST));
        }
    }

    #[Route('/update/{id}', name: 'update_course', methods: ['PUT'], requirements: ['id' => '\d+'])]
    /**
     * @OA\Tag(name="Courses")
     * @OA\Parameter(ref="#/components/parameters/locale")
     * @Security(name="Bearer")
     */
    public function update(Request $request, Course $course)
    {
        $data = $request->request->all();

        $form = $this->createForm(CreateCourseType::class, $course);
        $form->submit($data);

        if ($form->isValid() && $form->isSubmitted()) {

            $this->em->flush();

            $this->rest
                ->succeeded()
                ->addMessage('Course updated successfully')
                ->setData($course);

            return $this->handleView($this->view($this->rest->getResponse(), Response::HTTP_OK));
        }

        $this->rest->failed()->setFormErrors($form->getErrors(true));
        return $this->handleView($this->view($this->rest->getResponse(), Response::HTTP_BAD_REQUEST));
    }
}
