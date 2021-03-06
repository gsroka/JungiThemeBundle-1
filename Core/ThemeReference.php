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

use Symfony\Bundle\FrameworkBundle\Templating\TemplateReference;

/**
 * ThemeReference
 *
 * @author Piotr Kugla <piku235@gmail.com>
 */
class ThemeReference extends TemplateReference
{
    /**
     * @var string
     */
    const DELIMITER = '#';

    /**
     * @var TemplateReference
     */
    protected $origin;

    /**
     * Constructor
     *
     * @param TemplateReference $template A template reference
     * @param string            $theme    A theme name
     */
    public function __construct(TemplateReference $template, $theme)
    {
        $this->parameters = array('theme' => $theme) + $template->parameters;
        $this->origin = $template;
    }

    /**
     * Returns the origin template reference
     *
     * @return TemplateReference
     */
    public function getOrigin()
    {
        return $this->origin;
    }

    /**
     * Returns the path to the theme template
     *
     * @return string
     */
    public function getPath()
    {
        $path = '';
        if ($this->parameters['bundle']) {
            $path .= $this->parameters['bundle'] . '/';
        }
        if ($this->parameters['controller']) {
            $path .= $this->parameters['controller'] . '/';
        }
        $path .= $this->parameters['name'] . '.' . $this->parameters['format'] . '.' . $this->parameters['engine'];

        return $path;
    }

    /**
     * Returns the logical name
     *
     * @return string
     */
    public function getLogicalName()
    {
        return sprintf('%s%s%s:%s:%s.%s.%s',
            $this->parameters['theme'],
            self::DELIMITER,
            $this->parameters['bundle'],
            $this->parameters['controller'],
            $this->parameters['name'],
            $this->parameters['format'],
            $this->parameters['engine']
        );
    }
}
