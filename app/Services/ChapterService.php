<?php

namespace App\Services;

use App\Models\Chapter;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ChapterService
{
    protected Chapter $model;

    public function __construct(Chapter $model)
    {
        $this->model = $model;
    }

    /**
     * Get all chapters with pagination
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getAllPaginated(int $perPage = 15 , $with = []): LengthAwarePaginator
    {
        return $this->model->with($with)->latest()->paginate($perPage);
    }

    /**
     * Get all chapters without pagination
     *
     * @return Collection
     */
    public function getAll(): Collection
    {
        return $this->model->all();
    }

    /**
     * Find chapter by ID
     *
     * @param int $id
     * @return Chapter|null
     */
    public function findById(int $id): ?Chapter
    {
        return $this->model->find($id);
    }

    /**
     * Find chapter by ID or fail
     *
     * @param int $id
     * @return Chapter
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findByIdOrFail(int $id): Chapter
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Create a new chapter
     *
     * @param array $data
     * @return Chapter
     * @throws \Exception
     */
    public function create(array $data): Chapter
    {
        try {
            DB::beginTransaction();

            $chapter = $this->model->create($data);

            DB::commit();

            Log::info('Chapter created successfully', ['id' => $chapter->id]);

            return $chapter;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating Chapter', ['error' => $e->getMessage(), 'data' => $data]);
            throw $e;
        }
    }

    /**
     * Update an existing chapter
     *
     * @param Chapter $chapter
     * @param array $data
     * @return Chapter
     * @throws \Exception
     */
    public function update(Chapter $chapter, array $data): Chapter
    {
        try {
            DB::beginTransaction();

            $chapter->update($data);
            $chapter->refresh();

            DB::commit();

            Log::info('Chapter updated successfully', ['id' => $chapter->id]);

            return $chapter;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating Chapter', [
                'id' => $chapter->id,
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            throw $e;
        }
    }

    /**
     * Delete a chapter
     *
     * @param Chapter $chapter
     * @return bool
     * @throws \Exception
     */
    public function delete(Chapter $chapter): bool
    {
        try {
            DB::beginTransaction();

            $deleted = $chapter->delete();

            DB::commit();

            Log::info('Chapter deleted successfully', ['id' => $chapter->id]);

            return $deleted;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting Chapter', [
                'id' => $chapter->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Search chapters based on criteria
     *
     * @param array $criteria
     * @return LengthAwarePaginator
     */
    public function search(array $criteria): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        // Add search logic based on your model's searchable fields
        // Example implementation:
        if (isset($criteria['search']) && !empty($criteria['search'])) {
            $searchTerm = $criteria['search'];
            $query->where(function ($q) use ($searchTerm) {
                // Add searchable columns here
                // $q->where('name', 'LIKE', "%{$searchTerm}%")
                //   ->orWhere('email', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Add date range filtering
        if (isset($criteria['start_date']) && !empty($criteria['start_date'])) {
            $query->whereDate('created_at', '>=', $criteria['start_date']);
        }

        if (isset($criteria['end_date']) && !empty($criteria['end_date'])) {
            $query->whereDate('created_at', '<=', $criteria['end_date']);
        }

        // Add sorting
        $sortBy = $criteria['sort_by'] ?? 'created_at';
        $sortOrder = $criteria['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $criteria['per_page'] ?? 15;
        return $query->paginate($perPage);
    }

    /**
     * Bulk delete chapters
     *
     * @param array $ids
     * @return int
     * @throws \Exception
     */
    public function bulkDelete(array $ids): int
    {
        try {
            DB::beginTransaction();

            $deleted = $this->model->whereIn('id', $ids)->delete();

            DB::commit();

            Log::info('Bulk delete chapters completed', [
                'ids' => $ids,
                'deleted_count' => $deleted
            ]);

            return $deleted;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in bulk delete chapters', [
                'ids' => $ids,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get chapters by specific field
     *
     * @param string $field
     * @param mixed $value
     * @return Collection
     */
    public function getByField(string $field, $value): Collection
    {
        return $this->model->where($field, $value)->get();
    }

    /**
     * Count total chapters
     *
     * @return int
     */
    public function count(): int
    {
        return $this->model->count();
    }

    /**
     * Check if chapter exists
     *
     * @param int $id
     * @return bool
     */
    public function exists(int $id): bool
    {
        return $this->model->where('id', $id)->exists();
    }

    /**
     * Get latest chapters
     *
     * @param int $limit
     * @return Collection
     */
    public function getLatest(int $limit = 10): Collection
    {
        return $this->model->latest()->limit($limit)->get();
    }

    /**
     * Duplicate a chapter
     *
     * @param Chapter $chapter
     * @return Chapter
     * @throws \Exception
     */
    public function duplicate(Chapter $chapter): Chapter
    {
        try {
            DB::beginTransaction();

            $data = $chapter->toArray();
            unset($data['id'], $data['created_at'], $data['updated_at']);

            $newChapter = $this->model->create($data);

            DB::commit();

            Log::info('Chapter duplicated successfully', [
                'original_id' => $chapter->id,
                'new_id' => $newChapter->id
            ]);

            return $newChapter;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error duplicating Chapter', [
                'id' => $chapter->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
