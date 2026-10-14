<?php
/**
 * Acl Extras.
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2008-2013, Mark Story.
 * @link http://mark-story.com
 * @author Mark Story <mark@mark-story.com>
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 */

App::uses('Controller', 'Controller');
App::uses('ComponentCollection', 'Controller');
App::uses('AclComponent', 'Controller/Component');
App::uses('DbAcl', 'Model');
App::uses('Shell', 'Console');
App::uses('CakeObject', 'Core');
App::uses('ClassRegistry', 'Utility');

/**
 * Tools for synchronizing the ACO tree with the controllers and actions
 */
class AclExtras extends CakeObject
{
    /**
     * Contains instance of AclComponent
     *
     * @var AclComponent|null
     */
    public $Acl;

    /**
     * Contains arguments parsed from the command line.
     *
     * @var array
     */
    public $args = [];

    /**
     * Contains database source to use
     *
     * @var string
     */
    public $dataSource = 'default';

    /**
     * Root node name.
     *
     * @var string
     */
    public $rootNode = 'controllers';

    /**
     * Aco model instance
     *
     * @var Aco|null
     */
    public $Aco;

    /**
     * Controller instance used to start up AclComponent
     *
     * @var Controller|null
     */
    public $controller;

    /**
     * Shell instance used to output messages
     *
     * @var Shell|null
     */
    public $Shell;

    /**
     * Internal Clean Actions switch
     *
     * @var bool
     */
    protected $_clean = false;

    /**
     * Start up And load Acl Component / Aco model
     *
     * @param Controller|null $controller Controller instance. A new one is created when omitted.
     * @return void
     */
    public function startup($controller = null)
    {
        if (!$controller) {
            $controller = new Controller(new CakeRequest());
        }
        $collection = new ComponentCollection();
        $this->Acl = new AclComponent($collection);
        $this->Acl->startup($controller);
        // DbAcl shares the Aco model registered in ClassRegistry with AclComponent::$Aco.
        $this->Aco = ClassRegistry::init('Aco');
        $this->controller = $controller;
    }

    /**
     * Outputs a message to the shell, or sets it as a flash message when used in a controller
     *
     * @param string $msg Message to output.
     * @param int $newlines Number of newlines to append.
     * @param int $level The message's output level, see Shell::NORMAL etc.
     * @return int|bool|null Returns the number of bytes output to the shell.
     */
    public function out($msg, $newlines = 1, $level = Shell::NORMAL)
    {
        if (!empty($this->controller->Session)) {
            $this->controller->Flash->set($msg);
        } elseif ($this->Shell) {
            return $this->Shell->out($msg, $newlines, $level);
        }

        return null;
    }

    /**
     * Outputs an error message to the shell, or sets it as a flash message when used in a controller
     *
     * @param string $msg Message to output.
     * @param int $newlines Number of newlines to append.
     * @return void
     */
    public function err($msg, $newlines = 1)
    {
        if (!empty($this->controller->Session)) {
            $this->controller->Flash->set($msg);
        } elseif ($this->Shell) {
            $this->Shell->err($msg, $newlines);
        }
    }

    /**
     * Sync the ACO table
     *
     * @param array $params Parameters. Set `plugin` to sync only one plugin.
     * @return bool
     */
    public function aco_sync($params = []) // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    {
        $this->_clean = true;

