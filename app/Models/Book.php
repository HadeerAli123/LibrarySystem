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
        'added_by_user_id',
    ];

    protected $casts = [
        'publication_date' => 'date',
        'available' => 'boolean',
    ];

    public function borrowedBooks()
{
    return $this->hasMany(BorrowedBook::class);
}
public function addedByUser()
    {
        return $this->belongsTo(User::class, 'added_by_user_id');
    }
}