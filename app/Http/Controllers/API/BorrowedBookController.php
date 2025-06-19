<?php
namespace App\Http\Controllers\API;

use App\Models\BorrowedBook;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Book;
use App\Http\Resources\BorrowBookResource; 
use Illuminate\Support\Facades\DB;

class BorrowedBookController extends Controller
{
public function index(Request $request)
{
    try {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $borrowedBooks = BorrowedBook::with([
                'book:id,title,isbn,available',
                'user:id,name,email'
            ])
            ->whereHas('book', function ($query) {
                $query->where('available', false);
            })
            ->get(['id', 'user_id', 'book_id', 'borrow_date', 'return_date']);

        return response()->json($borrowedBooks);
    } catch (\Exception $e) {
        \Log::error('Error fetching borrowed books: ' . $e->getMessage());
        return response()->json(['message' => 'Failed to fetch borrowed books', 'error' => $e->getMessage()], 500);
    }
}

    public function store(Request $request)
    {
        try {
            if ($request->user()->role !== 'user') {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $request->validate([
                'book_id' => 'required|exists:books,id',
                'borrow_date' => 'required|date',
                'return_date' => 'nullable|date|after_or_equal:borrow_date',
            ]);

            $book = Book::find($request->book_id);
            if (!$book || !$book->available) {
                return response()->json(['message' => 'Book is not available'], 400);
            }

            $activeBorrow = BorrowedBook::where('user_id', $request->user()->id)
                ->where('book_id', $request->book_id)
                ->whereNull('return_date')->exists();

            if ($activeBorrow) {
                return response()->json(['message' => 'This book is already borrowed by the user'], 400);
            }

            $borrowedBook = DB::transaction(function () use ($request, $book) {
                $borrowedBook = BorrowedBook::create([
                    'user_id' => $request->user()->id,
                    'book_id' => $request->book_id,
                    'borrow_date' => $request->borrow_date,
                    'return_date' => $request->return_date,
                ]);

                $book->update(['available' => false]);

                return $borrowedBook;
            });

            return response()->json($borrowedBook, 201);
        } catch (\Exception $e) {
            \Log::error('Error borrowing book: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to borrow book', 'error' => $e->getMessage()], 500);
        }
    }

public function myBorrowedBooks()
{
    try {
        $borrowedBooks = BorrowedBook::with(['book:id,title,author,publication_date,available'])
            ->where('user_id', auth()->id())
            ->whereHas('book', function ($query) {
                $query->where('available', false);
            })
            ->get(['id', 'user_id', 'book_id', 'borrow_date', 'return_date']);

        return response()->json($borrowedBooks);
    } catch (\Exception $e) {
        \Log::error('Error fetching user borrowed books: ' . $e->getMessage());
        return response()->json(['message' => 'Failed to fetch your borrowed books', 'error' => $e->getMessage()], 500);
    }
}


public function show(Request $request, $id)
{
    try {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $borrowedBook = BorrowedBook::with(['user', 'book'])->find($id);

        if (!$borrowedBook) {
            return response()->json(['message' => 'Borrowed book not found'], 404);
        }

        if ($borrowedBook->book->available) {
            \Log::info('Book ID ' . $borrowedBook->book_id . ' is available');
            return response()->json(['message' => 'This book is available and not borrowed'], 400);
        }

        \Log::info('Fetched borrowed book: ' . $borrowedBook->toJson());
        return new BorrowBookResource($borrowedBook); 
    } catch (\Exception $e) {
        \Log::error('Error fetching borrowed book: ' . $e->getMessage());
        return response()->json(['message' => 'Failed to fetch borrowed book', 'error' => $e->getMessage()], 500);
    }
}

 public function update(Request $request, BorrowedBook $borrowedBook)
{
    try {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'return_date' => 'nullable|date|after_or_equal:borrow_date',
        ]);

        $borrowedBook->update($request->only(['return_date']));

        if ($request->has('return_date') && $request->return_date) {
            $borrowedBook->book->update(['available' => true]);
        }

        return response()->json($borrowedBook);
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return response()->json(['message' => 'Borrowed book not found'], 404);
    } catch (\Exception $e) {
        \Log::error('Error updating borrowed book: ' . $e->getMessage());
        return response()->json(['message' => 'Failed to update borrowed book', 'error' => $e->getMessage()], 500);
    }
}

    public function destroy(Request $request, BorrowedBook $borrowedBook)
    {
        try {
            if ($request->user()->role !== 'admin') {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $borrowedBook->delete();
            return response()->json(['message' => 'Borrowed book record deleted']);
        } catch (\Exception $e) {
            \Log::error('Error deleting borrowed book: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to delete borrowed book', 'error' => $e->getMessage()], 500);
        }
    }
}