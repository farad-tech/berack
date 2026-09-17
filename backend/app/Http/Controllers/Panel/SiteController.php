<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Services\JourneyReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SiteController extends Controller
{
    private function owned(Request $request, string $site): Site
    {
        return $request->user()->sites()->findOrFail($site);
    }

    public function dashboard(Request $request)
    {
        $site = $request->user()->sites()->orderBy('id')->first();

        return $site ? redirect()->route('panel.sites.reports', $site) : redirect()->route('panel.sites.create');
    }

    public function index(Request $request)
    {
        return view('panel.sites', ['sites' => $request->user()->sites()->withCount('trackerEvents')->orderBy('name')->paginate(12)]);
    }

    public function create()
    {
        return view('panel.site-form', ['site' => null]);
    }

    public function installation(Request $request)
    {
        $sites = $request->user()->sites();
        $count = (clone $sites)->count();

        if ($count === 0) {
            return redirect()->route('panel.sites.create');
        }

        if ($count === 1) {
            return redirect()->route('panel.sites.show', $sites->first());
        }

        return view('panel.sites', [
            'sites' => $sites->withCount('trackerEvents')->orderBy('name')->paginate(12),
            'installation' => true,
        ]);
    }

    public function edit(Request $request, string $site)
    {
        return view('panel.site-form', ['site' => $this->owned($request, $site)]);
    }

    private function data(Request $request): array
    {
        $domain = trim((string) $request->input('domain'));
        $host = parse_url(str_contains($domain, '://') ? $domain : 'https://'.$domain, PHP_URL_HOST);
        $request->merge(['domain' => strtolower((string) $host)]);

        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'domain' => ['required', 'string', 'max:253', 'regex:/^(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?$/i'],
        ], ['domain.regex' => __('panel.invalid_domain')]);
    }

    public function store(Request $request)
    {
        $site = $request->user()->sites()->create($this->data($request));

        return redirect()->route('panel.sites.show', $site)->with('status', __('panel.site_created'));
    }

    public function update(Request $request, string $site)
    {
        $this->owned($request, $site)->update($this->data($request));

        return back()->with('status', __('panel.saved'));
    }

    public function destroy(Request $request, string $site)
    {
        $site = $this->owned($request, $site);
        DB::transaction(function () use ($site) {
            $site->trackerEvents()->delete();
            $site->delete();
        });

        return redirect()->route('panel.sites.index')->with('status', __('panel.site_deleted'));
    }

    public function reports(Request $request, string $site)
    {
        $site = $this->owned($request, $site);
        $filters = $request->validate([
            'range' => 'nullable|in:1,7,30,all', 'status' => 'nullable|in:all,ended,pending',
            'q' => 'nullable|string|max:100', 'journeyId' => 'nullable|integer|min:1',
            'view' => 'nullable|in:journeys,last-pages', 'page' => 'nullable|integer|min:1',
            'stepsPage' => 'nullable|integer|min:1',
        ]);
        $report = new JourneyReport($site);
        $journeys = $report->filteredJourneys($filters)->paginate(12)->withQueryString();
        $selectedId = $request->integer('journeyId') ?: $journeys->first()?->id;
        $selected = $selectedId ? $report->journey($selectedId) : null;
        $first = $selected ? (clone $selected)->orderBy('sequence')->orderBy('id')->first() : null;
        $last = $selected ? (clone $selected)->orderByDesc('sequence')->orderByDesc('id')->first() : null;
        $steps = $selected ? $report->orderedSteps($selected)->paginate(50, ['*'], 'stepsPage')->withQueryString() : null;
        $previousSequence = $steps?->first() ? (int) (clone $selected)->where('sequence', '<', $steps->first()->sequence)->max('sequence') : 0;
        $stats = $report->overview($filters);
        $lastPages = $report->lastPagesForFilters($filters);

        return view('panel.journeys', compact('site', 'journeys', 'selectedId', 'first', 'last', 'steps', 'previousSequence', 'stats', 'lastPages'));
    }
}
