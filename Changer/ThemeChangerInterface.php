<?php

/*
 * This file is part of the JungiThemeBundle package.
 *
 * (c) Piotr Kugla <piku235@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jungi\Bundle\ThemeBundle\Changer;

use Jungi\Bundle\ThemeBundle\Core\ThemeInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * ThemeChangerInterface allows to change the theme for a given request
 *
 * @author Piotr Kugla <piku235@gmail.com>
 */
interface ThemeChangerInterface
{
    /**
     * Changes the current theme with a new one
     *
     * @param string|ThemeInterface $theme A theme instance or a theme name
     * @param Request $request A request instance
     *
     * @return void
     */
    public function change($theme, Request $request);
}