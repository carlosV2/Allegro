<?php

namespace carlosV2\Allegro;

use SebastianBergmann\GlobalState\RuntimeException;

final class ComposerPackageNotFound extends RuntimeException
{
    /**
     * @param string $packageName
     */
    public function __construct($packageName)
    {
        parent::__construct(sprintf('Package `%s` was not found in composer.', $packageName));
    }
}
