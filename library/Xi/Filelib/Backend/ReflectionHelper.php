<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Backend;

use ReflectionProperty;

/**
 * Reflection helper
 */
class ReflectionHelper
{
    /**
     * @var array
     */
    private $properties = array();

    /**
     * Return an accessible property or null.
     *
     * @param string $class
     * @param string $property
     * @return ReflectionProperty|null
     */
    public function getAccessibleProperty($class, $property)
    {
        if (isset($this->properties[$class][$property])) {
            return $this->properties[$class][$property];
        }

        $reflProperty = new ReflectionProperty($class, $property);
        $reflProperty->setAccessible(true);

        $this->properties[$class][$property] = $reflProperty;
        return $this->properties[$class][$property];
    }
}
