<?php

namespace Fhp\Segment;

/**
 * Class ElementDescriptor
 *
 * Contains information about an element (aka. field) in a segment or Deg.
 *
 * Elements implicitly have version 1.
 *
 * @package Fhp\Segment
 */
class ElementDescriptor
{
    /**
     * Name of the PHP field that this descriptor describes.
     * @var string
     */
    public $field;

    /**
     * The plain type of the PHP field (without array or nullable suffix). This is either a string, for scalar types, or
     * a \ReflectionClass for a sub-class of {@link BaseSegment} or {@link BaseDeg} for complex types.
     * @var string|\ReflectionClass
     */
    public $type;

    /**
     * Whether the field must be present (at least once, if repeated) in every segment/Deg instance (false) or can be
     * omitted (true). This is auto-detected from the nullable suffix `|null` in the PHP type.
     * @var boolean
     */
    public $optional = false;

    /**
     * Whether the field can have multiple values (if so, this field contains the maximum number of allowed values) or
     * not (if so, the value is zero). This is auto-detected from the array suffix `[]` in the PHP type.
     * @var integer
     */
    public $repeated = 0;

    /**
     * @param object $obj The object whose $field will be validated.
     * @throws \InvalidArgumentException If $obj->$field does not correspond to the schema in this descriptor.
     */
    public function validateField($obj)
    {
        if (!isset($obj->{$this->field})) {
            if ($this->optional) return;
            throw new \InvalidArgumentException("Missing field $this->field");
        }
        $value = $obj->{$this->field};
        if ($this->repeated) {
            if (!is_array($value)) {
                throw new \InvalidArgumentException("Expected array value for repeated field $this->field");
            }
            foreach ($value as $item) {
                $this->validateValue($item);
            }
        } else {
            $this->validateValue($value);
        }
    }

    /**
     * Maps types declared in a {@}var comment to the return format of `gettype()`.
     */
    const TYPE_MAP = [
        'int' => 'integer', 'integer' => 'integer',
        'float' => 'double',
        'bool' => 'boolean', 'boolean' => 'boolean',
        'string' => 'string',
    ];

    /**
     * @param string $type A potential PHP scalar type.
     * @return boolean True if parseDataElement() would understand it.
     */
    public static function isScalarType($type)
    {
        return array_key_exists($type, static::TYPE_MAP);
    }

    /**
     * @param mixed $value The (non-null) value to be validated.
     * @throws \InvalidArgumentException If $value is not a valid $type.
     */
    public function validateValue($value)
    {
        if (is_string($this->type) && array_key_exists($this->type, static::TYPE_MAP)) {
            $expectedType = static::TYPE_MAP[$this->type];
            $actualType = gettype($value);
            if ($actualType !== $expectedType) {
                throw new \InvalidArgumentException("Expected $expectedType, got $actualType: $value");
            }
        } elseif ($this->type instanceof \ReflectionClass) {
            if (!$this->type->isInstance($value)) {
                throw new \InvalidArgumentException("Expected {$this->type->name}, got $value");
            }
            if ($value instanceof BaseSegment || $value instanceof BaseDeg) {
                $value->validate();
            } else {
                throw new \AssertionError(); // Violates guarantees of what we put in $this->type.
            }
        } else {
            throw new \InvalidArgumentException("Unsupported type: $this->type");
        }
    }
}
