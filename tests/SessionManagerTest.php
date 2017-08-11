<?php
namespace WaffleSystems\Tests\Session;

use WaffleSystems\IO\MemoryStorage;
use WaffleSystems\Session\SessionManager;

class SessionManagerTest extends \PHPUnit_Framework_TestCase
{

    private $storage;
    private $manager;
    private $expiresInSeconds = 3600;

    public function setUp()
    {
        $this->storage = new MemoryStorage();
        $this->storage->open();

        $this->manager = new SessionManager($this->storage, $this->expiresInSeconds);
    }

    public function testLoading()
    {
        $session = $this->manager->load('a');
        $session->set('baz', 'Baz!');
        $this->manager->unload($session);

        $manager2 = new SessionManager($this->storage, $this->expiresInSeconds);
        $session = $manager2->load('a');
        $this->assertSame('Baz!', $session->get('baz'));

    }

    public function testGetOnEmpty()
    {
        $session = $this->manager->load('a');

        $this->assertSame(null, $session->get('foo'));

        $this->manager->unload($session);
    }

    public function testGetArrayOnEmpty()
    {
        $session = $this->manager->load('a');

        $this->assertSame(null, $session->get('foo.a'));

        $this->manager->unload($session);
    }

    public function testGet()
    {
        $session = $this->manager->load('a');

        $session->set('foo', 'Foo!');
        $this->assertSame('Foo!', $session->get('foo'));

        $this->manager->unload($session);
    }

    public function testGetWithArray()
    {
        $session = $this->manager->load('a');

        $session->set('bar.a', 'something');
        $this->assertSame('something', $session->get('bar.a'));
        $this->assertSame(['a' => 'something'], $session->get('bar'));

        $this->manager->unload($session);
    }

    public function testDelete()
    {
        $session = $this->manager->load('a');

        $session->set('foo', 'Foo!');
        $session->delete('foo');
        $this->assertSame(null, $session->get('foo'));

        $this->manager->unload($session);
    }

    public function testRemoveWithArray()
    {
        $session = $this->manager->load('a');

        $session->set('bar.a', 'something');

        $session->delete('bar.a');
        $this->assertSame(null, $session->get('bar.a'));
        $this->assertSame([], $session->get('bar'));

        $this->manager->unload($session);
    }

    public function testClear()
    {
        $session = $this->manager->load('a');

        $session->set('bar.a', 'something');

        $session->clear();
        $this->assertSame(null, $session->get('bar'));
        $this->assertSame(null, $session->get('bar.a'));

        $this->manager->unload($session);
    }

    public function testGetAll()
    {
        $session = $this->manager->load('a');

        $session->set('bar.a', 'something');
        $this->assertSame(['bar' => ['a' => 'something']], $session->get(''));

        $this->manager->unload($session);
    }

    public function testSetAll()
    {
        $session = $this->manager->load('a');

        $session->set('', ['bar' => ['a' => 'something']]);
        $this->assertSame(['bar' => ['a' => 'something']], $session->get(''));

        $this->manager->unload($session);
    }

}
