<?php

namespace App\Http\Controllers;

use App\Models\ShippingAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AddressController extends Controller
{
    /**
     * Display the addresses page.
     */
    public function index()
    {
        $user = Auth::user();
        $addresses = $user->shippingAddresses()->latest()->get();
        
        return view('pages.account.address', compact('addresses'));
    }
    
    /**
     * Store a new shipping address.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'address' => ['required', 'string'],
            'city' => ['required', 'string', 'max:100'],
            'province' => ['required', 'string', 'max:100'],
            'postal_code' => ['required', 'string', 'max:10'],
            'is_default' => ['sometimes', 'boolean'],
        ]);
        
        $user = Auth::user();
        
        // If this is set as default, unset other defaults
        if ($request->has('is_default') && $request->is_default) {
            $user->shippingAddresses()->update(['is_default' => false]);
        }
        
        $address = $user->shippingAddresses()->create($validated);
        
        return redirect()->route('account.address.index')
            ->with('status', 'Alamat berhasil ditambahkan');
    }
    
    /**
     * Update a shipping address.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'address' => ['required', 'string'],
            'city' => ['required', 'string', 'max:100'],
            'province' => ['required', 'string', 'max:100'],
            'postal_code' => ['required', 'string', 'max:10'],
            'is_default' => ['sometimes', 'boolean'],
        ]);
        
        $user = Auth::user();
        $address = $user->shippingAddresses()->findOrFail($id);
        
        // If this is set as default, unset other defaults
        if ($request->has('is_default') && $request->is_default) {
            $user->shippingAddresses()->where('id', '!=', $id)->update(['is_default' => false]);
        }
        
        $address->update($validated);
        
        return redirect()->route('account.address.index')
            ->with('status', 'Alamat berhasil diperbarui');
    }
    
    /**
     * Delete a shipping address.
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $address = $user->shippingAddresses()->findOrFail($id);
        
        $address->delete();
        
        return redirect()->route('account.address.index')
            ->with('status', 'Alamat berhasil dihapus');
    }
    
    /**
     * Set an address as default.
     */
    public function setDefault($id)
    {
        $user = Auth::user();
        $address = $user->shippingAddresses()->findOrFail($id);
        
        // Unset all defaults
        $user->shippingAddresses()->update(['is_default' => false]);
        
        // Set this as default
        $address->update(['is_default' => true]);
        
        return redirect()->route('account.address.index')
            ->with('status', 'Alamat default berhasil diubah');
    }
}

