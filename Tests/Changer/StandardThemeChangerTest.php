<?php

/*
 * This file is part of the JungiThemeBundle package.
 *
 * (c) Piotr Kugla <piku235@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jungi\Bundle\ThemeBundle\Tests;

use Jungi\Bundle\ThemeBundle\Changer\StandardThemeChanger;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Jungi\Bundle\ThemeBundle\Core\SimpleThemeHolder;
use Jungi\Bundle\ThemeBundle\Core\ThemeManager;
use Jungi\Bundle\ThemeBundle\Tests\Fixtures\Resolver\FakeThemeResolver;

/**
 * StandardThemeChanger Test Case
 *
 * @author Piotr Kugla <piku235@gmail.com>
 */
class StandardThemeChangerTest extends TestCase
{
    /**
     * @var StandardThemeChanger
     */
    private $changer;

    /**
     * @var SimpleThemeHolder
     */
    private $holder;

    /**
     * @var ThemeManager
     */
    private $manager;

    /**
     * @var FakeThemeResolver
     */
    private $resolver;

    /**
     * (non-PHPdoc)
     * @see PHPUnit_Framework_TestCase::setUp()
     */
    protected function setUp()
    {
        $this->resolver = new FakeThemeResolver('bootheme', false);
        $this->holder = new SimpleThemeHolder();
        $this->manager = new ThemeManager(array(
            $this->createThemeMock('footheme'),
            $this->createThemeMock('bootheme')
        ));
        $this->changer = new StandardThemeChanger($this->manager, $this->holder, $this->resolver, new EventDispatcher());
    }

    /**
     * (non-PHPdoc)
     * @see PHPUnit_Framework_TestCase::tearDown()
     */
    protected function tearDown()
    {
        $this->changer = null;
        $this->manager = null;
        $this->holder = null;
        $this->resolver = null;
    }

    /**
     * Tests change
     *
     * @dataProvider getThemesForChange
     */
    public function testChange($theme)
    {
        $request = $this->createDesktopRequest();
        $this->changer->change($theme, $request);

        $this->assertEquals('footheme', $this->resolver->resolveThemeName($request));
        $this->assertEquals('footheme', $this->holder->getTheme()->getName());
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function getThemesForChange()
    {
        return array(
            array($this->createThemeMock('footheme')),
            array('footheme')
        );
    }
}
