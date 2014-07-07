<?php

use Jungi\Bundle\ThemeBundle\Core\Theme;
use Jungi\Bundle\ThemeBundle\Core\Details;
use Jungi\Bundle\ThemeBundle\Tests\Fixtures\Tag\Own;
use Jungi\Bundle\ThemeBundle\Tag;
use Jungi\Bundle\ThemeBundle\Tag\Core\TagCollection;

$manager->addTheme(new Theme(
    'foo_1',
    $locator->locate('@JungiFooBundle/Resources/theme'),
    new Details('A fancy theme', '1.0.0', '<i>foo desc</i>', 'MIT', 'piku235', 'piku235@gmail.com', 'http://test.pl'),
    new TagCollection(array(
        new Tag\DesktopDevices(),
        $tagFactory->create('jungi.mobile_devices', array(array('iOS', 'AndroidOS'), Tag\MobileDevices::MOBILE)),
        new Own('test')
    ))
));