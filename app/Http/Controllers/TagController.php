<?php

namespace App\Http\Controllers;

use App\Http\Requests\TagStoreRequest;
use App\Http\Requests\TagUpdateRequest;
use App\Models\Tag;

class TagController extends Controller
{
    /**
     * 新規タグの保存
     */
    public function store(TagStoreRequest $request)
    {
        Tag::create($request->validated());

        return redirect()->route('admin.index');
    }

    /**
     * タグ編集画面への遷移
     */
    public function edit(Tag $tag)
    {
        return view(
            'admin.tags.edit', compact('tag')
        );
    }

    /**
     * タグの更新
     */
    public function update(TagUpdateRequest $request, Tag $tag)
    {
        $tag->update($request->validated());

        return redirect()->route('admin.index');
    }

    /**
     * タグの削除
     */
    public function destroy(Tag $tag)
    {
        $tag->delete();

        return redirect()->route('admin.index');
    }
}
