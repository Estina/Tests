<?php
namespace Estina\Tests;

use PHPUnit_Framework_TestCase;
use ReflectionProperty;

class TestCase extends PHPUnit_Framework_TestCase
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

    protected function setter($object, $property)
    {
        $value = 'myValue';
        
        $method = 'set' . ucfirst($property);
        $object->$method($value);
        $this->assertEquals($value, $this->getHiddenProperty($object, $property));
    }

    protected function getter($object, $property)
    {
        $value = 'myValue';

        $method = 'get' . ucfirst($property);
        $this->setHiddenProperty($object, $property, $value);
        $this->assertEquals($value, $object->$method());
    }

    protected function mutator($object, array $properties)
    {
        foreach ($properties as $name) {
            $this->setter($object, $name);
            $this->getter($object, $name);
        }
    }

    protected function adder($object, $property, $value)
    {
        $method = 'add' . ucfirst($property);
        $property .= 's';

        $object->$method($value);

        $data = $this->getHiddenProperty($object, $property);

        $this->assertEquals($value, array_pop($data));
    }
}
