<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TeamMember;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class TeamMemberController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/team/Index', [
            'teamMembers' => TeamMember::query()->ordered()->get(['id', 'name', 'slug', 'role', 'specialty', 'featured', 'is_active', 'sort_order']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/team/Edit', ['teamMember' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        TeamMember::create($this->validateData($request));

        return redirect()->route('admin.team.index')->with('success', 'Team member created.');
    }

    public function edit(TeamMember $teamMember): Response
    {
        return Inertia::render('admin/team/Edit', ['teamMember' => $teamMember]);
    }

    public function update(Request $request, TeamMember $teamMember): RedirectResponse
    {
        $teamMember->update($this->validateData($request, $teamMember));

        return redirect()->route('admin.team.index')->with('success', 'Team member saved.');
    }

    public function destroy(TeamMember $teamMember): RedirectResponse
    {
        $teamMember->delete();

        return redirect()->route('admin.team.index')->with('success', 'Team member deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateData(Request $request, ?TeamMember $teamMember = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:120', 'alpha_dash', Rule::unique('team_members', 'slug')->ignore($teamMember?->id)],
            'role' => ['nullable', 'string', 'max:120'],
            'specialty' => ['nullable', 'string', 'max:120'],
            'bio' => ['nullable', 'string'],
            'photo' => ['nullable', 'string', 'max:255'],
            'years_experience' => ['nullable', 'integer', 'min:0'],
            'linkedin' => ['nullable', 'string', 'max:255'],
            'github' => ['nullable', 'string', 'max:255'],
            'twitter' => ['nullable', 'string', 'max:255'],
            'featured' => ['boolean'],
            'is_active' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $data['slug'] = ($data['slug'] ?? null) ?: Str::slug($data['name']);
        $data['featured'] = $request->boolean('featured');
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
