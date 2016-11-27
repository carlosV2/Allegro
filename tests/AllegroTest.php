<?php

namespace test\carlosV2\Allegro;

use carlosV2\Allegro\Allegro;
use carlosV2\Allegro\ComposerHelper;
use Composer\Composer;
use Composer\Package\Link;
use Composer\Package\PackageInterface;
use Composer\Repository\RepositoryInterface;
use Composer\Repository\RepositoryManager;
use Composer\Semver\Constraint\ConstraintInterface;
use Prophecy\Prophecy\ObjectProphecy;

class AllegroTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectProphecy
     */
    private $composer;

    public function setUp()
    {
        $this->composer = $this->prophesize(Composer::class);

        // Composer is injected to avoid calling the factory plus it allows to perform
        // checks with it
        $rflProperty = new \ReflectionProperty(ComposerHelper::class, 'composer');
        $rflProperty->setAccessible(true);
        $rflProperty->setValue(null, $this->composer->reveal());
    }

    /** @test */
    public function itAppendsTheBundlesFoundOnTheExtraConfigurationOfComposer()
    {
        $this->configureComposerWith([AwesomeBundle::class, WonderBundle::class], [], []);

        $bundles = [];
        Allegro::appendTo($bundles);

        $this->assertEquals([new AwesomeBundle(), new WonderBundle()], $bundles);
    }

    /** @test */
    public function itAppendsTheBundlesFoundOnTheRequiredPackagesOfComposer()
    {
        $this->configureComposerWith([], [
            'package/awesome' => [AwesomeBundle::class],
            'package/wonder' => [WonderBundle::class]
        ], []);

        $bundles = [];
        Allegro::appendTo($bundles);

        $this->assertEquals([new AwesomeBundle(), new WonderBundle()], $bundles);
    }

    /** @test */
    public function itAppendsTheBundlesFoundOnTheRequiredDevPackagesOfComposer()
    {
        $this->configureComposerWith([], [], [
            'package/awesome' => [AwesomeBundle::class],
            'package/wonder' => [WonderBundle::class]
        ]);

        $bundles = [];
        Allegro::appendDevsTo($bundles);

        $this->assertEquals([new AwesomeBundle(), new WonderBundle()], $bundles);
    }

    /**
     * @param array $rootPackageClassNames
     * @param array $requirePackagesClassNames
     * @param array $requireDevPackagesClassName
     */
    private function configureComposerWith(array $rootPackageClassNames, array $requirePackagesClassNames, array $requireDevPackagesClassName)
    {
        $rootPackage = $this->getPackageWithClassNames($rootPackageClassNames);
        $this->composer->getPackage()->willReturn($rootPackage);

        $repositoryManager = $this->prophesize(RepositoryManager::class);
        $this->composer->getRepositoryManager()->willReturn($repositoryManager);

        $repository = $this->prophesize(RepositoryInterface::class);
        $repositoryManager->getLocalRepository()->willReturn($repository);

        $packages = [];
        foreach ($requirePackagesClassNames as $packageName => $classNames) {
            $constraint = $this->prophesize(ConstraintInterface::class);
            $packages[$packageName] = $this->getLink($packageName, $constraint);

            $repository->findPackage($packageName, $constraint)->willReturn(
                $this->getPackageWithClassNames($classNames)
            );
        }
        $rootPackage->getRequires()->willReturn($packages);

        $packages = [];
        foreach ($requireDevPackagesClassName as $packageName => $classNames) {
            $constraint = $this->prophesize(ConstraintInterface::class);
            $packages[$packageName] = $this->getLink($packageName, $constraint);

            $repository->findPackage($packageName, $constraint)->willReturn(
                $this->getPackageWithClassNames($classNames)
            );
        }
        $rootPackage->getDevRequires()->willReturn($packages);
    }

    /**
     * @param string[] $classNames
     *
     * @return ObjectProphecy
     */
    private function getPackageWithClassNames(array $classNames)
    {
        $bundlePackage = $this->prophesize(PackageInterface::class);
        $bundlePackage->getExtra()->willReturn(['symfony' => ['bundles' => $classNames]]);

        return $bundlePackage;
    }

    /**
     * @param string $packageName
     * @param $constraint
     *
     * @return ObjectProphecy
     */
    private function getLink($packageName, $constraint)
    {
        $link = $this->prophesize(Link::class);
        $link->getTarget()->willReturn($packageName);
        $link->getConstraint()->willReturn($constraint);

        return $link;
    }
}

class AwesomeBundle {}
class WonderBundle {}
