<?php
/**
 * AclExtrasShell test case
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 */

use PHPUnit\Framework\MockObject\MockObject;

App::uses('ConsoleOutput', 'Console');
App::uses('ConsoleInput', 'Console');
App::uses('AclExtrasShell', 'AclExtras.Console/Command');
App::uses('AclExtras', 'AclExtras.Lib');

/**
 * AclExtrasShell test case
 */
class AclExtrasShellTest extends CakeTestCase
{
    /**
     * Shell instance
     *
     * @var AclExtrasShell
     */
    public $Shell;

    /**
     * AclExtras mock
     *
     * @var AclExtras&MockObject
     */
    public $AclExtras;

    /**
     * setUp
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $out = $this->getMockBuilder(ConsoleOutput::class)->getMock();
        $in = $this->getMockBuilder(ConsoleInput::class)->getMock();

        $this->Shell = new AclExtrasShell($out, $out, $in);
        $this->AclExtras = $this->getMockBuilder(AclExtras::class)
            ->onlyMethods(['startup', 'aco_sync', 'aco_update', 'verify', 'recover'])
            ->getMock();
        $this->Shell->AclExtras = $this->AclExtras;
    }

    /**
     * tearDown
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->Shell, $this->AclExtras);
        parent::tearDown();
    }

    /**
     * test the constructor creates an AclExtras instance
     *
     * @return void
     */
    public function testConstruct(): void
    {
        $out = $this->getMockBuilder(ConsoleOutput::class)->getMock();
        $in = $this->getMockBuilder(ConsoleInput::class)->getMock();
        $Shell = new AclExtrasShell($out, $out, $in);

        $this->assertInstanceOf(AclExtras::class, $Shell->AclExtras);
    }

    /**
     * test startup
     *
     * @return void
     */
    public function testStartup(): void
    {
        $this->AclExtras->expects($this->once())->method('startup');

        $this->Shell->startup();
        $this->assertSame($this->Shell, $this->AclExtras->Shell);
    }

    /**
     * test aco_sync
     *
     * @return void
     */
    public function testAcoSync(): void
    {
        $this->Shell->params = ['plugin' => 'AclExtras'];
        $this->AclExtras->expects($this->once())
            ->method('aco_sync')
            ->with(['plugin' => 'AclExtras'])
            ->willReturn(true);

        $this->assertTrue($this->Shell->aco_sync());
    }

    /**
     * test aco_update
     *
     * @return void
     */
    public function testAcoUpdate(): void
    {
        $this->Shell->params = ['plugin' => 'AclExtras'];
        $this->AclExtras->expects($this->once())
            ->method('aco_update')
            ->with(['plugin' => 'AclExtras'])
            ->willReturn(true);

        $this->assertTrue($this->Shell->aco_update());
    }

    /**
     * test verify
     *
     * @return void
     */
    public function testVerify(): void
    {
        $this->Shell->args = ['aco'];
        $this->AclExtras->expects($this->once())
            ->method('verify')
            ->willReturn(false);

        $this->assertFalse($this->Shell->verify());
        $this->assertSame(['aco'], $this->AclExtras->args);
    }

    /**
     * test recover
     *
     * @return void
     */
    public function testRecover(): void
    {
        $this->Shell->args = ['aro'];
        $this->AclExtras->expects($this->once())
            ->method('recover')
            ->willReturn(false);

        $this->assertFalse($this->Shell->recover());
        $this->assertSame(['aro'], $this->AclExtras->args);
    }

    /**
     * test getOptionParser
     *
     * @return void
     */
    public function testGetOptionParser(): void
    {
        $parser = $this->Shell->getOptionParser();

        $this->assertInstanceOf(ConsoleOptionParser::class, $parser);
        $this->assertSame(
            ['aco_update', 'aco_sync', 'verify', 'recover'],
            array_keys($parser->subcommands())
        );
    }
}
