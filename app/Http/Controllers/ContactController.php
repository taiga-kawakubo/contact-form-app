<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;

class ContactController extends Controller
{
    /**
     * 問い合わせフォームの表示
     */
    public function index()
    {
        $categories = Category::orderBy('id')->get();

        $tags = Tag::orderBy('id')->get();

        return view('contact.index', compact('tags', 'categories'));
    }

    /**
     * 問い合わせ内容の確認
     */
    public function confirm(ContactRequest $request)
    {
        $validated = $request->validated();

        $category = Category::find($validated['category_id']);

        $tags = collect();
        if (! empty($validated['tag_ids'])) {
            $tags = Tag::whereIn(
                'id',
                $validated['tag_ids']
            )->get();
        }

        return view(
            'contact.confirm', compact('category', 'tags', 'validated')
        );
    }

    /**
     * お問い合わせ内容の保存
     */
    public function store(ContactRequest $request)
    {
        $validated = $request->validated();
        $contactData =
        [
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'gender' => $validated['gender'],
            'email' => $validated['email'],
            'tel' => $validated['tel'],
            'address' => $validated['address'],
            'building' => $validated['building'] ?? null,
            'category_id' => $validated['category_id'],
            'detail' => $validated['detail'],
        ];

        $contact = Contact::create($contactData);
        $contact->tags()->sync(
            $validated['tag_ids'] ?? []
        );

        return redirect()->route('contact.thanks');
    }

    /**
     * サンクスページ表示
     */
    public function thanks()
    {
        return view('contact.thanks');
    }
}
