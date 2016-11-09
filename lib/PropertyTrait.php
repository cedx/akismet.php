<?php
/**
 * Implementation of the `akismet\PropertyTrait` trait.
 */
namespace akismet;

/**
 * TODO `PropertyTrait` is the base class that implements the *property* feature.
 *
 * A property is defined by a getter method (e.g. `getLabel`), and/or a setter method (e.g. `setLabel`).
 * For example, the following getter and setter methods define a property named `label`:
 *
 * ```
 * private $_label;
 *
 * public function getLabel(): string {
 *   return $this->_label;
 * }
 *
 * public function setLabel(string $value) {
 *   $this->_label = $value;
 * }
 * ```
 *
 * Property names are *case-insensitive*.
 *
 * A property can be accessed like a member variable of an object.
 * Reading or writing a property will cause the invocation of the corresponding getter or setter method. For example:
 *
 * ```
 * // Equivalent to $label = $object->getLabel();
 * $label = $object->label;
 *
 * // Equivalent to $object->setLabel('abc');
 * $object->label = 'abc';
 * ```
 */
trait PropertyTrait {

  /**
   * Returns the value of an object property.
   * @param string $name The property name.
   * @return mixed The property value.
   * @throws \BadMethodCallException The specified property is not defined.
   * @throws \LogicException The specified property is write-only.
   */
  public function __get(string $name) {
    $getter = "get$name";
    if (method_exists($this, $getter)) return $this->$getter();
    throw method_exists($this, "set$name") ? new \LogicException("Getting write-only property: $name") : new \BadMethodCallException("Getting unknown property: $name");
  }

  /**
   * Checks if a property is set, e.g. defined and not `null`.
   * @param string $name The property name.
   * @return bool Whether the specified property is set (e.g. defined and not `null`).
   */
  public function __isset(string $name): bool {
    $getter = "get$name";
    return method_exists($this, $getter) ? $this->$getter() !== null : false;
  }

  /**
   * Sets value of an object property.
   * @param string $name The property name.
   * @param mixed $value The property value.
   * @throws \BadMethodCallException The specified property is not defined.
   * @throws \LogicException The specified property is read-only.
   */
  public function __set(string $name, $value) {
    $setter = "set$name";
    if (method_exists($this, $setter)) $this->$setter($value);
    else throw method_exists($this, "get$name") ? new \LogicException("Setting read-only property: $name") : new \BadMethodCallException("Setting unknown property: $name");
  }

  /**
   * Sets an object property to `null`.
   * @param string $name The property name.
   */
  public function __unset(string $name) {
    $this->__set($name, null);
  }

  /**
   * Returns a value indicating whether a property can be read.
   *
   * A property is readable if:
   * - the class has a getter method associated with the specified name (in this case, property name is case-insensitive).
   * - the class has a member variable with the specified name (when `$checkVars` is true).
   *
   * @param string $name The property name.
   * @param bool $checkVars Whether to treat member variables as properties.
   * @return bool Whether the property can be read.
   */
  public function canGetProperty(string $name, bool $checkVars = true): bool {
    return method_exists($this, 'get' . $name) || $checkVars && property_exists($this, $name);
  }

  /**
   * Returns a value indicating whether a property can be set.
   *
   * A property is writable if:
   * - the class has a setter method associated with the specified name (in this case, property name is case-insensitive).
   * - the class has a member variable with the specified name (when `$checkVars` is true).
   *
   * @param string $name The property name.
   * @param bool $checkVars Whether to treat member variables as properties.
   * @return bool Whether the property can be written.
   */
  public function canSetProperty(string $name, bool $checkVars = true): bool {
    return method_exists($this, 'set' . $name) || $checkVars && property_exists($this, $name);
  }

  /**
   * Returns a value indicating whether a property is defined.
   *
   * A property is defined if:
   * - the class has a getter or setter method associated with the specified name (in this case, property name is case-insensitive).
   * - the class has a member variable with the specified name (when `$checkVars` is true).
   *
   * @param string $name The property name.
   * @param bool $checkVars Whether to treat member variables as properties.
   * @return bool Whether the property is defined.
   */
  public function hasProperty(string $name, bool $checkVars = true): bool {
    return $this->canGetProperty($name, $checkVars) || $this->canSetProperty($name, false);
  }
}
