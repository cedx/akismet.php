<?php
/**
 * Implementation of the `akismet\Comment` class.
 */
namespace akismet;

/**
 * Represents a comment submitted by an author.
 */
class Comment implements \JsonSerializable {

  /**
   * Initializes a new instance of the class.
   * @param array $config Name-value pairs that will be used to initialize the object properties.
   */
  public function __construct($config = []) {
    foreach ($config as $property => $value) {
      if(property_exists($this, $property)) $this->$property = $value;
    }
  }

  /**
   * Returns a string representation of this object.
   * @return string The string representation of this object.
   */
  public function __toString(): string {
    $json = json_encode($this, JSON_FORCE_OBJECT | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    return static::class . " $json";
  }
}
