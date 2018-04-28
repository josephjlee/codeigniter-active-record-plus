<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Class ClassUtil
 */
class ClassUtil {

    /**
     * Creates an object of given class from a given array
     *
     * @param array $arr
     * @param $class
     *
     * @return Object
     */
    public static function arrayToObject($arr, $class) {
        if ($arr == null) {
            return null;
        }

        $recordChild = new $class();
        foreach ($arr as $key => $value) {
            if (property_exists($class, $key)) {
                $recordChild->{$key} = $value;
            }
        }
        return $recordChild;
    }

    /**
     * Casts a base class to one of it's derived class
     *
     * @param $baseClassInstance
     *      Base class's object instance which needs to cast to one of it's derived class
     * @param $derivedClass
     *      One of the children of baseClassInstance's class
     *
     * @return mixed
     */
    public static function castToDerivedClass($baseClassInstance, $derivedClass) {
        $derivedClassInstance = new $derivedClass();

        if (is_a($derivedClassInstance, get_class($baseClassInstance))) {
            foreach ($baseClassInstance as $key => $value) {
                $derivedClassInstance->{$key} = $baseClassInstance->{$key};
            }
        }

        return $derivedClassInstance;
    }

}