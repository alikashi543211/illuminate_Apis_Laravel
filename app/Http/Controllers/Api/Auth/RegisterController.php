<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\RegisterRequest;
use App\Http\Requests\Api\Auth\VerificationCodeResendRequest;
use App\Http\Requests\Api\Auth\VerifyEmailVerificationCodeRequest;
use App\Jobs\SendMailJob;
use App\Models\NotificationDevice;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    private $user, $notificationDevice;

    public function __construct()
    {
        $this->user = new User();
        $this->notificationDevice = new NotificationDevice();
    }

    /**
     * @OA\Post(
     *      path="/api/auth/register",
     *      operationId="register",
     *      tags={"auth,register"},
     *      summary="authentication",
     *      description="",
    *      @OA\Parameter(
    *          name="full_name",
    *          description="Full Name",
    *          required=true,
    *           in="query",
    *          @OA\Schema(
    *              type="string"
    *          )
    *      ),
     *      @OA\Parameter(
    *          name="email",
    *          description="Email",
    *          required=true,
    *           in="query",
    *          @OA\Schema(
    *              type="string"
    *          )
    *      ),
    *      @OA\Parameter(
    *          name="phone_no",
    *          description="Phone Number",
    *          required=false,
    *           in="query",
    *          @OA\Schema(
    *              type="string"
    *          )
    *      ),
     *      @OA\Parameter(
    *          name="password",
    *          description="Password",
    *          required=false,
    *           in="query",
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
     *      @OA\Parameter(
    *          name="social_user_id",
    *          description="Social User Id ( required if login type is Google, Facebook or Apple )",
    *          required=false,
    *           in="query",
    *          @OA\Schema(
    *              type="string"
    *          )
    *      ),
     *      @OA\Parameter(
    *          name="social_token",
    *          description="Social Token ( required if login type is Google, Facebook )",
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

    public function register(RegisterRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            if ($inputs['login_type'] != LOGIN_EMAIL) {
                if($inputs['login_type'] == LOGIN_APPLE)
                {
                    $user = $this->user->newQuery()->where('social_user_id', $inputs['social_user_id'])->first();
                }else{
                    $user = $this->user->newQuery()->where('social_user_id', $inputs['social_user_id'])->where('email', $inputs['email'])->first();
                }
                if($user)
                {
                    Auth::login($user);
                    $this->user = Auth::user();
                    $this->user->jwt_sign = null;
                    $toReturnUser = Auth::user();
                    $token = $toReturnUser->createToken('bearer_token');
                    $toReturnUser->token = $token->plainTextToken;
                    DB::commit();
                    return successWithData(__('auth.registration'), $toReturnUser);
                }
            }
            if (!empty($inputs['email']) && $this->user->newQuery()->where('email', $inputs['email'])->exists()) {
                return error('The email has already been taken.', ERROR_400);
            }
            $user = $this->user->newInstance();
            $user->fill($inputs);
            $user->password = $inputs['login_type'] == LOGIN_EMAIL ? Hash::make($inputs['password']) : NULL;
            $user->role_id = USER_APP;
            if ($inputs['login_type'] == LOGIN_EMAIL) {
                $user->verification_code = generateVerificationCode();
            } else {
                $user->status = STATUS_ACTIVE;
            }
            if ($user->save()) {
                $user = $user->fresh();
                if ($inputs['login_type'] == LOGIN_EMAIL) {
                    dispatch(new SendMailJob($user->email, 'Email Verification', ['verificationCode' => $user->verification_code], 'emails.email-verification'));
                }
                Auth::login($user);
                $this->user = Auth::user();
                $this->user->jwt_sign = null;
                $toReturnUser = Auth::user();
                $token = $toReturnUser->createToken('bearer_token');
                $toReturnUser->token = $token->plainTextToken;
                DB::commit();
                return successWithData(__('auth.registration'), $toReturnUser);
            }
            DB::rollback();
            return error(__('auth.registrationError'), ERROR_400);
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
     *      path="/api/auth/verifyEmailVerificationCode",
     *      operationId="verifyEmailVerificationCode",
     *      tags={"auth,verifyEmailVerificationCode"},
     *      summary="authentication",
     *      description="",
    *       @OA\Parameter(
    *          name="email",
    *          description="Email",
    *          required=true,
    *           in="query",
    *          @OA\Schema(
    *              type="string"
    *          )
    *      ),
     *      @OA\Parameter(
    *          name="verification_code",
    *          description="Verification Code",
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

    public function verifyEmailVerificationCode(VerifyEmailVerificationCodeRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            $user = $this->user->newQuery()->where('email', $inputs['email'])->where('verification_code', $inputs['verification_code'])->first();
            if (strtotime('-5 minutes') < strtotime($user->updated_at)) {
                $user->verification_code = NULL;
                $user->email_verified_at = Carbon::now();
                if ($user->save()) {
                    Auth::login($user);
                    $this->user = Auth::user();
                    $toReturnUser = Auth::user();
                    $token = $toReturnUser->createToken('bearer_token');
                    $toReturnUser->token = $token->plainTextToken;

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

                    DB::commit();
                    return successWithData(__('auth.registration'), $toReturnUser);
                }
            }
            DB::rollback();
            return error(__('auth.verificationCodeExpired'), ERROR_400);
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
     *      path="/api/auth/verificationCodeResend",
     *      operationId="verificationCodeResend",
     *      tags={"auth,verificationCodeResend"},
     *      summary="authentication",
     *      description="",
    *       @OA\Parameter(
    *          name="email",
    *          description="Email",
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
    public function verificationCodeResend(VerificationCodeResendRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            $user = $this->user->newQuery()->where('email', $inputs['email'])->first();
            if (!$user->email_verified_at) {
                $user->verification_code = generateVerificationCode();
                if ($user->save()) {
                    DB::commit();
                    dispatch(new SendMailJob($user->email, 'Email Verification', ['verificationCode' => $user->verification_code], 'emails.email-verification'));
                    return success(__('auth.emailVerificationCode', ['email' => $user->email]));
                }
            } else return error(__('auth.emailAreadyVerified'), ERROR_400);
            DB::rollback();
            return error(__('auth.registrationError'), ERROR_400);
        } catch (QueryException $e) {
            DB::rollBack();
            return error($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return error($e->getMessage(), ERROR_500);
        }
    }

}
