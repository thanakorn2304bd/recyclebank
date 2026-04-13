<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveCommunityRequest;
use App\Models\Community;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdminCommunityController extends Controller
{
    public function index(): View
    {
        $communities = Community::query()
            ->withCount('households')
            ->orderBy('community_id')
            ->paginate(20);

        return view('admin.communities.index', compact('communities'));
    }

    public function create(): View
    {
        return view('admin.communities.create');
    }

    public function store(SaveCommunityRequest $request): RedirectResponse
    {
        $data = $request->validated();

        Community::create($data);

        ActivityLogger::forCurrentUser('communities', "เพิ่มชุมชน {$data['community_id']}: {$data['community_name']}", [
            'entity_type' => 'community',
            'entity_id' => $data['community_id'],
            'after' => $data,
        ]);

        return redirect()->route('admin.communities.index')
            ->with('success', "เพิ่มชุมชน {$data['community_name']} เรียบร้อย");
    }

    public function edit(Community $community): View
    {
        return view('admin.communities.edit', compact('community'));
    }

    public function update(SaveCommunityRequest $request, Community $community): RedirectResponse
    {
        $before = $community->only(['community_id', 'community_name']);
        $data = $request->validated();

        // community_id เป็น PK ไม่ให้แก้ไข
        $community->update(['community_name' => $data['community_name']]);

        ActivityLogger::forCurrentUser('communities', "แก้ไขชุมชน {$community->community_id}: {$community->community_name}", [
            'entity_type' => 'community',
            'entity_id' => $community->community_id,
            'before' => $before,
            'after' => $community->only(['community_id', 'community_name']),
        ]);

        return redirect()->route('admin.communities.index')
            ->with('success', "แก้ไขชุมชน {$community->community_name} เรียบร้อย");
    }

    public function destroy(Community $community): RedirectResponse
    {
        if ($community->households()->exists()) {
            return back()->withErrors('ลบไม่ได้: มีครัวเรือนอยู่ในชุมชนนี้');
        }

        $name = $community->community_name;
        $id = $community->community_id;

        $community->delete();

        ActivityLogger::forCurrentUser('communities', "ลบชุมชน {$id}: {$name}", [
            'entity_type' => 'community',
            'entity_id' => $id,
        ]);

        return redirect()->route('admin.communities.index')
            ->with('success', "ลบชุมชน {$name} เรียบร้อย");
    }
}
