<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTagRequest;
use App\Http\Requests\UpdateTagRequest;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TagController extends Controller
{
    /**
     * 新規タグの保存
     */
    public function store(StoreTagRequest $request): RedirectResponse
    {
        Tag::create($request->validated());

        return redirect()->route('admin.index');
    }

    /**
     * タグ編集画面への遷移
     */
    public function edit(Tag $tag): View
    {
        return view('admin.tags.edit', compact('tag'));
    }

    /**
     * タグの更新
     */
    public function update(UpdateTagRequest $request, Tag $tag): RedirectResponse
    {
        $tag->update($request->validated());

        return redirect()->route('admin.index');
    }

    /**
     * タグの削除
     */
    public function destroy(Tag $tag): RedirectResponse
    {
        $tag->delete();

        return redirect()->route('admin.index');
    }
}
