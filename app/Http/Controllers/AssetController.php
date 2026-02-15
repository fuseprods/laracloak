<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;

class AssetController extends Controller
{
    /**
     * Serve admin base CSS file (layout, reset, common components)
     */
    public function adminCss()
    {
        $path = resource_path('css/panel-base.css');

        if (!File::exists($path)) {
            abort(404);
        }

        return response(File::get($path), 200)
            ->header('Content-Type', 'text/css')
            ->header('Cache-Control', 'public, max-age=3600');
    }

    /**
     * Serve theme-specific CSS file
     */
    public function themeCss($theme)
    {
        $path = resource_path("css/themes/{$theme}.css");

        if (!File::exists($path)) {
            abort(404);
        }

        return response(File::get($path), 200)
            ->header('Content-Type', 'text/css')
            ->header('Cache-Control', 'public, max-age=3600');
    }

    /**
     * Serve panel JS file (protected by auth middleware in routes)
     */
    public function adminJs()
    {
        // For now, if panel.js doesn't exist, we'll try to use a common one or ignore
        $path = resource_path('js/panel.js');

        if (!File::exists($path)) {
            // Fallback to empty JS if not exist yet to avoid 404 in layout
            return response('', 200)->header('Content-Type', 'application/javascript');
        }

        return response(File::get($path), 200)
            ->header('Content-Type', 'application/javascript')
            ->header('Cache-Control', 'public, max-age=3600');
    }

    /**
     * Serve page builder CSS (private panel area).
     */
    public function pageBuilderCss()
    {
        $path = resource_path('css/panel/page-builder.css');

        if (!File::exists($path)) {
            abort(404);
        }

        return response(File::get($path), 200)
            ->header('Content-Type', 'text/css')
            ->header('Cache-Control', 'private, max-age=600');
    }

    /**
     * Serve page builder JS (private panel area).
     */
    public function pageBuilderJs()
    {
        $path = resource_path('js/panel/page-builder.js');

        if (!File::exists($path)) {
            abort(404);
        }

        return response(File::get($path), 200)
            ->header('Content-Type', 'application/javascript')
            ->header('Cache-Control', 'private, max-age=600');
    }
}
//TO-DO: Revisar si los assets son accesibles sin autenticación y si es necesario agregar middleware auth a las rutas correspondientes.
