<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ThemeController extends Controller
{
    /**
     * Update the authenticated user's theme preference.
     */
    public function update(Request $request)
    {
        $request->validate([
            'theme' => 'required|string|in:light,dark,glass',
        ]);

        $user = Auth::user();
        $user->theme = $request->theme;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => __('Theme updated successfully.'),
            'theme' => $user->theme,
        ]);
    }
}
