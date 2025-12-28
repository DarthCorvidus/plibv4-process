<?php
namespace plibv4\process;
use ReflectionClass;
use ReflectionObject;
final class TestHelper {
	static function invoke(Object $object, string $methodName, array $args): mixed {
		$reflector = new ReflectionClass(get_class($object));
		$method = $reflector->getMethod($methodName);
		$method->setAccessible(true);
	return $method->invokeArgs($object, $args);
	}
	
	static function getPropertyValue(Object $object, string $propertyName): mixed {
		$reflector = new ReflectionObject($object);
		$property = $reflector->getProperty($propertyName);
	return $property->getValue($object);
	}

}
