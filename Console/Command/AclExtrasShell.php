<?php
/**
 * Acl Extras Shell.
 *
 * Enhances the existing Acl Shell with a few handy functions
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2008-2013, Mark Story.
 * @link http://mark-story.com
 * @author Mark Story <mark@mark-story.com>
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 */

App::uses('AppShell', 'Console/Command');
App::uses('AclExtras', 'AclExtras.Lib');

/**
 * Shell for ACO extras
 */
class AclExtrasShell extends AppShell
{
    /**
     * AclExtras instance
     *
     * @var AclExtras
     */
    public $AclExtras;

    /**
     * Constructor
     *
     * @param ConsoleOutput|null $stdout A ConsoleOutput object for stdout.
     * @param ConsoleOutput|null $stderr A ConsoleOutput object for stderr.
     * @param ConsoleInput|null $stdin A ConsoleInput object for stdin.
     */
    public function __construct($stdout = null, $stderr = null, $stdin = null)
    {
        parent::__construct($stdout, $stderr, $stdin);
        $this->AclExtras = new AclExtras();
    }

    /**
     * Start up And load Acl Component / Aco model
     *
     * @return void
     */
    public function startup()
    {
        parent::startup();
        $this->AclExtras->startup();
        $this->AclExtras->Shell = $this;
    }

    /**
     * Sync the ACO table
     *
     * @return bool
     */
    public function aco_sync()
    {
        return $this->AclExtras->aco_sync($this->params);
    }

    /**
     * Updates the Aco Tree with new controller actions.
     *
     * @return bool
     */
    public function aco_update()
    {
        return $this->AclExtras->aco_update($this->params);
    }

    /**
     * Gets the option parser instance and configures it.
     *
     * @return ConsoleOptionParser
     */
    public function getOptionParser()
    {
        $plugin = [
            'short' => 'p',
            'help' => __('Plugin to process'),
        ];

        return parent::getOptionParser()
            ->description(__("Better manage, and easily synchronize your application's ACO tree"))
            ->addSubcommand('aco_update', [
                'parser' => [
                    'options' => compact('plugin'),
                ],
                'help' => __('Add new ACOs for new controllers and actions. Does not remove nodes from the ACO table.'),
            ])
            ->addSubcommand('aco_sync', [
                'parser' => [
                    'options' => compact('plugin'),
                ],
                'help' => __('Perform a full sync on the ACO table. ' .
                    'Will create new ACOs or missing controllers and actions. ' .
                    'Will also remove orphaned entries that no longer have a matching controller/action'),
            ])
            ->addSubcommand('verify', [
                'help' => __('Verify the tree structure of either your Aco or Aro Trees'),
                'parser' => [
                    'arguments' => [
                        'type' => [
                            'required' => true,
                            'help' => __('The type of tree to verify'),
                            'choices' => ['aco', 'aro'],
                        ],
                    ],
                ],
            ])
            ->addSubcommand('recover', [
                'help' => __('Recover a corrupted Tree'),
                'parser' => [
                    'arguments' => [
                        'type' => [
                            'required' => true,
                            'help' => __('The type of tree to recover'),
                            'choices' => ['aco', 'aro'],
                        ],
                    ],
                ],
            ]);
    }

    /**
     * Verify a Acl Tree
     *
     * @return bool
     */
    public function verify()
    {
        $this->AclExtras->args = $this->args;

        return $this->AclExtras->verify();
    }

    /**
     * Recover an Acl Tree
     *
     * @return bool
     */
    public function recover()
    {
        $this->AclExtras->args = $this->args;

        return $this->AclExtras->recover();
    }
}