        return $this->aco_update($params);
    }

    /**
     * Updates the Aco Tree with new controller actions.
     *
     * @param array $params Parameters. Set `plugin` to update only one plugin.
     * @return bool
     */
    public function aco_update($params = []) // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    {
        $root = $this->_checkNode($this->rootNode, $this->rootNode, null);

        if (empty($params['plugin'])) {
            $controllers = $this->getControllerList();
            $this->_updateControllers($root, $controllers);
            $plugins = CakePlugin::loaded();
        } else {
            $plugin = $params['plugin'];
            if (!CakePlugin::loaded($plugin)) {
                $this->err(__('<error>Plugin %s not found or not activated</error>', $plugin));

                return false;
            }
            $plugins = [$plugin];
        }

        foreach ($plugins as $plugin) {
            $controllers = $this->getControllerList($plugin);

            $path = $this->rootNode . '/' . $plugin;
            $pluginRoot = $this->_checkNode($path, $plugin, $root['Aco']['id']);
            $this->_updateControllers($pluginRoot, $controllers, $plugin);
        }
        $this->out(__('<success>Aco Update Complete</success>'));

        return true;
    }

    /**
     * Updates a collection of controllers.
     *
     * @param array $root Array or ACO information for root node.
     * @param array $controllers Array of Controllers
     * @param string|null $plugin Name of the plugin you are making controllers for.
     * @return void
     */
    protected function _updateControllers($root, $controllers, $plugin = null)
    {
        $dotPlugin = $pluginPath = (string)$plugin;
        if ($plugin) {
            $dotPlugin .= '.';
            $pluginPath .= '/';
        }
        $appIndex = array_search($plugin . 'AppController', $controllers);
        if ($appIndex !== false) {
            App::uses($plugin . 'AppController', $dotPlugin . 'Controller');
            unset($controllers[$appIndex]);
        }
        // look at each controller
        foreach ($controllers as $controller) {
            App::uses($controller, $dotPlugin . 'Controller');
            $controllerName = preg_replace('/Controller$/', '', $controller);

            $path = $this->rootNode . '/' . $pluginPath . $controllerName;
            $controllerNode = $this->_checkNode($path, $controllerName, $root['Aco']['id']);
            $this->_checkMethods($controller, $controllerName, $controllerNode, $pluginPath);
        }
        if (!$this->_clean) {
            return;
        }

        if (!$plugin) {
            $controllers = array_merge($controllers, App::objects('plugin', null, false), CakePlugin::loaded());
        }
        $controllerFlip = array_flip($controllers);

        $this->Aco->id = $root['Aco']['id'];
        $controllerNodes = $this->Aco->children(null, true);
        foreach ($controllerNodes as $ctrlNode) {
            $alias = $ctrlNode['Aco']['alias'];
            $name = $alias . 'Controller';
            if (isset($controllerFlip[$name]) || isset($controllerFlip[$alias])) {
                continue;
            }
            if ($this->Aco->delete($ctrlNode['Aco']['id'])) {
                $this->out(__(
                    'Deleted %s and all children',
                    $this->rootNode . '/' . $pluginPath . $alias,
                ), 1, Shell::VERBOSE);
            }
        }
    }

    /**
     * Get a list of controllers in the app and plugins.
     *
     * @param string|null $plugin Name of plugin to get controllers for
     * @return array List of controller class names
     */
    public function getControllerList($plugin = null)
    {
        if (!$plugin) {
            return App::objects('Controller', null, false);
        }

        return App::objects($plugin . '.Controller', null, false);
    }

    /**
     * Check a node for existence, create it if it doesn't exist.
     *
     * @param string $path Path of the node
     * @param string $alias Alias of the node
     * @param string|int|null $parentId Id of the parent node
     * @return array Aco Node array
     */
    protected function _checkNode($path, $alias, $parentId = null)
    {
        $node = $this->Aco->node($path);
        if ($node) {
            return $node[0];
        }

        $this->Aco->create(['parent_id' => $parentId, 'model' => null, 'alias' => $alias]);
        $node = (array)$this->Aco->save();
        $node['Aco']['id'] = $this->Aco->id;
        $this->out(__('Created Aco node: <success>%s</success>', $path), 1, Shell::VERBOSE);

        return $node;
    }

    /**
     * Get a list of registered callback methods
     *
     * @param string $className Controller class name
     * @return array List of callback method names
     */
    protected function _getCallbacks($className)
    {
        $callbacks = [];
        $reflection = new ReflectionClass($className);
        if ($reflection->isAbstract()) {
            return $callbacks;
        }
        try {
            $method = $reflection->getMethod('implementedEvents');
        } catch (ReflectionException $e) {
            return $callbacks;
        }
        $object = $reflection->newInstanceWithoutConstructor();
        $implementedEvents = $method->invoke($object);
        foreach ($implementedEvents as $callable) {
            if (is_string($callable)) {
                $callbacks[] = $callable;
            }
            if (is_array($callable) && isset($callable['callable'])) {
                $callbacks[] = $callable['callable'];
            }
        }

        return $callbacks;
    }

    /**
     * Check and Add/delete controller Methods
     *
     * @param string $className Controller class name
     * @param string $controllerName Controller name without the Controller suffix
     * @param array $node Aco node of the controller
     * @param string $pluginPath Plugin path prefix (e.g. `PluginName/`)
     * @return bool
     */
    protected function _checkMethods($className, $controllerName, $node, $pluginPath = '')
    {
        if (!class_exists($className)) {
            $this->err(__('Unable to get methods for "%s"', $className));

            return false;
        }
        $excludes = $this->_getCallbacks($className);
        $baseMethods = get_class_methods('Controller');
        $actions = get_class_methods($className);
        if (empty($actions)) {
            $this->err(__('Unable to get methods for "%s"', $className));

            return false;
        }
        $methods = array_diff($actions, $baseMethods);
        $methods = array_diff($methods, $excludes);
        foreach ($methods as $action) {
            if (str_starts_with($action, '_')) {
                continue;
            }
            $path = $this->rootNode . '/' . $pluginPath . $controllerName . '/' . $action;
            $this->_checkNode($path, $action, $node['Aco']['id']);
        }

        if (!$this->_clean) {
            return true;
        }

        $actionNodes = $this->Aco->children($node['Aco']['id']);
        $methodFlip = array_flip($methods);
        foreach ($actionNodes as $action) {
            if (isset($methodFlip[$action['Aco']['alias']])) {
                continue;
            }
            $this->Aco->id = $action['Aco']['id'];
            if ($this->Aco->delete()) {
                $path = $this->rootNode . '/' . $pluginPath . $controllerName . '/' . $action['Aco']['alias'];
                $this->out(__('Deleted Aco node: <warning>%s</warning>', $path), 1, Shell::VERBOSE);
            }
        }

        return true;
    }

    /**
     * Verify a Acl Tree. The type of tree (`aco` or `aro`) is read from the first argument.
     *
     * @return bool
     */
    public function verify()
    {
        $type = Inflector::camelize($this->args[0] ?? '');
        $return = $this->Acl->{$type}->verify();
        if ($return === true) {
            $this->out(__('<success>Tree is valid and strong</success>'));

            return true;
        }

        $this->err(print_r($return, true));

        return false;
    }

    /**
     * Recover an Acl Tree. The type of tree (`aco` or `aro`) is read from the first argument.
     *
     * @return bool
     */
    public function recover()
    {
        $type = Inflector::camelize($this->args[0] ?? '');
        $return = $this->Acl->{$type}->recover();
        if ($return === true) {
            $this->out(__('Tree has been recovered, or tree did not need recovery.'));

            return true;
        }

        $this->err(__('<error>Tree recovery failed.</error>'));

        return false;
    }
}
