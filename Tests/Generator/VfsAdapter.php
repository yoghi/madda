<?php
namespace Yoghi\Bundle\MaddaBundle\Generator;

use League\Flysystem\Adapter\Local;

class VfsAdapter extends Local
{
    public function ensureDirectory($root)
    {
        return $root;
    }
}
