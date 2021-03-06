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

/**
 * SimpleThemeHolder is a default theme holder
 *
 * @author Piotr Kugla <piku235@gmail.com>
 */
class SimpleThemeHolder implements ThemeHolderInterface
{
    /**
     * @var ThemeInterface
     */
    protected $theme;

    /**
     * {@inheritdoc}
     */
    public function getTheme()
    {
        return $this->theme;
    }

    /**
     * {@inheritdoc}
     */
    public function setTheme(ThemeInterface $theme)
    {
        $this->theme = $theme;
    }
}
