<?php

namespace Yoghi\Bundle\MaddaBundleTest\Utils;

use League\Flysystem\Adapter\Local;

class VfsAdapter extends Local
{
    /**
   * Constructor.
   *
   * @param string $root
   * @param int    $writeFlags
   * @param int    $linkHandling
   * @param array  $permissions
   */
  public function __construct($root, $writeFlags = 0, $linkHandling = self::DISALLOW_LINKS, array $permissions = [])
  {
      parent::__construct($root, $writeFlags, $linkHandling, $permissions);
  }

    public function ensureDirectory($root)
    {
        return $root;
    }

    public function __toString()
    {
        return $this->getPathPrefix();
    }
}
