<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="Illuminae API Documentation",
 *      description="",
 *      @OA\Contact(
 *          email="admin@admin.com"
 *      ),
 * )
 * @OA\SecurityScheme(
 *    securityScheme="bearerAuth",
 *    in="header",
 *    name="bearerAuth",
 *    type="http",
 *    scheme="bearer",
 *    bearerFormat="JWT",
 *  ),
 *
 * @OA\Server(
 *      url="https://illuminate.io-devs.us/",
 *      description="Staging API Server"
 * )
 * @OA\Server(
 *      url="https://illuminate.io-devs.us/",
 *      description="Live API Server"
 * )
 * @OA\Server(
 *      url="http://backend_illuminate.test/",
 *      description="Local Server"
 * )
 * @OA\Server(
 *      url="http://192.168.0.143:8009/",
 *      description="Local Server Using IP"
 * )
*
* @OA\Tag(
*     name="Projects",
*     description="API Endpoints of Projects"
* )
*/
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;


}
