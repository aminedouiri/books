<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Book;
use Illuminate\Http\Request;
use App\Http\Requests\BookRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\BookResource;
use App\Http\Requests\ListBookRequest;
use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;

class BookController extends Controller
{
    public function store(StoreBookRequest $request)
    {
        DB::beginTransaction();
        try {
            $book = Book::create($request->validated());
            DB::commit();
            Log::info('Book added successfully.', ['book' => $book->title]);
            return $this->sendSuccessResponse($book, 'Book has been creatd');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Book added failed.', ['error' => $e->getMessage()]);
            return $this->sendErrorResponse($e->getMessage());
        }

    }

    public function index(ListBookRequest $request)
    {
        try {
            $perPage = $request->input('per_page', 10);
            $page = $request->input('page', 1);
            $books = Book::query();
            $totalBooks = $books->count();
            $paginator = $books->paginate($perPage, ['*'], 'page', $page);
            $currentPage = $paginator->currentPage();
            $totalPages = $paginator->lastPage();
            Log::info('Book list retrieved successfully.');
            return $this->sendSuccessResponse([
                'books' => BookResource::collection($paginator),
                'current_page' => $currentPage,
                'total_pages' => $totalPages,
            ], 'Book list retrieved successfully');
        } catch (Exception $e) {
            Log::info('Book list retreived failed.', ['error' => $e->getMessage()]);
            return $this->sendErrorResponse($e->getMessage());
        }
    }

    public function show($id)
    {
        try {
            $book = Book::find($id);
            if(!isset($book)) {
                Log::warning('Book not found');
                return $this->sendErrorResponse('Book not found', 404);
            }
            Log::info('Book retreived successfully.', ['book' => $book->title]);
            return $this->sendSuccessResponse(new BookResource($book), 'Book retreived successfully!');
        } catch (Exception $e) {
            Log::error('Book retreived failed.', ['error' => $e->getMessage()]);
            return $this->sendErrorResponse($e->getMessage());
        }
    }

    public function update(UpdateBookRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $book = Book::find($id);
            if(!isset($book)) {
                Log::warning('Book not found');
                return $this->sendErrorResponse('Book not found', 404);
            }
            $book->update($request->validated());
            Log::info('Book updated successfully.', ['book' => $book->title]);
            return $this->sendSuccessResponse(new BookResource($book), 'Book updated successfully!');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Book updated failed', ['error' => $e->getMessage()]);
            return $this->sendErrorResponse($e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $book = Book::find($id);
            if(!isset($book)) {
                Log::warning('Book not found');
                return $this->sendErrorResponse('Book not found');
            }
            $book->delete();
            DB::commit();
            Log::info('Book deleted successfully.', ['book' => $book->title]);
            return $this->SendSuccessResponse($book, 'Book deleted successfully!');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Book deleted failed', ['error' => $e->getMessage()]);
            return $this->sendErrorResponse($e->getMessage());
        }
    }
}
