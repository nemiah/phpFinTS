<?php

namespace Fhp\Segment;

use Fhp\DataTypes\Bin;

/**
 * Common functionality for segment/Deg descriptors.
 */
abstract class BaseDescriptor
{
    /** @var string Example: "Fhp\Segment\TAN\HITANSv6" (Segment) or "Fhp\Common\Kik" (Deg) */
    public $class;
    /** @var int Example: 1 */
    public $version = 1;

    /**
     * Descriptors for the elements inside the segment/Deg in the order of the wire format. The indices in this array
     * match the speficiation. In particular, the first index is 1 (not 0) and some indices may be missing if the
     * documentation does not specify it (anymore).
     * @var ElementDescriptor[]
     */
    public $elements = [];

    /**
     * The last index that can be present in an exploded serialized segment/DEG. If one were to append a new field to
     * segment/DEG described by this descriptor, it would get index $maxIndex+1.
     * Usually $maxIndex==array_key_last($elements), but when the last element is repeated, then $maxIndex is larger.
     * @var int
     */
    public $maxIndex;

    protected function __construct(\ReflectionClass $clazz)
    {
        // Use reflection to map PHP class fields to elements in the segment/Deg.
        $implicitIndex = true;
        $nextIndex = 0;
        foreach (static::enumerateProperties($clazz) as $property) {
            $docComment = $property->getDocComment();
            if (!is_string($docComment)) {
                throw new \InvalidArgumentException("Property $property must be annotated.");
            }
            if (static::getBoolAnnotation('Ignore', $docComment)) {
                continue; // Skip @Ignore-d propeties.
            }

            $index = static::getIntAnnotation('Index', $docComment);
            if ($index === null) {
                if ($implicitIndex) {
                    $index = $nextIndex;
                } else {
                    throw new \InvalidArgumentException("Property $property needs an explicit @Index");
                }
            } else {
                // After one field was marked with an @Index, all subsequent fields need an explicit index too.
                $implicitIndex = false;
            }

            $descriptor = new ElementDescriptor();
            $descriptor->field = $property->getName();
            $type = static::getVarAnnotation($docComment);
            if ($type === null) {
                throw new \InvalidArgumentException("Need type on property $property");
            }
            $maxCount = static::getIntAnnotation('Max', $docComment);
            if (substr($type, -5) === '|null') { // Nullable field
                $descriptor->optional = true;
                $type = substr($type, 0, -5);
            }
            if (substr($type, -2) === '[]') { // Array/repeated field
                if ($maxCount === null) {
                    throw new \InvalidArgumentException("Repeated property $property needs @Max() annotation");
                }
                $descriptor->repeated = $maxCount;
                $type = substr($type, 0, -2);
                // If a repeated field is followed by anything at all, there will be an empty entry for each possible
                // repeated value (in extreme cases, there can be hundreds of consecutive `+`, for instance).
                $nextIndex += $maxCount;
            } elseif ($maxCount !== null) {
                throw new \InvalidArgumentException("@Max() annotation not recognized on single $property");
            } else {
                ++$nextIndex; // Singular field, so the index advances by 1.
            }
            $descriptor->type = static::resolveType($type, $property->getDeclaringClass());
            $this->elements[$index] = $descriptor;
        }
        if (count($this->elements) === 0) {
            throw new \InvalidArgumentException("No fields found in $clazz->name");
        }
        ksort($this->elements); // Make sure elements are parsed in wire-format order.
        $this->maxIndex = $nextIndex - 1;
    }

    /**
     * @param object $obj The object to be validated.
     * @throws \InvalidArgumentException If any of the fields in the given object is not valid according to the schema
     *     defined by this descriptor.
     */
    public function validateObject($obj)
    {
        if (!is_a($obj, $this->class)) {
            throw new \InvalidArgumentException("Expected $this->class, got " . gettype($obj));
        }
        foreach ($this->elements as $elementDescriptor) {
            $elementDescriptor->validateField($obj);
        }
    }

    /**
     * @param \ReflectionClass $clazz The class name.
     * @return \Generator|\ReflectionProperty[] All non-static public properties of the given class and its parents, but
     *     with the parents' properties *first*.
     */
    private static function enumerateProperties(\ReflectionClass $clazz)
    {
        if ($clazz->getParentClass() !== false) {
            yield from static::enumerateProperties($clazz->getParentClass());
        }
        foreach ($clazz->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
            if (!$property->isStatic() && $property->getDeclaringClass()->name === $clazz->name) {
                yield $property;
            }
        }
    }

    /**
     * Looks for the annotation with the given name and extracts the content of the parentheses behind it. For instance,
     * when called with the name "Index" and a docComment that contains {@}Index(15), this would return "15".
     * @param string $name The name of the annotation.
     * @param string $docComment The documentation string of a PHP field.
     * @return string|null The content of the annotation, or null if absent.
     */
    private static function getAnnotation(string $name, string $docComment): ?string
    {
        $ret = preg_match("/@$name\\((.*?)\\)/", $docComment, $match);
        if ($ret === false) {
            throw new \RuntimeException("preg_match failed on $name");
        }
        return $ret === 1 ? $match[1] : null;
    }

    /**
     * Same as above, with integer parsing.
     * @param string $name The name of the annotation.
     * @param string $docComment The documentation string of a PHP field.
     * @return int|null The value of the annotation as an integer, or null if absent.
     */
    private static function getIntAnnotation(string $name, string $docComment): ?int
    {
        $val = static::getAnnotation($name, $docComment);
        if ($val === null) {
            return null;
        }
        if (!is_numeric($val)) {
            throw new \InvalidArgumentException("Annotation $name has non-integer value $val");
        }
        return intval($val);
    }

    /**
     * @param string $name The name of the annotation.
     * @param string $docComment The documentation string of a PHP field.
     * @return bool Whether the annotation with the given name is present.
     */
    private static function getBoolAnnotation(string $name, string $docComment): bool
    {
        return strpos("@$name ", $docComment) !== false
            || strpos("@$name())", $docComment) !== false;
    }

    /**
     * Separate parser for the {@}var` annotation because it does not use parentheses.
     * @param string $docComment The documentation string of a PHP field.
     * @return string|null The value of the {@}var annotation, or null if absent.
     */
    private static function getVarAnnotation(string $docComment): ?string
    {
        $ret = preg_match('/@var ([^\\s]+)/', $docComment, $match);
        if ($ret === false) {
            throw new \RuntimeException('preg_match failed for @var');
        }
        return $ret === 1 ? $match[1] : null;
    }

    /**
     * NOTE: This does *not* resolve `use` statements in the source file.
     * @param string $typeName A type name (PHP class name, fully qualified or not) or a scalar type name.
     * @param \ReflectionClass $contextClass The class where this type name was encountered, used for resolution of
     *     classes in the same package.
     * @return string|\ReflectionClass The class that the type name refers to, or the scalar type name as a string.
     */
    private static function resolveType(string $typeName, \ReflectionClass $contextClass)
    {
        if (ElementDescriptor::isScalarType($typeName)) {
            return $typeName;
        }
        if ($typeName === 'Bin') {
            $typeName = Bin::class;
        } elseif (strpos($typeName, '\\') === false) {
            // Let's assume it's a relative type name, e.g. `X` mentioned in a file that starts with `namespace Fhp\Y`
            // would become `\Fhp\X\Y`.
            $typeName = $contextClass->getNamespaceName() . '\\' . $typeName;
        }
        try {
            return new \ReflectionClass($typeName);
        } catch (\ReflectionException $e) {
            throw new \RuntimeException("$typeName not found in context of " . $contextClass->getName(), 0, $e);
        }
    }
}
