<?php

namespace Fhp\Segment;

use Fhp\Syntax\Delimiter;

/**
 * Class BaseDescriptor
 *
 * Common functionality for segment/Deg descriptors.
 *
 * @package Fhp\Segment
 */
abstract class BaseDescriptor
{
    /** @var string Example: "Fhp\Segment\HITANSv1" (Segment) or "Fhp\Segment\Segmentkopf" (Deg) */
    public $class;
    /** @var integer Example: 1 */
    public $version = 1;

    /**
     * Descriptors for the elements inside the segment/Deg in the order of the wire format. The indices in this array
     * match the speficiation. In particular, the first index is 1 (not 0) and some indices may be missing if the
     * documentation does not specify it (anymore).
     * @var ElementDescriptor[]
     */
    public $elements;

    /**
     * @param \ReflectionClass $clazz
     */
    protected function __construct($clazz)
    {
        // Use reflection to map PHP class fields to elements in the segment/Deg.
        $implicitIndex = true;
        $nextIndex = $clazz->isSubclassOf(BaseSegment::class) ? 1 : 0; // Segments have implicit Segmentkopf.
        foreach ($clazz->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
            if ($property->isStatic() || $property->getDeclaringClass()->name !== $clazz->name) {
                // Skip static and super properties.
                continue;
            }

            $docComment = $property->getDocComment();
            if (!is_string($docComment)) {
                throw new \InvalidArgumentException("Property $property must be annotated.");
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
            if (empty($type)) {
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
                $nextIndex++; // Singular field, so the index advances by 1.
            }
            $descriptor->type = static::resolveType($type, $clazz);
            $this->elements[$index] = $descriptor;
        }
        ksort($this->elements); // Make sure elements are parsed in wire-format order.
    }

    /**
     * @param object $obj The object to be validated.
     * @throws \InvalidArgumentException If any of the fields in the given object is not valid according to the schema
     *     defined by this descriptor.
     */
    public function validateObject($obj) {
        if (!is_a($obj, $this->class)) {
            throw new \InvalidArgumentException("Expected $this->class, got " . gettype($obj));
        }
        foreach ($this->elements as $elementDescriptor) {
            $elementDescriptor->validateField($obj);
        }
    }

    /**
     * Looks for the annotation with the given name and extracts the content of the parentheses behind it. For instance,
     * when called with the name "Index" and a docComment that contains {@}Index(15), this would return "15".
     * @param string $name The name of the annotation.
     * @param string $docComment The documentation string of a PHP field.
     * @return string|null The content of the annotation, or null if absent.
     */
    private static function getAnnotation($name, $docComment)
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
    private static function getIntAnnotation($name, $docComment)
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
     * @return boolean Whether the annotation with the given name is present.
     */
    private static function getBoolAnnotation($name, $docComment)
    {
        return strpos("@$name", $docComment) !== false;
    }

    /**
     * Separate parser for the {@}var` annotation because it does not use parentheses.
     * @param string $docComment The documentation string of a PHP field.
     * @return string|null The value of the {@}var annotation, or null if absent.
     */
    private static function getVarAnnotation($docComment)
    {
        $ret = preg_match("/@var ([^\\s]+)/", $docComment, $match);
        if ($ret === false) {
            throw new \RuntimeException("preg_match failed for @var");
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
    private static function resolveType($typeName, $contextClass)
    {
        if (ElementDescriptor::isScalarType($typeName)) {
            return $typeName;
        }
        if (strpos($typeName, '\\') === false) {
            // Let's assume it's a relative type name, e.g. `X` mentioned in a file that starts with `namespace Fhp\Y`
            // would become `\Fhp\X\Y`.
            $typeName = $contextClass->getNamespaceName() . '\\' . $typeName;
        }
        try {
            return new \ReflectionClass($typeName);
        } catch (\ReflectionException $e) {
            throw new \RuntimeException($e);
        }
    }
}
