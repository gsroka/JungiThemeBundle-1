<?php

/*
 * This file is part of the JungiThemeBundle package.
 *
 * (c) Piotr Kugla <piku235@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jungi\Bundle\ThemeBundle\Event;

use Jungi\Bundle\ThemeBundle\Resolver\ThemeResolverInterface;
use Symfony\Component\HttpFoundation\Request;
use Jungi\Bundle\ThemeBundle\Core\ThemeManagerInterface;
use Symfony\Component\EventDispatcher\Event;
use Jungi\Bundle\ThemeBundle\Core\ThemeInterface;

/**
 * ThemeEvent is a basic theme event
 *
 * @author Piotr Kugla <piku235@gmail.com>
 */
class ThemeEvent extends Event
{
    /**
     * @var ThemeManagerInterface
     */
    protected $manager;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var ThemeResolverInterface
     */
    protected $resolver;

    /**
     * @var ThemeInterface
     */
    protected $theme;

    /**
     * Constructor
     *
     * @param ThemeInterface $theme A theme
     * @param ThemeManagerInterface $manager A theme manager
     * @param ThemeResolverInterface $resolver A theme resolver
     * @param Request $request A request object
     */
    public function __construct(ThemeInterface $theme, ThemeManagerInterface $manager, ThemeResolverInterface $resolver, Request $request)
    {
        $this->theme = $theme;
        $this->manager = $manager;
        $this->resolver = $resolver;
        $this->request = $request;
    }

    /**
     * Returns the theme manager
     *
     * @return ThemeManagerInterface
     */
    public function getThemeManager()
    {
        return $this->manager;
    }

    /**
     * Returns the request object
     *
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Returns the theme resolver
     *
     * @return ThemeResolverInterface
     */
    public function getThemeResolver()
    {
        return $this->resolver;
    }

    /**
     * Returns the theme
     *
     * @return ThemeInterface
     */
    public function getTheme()
    {
        return $this->theme;
    }
}