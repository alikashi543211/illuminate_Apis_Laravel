<?php


namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\LoginRequest;
use App\Http\Requests\Api\Auth\LogoutRequest;
use App\Jobs\SendMailJob;
use App\Models\NotificationDevice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Exception;
use Illuminate\Database\QueryException;

class AuthController extends Controller
{
    private $user, $notificationDevice;
    public function __construct()
    {
        $this->user = new User();
        $this->notificationDevice = new NotificationDevice();
    }

    /**
     * @OA\Post(
     *      path="/api/auth/login",
     *      operationId="login",
     *      tags={"auth,login"},
     *      summary="authentication",
     *      description="",
     *      @OA\Parameter(
    *          name="username",
    *          description="Username or Email",
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
    *         in="query",
    *          @OA\Schema(
    *              type="string"
    *          )
    *      ),
    *      @OA\Parameter(
    *          name="login_type",
    *          description="Login Type ( 1=> Email, 2 => Google, 3 => Facebook, 4 => Apple ) ",
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
    public function login(LoginRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            if ($user  = $this->user->newQuery()->where('username', $inputs['username'])->orWhere('email', $inputs['username'])->first()) {
                if (Hash::check($inputs['password'], $user->password)) {
                    if ($user->status == STATUS_DEACTIVE) {
                        DB::rollback();
                        return error(__('auth.deactive'), ERROR_400);
                    }
                    Auth::login($user);
                    $this->user = Auth::user();
                    $toReturnUser = Auth::user();
                    $token = $toReturnUser->createToken('bearer_token');
                    if (!$user->email_verified_at) {
                        $user->verification_code = generateVerificationCode();
                        dispatch(new SendMailJob($user->email, 'Email Verification', ['verificationCode' => $user->verification_code], 'emails.email-verification'));
                        if ($user->save()) {
                            $toReturnUser->token = $token->plainTextToken;
                            DB::commit();
                            return successWithData(__('auth.notVerified'), $toReturnUser);
                        }
                    }


                    if(!empty($inputs['uuid']) && !empty($inputs['token']) && !empty($inputs['type']))
                    {
                        if (!$notificationDevice = $this->notificationDevice->newQuery()->where('uuid', $inputs['uuid'])->first()) {
                            $notificationDevice = $this->notificationDevice->newInstance();
                            $notificationDevice->user_id = Auth::id();
                        }
                        $notificationDevice->fill($inputs);
                        if (!$notificationDevice->save()) {
                            DB::rollBack();
                            return error(GENERAL_ERROR_MESSAGE, ERROR_400);
                        }
                    }
                    $toReturnUser->token = $token->plainTextToken;
                    DB::commit();
                    return successWithData(__('auth.loggedIn'), $toReturnUser);
                }
            }
            DB::rollback();
            return error(__('auth.invalidCredentials'), ERROR_400);
        } catch (QueryException $e) {
            DB::rollBack();
            return error($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return error($e->getMessage(), ERROR_500);
        }
    }



    public function logout(LogoutRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            $notificationDevice = $this->notificationDevice->newQuery()->where('uuid', $inputs['uuid'])->first();
            if(!$notificationDevice)
            {
                DB::rollBack();
                return error(GENERAL_ERROR_MESSAGE, ERROR_400);
            }
            if(!$notificationDevice->delete())
            {
                DB::rollBack();
                return error(GENERAL_ERROR_MESSAGE, ERROR_400);
            }
            $token = $request->header('Authorization');
            if($token)
            {
                JWTAuth::invalidate($token);
            }
            DB::commit();
            return success('Logged Out Succesfully');

        } catch (QueryException $e) {
            DB::rollBack();
            return error($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return error($e->getMessage(), ERROR_500);
        }

    }


}
