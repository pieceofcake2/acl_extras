<?php
/**
 * Acl Extras Shell.
 *
 * Enhances the existing Acl Shell with a few handy functions
 *
 * Copyright 2008, Mark Story.
 * 694B The Queensway
 * toronto, ontario M8Y 1K9
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2008-2009, Mark Story.
 * @link http://mark-story.com
 * @author Mark Story <mark@mark-story.com>
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 */

use PHPUnit\Framework\MockObject\MockObject;

App::uses('Shell', 'Console');
App::uses('Aco', 'Model');
App::uses('AclComponent', 'Controller/Component');
App::uses('Controller', 'Controller');
App::uses('AclExtras', 'AclExtras.Lib');

/**
 * AclExtras test case
 */
class AclExtrasTest extends CakeTestCase
{
    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = ['core.aco', 'core.aro', 'core.aros_aco'];

    /**
     * AclExtras mock
     *
     * @var AclExtras&MockObject
     */
    public $Task;

    /**
     * setUp
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        Configure::write('Acl.classname', 'DbAcl');
        Configure::write('Acl.database', 'test');

        $this->Task = $this->getMockBuilder(AclExtras::class)
            ->onlyMethods(['out', 'err', 'getControllerList'])
            ->getMock();
    }

    /**
     * tearDown
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->Task);
        parent::tearDown();
    }

    /**
     * test recover
     *
     * @return void
     */
    public function testRecover(): void
    {
        $this->Task->startup();
        $this->Task->args = ['Aco'];
        $this->Task->Acl->Aco = $this->getMockBuilder(Aco::class)
            ->addMethods(['recover'])
            ->getMock();
        $this->Task->Acl->Aco->expects($this->once())
            ->method('recover')
            ->willReturn(true);

        $this->Task->expects($this->once())
            ->method('out')
            ->with($this->matchesRegularExpression('/recovered/'));

        $this->assertTrue($this->Task->recover());
    }

    /**
     * test recover failure
     *
     * @return void
     */
    public function testRecoverFailure(): void
    {
        $this->Task->startup();
        $this->Task->args = ['Aco'];
        $this->Task->Acl->Aco = $this->getMockBuilder(Aco::class)
            ->addMethods(['recover'])
            ->getMock();
        $this->Task->Acl->Aco->expects($this->once())
            ->method('recover')
            ->willReturn(false);

        $this->Task->expects($this->once())
            ->method('err')
            ->with($this->matchesRegularExpression('/recovery failed/'));

        $this->assertFalse($this->Task->recover());
    }

    /**
     * test verify
     *
     * @return void
     */
    public function testVerify(): void
    {
        $this->Task->startup();
        $this->Task->args = ['Aco'];
        $this->Task->Acl->Aco = $this->getMockBuilder(Aco::class)
            ->addMethods(['verify'])
            ->getMock();
        $this->Task->Acl->Aco->expects($this->once())
            ->method('verify')
            ->willReturn(true);

        $this->Task->expects($this->once())
            ->method('out')
            ->with($this->matchesRegularExpression('/valid/'));

        $this->assertTrue($this->Task->verify());
    }

    /**
     * test verify failure
     *
     * @return void
     */
    public function testVerifyFailure(): void
    {
        $this->Task->startup();
        $this->Task->args = ['Aco'];
        $this->Task->Acl->Aco = $this->getMockBuilder(Aco::class)
            ->addMethods(['verify'])
            ->getMock();
        $this->Task->Acl->Aco->expects($this->once())
            ->method('verify')
            ->willReturn([['node', 1, 'missing']]);

        $this->Task->expects($this->once())
            ->method('err')
            ->with($this->stringContains('missing'));

        $this->assertFalse($this->Task->verify());
    }

    /**
     * test startup
     *
     * @return void
     */
    public function testStartup(): void
    {
        $this->assertNull($this->Task->Acl);
        $this->Task->startup();
        $this->assertInstanceOf(AclComponent::class, $this->Task->Acl);
        $this->assertInstanceOf(Aco::class, $this->Task->Aco);
        $this->assertInstanceOf(Controller::class, $this->Task->controller);
    }

    /**
     * clean fixtures and setup mock
     *
     * @return void
     */
    protected function _cleanAndSetup(): void
    {
        $tableName = $this->db->fullTableName('acos');
        $this->db->execute('DELETE FROM ' . $tableName);
        $this->Task->expects($this->any())
            ->method('getControllerList')
            ->willReturnCallback(function ($plugin = null) {
                if ($plugin) {
                    return [];
                }

                return ['CommentsController', 'PostsController', 'BigLongNamesController'];
            });

        $this->Task->startup();
    }

