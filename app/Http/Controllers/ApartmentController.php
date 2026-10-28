<?php

namespace App\Http\Controllers;

use App\Models\Apartment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ApartmentController extends Controller
{
    public function index()
    {
        $apartments = Apartment::latest()->paginate(10);

        $unitStats = Cache::remember('apartments:unit-stats', 60, fn () => Apartment::selectRaw("count(*) as total, sum(case when status='available' then 1 else 0 end) as avail, sum(case when status='occupied' then 1 else 0 end) as occ, sum(case when status='maintenance' then 1 else 0 end) as maint")->first());

        return view('apartments.index', compact('apartments', 'unitStats'));
    }

    public function create()
    {
        return view('apartments.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'unit_number' => 'required|string|max:50|unique:apartments,unit_number',
            'building_name' => 'nullable|string|max:255',
            'property_address' => 'nullable|string|max:2000',
            'floor' => 'nullable|string|max:50',
            'unit_type' => 'required|string|max:100',
            'monthly_rent' => 'required|numeric|min:0',
            'security_deposit' => 'nullable|numeric|min:0',
            'bedrooms' => 'required|integer|min:0',
            'bathrooms' => 'required|integer|min:0',
            'status' => 'required|in:available,occupied,reserved,maintenance',
            'description' => 'nullable|string',
        ]);

        Apartment::create($validated);

        return redirect()
            ->route('apartments.index')
            ->with('success', 'Apartment added successfully.');
    }

    public function show(Apartment $apartment)
    {
        return view('apartments.show', compact('apartment'));
    }

    public function edit(Apartment $apartment)
    {
        return view('apartments.edit', compact('apartment'));
    }

    public function update(Request $request, Apartment $apartment)
    {
        $validated = $request->validate([
            'unit_number' => 'required|string|max:50|unique:apartments,unit_number,'.$apartment->id,
            'building_name' => 'nullable|string|max:255',
            'property_address' => 'nullable|string|max:2000',
            'floor' => 'nullable|string|max:50',
            'unit_type' => 'required|string|max:100',
            'monthly_rent' => 'required|numeric|min:0',
            'security_deposit' => 'nullable|numeric|min:0',
            'bedrooms' => 'required|integer|min:0',
            'bathrooms' => 'required|integer|min:0',
            'status' => 'required|in:available,occupied,reserved,maintenance',
            'description' => 'nullable|string',
        ]);

        $apartment->update($validated);

        return redirect()
            ->route('apartments.index')
            ->with('success', 'Apartment updated successfully.');
    }

    public function destroy(Apartment $apartment)
    {
        $apartment->archive(auth()->id());

        return redirect()
            ->route('apartments.index')
            ->with('success', 'Apartment archived. You can restore it from Archive.');
    }
}
