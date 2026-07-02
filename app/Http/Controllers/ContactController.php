<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExportContactRequest;
use App\Http\Requests\StoreContactRequest;
use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ContactController extends Controller
{
    /**
     * 問い合わせフォームの表示
     */
    public function index(): View
    {
        $categories = Category::orderBy('id')->get();

        $tags = Tag::orderBy('id')->get();

        return view('contact.index', compact('tags', 'categories'));
    }

    /**
     * 問い合わせ内容の確認
     */
    public function confirm(StoreContactRequest $request): View
    {
        $validated = $request->validated();

        $category = Category::findOrFail($validated['category_id']);

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
     * 問い合わせ内容の修正
     */
    public function back(Request $request)
    {
        return redirect()
            ->route('contacts.index')
            ->withInput($request->all());
    }

    /**
     * お問い合わせ内容の保存
     */
    public function store(StoreContactRequest $request): RedirectResponse
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

        return redirect()->route('contacts.thanks');
    }

    /**
     * サンクスページ表示
     */
    public function thanks(): View
    {
        return view('contact.thanks');
    }

    /**
     * エクスポート
     */
    public function export(ExportContactRequest $request): StreamedResponse
    {
        $validated = $request->validated();

        $query = Contact::query()
            ->with('category');

        if (! empty($validated['keyword'])) {
            $keyword = $validated['keyword'];

            $query->where(function ($query) use ($keyword) {
                $query->where('first_name', 'like', '%'.$keyword.'%')
                    ->orWhere('last_name', 'like', '%'.$keyword.'%')
                    ->orWhere('email', 'like', '%'.$keyword.'%')
                    ->orWhereRaw(
                        'CONCAT(first_name, last_name) LIKE ?',
                        ['%'.$keyword.'%']
                    )
                    ->orWhereRaw(
                        "CONCAT(first_name, ' ', last_name) LIKE ?",
                        ['%'.$keyword.'%']
                    );
            });
        }

        if (isset($validated['gender']) && (int) $validated['gender'] !== 0) {
            $query->where('gender', $validated['gender']);
        }

        if (! empty($validated['category_id'])) {
            $query->where('category_id', $validated['category_id']);
        }

        if (! empty($validated['date'])) {
            $query->whereDate('created_at', $validated['date']);
        }

        $contacts = $query
            ->orderByDesc('created_at')
            ->get();

        $filename = 'contacts_'.now()->format('Ymd_His').'.csv';

        return response()->streamDownload(function () use ($contacts) {
            $stream = fopen('php://output', 'w');

            if ($stream === false) {
                return;
            }

            fwrite($stream, "\xEF\xBB\xBF");

            fputcsv($stream, [
                'ID',
                '氏名',
                '性別',
                'メール',
                '電話',
                '住所',
                '建物',
                'カテゴリ',
                '内容',
                '作成日時',
            ]);

            foreach ($contacts as $contact) {
                fputcsv($stream, [
                    $contact->id,
                    "{$contact->first_name} {$contact->last_name}",
                    $contact->gender_label,
                    $contact->email,
                    "=\"{$contact->tel}\"",
                    $contact->address,
                    $contact->building,
                    $contact->category?->content,
                    $contact->detail,
                    $contact->created_at?->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($stream);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
