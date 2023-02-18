<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Tymon\JWTAuth\Facades\JWTAuth;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;


    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'full_name',
        'phone_no',
        'email',
        'password',
        'age',
        'height',
        'gender',
        'about_you',
        'login_type',
        'social_user_id',
        'social_token',
        'longitude',
        'latitude',
        'facebook_url',
        'twitter_url',
        'instagram_url',
        'username',
    ];
    protected $appends = [
        'is_verified',
        'is_following'
    ];



    /**
     * Get the ProfilePicture
     *
     * @param  string  $value
     * @return string
     */
    public function getPhotoAttribute($value)
    {
        if($value)
        {
            if (env('AWS_ENV')) {

                $value = Storage::temporaryUrl($value, now()->addDay());
            } else {
                $value = url($value);
            }
        }
        return $value;
    }
    /**
     * Get the isVerified
     *
     * @param  string  $value
     * @return string
     */
    public function getIsVerifiedAttribute($value)
    {
        if($this->login_type != LOGIN_EMAIL || $this->email_verified_at)
        {
            return true;
        }
        return false;
    }
    /**
     * Get the IsFollowing
     *
     * @param  string  $value
     * @return string
     */
    public function getIsFollowingAttribute()
    {
        return false;
    }

    /* Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'role_id',
        'jwt_sign',
        'email_verified_at',
        'mobile_verified_at',
        'verification_code',
        'verification_code_email',
        'verification_code_mobile',
        'social_user_id',
        'social_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    /**
     * The following that belong to the Feed
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function following(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_follows', 'follow_id', 'user_id');
    }

    /**
     * The followers that belong to the Feed
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_follows', 'user_id', 'follow_id');
    }


}
