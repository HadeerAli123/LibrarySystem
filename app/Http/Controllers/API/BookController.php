<?php

namespace App\Http\Controllers\API;

use App\Models\Book;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\BookResource;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
   public function index()
    {
        $books = Book::with('addedByUser')->get(); 
        return response()->json($books);
    }
    /**
     * Store a newly created resource in storage.
     */
 public function store(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Only admins can add books'], 403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'isbn' => 'required|string|unique:books|max:13',
            'publication_date' => 'required|date',
        ]);

        $book = Book::create(array_merge($request->all(), [
            'added_by_user_id' => $request->user()->id,
        ]));

        return response()->json($book, 201); 
    }

    /**
     * Display the specified resource.
     */
    public function show(Book $book)
    {
        return new BookResource($book->load('addedByUser'));
    }

    /**
     * Update the specified resource in storage.
     */
   public function update(Request $request, Book $book)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Only admins can update books'], 403);
        }

        $request->validate([
            'title' => 'sometimes|string|max:255',
            'author' => 'sometimes|string|max:255',
            'isbn' => 'sometimes|string|unique:books,isbn,' . $book->id . '|max:13',
            'publication_date' => 'sometimes|date',
        ]);

        $book->update($request->all());
        return response()->json($book);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Book $book)
    {
        //
    }
}
