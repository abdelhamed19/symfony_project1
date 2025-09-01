<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\ArticleRepository;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Mapping\ClassMetadata;

#[ORM\Entity(repositoryClass: ArticleRepository::class)]
class Article
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]

    #[NotBlank(
        message: 'title should not be blank',
    )]
    #[NotNull(
        message: 'title should not be null',
    )]
    #[Length(
        min: 2,
        max: 50,
        minMessage: 'title must be at least {{ limit }} characters long',
        maxMessage: 'title cannot be longer than {{ limit }} characters',
    )]
    private ?string $title = null;

    #[ORM\ManyToOne(inversedBy: 'articles')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Category $category = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $created_at = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $updated_at = null;

    public function __construct()
    {
        $this->created_at = new \DateTimeImmutable();
        $this->updated_at = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updated_at;
    }

    public function toArray(bool $withRelations = false): array
    {
        $data = [
            'id' => $this->getId(),
            'title' => $this->getTitle(),
            'created_at' => $this->getCreatedAt(),
            'updated_at' => $this->getUpdatedAt(),
        ];

        if ($withRelations) {
            $data['category'] = $this->getCategory()->toArray();
        }

        return $data;
    }


    public function setCategory(?Category $category): static
    {
        $this->category = $category;

        return $this;
    }
}
