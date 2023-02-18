<?php

namespace App\Http\Controllers\Api\Home;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Home\FeedReportRequest;
use App\Models\Feed;
use App\Models\FeedReport;
use App\Models\User;
use App\Models\UserFollow;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    private $feedReport;
    public function __construct()
    {
        $this->feedReport = new FeedReport();
    }

    /**
     * @OA\Post(
     *      path="/api/user/home/feedReport",
     *      operationId="feedReport",
     *      tags={"home,feedReport"},
     *      summary="feedReport",
     *      security={
     *           {"bearerAuth": {}}
     *       },
     *      description="",
    *       @OA\Parameter(
    *          name="feed_id",
    *          description="feed id",
    *          required=true,
    *           in="query",
    *          @OA\Schema(
    *              type="string"
    *          )
    *       ),
    *       @OA\Parameter(
    *          name="reason",
    *          description="Reason for report",
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
    public function feedReport(FeedReportRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            $user = Auth::user();

            if(!$this->feedReport->newQuery()->whereFeedId($inputs['feed_id'])->whereUserId($user->id)->exists()){
                $new = $this->feedReport->newInstance();
                $new->feed_id = $inputs['feed_id'];
                $new->user_id = $user->id;
                $new->reason = $inputs['reason'];
                $new->save();
            }
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
