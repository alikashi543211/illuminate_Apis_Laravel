<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\User\StoreUsernameRequest;
use App\Http\Requests\Api\User\UploadPhotoRequest;
use App\Http\Requests\Api\User\UpdatePasswordRequest;
use App\Http\Requests\Api\Auth\CheckUsernameAvailabilityRequest;
use App\Http\Requests\Api\User\StoreGeneralDataRequest;
use App\Http\Requests\Api\User\StoreLocationRequest;
use App\Models\User;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    private $user;
    public function __construct()
    {
        $this->user = new User();
    }

    /**
     * @OA\Get(
     *      path="/api/user/profile/getUser",
     *      operationId="getUser",
     *      tags={"user,profile,getUser"},
     *      summary="user profile",
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      description="",
    *      @OA\Response(
    *          response=200,
    *          description="Successful operation",
    *       ),
    *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
    *      @OA\Response(
    *          response=403,
    *          description="Forbidden"
    *      )
    *     )
    */
    public function getUser(Request $request)
    {
        try {
            DB::beginTransaction();
            return successWithData(GENERAL_FETCHED_MESSAGE, $this->user->newQuery()->find(Auth::id()));
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
     *      path="/api/user/profile/updatePassword",
     *      operationId="updatePassword",
     *      tags={"user,profile,updatePassword"},
     *      summary="user profile",
     *      security={
     *           {"bearerAuth": {}}
     *       },
     *      description="",
    *       @OA\Parameter(
    *          name="old_password",
    *          description="Old Password",
    *          required=true,
    *           in="query",
    *          @OA\Schema(
    *              type="string"
    *          )
    *      ),
     *      @OA\Parameter(
    *          name="password",
    *          description="Password",
    *          required=true,
    *           in="query",
    *          @OA\Schema(
    *              type="string"
    *          )
    *      ),
     *      @OA\Parameter(
    *          name="password_confirmation",
    *          description="Password Confirmation",
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

    public function updatePassword(UpdatePasswordRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            $user = $this->user->newQuery()->find(Auth::id());
            if (Hash::check($inputs['old_password'], $user->password)) {
                $user->password = Hash::make($inputs['password']);
                if ($user->save()) {
                    DB::commit();
                    return success(GENERAL_UPDATED_MESSAGE);
                }
            }
            DB::rollback();
            return error('Incorrect Password', ERROR_400);
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
     *      path="/api/user/profile/uploadPhoto",
     *      operationId="uploadPhoto",
     *      tags={"user,profile,uploadPhoto"},
     *      summary="user profile",
     *      security={
     *           {"bearerAuth": {}}
     *       },
     *      description="",
     *     @OA\RequestBody(
    *          required=true,
    *          @OA\MediaType(
    *              mediaType="multipart/form-data",
    *              @OA\Schema(
    *                  @OA\Property(
    *                      property="photo",
    *                      description="Photo",
    *                      type="file",
    *                      format="binary"
    *                   ),
    *               ),
    *           ),
    *       ),
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
    public function uploadPhoto(UploadPhotoRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            $user = $this->user->newQuery()->whereId(Auth::id())->first();
            if (!uploadFile($inputs['photo'], $user, 'photo', 'user-photos')) {
                DB::rollBack();
                return error(GENERAL_ERROR_MESSAGE, ERROR_400);
            }
            if(!$user->save())
            {
                DB::rollBack();
                return error(GENERAL_ERROR_MESSAGE, ERROR_400);
            }
            DB::commit();
            return successWithData(GENERAL_FETCHED_MESSAGE, $user->fresh());
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
     *      path="/api/user/profile/storeUsername",
     *      operationId="storeUsername",
     *      tags={"user,profile,storeUsername"},
     *      summary="user profile",
     *      security={
     *           {"bearerAuth": {}}
     *       },
     *      description="",

    *       @OA\Parameter(
    *          name="username",
    *          description="Username",
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

    public function storeUsername(StoreUsernameRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            if($this->user->newQuery()->whereUsername($inputs['username'])->where('id', '!=',  Auth::id())->exists())
            {
                return error(USERNAME_NOT_AVAILABLE_MESSAGE, SUCCESS_200); // Code 200 but success will be false;
            }

            $user = $this->user->newQuery()->whereId(Auth::id())->first();
            $user->username = $inputs['username'];
            if(!$user->save())
            {
                DB::rollBack();
                return error(GENERAL_ERROR_MESSAGE, ERROR_400);
            }
            DB::commit();
            return successWithData(GENERAL_SUCCESS_MESSAGE, $user->fresh());
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
     *      path="/api/user/profile/checkUsernameAvailability",
     *      operationId="checkUsernameAvailability",
     *      tags={"user,profile,checkUsernameAvailability"},
     *      summary="user profile",
     *      security={
     *           {"bearerAuth": {}}
     *       },
     *      description="",

    *       @OA\Parameter(
    *          name="username",
    *          description="Username",
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

    public function checkUsernameAvailability(CheckUsernameAvailabilityRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            DB::commit();
            if($this->user->newQuery()->whereUsername($inputs['username'])->where('id', '!=',  Auth::id())->exists())
            {
                return error(USERNAME_NOT_AVAILABLE_MESSAGE, SUCCESS_200); // Code 200 but success will be false;
            }
            return success(USERNAME_AVAILABLE_MESSAGE);

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
     *      path="/api/user/profile/storeGeneralData",
     *      operationId="storeGeneralData",
     *      tags={"user,profile,storeGeneralData"},
     *      summary="user profile",
     *      security={
     *           {"bearerAuth": {}}
     *       },
     *      description="",

    *       @OA\Parameter(
    *          name="age",
    *          description="Age",
    *          required=false,
    *           in="query",
    *          @OA\Schema(
    *              type="string"
    *          )
    *      ),
    *       @OA\Parameter(
    *          name="gender",
    *          description="Gender",
    *          required=false,
    *           in="query",
    *          @OA\Schema(
    *              type="string"
    *          )
    *      ),
    *       @OA\Parameter(
    *          name="height",
    *          description="Height",
    *          required=false,
    *           in="query",
    *          @OA\Schema(
    *              type="string"
    *          )
    *      ),
    *       @OA\Parameter(
    *          name="about_you",
    *          description="About you",
    *          required=false,
    *           in="query",
    *          @OA\Schema(
    *              type="string"
    *          )
    *      ),
    *       @OA\Parameter(
    *          name="phone_no",
    *          description="Phone Number",
    *          required=false,
    *           in="query",
    *          @OA\Schema(
    *              type="string"
    *          )
    *      ),
    *       @OA\Parameter(
    *          name="facebook_url",
    *          description="Facebook Url",
    *          required=false,
    *           in="query",
    *          @OA\Schema(
    *              type="string"
    *          )
    *      ),
    *       @OA\Parameter(
    *          name="instagram_url",
    *          description="Instagram Url",
    *          required=false,
    *           in="query",
    *          @OA\Schema(
    *              type="string"
    *          )
    *      ),
    *       @OA\Parameter(
    *          name="twitter_url",
    *          description="Twitter Url",
    *          required=false,
    *           in="query",
    *          @OA\Schema(
    *              type="string"
    *          )
    *      ),
    *       @OA\Parameter(
    *          name="username",
    *          description="Username",
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
    public function storeGeneralData(StoreGeneralDataRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            $user = $this->user->newQuery()->whereId(Auth::id())->first();
            if(isset($inputs['username']) && $this->user->newQuery()->whereUsername($inputs['username'])->where('id', '!=',  Auth::id())->exists())
            {
                return error(USERNAME_NOT_AVAILABLE_MESSAGE, SUCCESS_200); // Code 200 but success will be false;
            }
            $user->fill($inputs);
            $user->profile_status = PROFILE_STATUS_COMPLETE;
            if(!$user->save())
            {
                DB::rollBack();
                return error(GENERAL_ERROR_MESSAGE, ERROR_400);
            }
            DB::commit();
            return successWithData(GENERAL_UPDATED_MESSAGE, $user->fresh());
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
     *      path="/api/user/profile/storeLocation",
     *      operationId="storeLocation",
     *      tags={"user,profile,storeLocation"},
     *      summary="user profile",
     *      security={
     *           {"bearerAuth": {}}
     *       },
     *      description="",

    *       @OA\Parameter(
    *          name="longitude",
    *          description="Longitude",
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
    public function storeLocation(StoreLocationRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            $user = $this->user->newQuery()->whereId(Auth::id())->first();
            $user->longitude = $inputs['longitude'];
            $user->latitude = $inputs['latitude'];
            if(!$user->save())
            {
                DB::rollBack();
                return error(GENERAL_ERROR_MESSAGE, ERROR_400);
            }
            DB::commit();
            return successWithData(GENERAL_FETCHED_MESSAGE, $user->fresh());
        } catch (QueryException $e) {
            DB::rollBack();
            return error($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return error($e->getMessage(), ERROR_500);
        }
    }
}
