<?php

/**
 * @OA\OpenApi(
 *     @OA\Info(
 *         title="Mpumalanga Business Hub API",
 *         version="1.0.0",
 *         description="API documentation for MPBH Business Platform",
 *         @OA\Contact(
 *             email="support@mpbusinesshub.co.za",
 *             name="MPBH Support"
 *         )
 *     ),
 *     @OA\Server(
 *         url="http://localhost:8080/api",
 *         description="Development Server"
 *     ),
 *     @OA\SecurityScheme(
 *         securityScheme="bearerAuth",
 *         type="http",
 *         scheme="bearer",
 *         bearerFormat="JWT"
 *     ),
 *     @OA\Components(
 *         @OA\Schema(
 *             schema="Business",
 *             required={"name", "category", "district"},
 *             @OA\Property(property="id", type="integer"),
 *             @OA\Property(property="name", type="string"),
 *             @OA\Property(property="description", type="string"),
 *             @OA\Property(property="category", type="string"),
 *             @OA\Property(property="district", type="string"),
 *             @OA\Property(property="address", type="string"),
 *             @OA\Property(property="phone", type="string"),
 *             @OA\Property(property="email", type="string"),
 *             @OA\Property(property="website", type="string"),
 *             @OA\Property(property="package_type", type="string"),
 *             @OA\Property(property="logo", type="string", format="uri"),
 *             @OA\Property(property="cover_image", type="string", format="uri"),
 *             @OA\Property(property="created_at", type="string", format="date-time"),
 *             @OA\Property(property="updated_at", type="string", format="date-time")
 *         ),
 *         @OA\Schema(
 *             schema="Product",
 *             required={"name", "description", "price"},
 *             @OA\Property(property="id", type="integer"),
 *             @OA\Property(property="name", type="string"),
 *             @OA\Property(property="description", type="string"),
 *             @OA\Property(property="price", type="number", format="float"),
 *             @OA\Property(property="business_id", type="integer"),
 *             @OA\Property(property="created_at", type="string", format="date-time"),
 *             @OA\Property(property="updated_at", type="string", format="date-time")
 *         ),
 *         @OA\Schema(
 *             schema="Review",
 *             required={"rating", "comment"},
 *             @OA\Property(property="id", type="integer"),
 *             @OA\Property(property="rating", type="integer", minimum=1, maximum=5),
 *             @OA\Property(property="comment", type="string"),
 *             @OA\Property(property="business_id", type="integer"),
 *             @OA\Property(property="user_id", type="integer"),
 *             @OA\Property(property="created_at", type="string", format="date-time"),
 *             @OA\Property(property="updated_at", type="string", format="date-time")
 *         ),
 *         @OA\Schema(
 *             schema="BusinessUpdate",
 *             @OA\Property(property="name", type="string"),
 *             @OA\Property(property="description", type="string"),
 *             @OA\Property(property="category", type="string"),
 *             @OA\Property(property="district", type="string"),
 *             @OA\Property(property="address", type="string"),
 *             @OA\Property(property="phone", type="string"),
 *             @OA\Property(property="email", type="string"),
 *             @OA\Property(property="website", type="string"),
 *             @OA\Property(property="package_type", type="string")
 *         )
 *     )
 */