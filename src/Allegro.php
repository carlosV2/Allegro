<?php

namespace carlosV2\Allegro;

use Composer\Package\PackageInterface;

final class Allegro
{
    /**
     * @param array $bundles
     *
     * @throws ComposerPackageNotFound
     */
    public static function appendTo(&$bundles)
    {
        $packages = ComposerHelper::getRequiredPackages();
        $packages[] = ComposerHelper::getRootPackage();

        $bundles = array_merge($bundles, self::getBundlesFromPackages($packages));
    }

    /**
     * @param array $bundles
     *
     * @throws ComposerPackageNotFound
     */
    public static function appendDevsTo(&$bundles)
    {
        $packages = ComposerHelper::getRequiredDevPackages();

        $bundles = array_merge($bundles, self::getBundlesFromPackages($packages));
    }

    /**
     * @param PackageInterface[] $packages
     *
     * @return array
     */
    private static function getBundlesFromPackages(array $packages)
    {
        $bundles = [];
        foreach ($packages as $package) {
            $bundles = array_merge($bundles, array_map(function ($className) {
                return new $className();
            }, self::getBundleClassNamesFromPackage($package)));
        }

        return $bundles;
    }

    /**
     * @param PackageInterface $package
     *
     * @return string[]
     */
    private static function getBundleClassNamesFromPackage(PackageInterface $package)
    {
        $extra = $package->getExtra();

        if (!is_array($extra) || !array_key_exists('symfony', $extra)) {
            return [];
        }

        if (!is_array($extra['symfony']) || !array_key_exists('bundles', $extra['symfony'])) {
            return [];
        }

        if (!is_array($extra['symfony']['bundles'])) {
            return [];
        }

        return $extra['symfony']['bundles'];
    }
}
