<?php

namespace Fhp\Segment;

/**
 * Contains meta information about a segment, i.e. anything that can be statically known about a sub-class of
 * {@link BaseSegment} through reflection.
 */
class SegmentDescriptor extends BaseDescriptor
{
    /** @var SegmentDescriptor[] */
    private static array $descriptors = [];

    /**
     * @param string $class The name of a sub-class of {@link BaseSegment}.
     * @return SegmentDescriptor The descriptor for the class.
     */
    public static function get(string $class): SegmentDescriptor
    {
        if (!array_key_exists($class, static::$descriptors)) {
            static::$descriptors[$class] = new SegmentDescriptor($class);
        }
        return static::$descriptors[$class];
    }

    /** Example: "HITANS" */
    public string $kennung;

    /**
     * Please use the factory above.
     * @param string $class The name of a sub-class of {@link BaseSegment}.
     */
    protected function __construct(string $class)
    {
        $this->class = $class;
        try {
            $clazz = new \ReflectionClass($class);
            if (!$clazz->isSubclassOf(BaseSegment::class)) {
                throw new \InvalidArgumentException("Must inherit from BaseSegment: $class");
            }
            parent::__construct($clazz);

            // Parse the class name into segment type (Kennung) and version.
            if (preg_match('/^([A-Z]+)v([0-9]+)$/', $clazz->getShortName(), $match) !== 1) {
                throw new \InvalidArgumentException("Invalid segment class name: $class");
            }
            $this->kennung = strval($match[1]);
            $this->version = intval($match[2]);
        } catch (\ReflectionException $e) {
            throw new \RuntimeException($e);
        }
    }

    public function validateObject($obj): void // Override
    {
        parent::validateObject($obj);
        if (!($obj instanceof BaseSegment)) {
            throw new \InvalidArgumentException('Expected sub-class of BaseSegment, got ' . gettype($obj));
        }
        if ($obj->getName() !== $this->kennung) {
            throw new \InvalidArgumentException("Expected $this->kennung, got " . $obj->getName());
        }
    }
}
