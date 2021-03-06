<?php

/*
 * This file is part of the JungiThemeBundle package.
 *
 * (c) Piotr Kugla <piku235@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jungi\Bundle\ThemeBundle\Tests\Mapping\Loader;

use Jungi\Bundle\ThemeBundle\Core\Details;
use Jungi\Bundle\ThemeBundle\Core\Theme;
use Jungi\Bundle\ThemeBundle\Mapping\Loader\PhpFileLoader;
use Jungi\Bundle\ThemeBundle\Tests\Fixtures\Tag\Own;
use Jungi\Bundle\ThemeBundle\Tag;
use Jungi\Bundle\ThemeBundle\Tag\TagCollection;
use Symfony\Component\HttpKernel\Config\FileLocator;

/**
 * PhpFileLoader Test Case
 *
 * @author Piotr Kugla <piku235@gmail.com>
 */
class PhpFileLoaderTest extends AbstractFileLoaderTest
{
    /**
     * @var PhpFileLoader
     */
    private $loader;

    /**
     * @var FileLocator
     */
    private $locator;

    /**
     * Set up
     */
    protected function setUp()
    {
        parent::setUp();

        $this->locator = new FileLocator($this->kernel, __DIR__ . '/Fixtures/php');
        $this->loader = new PhpFileLoader($this->manager, $this->locator, $this->tagFactory);
    }

    protected function tearDown()
    {
        parent::tearDown();

        $this->loader = null;
        $this->locator = null;
    }

    /**
     * Tests file load
     */
    public function testLoad()
    {
        $this->loader->load('theme.php');

        $this->assertEquals(new Theme(
            'foo_1',
            $this->locator->locate('@JungiFooBundle/Resources/theme'),
            new Details(array(
                'name' => 'A fancy theme',
                'version' => '1.0.0',
                'description' => '<i>foo desc</i>',
                'license' => 'MIT',
                'author.name' => 'piku235',
                'author.email' => 'piku235@gmail.com',
                'author.site' => 'http://test.pl'
            )),
            new TagCollection(array(
                new Tag\DesktopDevices(),
                new Tag\MobileDevices(array('iOS', 'AndroidOS'), Tag\MobileDevices::MOBILE),
                new Own('test')
            ))
        ), $this->manager->getTheme('foo_1'));
    }
}
