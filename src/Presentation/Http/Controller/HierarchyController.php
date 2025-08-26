<?php
declare(strict_types=1);

namespace App\Presentation\Http\Controller;

use App\Application\Api\ApiResponder;
use Symfony\Component\HttpFoundation\Request;
use App\Application\UseCase\BuildTeamHierarchy;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Domain\Exception\DomainValidationException;

final class HierarchyController
{
    /**
     * @param BuildTeamHierarchy $useCase The use case for building team hierarchy
     * @param ApiResponder $responder The API responder for generating responses
     */
    public function __construct(
        private readonly BuildTeamHierarchy $useCase,
        private readonly ApiResponder $responder
        ) {}

    /**
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('/api/hierarchy', name: 'api_hierarchy', methods: ['POST'])]
    public function upload(Request $request): JsonResponse
    {
        $file = $request->files->get('file');
        if ($file === null) {
            throw new DomainValidationException(['file' => ['Missing "file" upload (multipart/form-data).']]);
        }

        if (!in_array($file->getClientOriginalExtension(), ['csv'], true)) {
            throw new DomainValidationException(['file' => ['Only CSV files are accepted.']]);
        }

        $csvPath = $file->getRealPath();
        if ($csvPath === false) {
            throw new DomainValidationException(['file' => ['Cannot read uploaded file.']]);
        }

        $q = $request->query->get('_q');
        $hierarchy = $this->useCase->execute($csvPath, is_string($q) ? $q : null);

        return new JsonResponse(
            $hierarchy
        );

        // return $this->responder->success($hierarchy, 'Hierarchy');
    }
}
