<?php

/*
 * This file is part of the JungiThemeBundle package.
 *
 * (c) Piotr Kugla <piku235@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jungi\Bundle\ThemeBundle\Core;

use Jungi\Bundle\ThemeBundle\Exception\ThemeNotFoundException;
use Jungi\Bundle\ThemeBundle\Tag\TagInterface;

/**
 * Classes that implements this interface manages all themes in the system
 *
 * @author Piotr Kugla <piku235@gmail.com>
 */
interface ThemeManagerInterface
{
    /**
     * Adds a new theme
     *
     * @param ThemeInterface $theme A theme
     *
     * @return void
     */
    public function addTheme(ThemeInterface $theme);

    /**
     * Checks if a given theme exists
     *
     * @param string $name A theme name
     *
     * @return bool
     */
    public function hasTheme($name);

    /**
     * Returns the theme by name
     *
     * @param string $name A theme name
     *
     * @return ThemeInterface
     *
     * @throws ThemeNotFoundException If the theme can not be found
     */
    public function getTheme($name);

    /**
     * Returns all themes
     *
     * @return ThemeInterface[]
     */
    public function getThemes();

    /**
     * Returns the theme which have given tags
     *
     * @param TagInterface[]|TagInterface $tags A one tag or tags
     *
     * @return ThemeInterface|null Null if the theme can not be found
     */
    public function getThemeWithTags($tags);

    /**
     * Returns all themes which have given tags
     *
     * @param TagInterface[]|TagInterface $tags A one tag or tags
     *
     * @return ThemeInterface[]
     */
    public function getThemesWithTags($tags);
}
