<?php
namespace Estina\Tests;

use ReflectionProperty;

class TestCase extends \PHPUnit\Framework\TestCase
{
    public function getContainerMock()
    {
        return $this->getPlainMock('Symfony\Component\DependencyInjection\Container');
    }

    public function getTempDir($name = '')
    {
        $dir = sys_get_temp_dir();
        if (!empty($name)) {
            $dir .= '/' . $name;
        }

        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        return $dir;
    }

    /**
     * Create temp file
     *
     * @param  string $name
     * @return string path of newly created file
     */
    public function getTempFile($name = 'Default')
    {
        return tempnam($this->getTempDir(), $name);
    }

    /**
     * Create mock object from given class name.
     *
     * @param string $class              Class name
     * @param bool   $disableConstructor
     */
    protected function getPlainMock($class, $disableConstructor = true)
    {
        $mock = $this->getMockBuilder($class);
        if (true === $disableConstructor) {
            $mock->disableOriginalConstructor();
        }

        return $mock->getMock();
    }

    /**
     * Return value protected/private property from object. Chaining could be
     * used to configure mocked objects:
     *
     * $this->getHiddenProperty($someObject, 'catalogModel')
     *      ->expects($this->once())
     *      ->method('someMethod');
     *
     * @param object $object Target object
     * @param string $name   Name of hidden property
     *
     * @return object
     */
    protected function getHiddenProperty($object, $name)
    {
        $refl = new ReflectionProperty(get_class($object), $name);
        $refl->setAccessible(true);

        return $refl->getValue($object);
    }

    /**
     * Set value for protected/private property on object.
     *
     * @param object $object Target object
     * @param string $name   Property name
     * @param mixed  $value  New value
     *
     * @return void
     */
    protected function setHiddenProperty($object, $name, $value)
    {
        $refl = new ReflectionProperty(get_class($object), $name);
        $refl->setAccessible(true);
        $refl->setValue($object, $value);
    }

    /**
     * Invoke private or protected method on object.
     * 
     * @param object $object Target object
     * @param string $method Method name
     * @param mixed $arg1 Arguments for method
     * @return mixed
     */
    protected function invokeHiddenMethod($object, $method)
    {
        $reflector = new \ReflectionClass($object);
        $method = $reflector->getMethod($method);
        $method->setAccessible(true);

        $args = array_slice(func_get_args(), 2);
        return $method->invokeArgs($object, $args);
    }
}
