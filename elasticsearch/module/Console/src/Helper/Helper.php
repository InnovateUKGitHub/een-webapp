<?php

namespace Console\Helper;

class Helper
{
    const VALID_TYPE = [
        'event',
        'opportunity',
        'all'
    ];

    /**
     * @param string $type
     *
     * @return bool
     */
    public static function checkValidType($type)
    {
        if (in_array($type, self::VALID_TYPE, true) === false) {
            return false;
        }

        return true;
    }
}