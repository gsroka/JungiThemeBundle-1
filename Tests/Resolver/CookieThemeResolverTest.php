<?php

/*
 * This file is part of the JungiThemeBundle package.
 *
 * (c) Piotr Kugla <piku235@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jungi\Bundle\ThemeBundle\Tests\Resolver;

use Jungi\Bundle\ThemeBundle\Tests\TestCase;
use Jungi\Bundle\ThemeBundle\Resolver\CookieThemeResolver;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;

/**
 * CookieThemeResolver Test Case
 *
 * @author Piotr Kugla <piku235@gmail.com>
 */
class CookieThemeResolverTest extends TestCase
{
    /**
     * @var CookieThemeResolver
     */
    private $resolver;

    /**
     * @var array
     */
    private $options;

    /**
     * (non-PHPdoc)
     * @see PHPUnit_Framework_TestCase::setUp()
     */
    protected function setUp()
    {
        $this->options = array(
            'lifetime' => 86400, // +24h
            'path' => '/foo',
            'domain' => 'fooweb.com',
            'secure' => true,
            'httpOnly' => false
        );
        $this->resolver = new CookieThemeResolver($this->options);
    }

    /**
     * (non-PHPdoc)
     * @see PHPUnit_Framework_TestCase::tearDown()
     */
    protected function tearDown()
    {
        $this->resolver = null;
    }

    /**
     * Tests resolve theme name method
     */
    public function testResolveThemeName()
    {
        $desktopReq = $this->createDesktopRequest();
        $helpReq = $this->createMobileRequest();
        $this->resolver->setThemeName('footheme', $desktopReq);

        $this->assertEquals('footheme', $this->resolver->resolveThemeName($desktopReq));
        $this->assertNull($this->resolver->resolveThemeName($helpReq));
    }

    /**
     * Tests writes to the response when they were theme changes
     */
    public function testWriteResponseOnChanges()
    {
        $response = new Response();
        $request = $this->createDesktopRequest();
        $this->resolver->setThemeName('footheme_new', $request);
        $this->resolver->writeResponse($request, $response);

        $cookies = $response->headers->getCookies();
        $this->assertContains(new Cookie(
            CookieThemeResolver::COOKIE_NAME,
            'footheme_new',
            time() + $this->options['lifetime'],
            $this->options['path'],
            $this->options['domain'],
            $this->options['secure'],
            $this->options['httpOnly']
        ), $cookies, '', false, false);
    }

    /**
     * Tests writes to the response when they were not any theme changes
     */
    public function testWriteResponseOnNoChanges()
    {
        $response = new Response();
        $request = $this->createDesktopRequest();
        $request->cookies->set(CookieThemeResolver::COOKIE_NAME, 'footheme_from_previous_request');
        $this->resolver->writeResponse($request, $response);

        // If cookies are empty then is fine
        $this->assertEmpty($response->headers->getCookies());
    }
}
