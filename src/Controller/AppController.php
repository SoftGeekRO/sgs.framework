<?php

namespace SGS\Controller;

use Exception;
use ReflectionClass;
use SGS\View\AppView;

class AppController {

    /**
     * Twig renderer instance.
     *
     * @var AppView
     */
    protected $view;

    /**
     * Variables to be passed to the view.
     *
     * @var array
     */
    protected $viewVars = [];

    protected $templatePath = null;

    protected $rendered = false; // Flag to track rendering

    public function __construct() {
        $this->view = new AppView();
    }

    /**
     * Set variables for the view.
     *
     * @param array|string $name
     * @param mixed|null $value
     * @return AppController
     * @throws Exception
     */
    protected function set(array|string $name, mixed $value = null): AppController {
        if (is_array($name)) {
            if (is_array($value)) {
                /** @var array|false $data Coerce phpstan to accept failure case */
                $data = array_combine($name, $value);
                if ($data === false) {
                    throw new Exception(
                        'Invalid data provided for array_combine() to work: Both $name and $value require same count.',
                    );
                }
            } else {
                $data = $name;
            }
        } else {
            $data = [$name => $value];
        }
        $this->viewVars = $data + $this->viewVars;

        return $this;
    }

    protected function setTemplatePath($path): void {
        $this->templatePath = $path;
    }

    /**
     * Render the view.
     *
     * @param string $templatePath
     * @param array $viewVars
     * @return string
     * @throws Exception
     */
    protected function render(string $templatePath, array $viewVars = []): string {
        if (!$this->rendered) {
            $this->rendered = true; // Mark as rendered
            return $this->view->render($templatePath, $viewVars);
        }
        return ''; // Skip rendering if already rendered
    }

    /**
     * Render a Twig template based on the controller and action names.
     *
     * @param string $controllerName
     * @param string $actionName
     * @param array $viewVars
     * @return string
     * @throws Exception If the template file is not found.
     */
    public function renderControllerAction(string $controllerName, string $actionName, array $viewVars = []): string {
        // Convert controller name to template path
        $controllerPath = str_replace('Controller', '', $controllerName);
        $controllerPath = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $controllerPath));

        // Build the template path
        $templatePath = "$controllerPath/$actionName.twig";

        return $this->render($templatePath, $viewVars);
    }


    /**
     * Automatically render the view after the action is executed.
     * @throws Exception
     */
    public function __destruct() {
        if (!empty($this->viewVars)) {

            if ($this->templatePath) {
                // Use custom template path if set
                echo $this->render($this->templatePath, $this->viewVars);
            } else {
                $controllerName = (new \ReflectionClass($this))->getShortName();
                $actionName = $this->getActionName();
                // Automatically resolve template based on controller and action
                echo $this->renderControllerAction($controllerName, $actionName, $this->viewVars);
            }
        }
    }

    /**
     * Get the short name of the class (without namespace).
     *
     * @return string
     * @throws Exception if the class cannot be reflected.
     */
    protected function getShortName(): string {
        try {
            $reflection = new ReflectionClass($this);
            return $reflection->getShortName();
        } catch (Exception $e) {
            throw new Exception("Failed to get short name of the class: " . $e->getMessage());
        }
    }

    /**
     * Get the file name of the class.
     *
     * @return string
     * @throws Exception if the class cannot be reflected or the file name is not available.
     */
    protected function getClassFileName(): string {
        try {
            $reflection = new ReflectionClass($this);
            $fileName = $reflection->getFileName();
            if ($fileName === false) {
                throw new Exception("Class file name could not be determined.");
            }
            return $fileName;
        } catch (Exception $e) {
            throw new Exception("Failed to get class file name: " . $e->getMessage());
        }
    }

    /**
     * Get the current action name.
     *
     * @return string
     */
    private function getActionName() {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        return $trace[1]['function'];
    }
}