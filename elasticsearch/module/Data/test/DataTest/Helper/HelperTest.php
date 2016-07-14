<?php

namespace DataTest\Helper;

use Data\Helper\Helper;

/**
 * @covers Data\Helper\Helper
 */
class HelperTest extends \PHPUnit_Framework_TestCase
{
    public function testTypeValid()
    {
        self::assertTrue(Helper::checkValidType('opportunity'));
        self::assertTrue(Helper::checkValidType('event'));
        self::assertTrue(Helper::checkValidType('all'));
    }

    public function testTypeInValid()
    {
        self::assertFalse(Helper::checkValidType('Opportunity'));
        self::assertFalse(Helper::checkValidType('Event'));
        self::assertFalse(Helper::checkValidType('All'));
        self::assertFalse(Helper::checkValidType('InvalidType'));
    }
}
