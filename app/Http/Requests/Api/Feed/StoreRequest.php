<?php

namespace App\Http\Requests\Api\Feed;

use App\Http\Requests\BaseRequest;

class StoreRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'title' => 'required|min:3|max:190',
            'location' => 'required',
            'body' => 'nullable|min:3|max:300',
            'date' => 'required',
            'time' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
            'tags' => 'nullable|array',
            'medias' => 'nullable|array',
            'type' => 'nullable|in:' .FEED_PUBLIC.','.FEED_PRIVATE,
            'medias.*.type' => 'nullable|in:' .MEDIA_PHOTO.','.MEDIA_VIDEO,
            'tags.*.user_id' => 'nullable|exists:users,id',
            'flair' => 'required|in:' .FLAIR_GOING_THERE.','.FLAIR_CURRENTLY_THERE.','.FLAIR_WERE_THERE,
        ];
    }
}
