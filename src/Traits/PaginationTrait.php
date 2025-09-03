<?php

namespace App\Traits;

use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

trait PaginationTrait
{
    public function __construct(private NormalizerInterface $normalizer) {}

    public function paginateData($query, $request, $normalizerOptions = [], PaginatorInterface $paginator, UrlGeneratorInterface $urlGenerator)
    {
        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            $request->query->getInt('limit', 10)
        );

        $items = $this->normalizer->normalize($pagination->getItems(), null, $normalizerOptions);

        $routeName = $request->attributes->get('_route');

        $links = [
            'first' => $urlGenerator->generate($routeName, ['page' => 1], UrlGeneratorInterface::ABSOLUTE_URL),
            'last' => $urlGenerator->generate(
                $routeName,
                ['page' => ceil($pagination->getTotalItemCount() / $pagination->getItemNumberPerPage())],
                UrlGeneratorInterface::ABSOLUTE_URL
            ),
        ];

        if ($pagination->getCurrentPageNumber() > 1) {
            $links['prev'] = $urlGenerator->generate(
                $routeName,
                ['page' => $pagination->getCurrentPageNumber() - 1],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
        }

        if ($pagination->getCurrentPageNumber() < ceil($pagination->getTotalItemCount() / $pagination->getItemNumberPerPage())) {
            $links['next'] = $this->urlGenerator->generate(
                $routeName,
                ['page' => $pagination->getCurrentPageNumber() + 1],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
        }

        $data = [
            'data' => $items,
            'pagination' => [
                'current_page' => $pagination->getCurrentPageNumber(),
                'total_items' => $pagination->getTotalItemCount(),
                'items_per_page' => $pagination->getItemNumberPerPage(),
                'total_pages' => ceil($pagination->getTotalItemCount() / $pagination->getItemNumberPerPage()),
                'links' => $links,
            ],
        ];
        return $data;
    }
}
