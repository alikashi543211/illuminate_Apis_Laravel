<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Feed extends Model
{
    use SoftDeletes, HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'flair',
        'body',
        'type',
        'location',
        'time',
        'date',
        'latitude',
        'longitude',
    ];

    /**
     * The user that belong to the Feed
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    /**
     * The tagUsers that belong to the Feed
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function tagUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'feed_tags', 'feed_id', 'user_id');
    }

    /**
     * Get all of the media for the Feed
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function media(): HasMany
    {
        return $this->hasMany(FeedMedia::class, 'feed_id', 'id');
    }


    /**
     * The reportsBy that belong to the Feed
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function reportsBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'feed_reports', 'feed_id', 'user_id');
    }

    public function scopeValidFeed($query)
    {
        // Skip Reported By Feeds
        $query->whereDoesntHave('reportsBy', function($q){
            $q->where('feed_reports.user_id', Auth::id());
        });

        // Only Public or Follow By Me Feed
         $query->where(function($q){
            $q->whereIn('user_id', DB::table('user_follows')->whereUserId(Auth::id())->pluck('follow_id')->toArray())
                ->orWhere('type', FEED_PUBLIC);
         })->orWhere('user_id', Auth::id());
    }

    public function scopeValidFeedListing($query)
    {
        // Skip Reported By Feeds
        $query->whereDoesntHave('reportsBy', function($q){
            $q->where('feed_reports.user_id', Auth::id());
        });

        // Only Public or Follow By Me Feed
         $query->where(function($q){
            $q->whereIn('user_id', DB::table('user_follows')->whereUserId(Auth::id())->pluck('follow_id')->toArray())
            ->orWhere('user_id', Auth::id());
         });
    }
}
