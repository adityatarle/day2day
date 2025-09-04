<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\City;
use App\Models\Role;
use Illuminate\Http\Request;

class OutletWebController extends Controller
{
    /**
     * Display a listing of outlets.
     */
    public function index()
    {
        $outlets = Branch::with(['city', 'users'])->active()->get();
        $cities = City::active()->get();
        
        return view('outlets.index', compact('outlets', 'cities'));
    }

    /**
     * Show the form for creating a new outlet.
     */
    public function create()
    {
        $cities = City::active()->get();
        return view('outlets.create', compact('cities'));
    }

    /**
     * Store a newly created outlet.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:branches,code',
            'address' => 'required|string',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|unique:branches,email',
            'city_id' => 'required|exists:cities,id',
            'outlet_type' => 'required|in:retail,wholesale,kiosk',
            'operating_hours' => 'nullable|array',
            'pos_enabled' => 'boolean',
        ]);

        $outlet = Branch::create($request->all());

        return redirect()->route('outlets.index')
            ->with('success', 'Outlet created successfully');
    }

    /**
     * Display the specified outlet.
     */
    public function show($id)
    {
        $outlet = Branch::with(['city', 'users.role', 'posSessions' => function($q) {
            $q->latest()->limit(20);
        }])->findOrFail($id);

        return view('outlets.show', compact('outlet'));
    }

    /**
     * Show the form for editing the specified outlet.
     */
    public function edit($id)
    {
        $outlet = Branch::findOrFail($id);
        $cities = City::active()->get();
        
        return view('outlets.edit', compact('outlet', 'cities'));
    }

    /**
     * Update the specified outlet.
     */
    public function update(Request $request, $id)
    {
        $outlet = Branch::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:branches,code,' . $outlet->id,
            'address' => 'required|string',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|unique:branches,email,' . $outlet->id,
            'city_id' => 'required|exists:cities,id',
            'outlet_type' => 'required|in:retail,wholesale,kiosk',
            'operating_hours' => 'nullable|array',
            'pos_enabled' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $outlet->update($request->all());

        return redirect()->route('outlets.show', $outlet)
            ->with('success', 'Outlet updated successfully');
    }

    /**
     * Remove the specified outlet.
     */
    public function destroy($id)
    {
        $outlet = Branch::findOrFail($id);
        
        if ($outlet->posSessions()->active()->exists()) {
            return back()->withErrors(['error' => 'Cannot delete outlet with active POS sessions']);
        }

        $outlet->delete();

        return redirect()->route('outlets.index')
            ->with('success', 'Outlet deleted successfully');
    }

    /**
     * Show outlet staff management.
     */
    public function manageStaff($id)
    {
        $outlet = Branch::with(['users.role'])->findOrFail($id);
        $roles = Role::all();
        
        return view('outlets.staff', compact('outlet', 'roles'));
    }
}
