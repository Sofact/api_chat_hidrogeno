<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileUserGeneralResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "name"=>$this->resource->name,
            "surname" =>$this->resource->surname,
            "email" =>$this->resource->email,
            "avatar"=> $this->resource->acatar ? env("APP_URL")."storage/".$this->resource->avatar : "https://www.flaticon.es/icono-gratis/usuario_1177568?term=usuario&page=1&position=2&origin=tag&related_id=1177568"

        ];
    }
}
