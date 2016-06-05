<?php

namespace Yoghi\Bundle\MaddaBundle\Generator;

class SplFileInfo extends \Symfony\Component\Finder\SplFileInfo
{

    public function getRealpath()
    {
        return $this->getPathname();
    }
}
