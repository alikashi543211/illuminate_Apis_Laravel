<?php

namespace App\Http\Controllers\Api\Home;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Home\FindNearByRequest;
use App\Http\Requests\Api\Home\FindNearFeedsByRequest;
use App\Models\Feed;
use App\Models\User;
use App\Models\UserFollow;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    private $user;
    public function __construct()
    {
        $this->user = new User();
    }

    /**
     * @OA\Post(
     *      path="/api/user/home/getUsers",
     *      operationId="getUsers",
     *      tags={"home,getUsers"},
     *      summary="Home",
     *      security={
     *           {"bearerAuth": {}}
     *       },
     *      description="",
    *      @OA\Response(
    *          response=200,
    *          description="Successful operation",
    *       ),
    *      @OA\Response(
    *          response=403,
    *          description="Forbidden"
    *      )
    *     )
    */
    public function getUsers(Request $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();

            return successWithData(GENERAL_FETCHED_MESSAGE, $this->user->newQuery()->where('id', '!=', Auth::id())->whereNotNull('username')->where('role_id', USER_APP)->get(['id', 'username'])->toArray());
        } catch (QueryException $e) {
            DB::rollBack();
            return error($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return error($e->getMessage(), ERROR_500);
        }
    }

}
