<?php

namespace App\Http\Controllers\Api\Feed;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Feed\DeleteRequest;
use App\Http\Requests\Api\Feed\StoreRequest;
use App\Http\Requests\Api\Feed\UpdateRequest;
use App\Models\Feed;
use App\Models\FeedMedia;
use App\Models\User;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FeedCrudController extends Controller
{
    private $user;
    private $feed;
    private $media;
    public function __construct()
    {
        $this->user = new User();
        $this->feed = new Feed();
        $this->media = new FeedMedia();
    }

    /**
     * @OA\Post(
     *      path="/api/user/feed/listing",
     *      operationId="listing",
     *      tags={"feed,listing"},
     *      summary="user feed",
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
    public function listing(Request $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            $feeds = $this->feed->newQuery()->validFeedListing()->orderBy("created_at",'DESC')->with(['tagUsers', 'media', 'user'])->get();
            return successWithData(GENERAL_FETCHED_MESSAGE, $feeds);

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
     *      path="/api/user/feed/store",
     *      operationId="store",
     *      tags={"feed,store"},
     *      summary="user feed",
     *      security={
     *           {"bearerAuth": {}}
     *       },
     *      description="",

    *       @OA\Parameter(
    *          name="type",
    *          description="Feed Type public = 1, private=2",
    *          required=true,
    *           in="query",
    *          @OA\Schema(
    *              type="integer"
    *          )
    *      ),
    *       @OA\Parameter(
    *          name="title",
    *          description="Title",
    *          required=true,
    *           in="query",
    *          @OA\Schema(
    *              type="string"
    *          )
    *      ),
    *       @OA\Parameter(
    *          name="flair",
    *          description="Flair (Going There, Currently There, Were There)  ",
    *          required=true,
    *           in="query",
    *          @OA\Schema(
    *              type="string"
    *          )
    *      ),
    *       @OA\Parameter(
    *          name="location",
    *          description="Location",
    *          required=true,
    *           in="query",
    *          @OA\Schema(
    *              type="string"
    *          )
    *      ),
    *       @OA\Parameter(
    *          name="body",
    *          description="Body",
    *          required=false,
    *           in="query",
    *          @OA\Schema(
    *              type="string"
    *          )
    *      ),
    *       @OA\Parameter(
    *          name="time",
    *          description="Time",
    *          required=true,
    *           in="query",
    *          @OA\Schema(
    *              type="string"
    *          )
    *      ),
    *       @OA\Parameter(
    *          name="date",
    *          description="Date",
    *          required=true,
    *           in="query",
    *          @OA\Schema(
    *              type="string"
    *          )
    *      ),
    *       @OA\Parameter(
    *          name="latitude",
    *          description="latitude",
    *          required=true,
    *           in="query",
    *          @OA\Schema(
    *              type="string"
    *          )
    *      ),
    *       @OA\Parameter(
    *          name="longitude",
    *          description="longitude",
    *          required=true,
    *           in="query",
    *          @OA\Schema(
    *              type="string"
    *          )
    *      ),
    *       @OA\Parameter(
    *          name="tags",
    *          description="Tags array [{user_id:1}, {user_id:4}]",
    *          required=false,
    *           in="query",
    *          @OA\Schema(
    *              type="object"
    *          )
    *      ),
    *       @OA\Parameter(
    *          name="medias",
    *          description="Media array [{media:'path', thumbnail: 'path', type: 'Photo/Video'}]",
    *          required=false,
    *           in="query",
    *          @OA\Schema(
    *              type="object"
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
    public function store(StoreRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();

            $feed = $this->feed->newInstance();
            $feed->fill($inputs);
            $feed->user_id = Auth::id();

            if($feed->save()){
                if(isset($inputs['tags']))
                {
                    $feed->tagUsers()->sync($inputs['tags']);
                }
                if(!empty($inputs['medias'])){
                    foreach($inputs['medias'] AS $photo){
                        $media = $this->media->newInstance();
                        $media->feed_id = $feed->id;
                        $media->media = $photo['media'];
                        $media->type = $photo['type'];
                        $media->thumbnail = $photo['thumbnail'];
                        $media->save();
                    }
                }
            }
            DB::commit();
            return successWithData(GENERAL_SUCCESS_MESSAGE, $this->getFeedDetail($feed->id));

        } catch (QueryException $e) {
            DB::rollBack();
            return error($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return error($e->getMessage(), ERROR_500);
        }
    }

    private function getFeedDetail($feedId)
    {
        return $this->feed->newQuery()->whereId($feedId)->with(['tagUsers', 'media'])->first();
    }
    /**
     * @OA\Post(
     *      path="/api/user/feed/update",
     *      operationId="update",
     *      tags={"feed,update"},
     *      summary="user feed",
     *      security={
     *           {"bearerAuth": {}}
     *       },
     *      description="",

    *       @OA\Parameter(
    *          name="id",
    *          description="id",
    *          required=true,
    *           in="query",
    *          @OA\Schema(
    *              type="string"
    *          )
    *      ),
    *       @OA\Parameter(
    *          name="type",
    *          description="Feed Type public = 1, private=2",
    *          required=true,
    *           in="query",
    *          @OA\Schema(
    *              type="integer"
    *          )
    *      ),
    *       @OA\Parameter(
    *          name="title",
    *          description="Title",
    *          required=true,
    *           in="query",
    *          @OA\Schema(
    *              type="string"
    *          )
    *      ),
    *       @OA\Parameter(
    *          name="flair",
    *          description="Flair (Going There, Currently There, Were There)  ",
    *          required=true,
    *           in="query",
    *          @OA\Schema(
    *              type="string"
    *          )
    *      ),
    *       @OA\Parameter(
    *          name="location",
    *          description="Location",
    *          required=true,
    *           in="query",
    *          @OA\Schema(
    *              type="string"
    *          )
    *      ),
    *       @OA\Parameter(
    *          name="body",
    *          description="Body",
    *          required=false,
    *           in="query",
    *          @OA\Schema(
    *              type="string"
    *          )
    *      ),
    *       @OA\Parameter(
    *          name="time",
    *          description="Time",
    *          required=true,
    *           in="query",
    *          @OA\Schema(
    *              type="string"
    *          )
    *      ),
    *       @OA\Parameter(
    *          name="date",
    *          description="Date",
    *          required=true,
    *           in="query",
    *          @OA\Schema(
    *              type="string"
    *          )
    *      ),
    *       @OA\Parameter(
    *          name="latitude",
    *          description="latitude",
    *          required=true,
    *           in="query",
    *          @OA\Schema(
    *              type="string"
    *          )
    *      ),
    *       @OA\Parameter(
    *          name="longitude",
    *          description="longitude",
    *          required=true,
    *           in="query",
    *          @OA\Schema(
    *              type="string"
    *          )
    *      ),
    *       @OA\Parameter(
    *          name="tags",
    *          description="Tags array [{user_id:1}, {user_id:4}]",
    *          required=false,
    *           in="query",
    *          @OA\Schema(
    *              type="object"
    *          )
    *      ),
    *       @OA\Parameter(
    *          name="medias",
    *          description="Media array [{media:'path', thumbnail: 'path', type: 'Photo/Video'}]",
    *          required=false,
    *           in="query",
    *          @OA\Schema(
    *              type="object"
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
    public function update(UpdateRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();

            $feed = $this->feed->newQuery()->where('id', $inputs['id'])->first();
            $feed->fill($inputs);
            if($feed->save()){
                if(isset($inputs['tags']))
                {
                    $feed->tagUsers()->sync($inputs['tags']);
                }

                if(!empty($inputs['medias'])){
                    $feed->media()->delete();
                    foreach($inputs['medias'] AS $photo){
                        $media = $this->media->newInstance();
                        $media->feed_id = $feed->id;
                        $media->media = $photo['media'];
                        $media->type = $photo['type'];
                        $media->thumbnail = $photo['thumbnail'];
                        $media->save();
                    }
                }
            }
            DB::commit();
            return successWithData(GENERAL_UPDATED_MESSAGE, $this->getFeedDetail($feed->id));

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
     *      path="/api/user/feed/delete",
     *      operationId="delete",
     *      tags={"feed,delete"},
     *      summary="Feed delete",
     *      security={
     *           {"bearerAuth": {}}
     *       },
     *      description="",
     *       @OA\Parameter(
    *          name="id",
    *          description="id",
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
    public function delete(DeleteRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            $feed = $this->feed->newQuery()->whereId($inputs['id'])->first();
            $feed->tagUsers()->detach();
            if($this->media->newQuery()->whereFeedId($inputs['id'])->count() > 0)
            {
                $this->media->newQuery()->whereFeedId($inputs['id'])->delete();
            }
            if(!$feed->delete()){
                DB::rollBack();
                return error(GENERAL_ERROR_MESSAGE, ERROR_400);
            }
            DB::commit();
            return success(GENERAL_DELETED_MESSAGE);
        } catch (QueryException $e) {
            DB::rollBack();
            return error($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return error($e->getMessage(), ERROR_500);
        }
    }
}
