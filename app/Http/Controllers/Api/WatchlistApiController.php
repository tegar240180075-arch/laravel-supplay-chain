<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Watchlist;
use App\Models\Country;
use Illuminate\Support\Facades\Auth;

class WatchlistApiController extends Controller
{
    public function index()
    {
        // For simplicity in this project, if auth is not strictly enforced via tokens,
        // we might just return all or a specific user's watchlist.
        $userId = Auth::id() ?? 1; // Fallback to user 1 for demo purposes
        
        $watchlist = Watchlist::with('country')->where('user_id', $userId)->get();
        return response()->json($watchlist);
    }

    public function store(Request $request)
    {
        $request->validate([
            'country_code' => 'required|string'
        ]);

        $country = Country::where('code', $request->country_code)->firstOrFail();
        $userId = Auth::id() ?? 1;

        $watchlist = Watchlist::firstOrCreate([
            'user_id' => $userId,
            'country_id' => $country->id
        ]);

        return response()->json(['success' => true, 'data' => $watchlist]);
    }

    public function destroy($id)
    {
        $watchlist = Watchlist::findOrFail($id);
        $userId = Auth::id() ?? 1;

        if ($watchlist->user_id == $userId) {
            $watchlist->delete();
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
    }
}
