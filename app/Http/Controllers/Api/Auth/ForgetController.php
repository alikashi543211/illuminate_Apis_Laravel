<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\ForgetPasswordMailRequest;
use App\Http\Requests\Api\Auth\ResetPasswordRequest;
use App\Http\Requests\Api\Auth\VerifyResetCodeRequest;
use App\Jobs\SendMailJob;
use App\Models\NotificationDevice;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ForgetController extends Controller
{
    private $user, $notificationDevice;

    public function __construct()
    {
        $this->user = new User();
        $this->notificationDevice = new NotificationDevice();
    }

    /**
     * @OA\Post(
     *      path="/api/auth/forgetPasswordMail",
     *      operationId="forgetPasswordMail",
     *      tags={"auth,forgetPasswordMail"},
     *      summary="authentication",
     *      description="",
     *      @OA\Parameter(
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
    public function forgetPasswordMail(ForgetPasswordMailRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            if ($this->user->newQuery()->where('email', $inputs['email'])->exists()) {
                DB::table('password_resets')->where('email', $inputs['email'])->delete();
                $code = generateVerificationCode('password_resets', 'token');
                DB::table('password_resets')->insert([
                    [
                        'email' => $inputs['email'],
                        'token' => $code,
                        'created_at' => Carbon::now()
                    ]
                ]);
                dispatch(new SendMailJob($inputs['email'], 'Reset Password', ['verification_code' => $code], 'emails.forgot-password'));
            }
            DB::commit();
            return success(__('auth.emailVerificationCode', ['email' => $inputs['email']]));
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
     *      path="/api/auth/verifyResetCode",
     *      operationId="verifyResetCode",
     *      tags={"auth,verifyResetCode"},
     *      summary="authentication",
     *      description="",
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

    public function verifyResetCode(VerifyResetCodeRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            if ($verify = DB::table('password_resets')->where('email', $inputs['email'])->where('token', $inputs['verification_code'])->first()) {
                if (strtotime('-5 minutes') > strtotime($verify->created_at)) {
                    DB::rollback();
                    return error(__('auth.verificationCodeExpired'), ERROR_400);
                } else {
                    $user = $this->user->where('email', $inputs['email'])->first();
                    Auth::login($user);
                    $this->user = Auth::user();
                    $this->user->jwt_sign = null;
                    $toReturnUser = Auth::user();
                    $token = $toReturnUser->createToken('bearer_token');
                    $toReturnUser->token = $token->plainTextToken;
                    DB::commit();
                    return successWithData(__('auth.codeVerified'), $toReturnUser);
                }
            }
            DB::commit();
            return error(__('auth.invalidVerificationCode'), ERROR_400);
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
     *      path="/api/auth/resetPassword",
     *      operationId="resetPassword",
     *      tags={"auth,resetPassword"},
     *      summary="authentication",
     *      description="",
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
    *          name="verification_code",
    *          description="Verification Code",
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
    public function resetPassword(ResetPasswordRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            if ($verify = DB::table('password_resets')->where('email', $inputs['email'])->where('token', $inputs['verification_code'])->first()) {
                if (strtotime('-5 minutes') > strtotime($verify->created_at)) {
                    DB::rollback();
                    return error(__('auth.verificationCodeExpired'), ERROR_400);
                } else {
                    $user = $this->user->newQuery()->where('email', $inputs['email'])->first();
                    $user->password = Hash::make($inputs['password']);
                    if ($user->save()) {
                        if (DB::table('password_resets')->where('email', $inputs['email'])->where('token', $inputs['verification_code'])->delete()) {
                            $user = $this->user->where('email', $inputs['email'])->first();
                            Auth::login($user);
                            $this->user = Auth::user();
                            $this->user->jwt_sign = null;
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
                            return successWithData(__('passwords.reset'), $toReturnUser);
                        }
                    }
                    DB::rollback();
                    return error(__('passwords.errorReset'), ERROR_400);
                }
            }
            DB::rollBack();
            return error(__('auth.invalidVerificationCode'), ERROR_400);
        } catch (QueryException $e) {
            DB::rollBack();
            return error($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return error($e->getMessage(), ERROR_500);
        }
    }
}
