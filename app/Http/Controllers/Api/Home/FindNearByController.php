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

class FindNearByController extends Controller
{
    private $user, $feed;
    public function __construct()
    {
        $this->user = new User();
        $this->feed = new Feed();
    }

    /**
     * @OA\Post(
     *      path="/api/user/home/findNearBy",
     *      operationId="findNearBy",
     *      tags={"home,findNearBy"},
     *      summary="Home",
     *      security={
     *           {"bearerAuth": {}}
     *       },
     *      description="",
    *       @OA\Parameter(
    *          name="radius",
    *          description="Radius",
    *          required=false,
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
    public function findNearBy(FindNearByRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            $user = Auth::user();
            $nearByUsers = [];
            // Get Radius
            $radius = NEAR_BY_FIVE_HUNDERED_FEET_RADIUS;
            if(isset($inputs['radius']))
            {
                $radius = ( $inputs['radius'] * ONE_FOOT_IN_METERS);
                $radius = nthDecimal($radius, 4);
            }
            if($user->latitude && $user->longitude)
            {
                $this->findNearByUsers($user->latitude, $user->longitude, $radius, $nearByUsers);
                foreach($nearByUsers AS $user)
                {
                    $user->is_following = false;
                    if(UserFollow::whereFollowId($user->id)->whereUserId(Auth::id())->exists()){
                        $user->is_following = true;
                    }
                }
            }
            return successWithData(GENERAL_FETCHED_MESSAGE, $nearByUsers);
        } catch (QueryException $e) {
            DB::rollBack();
            return error($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return error($e->getMessage(), ERROR_500);
        }
    }

    /**
     * @OA\Post(
     *      path="/api/user/home/findNearByFeeds",
     *      operationId="findNearByFeeds",
     *      tags={"home,findNearByFeeds"},
     *      summary="Home",
     *      security={
     *           {"bearerAuth": {}}
     *       },
     *      description="",
    *       @OA\Parameter(
    *          name="radius",
    *          description="Radius",
    *          required=false,
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
    public function findNearByFeeds(FindNearFeedsByRequest $request )
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            $user = Auth::user();
            $nearByFeeds = [];
            // Get Radius
            $radius = NEAR_BY_FIVE_HUNDERED_FEET_RADIUS;
            if(isset($inputs['radius']))
            {
                $radius = ( $inputs['radius'] * ONE_FOOT_IN_METERS);
                $radius = nthDecimal($radius, 4);
            }

            if($user->latitude && $user->longitude)
            {
                $this->findNearByFeedsHelper($user->latitude, $user->longitude, $radius, $nearByFeeds);
            }
            return successWithData(GENERAL_FETCHED_MESSAGE, $nearByFeeds);
        } catch (QueryException $e) {
            DB::rollBack();
            return error($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return error($e->getMessage(), ERROR_500);
        }

    }

    private function findNearByUsers($latitude, $longitude, $radius, &$nearByUsers)
    {
        // 152.4 meters =  500 Feets
        /*
            In Circle Center point is current location ( latitude and longitude )
            In Circle Radius is distance between center point ( latitude and longitude ) to defined kilo meters or meters
            If radius in meters then code will be 6371000 , for kilo meters code will be 6371
        */
        /*
        * using eloquent approach, make sure to replace the "Restaurant" with your actual model name
        * replace 6371000 with 6371 for kilometer and 3956 for miles
        */
        $nearByUsers = User::selectRaw("*,
                        ( 6371000 * acos( cos( radians(?) ) *
                        cos( radians( latitude ) )
                        * cos( radians( longitude ) - radians(?)
                        ) + sin( radians(?) ) *
                        sin( radians( latitude ) ) )
                        ) AS distance", [$latitude, $longitude, $latitude])
            ->where('id', '!=', Auth::id())
            ->where('status', '=', STATUS_ACTIVE)
            ->having("distance", "<", $radius)
            ->orderBy("distance",'asc')
            ->get();
    }

    private function findNearByFeedsHelper($latitude, $longitude, $radius, &$nearByFeeds)
    {
        // 152.4 meters =  500 Feets
        /*
            In Circle Center point is current location ( latitude and longitude )
            In Circle Radius is distance between center point ( latitude and longitude ) to defined kilo meters or meters
            If radius in meters then code will be 6371000 , for kilo meters code will be 6371
        */
        /*
        * using eloquent approach, make sure to replace the "Restaurant" with your actual model name
        * replace 6371000 with 6371 for kilometer and 3956 for miles
        */
        $nearByFeeds = Feed::validFeed()->selectRaw("*,
                        ( 6371000 * acos( cos( radians(?) ) *
                        cos( radians( latitude ) )
                        * cos( radians( longitude ) - radians(?)
                        ) + sin( radians(?) ) *
                        sin( radians( latitude ) ) )
                        ) AS distance", [$latitude, $longitude, $latitude])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->having("distance", "<", $radius)
            ->orderBy("created_at",'DESC')
            ->with(['tagUsers', 'media', 'user'])
            ->paginate(PAGINATE);
    }
}
