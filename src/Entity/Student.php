<?php

namespace App\Entity;

use App\Repository\StudentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use JMS\Serializer\Annotation as Serializer;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: StudentRepository::class)]
#[Serializer\ExclusionPolicy('all')]
#[ORM\HasLifecycleCallbacks]
class Student
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Serializer\Expose()]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Serializer\Expose()]
    #[Assert\NotBlank()]
    #[Assert\Length(min: 2, max: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Serializer\Expose()]
    private ?string $image = null;

    #[ORM\Column(type: 'integer')]
    #[Serializer\Expose()]
    private ?int $number = null;

    #[ORM\Column(type: 'boolean')]
    #[Serializer\Expose()]
    #[Assert\NotBlank()]
    private ?bool $gender = null;

    #[ORM\Column]
    #[Serializer\Expose()]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Serializer\Expose()]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToMany(targetEntity: Course::class, inversedBy: 'students')]
    #[Serializer\Expose()]
    private Collection $courses;

    public function __construct()
    {
        $this->courses = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getNumber(): ?int
    {
        return $this->number;
    }

    #[Serializer\VirtualProperty()]
    #[Serializer\Expose()]
    public function getGenderText()
    {
        return $this->gender == 1 ? "Male" : "Female";
    }

    public function getImage(): ?string
    {
        return $this->image;
    }
    public function setImage(string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function setNumber(int $number): static
    {
        $this->number = $number;

        return $this;
    }

    public function isGender()
    {
        return $this->gender;
    }

    public function setGender(bool $gender): static
    {
        $this->gender = $gender;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createAt): static
    {
        $this->createdAt = $createAt;

        return $this;
    }


    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
    public function getCourses(): Collection
    {
        return $this->courses;
    }

    public function addCourse(Course $course): static
    {
        if (!$this->courses->contains($course)) {
            $this->courses->add($course);
        }

        return $this;
    }

    public function removeCourse(Course $course): static
    {
        $this->courses->removeElement($course);

        return $this;
    }
    #[ORM\PrePersist]
    public function setTimestamps(): void
    {
        $now = new \DateTimeImmutable();
        $this->createdAt = $now;
        $this->updatedAt = $now;
    }

    #[ORM\PrePersist]
    public function setStudentNumberValue(): void
    {
        $this->number = random_int(100, 9999);
    }

    #[ORM\PreUpdate]
    public function updateTimestamp(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
