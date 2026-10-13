<?php
/**
 * All AclExtras plugin tests
 */
class AllTestsTest extends CakeTestCase
{
    /**
     * Suite define the tests for this suite
     *
     * @return CakeTestSuite
     */
    public static function suite(): CakeTestSuite
    {
        $suite = new CakeTestSuite('All AclExtras tests');

        $path = CakePlugin::path('AclExtras') . 'tests' . DS . 'TestCase' . DS;
        $suite->addTestDirectoryRecursive($path);

        return $suite;
    }
}
