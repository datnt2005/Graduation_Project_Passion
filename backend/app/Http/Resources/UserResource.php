<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;


class UserResource extends JsonResource
{
  public function toArray($request)
        {
            return [
                'id'         => $this->id,
                'name'       => $this->name,
                'email'      => $this->email,
                'phone'      => $this->phone,
                'avatar'     => $this->avatar,
                'avatar_url' => $this->avatar ? Storage::disk('r2')->url($this->avatar) : null,
                'role'       => $this->role,
                'status'     => $this->status,
                'is_verified'=> $this->is_verified,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ];
        }

}
