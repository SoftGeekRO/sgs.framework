<?php

namespace SGS\View;

use SGS\Config\Config;
use Twig\Markup;

class TwigHelpers {

    // Define base paths for known asset types
    private static array $basePaths = [];
    private static string $baseUrl = '';
    private static bool $globalCacheBust = false;

    /**
     * Initialize base paths from configuration
     */
    public static function init(): void {
        if (empty(self::$basePaths)) {
            self::$basePaths = [
                'css' => Config::get('assets.cssBaseUrl', 'css/'),
                'js' => Config::get('assets.jsBaseUrl', 'js/'),
                'fonts' => Config::get('assets.fontsBaseUrl', 'fonts/'),
                'img' => Config::get('assets.imageBaseUrl', 'img/'),
                'default' => Config::get('assets.default', 'assets/'),
            ];
        }

        if (empty(self::$baseUrl)) {
            self::$baseUrl = rtrim(Config::get('App.baseUrl', ''), '/');
        }

        // Load global cache-busting setting
        self::$globalCacheBust = Config::get('debug', false);
    }

    /**
     * Resolves asset paths for both local files and external URLs
     * @param string $path Asset path or URL
     * @param bool|null $cacheBust Override global cache-busting (true/false/null for default)
     * @param bool $absolute Return full URL if true, relative path if false
     * @return string Resolved asset URL
     */
    public static function asset(string $path, ?bool $cacheBust = null, ?bool $absolute = null): string {
        self::init();

        // Determine whether to apply cache busting
        $applyCacheBust = $cacheBust ?? self::$globalCacheBust;

        // Check if the path is an external URL
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return self::appendTimestamp($path, $applyCacheBust);
        }

        $absolute = $absolute ?? Config::get('App.absolutePath', false);

        // Detect asset type from folder or file extension
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        foreach (self::$basePaths as $folder => $baseUrl) {
            if (str_starts_with($path, "$folder" . DS) || $extension === $folder) {
                //return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
                $assetPath = rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
                return self::appendTimestamp($absolute ? self::$baseUrl . $assetPath : $assetPath, $applyCacheBust);
            }
        }

        // Default fallback asset path
        $assetPath = rtrim(self::$basePaths['default'], '/') . '/' . ltrim($path, '/');
        return self::appendTimestamp($absolute ? self::$baseUrl . $assetPath : $assetPath, $applyCacheBust);
    }

    public static function uppercase($string): string {
        return strtoupper($string);
    }

    public static function lowercase($string): string {
        return strtolower($string);
    }

    public static function timestamp(): string {
        return date('Y-m-d H:i:s');
    }

    /**
     * Generates a script tag for JavaScript files or URLs
     */
    public static function script(string $src, ?bool $cacheBust = null, ?bool $absolute = null): string {
        $src = self::asset($src, $cacheBust, $absolute ?? false);
        return new Markup("<script src=\"$src\"></script>", 'UTF-8');
    }

    /**
     * Generates a link tag for CSS files or URLs
     */
    public static function style(string $href, ?bool $cacheBust = null, ?bool $absolute=null): string {
        $href = self::asset($href, $cacheBust, $absolute ?? false);
        return new Markup("<link rel=\"stylesheet\" href=\"$href\">", 'UTF-8');
    }

    /**
     * Generates a Google Fonts link or a local font file reference
     */
    public static function font(string $fontName, bool $cacheBust): string {
        // If it's a full URL, treat it as an external font
        if (filter_var($fontName, FILTER_VALIDATE_URL)) {
            return new Markup("<link rel=\"stylesheet\" href=\"$fontName\">", 'UTF-8');
        }

        // Otherwise, assume it's a Google Font
        $fontUrl = "https://fonts.googleapis.com/css2?family=" . urlencode($fontName);
        $fontUrl = self::appendTimestamp($fontUrl, $cacheBust);
        return new Markup("<link rel=\"stylesheet\" href=\"$fontUrl\">", 'UTF-8');
    }

    /**
     * Generates an <img> tag for images
     */
    public static function image(string $src, string $alt = '', string $attributes = ''): string {
        $src = self::asset($src);
        return new Markup("<img src=\"$src\" alt=\"$alt\" $attributes>", 'UTF-8');
    }

    /**
     * Appends a timestamp to the URL if cache busting is enabled
     */
    private static function appendTimestamp(string $url, bool $cacheBust): string {
        if ($cacheBust) {
            $timestamp = time();
            $url .= (str_contains($url, '?') ? '&' : '?') . "v=$timestamp";
        }
        return $url;
    }
}
