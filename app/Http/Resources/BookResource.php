<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
       return [
            'id' => $this->id,
            'title' => $this->title,
            'author' => $this->author,
            'isbn' => $this->isbn,
            'publication_date' => $this->publication_date,
            'available' => $this->available,
            'added_by_user_id' => $this->added_by_user_id,
            'added_by' => $this->whenLoaded('addedByUser', function () {
                return $this->addedByUser->name; 
            }),
            'borrowed_books_count' => $this->whenCounted('borrowedBooks'), 
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
    