<?php

namespace App\Controllers;

use App\Services\{
    BusinessService,
    ImageUploadService,
    AnalyticsService,
    ErrorService
};
use App\Helpers\ResponseHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * @OA\Tag(
 *     name="Business",
 *     description="Business management endpoints"
 * )
 */
class BusinessController
{
    public function __construct(
        private BusinessService $businessService,
        private ImageUploadService $imageService,
        private AnalyticsService $analyticsService,
        private ErrorService $errorService
    ) {}

    /**
     * @OA\Get(
     *     path="/businesses",
     *     tags={"Business"},
     *     summary="Get list of businesses",
     *     description="Retrieve a paginated list of businesses with optional filters",
     *     @OA\Parameter(...),
     *     @OA\Response(...)
     * )
     */
    public function getAllBusinesses(Request $request, Response $response): Response
    {
        try {
            $queryParams = $request->getQueryParams();
            $result = $this->businessService->getPaginatedBusinesses($queryParams);
            
            // Format response to match frontend expectations
            $responseFormat = [
                'businesses' => $result['total'],
                'filtered' => $result['total'],
                'error' => null,
                'loading' => false,
                'searchTerm' => $queryParams['search'] ?? '',
                'selectedCategory' => $queryParams['category'] ?? '',
                'selectedDistrict' => $queryParams['district'] ?? '',
                'data' => $result['data']
            ];
            
            return ResponseHelper::withJson($response, $responseFormat);
        } catch (\Exception $e) {
            return ResponseHelper::withJson($response, [
                'businesses' => 0,
                'filtered' => 0,
                'error' => $e->getMessage(),
                'loading' => false,
                'searchTerm' => '',
                'selectedCategory' => '',
                'selectedDistrict' => ''
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/businesses/{id}",
     *     tags={"Business"},
     *     summary="Get a business by ID",
     *     description="Retrieve a business by its ID",
     *     @OA\Parameter(...),
     *     @OA\Response(...)
     * )
     */
    public function getBusinessById(Request $request, Response $response, array $args): Response
    {
        try {
            $business = $this->businessService->getBusinessDetails(
                (int)$args['id'], 
                $request->getQueryParams()
            );
            
            $this->analyticsService->trackPageView($request, $business->id);
            return ResponseHelper::success($response, $business);
        } catch (\Exception $e) {
            return $this->errorService->handle($e, $response, 'business.view');
        }
    }

    /**
     * @OA\Get(
     *     path="/my-business",
     *     tags={"Business"},
     *     summary="Get authenticated user's business",
     *     description="Retrieve the business associated with the authenticated user",
     *     @OA\Response(...)
     * )
     */
    public function getMyBusiness(Request $request, Response $response): Response
    {
        try {
            $user = $request->getAttribute('user');
            $business = $this->businessService->getUserBusiness($user);
            
            return ResponseHelper::success($response, $business);
        } catch (\Exception $e) {
            return $this->errorService->handle($e, $response, 'business.my');
        }
    }

    /**
     * @OA\Post(
     *     path="/my-business/logo",
     *     tags={"Business"},
     *     summary="Upload business logo",
     *     description="Upload a logo for the business",
     *     @OA\RequestBody(...),
     *     @OA\Response(...)
     * )
     */
    public function uploadLogo(Request $request, Response $response): Response
    {
        return $this->handleImageUpload($request, $response, 'logo');
    }

    /**
     * @OA\Post(
     *     path="/my-business/cover",
     *     tags={"Business"},
     *     summary="Upload business cover image",
     *     description="Upload a cover image for the business",
     *     @OA\RequestBody(...),
     *     @OA\Response(...)
     * )
     */
    public function uploadCover(Request $request, Response $response): Response
    {
        return $this->handleImageUpload($request, $response, 'cover_image');
    }

    private function handleImageUpload(Request $request, Response $response, string $type): Response
    {
        try {
            $user = $request->getAttribute('user');
            $uploadedFile = $this->getUploadedFile($request);
            
            $imagePath = $this->imageService->uploadBusinessImage(
                $user->business_id,
                $uploadedFile,
                $type
            );

            return ResponseHelper::success($response, [
                'status' => 'success',
                'data' => [$type => $imagePath]
            ]);
        } catch (\Exception $e) {
            return $this->errorService->handle($e, $response, 'business.upload');
        }
    }

    private function getUploadedFile(Request $request): UploadedFileInterface
    {
        $files = $request->getUploadedFiles();
        return $files['image'] ?? throw new BadRequestException('No image uploaded');
    }
}