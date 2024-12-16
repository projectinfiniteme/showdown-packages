<?php

namespace AttractCores\LaravelCoreMedia\Http\Resources;

use AttractCores\LaravelCoreMedia\Models\Media;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class MediaResource
 *
 * @property Media resource - File resource.
 *
 * @package App\Http\Resources
 */
class MediaResource extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function toArray($request)
    {
        $canBeChanged = $this->canBeChanged();

        return [
            'id'            => $this->resource->getKey(),
            'order'         => $this->resource->order,
            'disk'          => $this->resource->disk,
            'original_name' => $this->resource->original_name,
            'ext'           => $this->resource->ext,
            'name'          => $this->resource->name,
            'path'          => $this->resource->path,
            'user_id'       => $this->resource->user_id,
            'url'           => $this->resource->url,
            'media_type'    => $this->resource->media_type_in_model,
            'resize_url'    => $canBeChanged ? $this->resource->resize_url : NULL,
            'crop_url'      => $canBeChanged ? $this->resource->crop_url : NULL,
            'created_at'    => $this->resource->created_at ? $this->resource->created_at->getPreciseTimestamp(3) : NULL,
            'updated_at'    => $this->resource->updated_at ? $this->resource->updated_at->getPreciseTimestamp(3) : NULL,
        ];
    }

    /**
     * @return false|int
     */
    protected function canBeChanged()
    {
        return preg_match('/\.(jpg|png|jpeg)$/i', $this->resource->path);
    }

}
