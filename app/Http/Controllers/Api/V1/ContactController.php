<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\IndexContactRequest;
use App\Http\Requests\Api\V1\StoreContactRequest;
use App\Http\Requests\Api\V1\UpdateContactRequest;
use App\Http\Resources\ContactResource;
use App\Models\Contact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ContactController extends Controller
{
    /**
     * お問い合わせ一覧取得
     */
    public function index(IndexContactRequest $request): AnonymousResourceCollection
    {
        $query = Contact::with(['category', 'tags']);

        // 姓・名・メールの検索
        if ($request->filled('keyword')) {
            $keyword = $request->input('keyword');
            $query->where(function ($query) use ($keyword) {
                $query->where('first_name', 'like', '%'.$keyword.'%')
                    ->orWhere('last_name', 'like', '%'.$keyword.'%')
                    ->orWhere('email', 'like', '%'.$keyword.'%')
                    ->orWhereRaw(
                        'CONCAT(first_name, last_name) LIKE ?',
                        ["%{$keyword}%"]
                    )
                    ->orWhereRaw(
                        "CONCAT(first_name, ' ', last_name) LIKE ?",
                        ["%{$keyword}%"]
                    );
            });
        }
        // 性別フィルタ
        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }
        // 作成日フィルタ
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        // カテゴリIDフィルタ
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // ページネーション
        $perPage = $request->input('per_page', 20);
        $contacts = $query->latest()->paginate($perPage)->withQueryString();

        return ContactResource::collection($contacts);
    }

    /**
     * お問い合わせ登録
     */
    public function store(StoreContactRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $tagIds = $validated['tag_ids'] ?? [];
        unset($validated['tag_ids']);

        $contact = Contact::create($validated);

        if (! empty($tagIds)) {
            $contact->tags()->attach($tagIds);
        }

        return (new ContactResource(
            $contact->load(['category', 'tags'])
        ))->response()->setStatusCode(201);
    }

    /**
     * お問い合わせ詳細取得
     */
    public function show(Contact $contact): ContactResource
    {
        return new ContactResource($contact->load(['category', 'tags']));
    }

    /**
     * お問い合わせ更新
     */
    public function update(UpdateContactRequest $request, Contact $contact): ContactResource
    {
        $validated = $request->validated();
        $tagIds = $validated['tag_ids'] ?? [];
        unset($validated['tag_ids']);

        $contact->update($validated);
        $contact->tags()->sync($tagIds);

        return new ContactResource(
            $contact->load(['category', 'tags'])
        );
    }

    /**
     * お問い合わせ削除
     */
    public function destroy(Contact $contact): JsonResponse
    {
        $contact->delete();

        return response()->json(null, 204);
    }
}
