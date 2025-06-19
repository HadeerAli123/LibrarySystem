<?php

namespace App\Http\Controllers\API;

use App\Models\Book;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\BookResource;

class BookController extends Controller
{
    public function index()
    {
        try {
            $books = Book::with('addedByUser')->get();
            return response()->json($books);
        } catch (\Exception $e) {
            \Log::error('Error fetching books: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to fetch books',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            if ($request->user()->role !== 'admin') {
                return response()->json(['message' => 'Only admins can add books'], 403);
            }

            $request->validate([
                'title' => 'required|string|max:255',
                'author' => 'required|string|max:255',
                'isbn' => 'required|string|unique:books,isbn|max:13',
                'publication_date' => 'required|date',
            ]);

            $book = Book::create(array_merge($request->all(), [
                'added_by_user_id' => $request->user()->id,
            ]));

            return response()->json($book, 201);
        } catch (\Exception $e) {
            \Log::error('Error creating book: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to create book',
                'error' => $e->getMessage()
            ], 500);
        }
    }

public function show($id)
{
    $book = Book::find($id); 
    if (!$book) {
        return response()->json(['message' => 'Book not found'], 404);
    }

    try {
        return new BookResource($book->load('addedByUser'));
    } catch (\Exception $e) {
        \Log::error('Error fetching book: ' . $e->getMessage());
        return response()->json([
            'message' => 'Failed to fetch book',
            'error' => $e->getMessage()
        ], 500);
    }
}

   public function update(Request $request, $id)
{
    try {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Only admins can update books'], 403);
        }

        $book = Book::find($id);
        if (!$book) {
            return response()->json(['message' => 'Book not found'], 404);
        }

        $request->validate([
            'title' => 'sometimes|string|max:255',
            'author' => 'sometimes|string|max:255',
            'isbn' => 'sometimes|string|unique:books,isbn,' . $book->id . '|max:13',
            'publication_date' => 'sometimes|date',
        ]);

        $book->update($request->all());
        return response()->json($book);
    } catch (\Exception $e) {
        \Log::error('Error updating book: ' . $e->getMessage());
        return response()->json([
            'message' => 'Failed to update book',
            'error' => $e->getMessage()
        ], 500);
    }
}

   public function destroy(Request $request, $id)
{
    try {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Only admins can delete books'], 403);
        }

        $book = Book::find($id); 
        if (!$book) {
            return response()->json(['message' => 'Book not found'], 404);
        }

        $book->delete();
        return response()->json(['message' => 'Book deleted']);
    } catch (\Exception $e) {
        \Log::error('Error deleting book: ' . $e->getMessage());
        return response()->json([
            'message' => 'Failed to delete book',
            'error' => $e->getMessage()
        ], 500);
    }
}
}
