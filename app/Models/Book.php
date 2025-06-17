<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
   protected $fillable = [
        'title',
        'author',
        'isbn',
        'publication_date',
        'available',
    ];
    
    protected $casts = [
        'publication_date' => 'date',
        'available' => 'boolean',
    ];

    public function borrowedBooks()
{
    return $this->hasMany(BorrowedBook::class);
}
}