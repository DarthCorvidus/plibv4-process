<?php
class TestHelper {
	static function invoke($object, $methodName, array $args) {
		$reflector = new ReflectionClass(get_class($object));
		$method = $reflector->getMethod($methodName);
		$method->setAccessible(true);
	return $method->invokeArgs($object, $args);
	}
	
	static function getPropertyValue($object, $propertyName) {
		$reflector = new ReflectionObject($object);
		$property = $reflector->getProperty($propertyName);
		$property->setAccessible(true);
	return $property->getValue($object);
	}

}
