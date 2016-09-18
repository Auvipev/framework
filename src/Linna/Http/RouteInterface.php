<?php

/**
 * Linna Framework
 *
 * @author Sebastian Rapetti <sebastian.rapetti@alice.it>
 * @copyright (c) 2016, Sebastian Rapetti
 * @license http://opensource.org/licenses/MIT MIT License
 *
 */

namespace Linna\Http;

/**
 * Interface for routes.
 *
 */
interface RouteInterface
{
    /**
     * Contructor
     *
     * @param string $name
     * @param string $method
     * @param string $model
     * @param string $view
     * @param string $controller
     * @param mixed $action
     * @param array $param
     */
    public function __construct(string $name, string $method, string $model, string $view, string $controller, string $action, array $param);

    /**
     * Return model name
     *
     */
    public function getModel();
    
    /**
     * Return view name
     *
     */
    public function getView();
    
    /**
     * Return controller
     *
     */
    public function getController();

    /**
     * Return action name
     *
     */
    public function getAction();

    /**
     * Return parameters
     *
     */
    public function getParam();
}
