<?php
namespace App\Job\Message;
class CategoryDeleteMessage
{
    private $categoryId;

    public function __construct(string $categoryId)
    {
        $this->categoryId = $categoryId;
    }

    public function getCategoryId(): string
    {
        return $this->categoryId;
    }

    public function setCategoryId(string $categoryId)
    {
        $this->categoryId = $categoryId;
        return $this;
    }
}
