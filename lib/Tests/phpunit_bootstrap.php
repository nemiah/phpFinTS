<?php

use SebastianBergmann\Comparator\Factory;
use Tests\Fhp\Segment\SegmentComparator;

Factory::getInstance()->register(new SegmentComparator());
