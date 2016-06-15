<?php

namespace Yoghi\Bundle\MaddaBundleTest\Utils;

class SplFileInfo extends \Symfony\Component\Finder\SplFileInfo
{

    public function getRealpath()
    {
        return $this->getPathname();
    }
}
