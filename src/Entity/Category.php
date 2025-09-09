<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'category')]
#[Serializer\ExclusionPolicy('all')]
class Category
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Serializer\Expose()]
    private ?int $id = null;

    #[ORM\Column(length: 255, type:"string", nullable: false)]
    #[Serializer\Expose()]
    #[Assert\NotBlank()]
    #[Assert\Length(min: 2, max: 255)]
    private ?string $name = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Serializer\Expose()]
    private ?string $image = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Serializer\Expose()]
    private ?string $imageType = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Serializer\Expose()]
    private ?int $imageSize = null;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    #[Serializer\Expose()]
    private ?int $sort_order = 0;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Serializer\Expose()]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    #[Serializer\Expose()]
    private ?\DateTimeImmutable $updated_at = null;

    /**
     * @var Collection<int, Article>
     */
    #[ORM\OneToMany(targetEntity: Article::class, mappedBy: 'category')]
    #[Serializer\Expose()]
    private Collection $articles;

    #[ORM\Column(type: 'boolean')]
    #[Assert\Type(type: 'bool')]
    #[Serializer\Expose()]
    private ?bool $status = null;

    public function __construct()
    {
        $this->articles = new ArrayCollection();
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

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;
        return $this;
    }

    public function isStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): self
    {
        $this->status = $status;
        return $this;
    }

    #[Serializer\VirtualProperty()]
    #[Serializer\Expose()]
    #[Serializer\SerializedName('status_text')]
    public function getStatusValue()
    {
        return $this->status == 1 ? "Active" : "Inactive";
    }

    /**
     * @return Collection<int, Article>
     */
    public function getArticles(): Collection
    {
        return $this->articles;
    }

    public function getSortOrder(): ?int
    {
        return $this->sort_order;
    }

    public function setSortOrder(int $sort_order): static
    {
        $this->sort_order = $sort_order;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function getImageType(): ?string
    {
        return $this->imageType;
    }

    public function getImageSize(): ?string
    {
        return $this->imageSize;
    }

    public function setImageType(?string $imageType): self
    {
        $this->imageType = strtolower($imageType);
        return $this;
    }

    public function setImageSize(?int $imageSize): self
    {
        $this->imageSize = $imageSize;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updated_at;
    }

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->created_at = new \DateTimeImmutable();
        $this->updated_at = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updated_at = new \DateTimeImmutable();
    }

    public function addArticle(Article $article): static
    {
        if (!$this->articles->contains($article)) {
            $this->articles->add($article);
            $article->setCategory($this);
        }
        return $this;
    }

    public function removeArticle(Article $article): static
    {
        if ($this->articles->removeElement($article)) {
            if ($article->getCategory() === $this) {
                $article->setCategory(null);
            }
        }
        return $this;
    }
}
