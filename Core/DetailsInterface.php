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
 * DetailsInterface
 *
 * @author Piotr Kugla <piku235@gmail.com>
 */
interface DetailsInterface
{
    /**
     * Returns the friendly theme name
     *
     * @return string
     */
    public function getName();

    /**
     * Returns the author
     *
     * @return string|null
     */
    public function getAuthor();

    /**
     * Returns the author mail
     *
     * @return string|null
     */
    public function getAuthorMail();

    /**
     * Returns the author site
     *
     * @return string|null
     */
    public function getAuthorSite();

    /**
     * Returns the version
     *
     * @return string
     */
    public function getVersion();

    /**
     * Returns the description
     *
     * @return string|null
     */
    public function getDescription();

    /**
     * Returns the type of license
     *
     * @return string|null
     */
    public function getLicense();
}