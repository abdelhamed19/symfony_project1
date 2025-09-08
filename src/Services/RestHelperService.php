<?php

namespace App\Services;

use Knp\Component\Pager\Pagination\PaginationInterface;
use Symfony\Component\Form\FormErrorIterator;

class RestHelperService
{

    private bool $success = true;
    private ?FormErrorIterator $formErrors = null;
    private array $messages = [];
    private $data = null;
    private ?PaginationInterface $pagination = null;
    private array $custom = [];

    public function reset(): self
    {
        $this->success = true;
        $this->messages = [];
        $this->custom = [];
        return $this;
    }

    public function succeeded(): self
    {
        $this->success = true;
        return $this;
    }

    public function failed(): self
    {
        $this->success = false;
        return $this;
    }

    public function isSucceeded(): bool
    {
        return $this->success;
    }

    public function isFailed(): bool
    {
        return !$this->success;
    }

    public function setFormErrors(FormErrorIterator $formErrors): self
    {
        $this->formErrors = $formErrors;
        return $this;
    }

    public function setPagination(PaginationInterface $pagination): self
    {
        $this->pagination = $pagination;
        return $this;
    }

    public function addMessage($message): self
    {
        $this->messages[] = $message;
        return $this;
    }

    public function setData($data): self
    {
        $this->data = $data;
        return $this;
    }

    public function set($key, $value): self
    {
        $this->custom[$key] = $value;
        return $this;
    }

    public function getResponse(): array
    {
        $response = [
            'success' => $this->success,
        ];
        if (count($this->messages)) {
            $response = array_merge($response, ['messages' => $this->messages]);
        }
        if ($this->pagination !== null) {
            $response = array_merge($response, ['pagination' => self::getPaginationInfo($this->pagination)]);
        }
        if ($this->data !== null) {
            $response = array_merge($response, ['data' => $this->data]);
        }
        if ($this->formErrors !== null) {
            $response = array_merge($response, ['formErrors' => $this->formErrors]);
        }
        if(count($this->custom)) {
            foreach ($this->custom as $itemKey => $itemValue) {
                $response = array_merge($response, [$itemKey => $itemValue]);
            }
        }
        return $response;
    }


    private static function getPaginationInfo(PaginationInterface $pagination): array
    {
        return [
            'page' => $pagination->getCurrentPageNumber(),
            'pages' => $pagination->getPaginationData()['pageCount'],
            'totalItems' => $pagination->getTotalItemCount(),
            'items' => $pagination->getItems()
        ];
    }


}
