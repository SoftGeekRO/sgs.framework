<?php
namespace SGS\View;

use Exception;
use SGS\Config\Config;
use SGS\View\TwigHelpers;
use SGS\View\TwigGlobals;

use Twig\Environment;
use Twig\TwigFunction;
use Twig\Loader\ChainLoader;
use Twig\Loader\FilesystemLoader;
use Twig\Error\LoaderError;

class AppView {
    /**
     * Twig environment instance.
     *
     * @var Environment
     */
    protected static $twig;

    /**
     * Initialize the Twig environment.
     */
    public static function init(): void {

        $applicationTemplates = Config::get('App.paths.templates', []);
        $coreTemplates = [
            SRC . DS . 'View/templates'
        ];

        // Merge template paths (Application paths take priority)
        $templatePaths = array_merge($applicationTemplates, $coreTemplates);

        // Create Twig loaders for each path
        $loaders = [];
        foreach ($templatePaths as $path) {
            if (is_dir($path)) { // Ensure directory exists
                $loaders[] = new FilesystemLoader($path);
            }
        }

        // Create a ChainLoader to handle multiple directories
        $chainLoader = new ChainLoader($loaders);

        if (!self::$twig) {
            self::$twig = new Environment($chainLoader, [
                'cache' => Config::get('cache.views.enabled', false) ? Config::get('cache.views.path') : false,
                'auto_reload' => true,
                'autoescape' => false
            ]);

            // Get all public static methods of TwigGlobals
            $globalMethods = get_class_methods(TwigGlobals::class);
            // Register each method as a global variable in Twig
            foreach ($globalMethods as $method) {
                $globals = TwigGlobals::$method();
                // Ensure it's an array
                if (is_array($globals)) {
                    // Add each key-value pair to Twig as a separate global
                    foreach ($globals as $key => $value) {
                        self::$twig->addGlobal($key, $value);
                    }
                }
            }

            // Add custom Twig filters or functions if needed
            $helperMethods = get_class_methods(TwigHelpers::class);
            foreach ($helperMethods as $method) {
                self::$twig->addFunction(new TwigFunction($method, [TwigHelpers::class, $method]));
            }
        }
    }

    /**
     * Render a Twig template.
     *
     * @param string $templatePath
     * @param array $viewVars
     * @return string
     * @throws Exception If the template file is not found.
     */
    public function render(string $templatePath, array $viewVars = []): string {
        return $this->renderTemplate($templatePath, $viewVars);
    }

    /**
     * Render an error template.
     *
     * @param string $templatePath
     * @param array $data
     * @return string
     */
    public function renderError(string $templatePath, array $data = []): string {
        try {
            return $this->renderTemplate($templatePath, $data);
        } catch (\Exception $e) {
            // Fallback to a generic error message if the template is not found
            return "Error: " . $e->getMessage();
        }
    }


    /**
     * Render a Twig template using the full template path.
     *
     * @param string $templatePath
     * @param array $viewVars
     * @return string
     * @throws Exception If the template file is not found.
     */
    private function renderTemplate(string $templatePath, array $viewVars = []): string {
        self::init();
        try {
            return self::$twig->render($templatePath, $viewVars);
        } catch (LoaderError $e) {
            throw new Exception("Template not found: $templatePath");
        }
    }

    /**
     * Custom Twig function to generate asset URLs.
     *
     * @param string $path
     * @return string
     */
    public static function assetFunction(string $path): string {
        return '/webroot/' . ltrim($path, '/');
    }
}