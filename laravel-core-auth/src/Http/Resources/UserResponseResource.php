<?php

namespace AttractCores\LaravelCoreAuth\Http\Resources;

use AttractCores\LaravelCoreAuth\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class UserResource
 *
 * @property User resource - User resource model
 *
 * @package App\Http\Resources
 */
class UserResponseResource extends JsonResource
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
        return [
            'id'                => $this->resource->getKey(),
            'email'             => $this->resource->email,
            'name'              => $this->resource->name,
            'email_verified_at' => $this->resource->email_verified_at ? $this->resource->email_verified_at->getPreciseTimestamp(3) : NULL,
            'terms_accepted_at' => $this->resource->terms_accepted_at ? $this->resource->terms_accepted_at->getPreciseTimestamp(3) : NULL,
            'created_at'        => $this->resource->created_at ? $this->resource->created_at->getPreciseTimestamp(3) : NULL,
            'updated_at'        => $this->resource->updated_at ? $this->resource->updated_at->getPreciseTimestamp(3) : NULL,
            'permissions'       => $this->resource->permissions_codes,
        ];
    }
}
