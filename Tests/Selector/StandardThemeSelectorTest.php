<?php

/*
 * This file is part of the JungiThemeBundle package.
 *
 * (c) Piotr Kugla <piku235@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jungi\Bundle\ThemeBundle\Tests\Selector;

use Jungi\Bundle\ThemeBundle\Exception\NullThemeException;
use Jungi\Bundle\ThemeBundle\Selector\EventListener\ValidationListener;
use Jungi\Bundle\ThemeBundle\Selector\StandardThemeSelector;
use Jungi\Bundle\ThemeBundle\Tests\TestCase;
use Jungi\Bundle\ThemeBundle\Core\ThemeManagerInterface;
use Jungi\Bundle\ThemeBundle\Selector\EventListener\DeviceThemeSwitch;
use Jungi\Bundle\ThemeBundle\Core\MobileDetect;
use Jungi\Bundle\ThemeBundle\Tag\TagCollection;
use Jungi\Bundle\ThemeBundle\Tag;
use Jungi\Bundle\ThemeBundle\Core\ThemeManager;
use Jungi\Bundle\ThemeBundle\Resolver\InMemoryThemeResolver;
use Jungi\Bundle\ThemeBundle\Tests\Fixtures\Validation\FakeMetadataFactory;
use Jungi\Bundle\ThemeBundle\Tests\Fixtures\Validation\Constraints\FakeClassConstraint;
use Symfony\Component\Validator\ConstraintValidatorFactory;
use Symfony\Component\Validator\DefaultTranslator;
use Symfony\Component\Validator\Validator;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * StandardThemeSelector Test Case
 *
 * @author Piotr Kugla <piku235@gmail.com>
 */
class StandardThemeSelectorTest extends TestCase
{
    /**
     * @var StandardThemeSelector
     */
    private $selector;

    /**
     * @var ThemeManagerInterface
     */
    private $manager;

    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;

    /**
     * @var InMemoryThemeResolver
     */
    private $resolver;

    /**
     * (non-PHPdoc)
     * @see PHPUnit_Framework_TestCase::setUp()
     */
    protected function setUp()
    {
        $theme = $this->createThemeMock('footheme');
        $theme
            ->expects($this->any())
            ->method('getTags')
            ->will($this->returnValue(new TagCollection(array(
                new Tag\DesktopDevices()
            ))));

        $this->eventDispatcher = new EventDispatcher();
        $this->manager = new ThemeManager(array(
            $theme
        ));
        $this->resolver = new InMemoryThemeResolver('footheme', false);
        $this->selector = new StandardThemeSelector($this->manager, $this->eventDispatcher, $this->resolver);
    }

    /**
     * (non-PHPdoc)
     * @see PHPUnit_Framework_TestCase::tearDown()
     */
    protected function tearDown()
    {
        $this->selector = null;
        $this->resolver = null;
        $this->manager = null;
        $this->eventDispatcher = null;
    }

    /**
     * Tests a event listener (DeviceSwitch) cooperation with StandardThemeSelector
     */
    public function testDeviceSwitchListener()
    {
        // Prepare
        $theme = $this->createThemeMock('footheme_mobile');
        $theme
            ->expects($this->any())
            ->method('getTags')
            ->will($this->returnValue(new TagCollection(array(
                new Tag\Link('footheme'),
                new Tag\MobileDevices()
            ))));
        $this->manager->addTheme($theme);

        // Add the DeviceThemeSwitch
        $this->eventDispatcher->addSubscriber(new DeviceThemeSwitch(new MobileDetect(), $this->manager));

        // The main thread
        $request = $this->createMobileRequest();
        $theme = $this->selector->select($request);

        // Assert
        $this->assertEquals('footheme_mobile', $theme->getName());
    }

    /**
     * Tests the behaviour in situations when a theme has been invalidated e.g. by validation process
     *
     * @expectedException \Jungi\Bundle\ThemeBundle\Exception\InvalidatedThemeException
     */
    public function testOnInvalidatedTheme()
    {
        // Add the ValidationListener
        $this->eventDispatcher->addSubscriber(new ValidationListener($this->getValidator()));

        // Execute
        $request = $this->createDesktopRequest();
        $this->selector->select($request);
    }

    /**
     * Tests the fallback functionality when a theme has been invalidated
     */
    public function testFallbackOnInvalidatedTheme()
    {
        // Add the ValidationListener
        $this->eventDispatcher->addSubscriber(new ValidationListener($this->getValidator()));

        // Add the default theme
        $this->manager->addTheme($this->createThemeMock('default'));

        // Prepare the request
        $request = $this->createDesktopRequest();
        $this->resolver->setThemeName('footheme', $request);

        // Sets the fallback theme resolver
        $this->selector->setFallback(new InMemoryThemeResolver('default'));
        $theme = $this->selector->select($request);

        // Assert
        $this->assertEquals('default', $theme->getName());
    }

