<?php
namespace Yoghi\Bundle\MaddaBundleTest\Utils;

use League\Flysystem\Adapter\Local;

class VfsAdapter extends Local
{
    public function ensureDirectory($root)
    {
        return $root;
    }
}
