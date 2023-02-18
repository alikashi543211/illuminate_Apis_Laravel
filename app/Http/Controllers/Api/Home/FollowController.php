<?php

namespace App\Http\Controllers\Api\Home;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Home\FollowingRequest;
use App\Models\Feed;
use App\Models\User;
use App\Models\UserFollow;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FollowController extends Controller
{
    private $userFollow;
    public function __construct()
    {
        $this->userFollow = new UserFollow();
    }

    /**
     * @OA\Post(
     *      path="/api/user/home/following",
     *      operationId="following",
     *      tags={"home,following"},
     *      summary="following",
     *      security={
     *           {"bearerAuth": {}}
     *       },
     *      description="",
    *       @OA\Parameter(
    *          name="follow_id",
    *          description="Follow user id",
    *          required=true,
    *           in="query",
    *          @OA\Schema(
    *              type="string"
    *          )
    *      ),
    *       @OA\Parameter(
    *          name="is_follow",
    *          description="True or False",
    *          required=true,
    *           in="query",
    *          @OA\Schema(
    *              type="string"
    *          )
    *      ),
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
    public function following(FollowingRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            $user = Auth::user();
            if($inputs['is_follow'])
            {
                if(!$this->userFollow->newQuery()->whereFollowId($inputs['follow_id'])->whereUserId($user->id)->exists()){
                    $new = $this->userFollow->newInstance();
                    $new->follow_id = $inputs['follow_id'];
                    $new->user_id = $user->id;
                    $new->save();
                }
            }else{
                if($this->userFollow->newQuery()->whereFollowId($inputs['follow_id'])->whereUserId($user->id)->exists()){
                    $old = $this->userFollow->newQuery()->whereFollowId($inputs['follow_id'])->whereUserId($user->id)->first();
                    if(!$old->delete())
                    {
                        DB::rollBack();
                        return error(GENERAL_ERROR_MESSAGE, ERROR_400);
                    }
                }
            }
            DB::commit();
            return success(GENERAL_SUCCESS_MESSAGE);
        } catch (QueryException $e) {
            DB::rollBack();
            return error($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return error($e->getMessage(), ERROR_500);
        }
    }

}
