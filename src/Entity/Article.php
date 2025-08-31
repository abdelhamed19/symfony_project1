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
    private ?string $title = null;

    #[ORM\ManyToOne(inversedBy: 'articles')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Category $category = null;

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

    public function toArray(bool $withRelations = false): array
    {
        $data = [
            'id' => $this->getId(),
            'title' => $this->getTitle(),
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

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addPropertyConstraint('title', new NotBlank([
            'message' => 'Title should not be blank',
        ]));

        $metadata->addPropertyConstraint('title', new NotNull([
            'message' => 'Title should not be null',
        ]));

        $metadata->addPropertyConstraint('title', new Length([
            'min' => 3,
            'max' => 50,
            'minMessage' => 'Title must be at least {{ limit }} characters',
            'maxMessage' => 'Title cannot be longer than {{ limit }} characters',
        ]));

        $metadata->addPropertyConstraint('category', new NotNull([
            'message' => 'category is required',
        ]));

        $metadata->addPropertyConstraint('category', new NotBlank([
            'message' => 'category cannot be blank',
        ]));
    }
}
