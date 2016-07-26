<?php

/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 6/10/16
 * Time: 10:38 PM
 */
class ModuleManagerTest extends \PHPUnit_Framework_TestCase
{

    public function testSingleton()
    {
        $manager = new \App\Source\ModuleManager(MODULE_PATH);
        $this->assertInstanceOf('\Pimple\Container', $manager->getModules());
    }

    public function test()
    {
        /*
        $this->assertInstanceOf('Pimple\Tests\Fixtures\Service', $serviceOne);
        $this->assertTrue(isset($pimple['null']));
        $this->assertFalse(isset($pimple['non_existent']));
        $this->assertSame($params['param'], $pimple['param']);
        $this->assertEmpty($p->getValue($pimple));
        $this->assertCount(0, $p->getValue($pimple));
        $this->assertEquals(array('foo', 'bar'), $pimple->keys());
        $this->assertNotSame($serviceOne, $serviceTwo);
        */
    }
}
