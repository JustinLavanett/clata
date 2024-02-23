<?php

declare(strict_types=1);

namespace TicketKitten\Http\Actions;

use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response as LaravelResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use TicketKitten\DomainObjects\Enums\Role;
use TicketKitten\DomainObjects\Interfaces\DomainObjectInterface;
use TicketKitten\DomainObjects\Interfaces\IsFilterable;
use TicketKitten\DomainObjects\Interfaces\IsSortable;
use TicketKitten\DomainObjects\UserDomainObject;
use TicketKitten\DataTransferObjects\BaseDTO;
use TicketKitten\Exceptions\UnauthorizedException;
use TicketKitten\Http\ResponseCodes;
use TicketKitten\Resources\BaseResource;
use TicketKitten\Service\Authorization\IsAuthorizedService;

abstract class BaseAction extends Controller
{
    use ValidatesRequests;

    /**
     * @param class-string<BaseResource> $resource
     * @param Collection|DomainObjectInterface|LengthAwarePaginator $data
     * @param int $statusCode
     * @param class-string<IsSortable|IsFilterable> $domainObject
     * @return JsonResponse
     */
    protected function filterableResourceResponse(
        string                                                $resource,
        Collection|DomainObjectInterface|LengthAwarePaginator $data,
        string                                                $domainObject,
        int                                                   $statusCode = ResponseCodes::HTTP_OK,
    ): JsonResponse
    {
        $metaFields = [];

        if (is_a($domainObject, IsFilterable::class, true)) {
            $metaFields['allowed_filter_fields'] = $domainObject::getAllowedFilterFields();
        }

        if (is_a($domainObject, IsSortable::class, true)) {
            $metaFields['allowed_sorts'] = $domainObject::getAllowedSorts()->toArray();
            $metaFields['default_sort'] = $domainObject::getDefaultSort();
            $metaFields['default_sort_direction'] = $domainObject::getDefaultSortDirection();
        }

        return $this->resourceResponse($resource, $data, $statusCode, $metaFields);
    }

    /**
     * @param class-string<BaseResource> $resource
     * @param Collection|DomainObjectInterface|LengthAwarePaginator|BaseDTO $data
     * @param int $statusCode
     * @param array $meta
     * @param array $headers
     * @return JsonResponse
     */
    protected function resourceResponse(
        string                                                        $resource,
        Collection|DomainObjectInterface|LengthAwarePaginator|BaseDTO $data,
        int                                                           $statusCode = ResponseCodes::HTTP_OK,
        array                                                         $meta = [],
        array                                                         $headers = []
    ): JsonResponse
    {
        if ($data instanceof Collection || $data instanceof LengthAwarePaginator) {
            $response = ($resource::collection($data)->additional(['meta' => $meta]))
                ->response()
                ->setStatusCode($statusCode);
        } else {
            $response = (new $resource($data))
                ->response()
                ->setStatusCode($statusCode);
        }

        foreach ($headers as $header => $value) {
            $response->header($header, $value);
        }

        return $response;
    }

    protected function noContentResponse(int $status = ResponseCodes::HTTP_NO_CONTENT): LaravelResponse
    {
        return Response::noContent($status);
    }

    protected function deletedResponse(): LaravelResponse
    {
        return Response::noContent();
    }

    protected function notFoundResponse(): LaravelResponse
    {
        return Response::noContent(ResponseCodes::HTTP_NOT_FOUND);
    }

    protected function errorResponse(
        string $message,
        int    $statusCode = ResponseCodes::HTTP_BAD_REQUEST,
        array  $errors = [],
    ): JsonResponse
    {
        return $this->jsonResponse([
            'message' => $message,
            'errors' => $errors,
        ], $statusCode);
    }

    protected function jsonResponse(mixed $data, $statusCode = ResponseCodes::HTTP_OK): JsonResponse
    {
        return new JsonResponse($data, $statusCode);
    }

    protected function isActionAuthorized(
        int    $entityId,
        string $entityType,
        Role   $minimumRole = Role::ORGANIZER
    ): void
    {
        /** @var IsAuthorizedService $authService */
        $authService = app()->make(IsAuthorizedService::class);

        $authService->isActionAuthorized($entityId, $entityType, $this->getAuthenticatedUser(), $minimumRole);
    }

    protected function getAuthenticatedUser(bool $failForUnauthenticatedUsers = true): null|UserDomainObject|DomainObjectInterface
    {
        if (Auth::check()) {
            return UserDomainObject::hydrateFromModel(Auth::user());
        }

        throw new UnauthorizedException();
    }

    protected function isUserAuthenticated(): bool
    {
        return Auth::check();
    }

    protected function minimumAllowedRole(Role $minimumRole): void
    {
        /** @var IsAuthorizedService $authService */
        $authService = app()->make(IsAuthorizedService::class);

        $authService->validateUserRole($minimumRole, $this->getAuthenticatedUser());
    }
}
