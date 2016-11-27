<?php

namespace carlosV2\Allegro;

use Composer\Composer;
use Composer\Factory;
use Composer\IO\NullIO;
use Composer\Package\Link;
use Composer\Package\PackageInterface;

final class ComposerHelper
{
    /**
     * @var Composer
     */
    private static $composer;

    /**
     * @return PackageInterface
     */
    public static function getRootPackage()
    {
        return self::getComposer()->getPackage();
    }

    /**
     * @return PackageInterface[]
     *
     * @throws ComposerPackageNotFound
     */
    public static function getRequiredPackages()
    {
        return self::getPackagesFromLinks(self::getRootPackage()->getRequires());
    }

    /**
     * @return PackageInterface[]
     *
     * @throws ComposerPackageNotFound
     */
    public static function getRequiredDevPackages()
    {
        return self::getPackagesFromLinks(self::getRootPackage()->getDevRequires());
    }

    /**
     * @param Link[] $links
     *
     * @return PackageInterface[]
     *
     * @throws ComposerPackageNotFound
     */
    private static function getPackagesFromLinks(array $links)
    {
        return array_map(function (Link $link) {
            return self::getPackageFromLink($link);
        }, array_values($links));
    }

    /**
     * @param Link $link
     *
     * @return PackageInterface
     *
     * @throws ComposerPackageNotFound
     */
    private static function getPackageFromLink(Link $link)
    {
        if ($package = self::getComposer()->getRepositoryManager()->getLocalRepository()->findPackage(
            $link->getTarget(),
            $link->getConstraint()
        )) {
            return $package;
        }

        throw new ComposerPackageNotFound($link->getTarget());
    }

    /**
     * @return Composer
     */
    private static function getComposer()
    {
        if (is_null(self::$composer)) {
            self::$composer = Factory::create(new NullIO());
        }

        return self::$composer;
    }
}
