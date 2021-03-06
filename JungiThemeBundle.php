<?php

/*
 * This file is part of the JungiThemeBundle package.
 *
 * (c) Piotr Kugla <piku235@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jungi\Bundle\ThemeBundle;

use Jungi\Bundle\ThemeBundle\DependencyInjection\Compiler\TagProviderPass;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Jungi\Bundle\ThemeBundle\DependencyInjection\Compiler\CacheWarmerPass;

/**
 * The jungi theme bundle
 *
 * @author Piotr Kugla <piku235@gmail.com>
 */
class JungiThemeBundle extends Bundle
{
    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\HttpKernel\Bundle\Bundle::build()
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new CacheWarmerPass());
        $container->addCompilerPass(new TagProviderPass());
    }
}
