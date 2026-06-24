<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Category;
use App\Models\Tag;
use App\Http\Requests\AdminSearchRequest;

class AdminController extends Controller
{
    /**
     * 管理画面一覧を表示＆検索
     */
    public function index(AdminSearchRequest $request)
    {
        $query = Contact::with('category','tags');

        // 名前・メール検索
        if ($request->filled('keyword')) {
            
            $query->where(function ($query) use ($request) {

                $query->where(
                        'first_name',
                        'like',
                        '%' . $request->keyword . '%'
                    )
                    ->orWhere(
                        'last_name',
                        'like',
                        '%' . $request->keyword . '%'
                    )
                    ->orWhere(
                        'email',
                        'like',
                        '%' . $request->keyword . '%'
                    )
                    ->orWhereRaw(
                        "CONCAT(first_name, last_name) LIKE ?",
                        ["%{$request->keyword}%"]
                    )
                    ->orWhereRaw(
                        "CONCAT(first_name, ' ', last_name) LIKE ?",
                        ["%{$request->keyword}%"]
                    );
            });
        }

        // 性別
        if (
            $request->filled('gender')
            && $request->gender !== '0'
        ) {
            $query->where(
                'gender',
                $request->gender
            );
        }

        // 日付
        if ($request->filled('date')) {
            $query->whereDate(
                'created_at',
                $request->date
            );
        }

        // カテゴリー
        if ($request->filled('category_id')) {
            $query->where(
                'category_id',
                $request->category_id
            );
        }

        $contacts = $query->paginate(7);

        $categories = Category::orderBy('id')->get();

        $tags = Tag::orderBy('id')->get();

        return view(
            'admin.index',
            compact('contacts', 'categories','tags')
        );
    }
    
    /**
     * お問い合わせ詳細の表示
     */
    public function show(string $id)
    {
        $contact = Contact::with('category','tags')->findOrFail($id);

        return view('admin.show',compact('contact'));
    }


    /**
     * お問い合わせの削除
     */
    public function destroy(string $id)
    {
        $contact = Contact::findOrFail($id);

        $contact -> delete();

        return redirect()->route('admin.index');
    }
}