    /**
     * Tests the fallback functionality when a real theme is not exist
     */
    public function testFallbackOnEmptyTheme()
    {
        // Default theme
        $this->manager->addTheme($this->createThemeMock('default'));

        // Prepare the request
        $request = $this->createDesktopRequest();
        $this->resolver->setThemeName(null, $request);

        // Sets the fallback theme resolver
        $this->selector->setFallback(new InMemoryThemeResolver('default'));
        $theme = $this->selector->select($request);

        // Assert
        $this->assertEquals('default', $theme->getName());
    }

    /**
     * Tests the fallback functionality when a real theme is not exist
     */
    public function testFallbackOnNonExistingTheme()
    {
        // Default theme
        $this->manager->addTheme($this->createThemeMock('default'));

        // Prepare the request
        $request = $this->createDesktopRequest();
        $this->resolver->setThemeName('missing_theme', $request);

        // Sets the fallback theme resolver
        $this->selector->setFallback(new InMemoryThemeResolver('default'));
        $theme = $this->selector->select($request);

        // Assert
        $this->assertEquals('default', $theme->getName());
    }

    /**
     * Tests the fallback functionality when a real theme is exist
     */
    public function testFallbackOnExistingTheme()
    {
        // Default theme
        $this->manager->addTheme($this->createThemeMock('default'));

        // Prepare the request
        $request = $this->createDesktopRequest();

        // Sets the fallback theme resolver
        $this->selector->setFallback(new InMemoryThemeResolver('default'));
        $theme = $this->selector->select($request);

        // Assert
        $this->assertNotEquals('default', $theme->getName());
    }

    /**
     * Tests on an existing theme
     */
    public function testOnExistingTheme()
    {
        $request = $this->createDesktopRequest();
        $theme = $this->selector->select($request);

        $this->assertEquals('footheme', $theme->getName());
    }

    /**
     * Tests on an empty theme name
     *
     * @expectedException \Jungi\Bundle\ThemeBundle\Exception\NullThemeException
     */
    public function testOnNullTheme()
    {
        $request = $this->createDesktopRequest();

        $this->resolver->setThemeName(null, $request);
        $this->selector->select($request);
    }

    /**
     * Tests on an empty theme name with enabled "ignore null themes"
     */
    public function testOnNullThemeWithNullThemesIgnore()
    {
        $request = $this->createDesktopRequest();

        $this->resolver->setThemeName(null, $request);
        $this->selector->setOption('ignore_null_themes', true);

        try {
            $this->selector->select($request);
        } catch (NullThemeException $e) {
            $this->fail('When the option "ignore null themes" is enabled the NullThemeException should not be thrown.');
        }
    }

    /**
     * Tests on an empty theme name
     *
     * @expectedException \Jungi\Bundle\ThemeBundle\Exception\NullThemeException
     */
    public function testFallbackOnNullTheme()
    {
        $request = $this->createDesktopRequest();

        $this->resolver->setThemeName('missing_theme', $request);
        $this->selector->setFallback(new InMemoryThemeResolver(null));
        $this->selector->select($request);
    }

    /**
     * Tests on an empty fallback theme name with enabled "ignore null themes"
     */
    public function testFallbackOnNullThemeWithNullThemesIgnore()
    {
        $request = $this->createDesktopRequest();

        $this->resolver->setThemeName('missing_theme', $request);
        $this->selector->setFallback(new InMemoryThemeResolver(null));
        $this->selector->setOption('ignore_null_themes', true);

        try {
            $this->selector->select($request);
        } catch (NullThemeException $e) {
            $this->fail('When the option "ignore null themes" is enabled the NullThemeException should not be thrown.');
        }
    }

    /**
     * Tests on a bad request
     *
     * @expectedException \Jungi\Bundle\ThemeBundle\Exception\ThemeNotFoundException
     */
    public function testOnNonExistingTheme()
    {
        $request = $this->createDesktopRequest();
        $this->resolver->setThemeName('footheme_missing', $request);

        $this->selector->select($request);
    }

    /**
     * Returns the configured validator helper
     *
     * @return Validator
     */
    private function getValidator()
    {
        $validator = new Validator(new FakeMetadataFactory(), new ConstraintValidatorFactory(), new DefaultTranslator());

        // Constraints for the ThemeInterface
        $metadata = new ClassMetadata('Jungi\Bundle\ThemeBundle\Core\ThemeInterface');
        $metadata->addConstraint(new FakeClassConstraint());
        $validator->getMetadataFactory()->addMetadata($metadata);

        return $validator;
    }
}
