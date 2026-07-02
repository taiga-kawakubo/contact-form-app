<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexContactRequest;
use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdminController extends Controller
{
    /**
     * 管理画面一覧を表示・検索する
     */
    public function index(IndexContactRequest $request): View
    {
        $validated = $request->validated();

        $query = Contact::with('category', 'tags');

        // 名前・メール検索
        if (! empty($validated['keyword'])) {
            $keyword = $validated['keyword'];

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

        // 性別
        if (isset($validated['gender']) && (int) $validated['gender'] !== 0) {
            $query->where('gender', $validated['gender']);
        }

        // 日付
        if (! empty($validated['date'])) {
            $query->whereDate('created_at', $validated['date']);
        }

        // カテゴリー
        if (! empty($validated['category_id'])) {
            $query->where('category_id', $validated['category_id']);
        }

        // ページネーション
        $contacts = $query
            ->paginate(7);

        $categories = Category::orderBy('id')->get();
        $tags = Tag::orderBy('id')->get();

        return view(
            'admin.index',
            compact('contacts', 'categories', 'tags')
        );
    }

    /**
     * お問い合わせ詳細を表示する
     */
    public function show(Contact $contact): View
    {
        $contact->load('category', 'tags');

        return view('admin.show', compact('contact'));
    }

    /**
     * お問い合わせを削除する
     */
    public function destroy(Contact $contact): RedirectResponse
    {
        $contact->delete();

        return redirect()->route('admin.index');
    }
}
