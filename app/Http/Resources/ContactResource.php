<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactResource extends JsonResource
{
    /**
     * お問い合わせ情報の定義
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'category' => [
                'id' => $this->category_id,
                'content' => $this->category->content,
            ],
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'gender' => $this->gender,
            'email' => $this->email,
            'tel' => $this->tel,
            'address' => $this->address,
            'building' => $this->building,
            'tag_ids' => [
                'contact_id' => $this->id,
                'id' => $this->tags->pluck('id')->toArray(),
                'name' => $this->tags->pluck('name')->toArray(),
            ],
            'detail' => $this->detail,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
