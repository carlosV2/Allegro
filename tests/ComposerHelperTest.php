<?php

namespace test\carlosV2\Allegro;

use carlosV2\Allegro\ComposerHelper;
use Composer\Composer;
use Composer\Package\Link;
use Composer\Package\PackageInterface;
use Composer\Package\RootPackageInterface;
use Composer\Repository\RepositoryInterface;
use Composer\Repository\RepositoryManager;
use Composer\Semver\Constraint\ConstraintInterface;
use Prophecy\Prophecy\ObjectProphecy;

class ComposerHelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectProphecy
     */
    private $composer;

    protected function setUp()
    {
        $this->composer = $this->prophesize(Composer::class);

        // Composer is injected to avoid calling the factory plus it allows to perform
        // checks with it
        $rflProperty = new \ReflectionProperty(ComposerHelper::class, 'composer');
        $rflProperty->setAccessible(true);
        $rflProperty->setValue(null, $this->composer->reveal());
    }

    /** @test */
    public function itReturnsTheRootPackage()
    {
        $rootPackage = $this->prophesize(RootPackageInterface::class);
        $this->composer->getPackage()->willReturn($rootPackage);

        $this->assertEquals($rootPackage->reveal(), ComposerHelper::getRootPackage());
    }

    /** @test */
    public function itReturnsTheRequiredPackages()
    {
        $rootPackage = $this->prophesize(RootPackageInterface::class);
        $this->composer->getPackage()->willReturn($rootPackage);

        $constraint = $this->prophesize(ConstraintInterface::class);
        $link = $this->getLink('my/package', $constraint);
        $rootPackage->getRequires()->willReturn(['my/package' => $link]);

        $repositoryManager = $this->prophesize(RepositoryManager::class);
        $this->composer->getRepositoryManager()->willReturn($repositoryManager);

        $repository = $this->prophesize(RepositoryInterface::class);
        $repositoryManager->getLocalRepository()->willReturn($repository);

        $package = $this->prophesize(PackageInterface::class);
        $repository->findPackage('my/package', $constraint)->willReturn($package);

        $this->assertEquals([$package->reveal()], ComposerHelper::getRequiredPackages());
    }

    /** @test */
    public function itIgnoresRequiredPackagesIfNotFound()
    {
        $rootPackage = $this->prophesize(RootPackageInterface::class);
        $this->composer->getPackage()->willReturn($rootPackage);

        $constraint = $this->prophesize(ConstraintInterface::class);
        $link = $this->getLink('my/package', $constraint);
        $rootPackage->getRequires()->willReturn(['my/package' => $link]);

        $repositoryManager = $this->prophesize(RepositoryManager::class);
        $this->composer->getRepositoryManager()->willReturn($repositoryManager);

        $repository = $this->prophesize(RepositoryInterface::class);
        $repositoryManager->getLocalRepository()->willReturn($repository);

        $repository->findPackage('my/package', $constraint)->willReturn(null);

        $this->assertEquals([], ComposerHelper::getRequiredPackages());
    }

    /** @test */
    public function itReturnsTheDevRequiredPackages()
    {
        $rootPackage = $this->prophesize(RootPackageInterface::class);
        $this->composer->getPackage()->willReturn($rootPackage);

        $constraint = $this->prophesize(ConstraintInterface::class);
        $link = $this->getLink('my/package', $constraint);
        $rootPackage->getDevRequires()->willReturn(['my/package' => $link]);

        $repositoryManager = $this->prophesize(RepositoryManager::class);
        $this->composer->getRepositoryManager()->willReturn($repositoryManager);

        $repository = $this->prophesize(RepositoryInterface::class);
        $repositoryManager->getLocalRepository()->willReturn($repository);

        $package = $this->prophesize(PackageInterface::class);
        $repository->findPackage('my/package', $constraint)->willReturn($package);

        $this->assertEquals([$package->reveal()], ComposerHelper::getRequiredDevPackages());
    }

    /** @test */
    public function itIgnoresRequiredDevPackagesIfNotFound()
    {
        $rootPackage = $this->prophesize(RootPackageInterface::class);
        $this->composer->getPackage()->willReturn($rootPackage);

        $constraint = $this->prophesize(ConstraintInterface::class);
        $link = $this->getLink('my/package', $constraint);
        $rootPackage->getDevRequires()->willReturn(['my/package' => $link]);

        $repositoryManager = $this->prophesize(RepositoryManager::class);
        $this->composer->getRepositoryManager()->willReturn($repositoryManager);

        $repository = $this->prophesize(RepositoryInterface::class);
        $repositoryManager->getLocalRepository()->willReturn($repository);

        $repository->findPackage('my/package', $constraint)->willReturn(null);

        $this->assertEquals([], ComposerHelper::getRequiredDevPackages());
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