    /**
     * Test aco_update method.
     *
     * @return void
     */
    public function testAcoUpdate(): void
    {
        $this->_cleanAndSetup();
        $this->assertTrue($this->Task->aco_update());

        $Aco = $this->Task->Acl->Aco;

        $result = $Aco->node('controllers/Comments');
        $this->assertSame('Comments', $result[0]['Aco']['alias']);

        $result = $Aco->children($result[0]['Aco']['id']);
        $this->assertCount(3, $result);
        $this->assertSame('add', $result[0]['Aco']['alias']);
        $this->assertSame('index', $result[1]['Aco']['alias']);
        $this->assertSame('delete', $result[2]['Aco']['alias']);

        $result = $Aco->node('controllers/Posts');
        $this->assertSame('Posts', $result[0]['Aco']['alias']);
        $result = $Aco->children($result[0]['Aco']['id']);
        $this->assertCount(3, $result);

        $result = $Aco->node('controllers/BigLongNames');
        $this->assertSame('BigLongNames', $result[0]['Aco']['alias']);
        $result = $Aco->children($result[0]['Aco']['id']);
        $this->assertCount(4, $result);

        $result = $Aco->node('controllers/AclExtras');
        $this->assertSame('AclExtras', $result[0]['Aco']['alias']);
    }

    /**
     * Test aco_update method with a plugin that is not loaded.
     *
     * @return void
     */
    public function testAcoUpdateMissingPlugin(): void
    {
        $this->_cleanAndSetup();
        $this->Task->expects($this->once())
            ->method('err')
            ->with($this->stringContains('Plugin NotLoaded not found or not activated'));

        $this->assertFalse($this->Task->aco_update(['plugin' => 'NotLoaded']));
    }

    /**
     * Test aco_update method with a plugin.
     *
     * @return void
     */
    public function testAcoUpdatePlugin(): void
    {
        $this->_cleanAndSetup();

        $this->assertTrue($this->Task->aco_update(['plugin' => 'AclExtras']));

        $Aco = $this->Task->Acl->Aco;
        $result = $Aco->node('controllers/AclExtras');
        $this->assertSame('AclExtras', $result[0]['Aco']['alias']);
        $this->assertFalse($Aco->node('controllers/Comments'));
    }

    /**
     * test syncing of Aco records
     *
     * @return void
     */
    public function testAcoSyncRemoveMethods(): void
    {
        $this->_cleanAndSetup();
        $this->Task->aco_update();

        $Aco = $this->Task->Acl->Aco;
        $Aco->cacheQueries = false;

        $result = $Aco->node('controllers/Comments');
        $new = [
            'parent_id' => $result[0]['Aco']['id'],
            'alias' => 'some_method',
        ];
        $Aco->create($new);
        $Aco->save();
        $children = $Aco->children($result[0]['Aco']['id']);
        $this->assertCount(4, $children);

        $this->Task->aco_sync();
        $children = $Aco->children($result[0]['Aco']['id']);
        $this->assertCount(3, $children);

        $method = $Aco->node('controllers/Comments/some_method');
        $this->assertFalse($method);
    }

    /**
     * test syncing removes controllers that no longer exist
     *
     * @return void
     */
    public function testAcoSyncRemoveControllers(): void
    {
        $this->_cleanAndSetup();
        $this->Task->aco_update();

        $Aco = $this->Task->Acl->Aco;
        $Aco->cacheQueries = false;

        $root = $Aco->node('controllers');
        $Aco->create(['parent_id' => $root[0]['Aco']['id'], 'alias' => 'Removed']);
        $Aco->save();
        $this->assertNotEmpty($Aco->node('controllers/Removed'));

        $this->Task->aco_sync();
        $this->assertFalse($Aco->node('controllers/Removed'));
        $this->assertNotEmpty($Aco->node('controllers/Comments'));
        $this->assertNotEmpty($Aco->node('controllers/AclExtras'));
    }

    /**
     * test adding methods with aco_update
     *
     * @return void
     */
    public function testAcoUpdateAddingMethods(): void
    {
        $this->_cleanAndSetup();
        $this->Task->aco_update();

        $Aco = $this->Task->Acl->Aco;
        $Aco->cacheQueries = false;

        $result = $Aco->node('controllers/Comments');
        $children = $Aco->children($result[0]['Aco']['id']);
        $this->assertCount(3, $children);

        $Aco->delete($children[0]['Aco']['id']);
        $Aco->delete($children[1]['Aco']['id']);
        $this->Task->aco_update();

        $children = $Aco->children($result[0]['Aco']['id']);
        $this->assertCount(3, $children);
    }

    /**
     * test adding controllers on sync
     *
     * @return void
     */
    public function testAddingControllers(): void
    {
        $this->_cleanAndSetup();
        $this->Task->aco_update();

        $Aco = $this->Task->Acl->Aco;
        $Aco->cacheQueries = false;

        $result = $Aco->node('controllers/Comments');
        $Aco->delete($result[0]['Aco']['id']);

        $this->Task->aco_update();
        $newResult = $Aco->node('controllers/Comments');
        $this->assertNotEquals($result[0]['Aco']['id'], $newResult[0]['Aco']['id']);
    }

    /**
     * test getControllerList
     *
     * @return void
     */
    public function testGetControllerList(): void
    {
        $AclExtras = new AclExtras();

        $this->assertIsArray($AclExtras->getControllerList());
        $this->assertSame([], $AclExtras->getControllerList('AclExtras'));
    }
}
