<?php

/*
 * Copyright (c) 2015 Anuj Nair
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpJsonMarshaller\Marshaller;

use PhpJsonMarshaller\Decoder\ClassDecoder;
use PhpJsonMarshaller\Decoder\Object\ClassObject;
use PhpJsonMarshaller\Decoder\Object\PropertyTypeObject;
use PhpJsonMarshaller\Exception\InvalidTypeException;
use PhpJsonMarshaller\Exception\JsonDecodeException;
use PhpJsonMarshaller\Exception\UnknownPropertyException;

/**
 * Marshall and Unmarshall a JSON string to and from a particular class
 * Class JsonMarshaller
 * @package PhpJsonMarshaller\Marshaller
 */
class JsonMarshaller
{

    /**
     * An instance of the class decoder used to decode a class into a ClassObject
     * container PropertyObjects
     * @var ClassDecoder
     */
    protected $classDecoder;

    /**
     * @param ClassDecoder $classDecoder
     */
    public function __construct(
        ClassDecoder $classDecoder
    )
    {
        $this->classDecoder = $classDecoder;
    }

    /**
     * Marshall an object into a json string
     * @param mixed $class the class object to marshall
     * @param boolean $encode whether to json encode the result or not
     * @return string the json string
     * @throws JsonDecodeException
     */
    public function marshall($class, $encode = true)
    {
        return $this->marshallClass($class, $encode);
    }

    /**
     * Recursive function to marshall a class into a json string (encoded) or an array (not encoded)
     * @param mixed $class the class to marshall
     * @param boolean $encode whether to json encode the result or not
     * @return array|string
     * @throws JsonDecodeException
     * @throws \PhpJsonMarshaller\Exception\ClassNotFoundException
     */
    protected function marshallClass($class, $encode)
    {
        if (!is_object($class) || false === ($classString = get_class($class))) {
            throw new JsonDecodeException("Class does not exist");
        }

        // Decode the class and it's properties
        $decodedClass = $this->classDecoder->decodeClass($classString);
        if (count($decodedClass->getProperties()) == 0) {
            throw new \InvalidArgumentException("Class $classString doesn't have any @MarshallProperty annotations defined");
        }

        $result = [];

        foreach ($decodedClass->getProperties() as $property) {

            $hasDirect = $property->hasDirect();
            $hasGetter = $property->hasGetter();

            if($hasDirect || $hasGetter) {

                $value = null;
                $propertyType = $property->getPropertyType();

                // Get the value from the class
                if ($hasDirect) {
                    $value = $class->{$property->getDirect()};
                } elseif ($hasGetter) {
                    $value = $class->{$property->getGetter()}();
                }

                // Encode it into our json result
                $result[$property->getAnnotationName()] = $this->encodeValue($value, $propertyType);

            }
        }

        return ($encode ? json_encode($result) : $result);
    }

    /**
     * Encode a value into it's expected type
     * @param mixed $value
     * @param PropertyTypeObject $propertyType
     * @return array|mixed|null
     */
    protected function encodeValue($value, PropertyTypeObject $propertyType)
    {
        $result = null;

        if ($value === null) {
            $result = $value;
        } elseif ($propertyType->getType() === PropertyTypeObject::TYPE_SCALAR) {
            $result = $propertyType->getValue()->encodeValue($value);
        } elseif ($propertyType->getType() === PropertyTypeObject::TYPE_OBJECT) {
            $result = $this->marshallClass($value, false);
        } elseif ($propertyType->getType() === PropertyTypeObject::TYPE_ARRAY) {
            $subResult = [];
            $subPropertyType = $propertyType->getValue();
            foreach ($value as $key => $val) {
                if ($subPropertyType->getType() === PropertyTypeObject::TYPE_SCALAR) {
                    $subResult[$key] = $subPropertyType->getValue()->encodeValue($val);
                } else {
                    $subResult[$key] = $this->marshallClass($val, false);
                }
            }
            $result = $subResult;
        }

        return $result;
    }

    /**
     * UnMarshall a json string into a PHP class
     * @param string $string the json string
     * @param string|object $class a fully qualified namespaced class or object for the json to be inserted into
     * @return mixed A fully populated <$classString> class, containing values from the json string
     * @throws JsonDecodeException
     * @throws UnknownPropertyException
     */
    public function unmarshall($string, $class)
    {
        if ($string === null || $string === 'null' || $string === '') {
            return null;
        }

        // Decode the string into an assoc array and check it's valid
        $assocArray = json_decode($string, true);

        if (json_last_error() !== 0) {
            throw new JsonDecodeException('Could not decode the JSON string');
        }

        return $this->unmarshallClass($assocArray, $class);
    }

