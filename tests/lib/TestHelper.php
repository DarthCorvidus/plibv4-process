<?php
use plibv4\process\RoundRobin;
final class TestHelper {
	static function invoke($object, $methodName, array $args) {
		$reflector = new ReflectionClass(get_class($object));
		$method = $reflector->getMethod($methodName);
		$method->setAccessible(true);
	return $method->invokeArgs($object, $args);
	}
	
	static function getPropertyValue(RoundRobin $object, string $propertyName) {
		$reflector = new ReflectionObject($object);
		$property = $reflector->getProperty($propertyName);
	return $property->getValue($object);
	}

}
