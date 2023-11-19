<?php

namespace App\Helpers\Routes;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class RouteHelper
{
    public static function includeRouteFiles(string $folder)
    {
        // Loop through the v1 folder recursively
        $dirIterator = new RecursiveDirectoryIterator($folder);

        /** @var RecursiveDirectoryIterator | RecursiveIteratorIterator $it */
        $it = new RecursiveIteratorIterator($dirIterator);

        // Require the file in reach iteration
        while ($it->valid())
        {
            if (!$it->isDot() &&
                $it->isFile() &&
                $it->isReadable() &&
                $it->current()->getExtension() === 'php')
            {
                require $it->key();
            }

            $it->next();
        }
    }
}