    /**
     * Sets the values of an associative array into a <$classString> class
     * @param array $assocArray the associative array containing all of our data
     * @param string|object $class the fully qualified namespaced class or object which will receive the data
     * @return mixed A fully populated <$classString> class, containing values from the assoc array
     * @throws InvalidTypeException
     * @throws UnknownPropertyException
     * @throws \PhpJsonMarshaller\Exception\ClassNotFoundException
     */
    protected function unmarshallClass($assocArray, $class)
    {

        if ($assocArray === null || $assocArray === 'null' || $assocArray === '') {
            return null;
        }

        $classString = is_object($class) ? get_class($class) : $class;

        // Decode the class and it's properties
        $decodedClass = $this->classDecoder->decodeClass($classString);
        if (count($decodedClass->getProperties()) == 0 && count($decodedClass->getConstructorParams()) === 0) {
            throw new \InvalidArgumentException("Class $classString doesn't have any @MarshallProperty annotations defined");
        }

        // Create a new class if class is not object
        $classInstance = is_object($class) ? $class : $this->createClass($classString, $decodedClass, $assocArray);

        foreach ($assocArray as $key => $value) {

            if ($decodedClass->hasProperty($key)) {
                $property = $decodedClass->getProperty($key);
                $propertyType = $property->getPropertyType();

                // Decode the result
                $result = $this->decodeValue($value, $propertyType);

                // Set our result into the class
                if ($property->hasDirect()) {
                    $classInstance->{$property->getDirect()} = $result;
                } elseif ($property->hasSetter()) {
                    $setter = $property->getSetter();
                    if($propertyType->getType() == 'array') {
                        $reflection = new \ReflectionMethod($classInstance, $setter);
                        if($reflection->getParameters()[0]->isVariadic()) {
                            $result = is_array($result) && $result ? $result : [];
                            $classInstance->{$setter}(...$result);
                        }
                        else {
                            $classInstance->{$setter}($result);
                        }
                    }
                    else {
                        $classInstance->{$setter}($result);
                    }
                }
            } else {
                if ($decodedClass->canIgnoreUnknown() === false) {
                    throw new UnknownPropertyException(
                        "Unknown property '$key' in class '$classString' and cannot ignore unknown properties.
                        (You can add a MarshallConfig annotation on the class to change this)"
                    );
                }
            }
        }

        return $classInstance;
    }

    /**
     * Decode a value into it's expected type
     * @param mixed $value
     * @param PropertyTypeObject $propertyType
     * @return array|mixed|null
     */
    protected function decodeValue($value, PropertyTypeObject $propertyType)
    {
        $result = null;

        // Decode the value into our result
        if ($value === null) {
            $result = $value;
        } elseif ($propertyType->getType() === PropertyTypeObject::TYPE_SCALAR) {
            $result = $propertyType->getValue()->decodeValue($value);
        } elseif ($propertyType->getType() === PropertyTypeObject::TYPE_OBJECT) {
            $result = $this->unmarshallClass($value, $propertyType->getValue());
        } elseif ($propertyType->getType() === PropertyTypeObject::TYPE_ARRAY) {
            $subPropertyType = $propertyType->getValue();
            foreach ($value as $key => $val) {
                if ($subPropertyType->getType() === PropertyTypeObject::TYPE_SCALAR) {
                    $result[$key] = $subPropertyType->getValue()->decodeValue($val);
                } else {
                    $result[$key] = $this->unmarshallClass($val, $subPropertyType->getValue());
                }
            }
        }

        return $result;
    }

    /**
     * Create an instance of a new class
     * @param string $classString
     * @param ClassObject $decodedClass
     * @param array $assocArray
     * @return object
     */
    protected function createClass($classString, $decodedClass, $assocArray)
    {
        $constructorParams = $decodedClass->getConstructorParams();
        if (count($constructorParams) === 0) {
            return new $classString;
        }

        $decodedParams = [];
        foreach ($constructorParams as $param) {
            $val = null;
            if (isset($assocArray[$param->getAnnotationName()])) {
                $val = $this->decodeValue($assocArray[$param->getAnnotationName()], $param->getPropertyType());
            }
            $decodedParams[] = $val;
        }

        $reflectedClass = new \ReflectionClass($classString);
        return $reflectedClass->newInstanceArgs($decodedParams);
    }

}