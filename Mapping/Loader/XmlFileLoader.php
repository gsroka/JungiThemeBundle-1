<?php

/*
 * This file is part of the JungiThemeBundle package.
 *
 * (c) Piotr Kugla <piku235@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jungi\Bundle\ThemeBundle\Mapping\Loader;

use Jungi\Bundle\ThemeBundle\Tag\Factory\TagFactoryInterface;
use Jungi\Bundle\ThemeBundle\Tag\TagCollection;
use Jungi\Bundle\ThemeBundle\Tag\TagInterface;
use Jungi\Bundle\ThemeBundle\Core\Theme;
use Jungi\Bundle\ThemeBundle\Core\Details;
use Jungi\Bundle\ThemeBundle\Core\ThemeManagerInterface;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Config\Util\XmlUtils;

/**
 * XmlFileLoader is responsible for creating theme instances from a xml mapping file
 *
 * @author Piotr Kugla <piku235@gmail.com>
 */
class XmlFileLoader extends FileLoader
{
    /**
     * @var LoaderHelper
     */
    private $helper;

    /**
     * Constructor
     *
     * @param ThemeManagerInterface $themeManager A theme manager
     * @param FileLocatorInterface  $locator      A file locator
     * @param TagFactoryInterface   $factory      A tag factory
     * @param LoaderHelper          $helper       A loader helper
     */
    public function __construct(ThemeManagerInterface $themeManager, FileLocatorInterface $locator, TagFactoryInterface $factory, LoaderHelper $helper)
    {
        parent::__construct($themeManager, $locator, $factory);

        $this->helper = $helper;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($file)
    {
        return pathinfo($file, PATHINFO_EXTENSION) == 'xml';
    }

    /**
     * Loads themes from a given xml theme mapping file
     *
     * @param string $file A file
     *
     * @return void
     */
    public function load($file)
    {
        $path = $this->locator->locate($file);

        $xml = $this->loadFile($path);
        foreach ($xml->children() as $child) {
            $this->themeManager->addTheme($this->parseTheme($child));
        }
    }

    /**
     * Returns arguments to the proper php values
     *
     * @param \SimpleXmlElement $element An element
     *
     * @return mixed
     */
    private function getArgumentsAsPhp(\SimpleXmlElement $element)
    {
        $arguments = array();
        foreach ($element->argument as $arg) {
            switch ($arg['type']) {
                case 'collection':
                    $arguments[] = $this->getArgumentsAsPhp($arg);
                    break;
                case 'string':
                    $arguments[] = (string) $arg;
                    break;
                case 'constant':
                    $arguments[] = $this->helper->resolveConstant((string) $arg);
                    break;
                default:
                    $arguments[] = XmlUtils::phpize($arg);
            }
        }

        return $arguments;
    }

    /**
     * Parses a theme element from a dom document
     *
     * @param \SimpleXMLElement $elm A dom element
     *
     * @return Theme
     *
     * @throws \InvalidArgumentException If a theme node has some missing attributes
     */
    private function parseTheme(\SimpleXMLElement $elm)
    {
        if (!isset($elm['name']) || !isset($elm['path'])) {
            throw new \InvalidArgumentException('The node theme has some required missing attributes. Have you not forgot to specify attributes "path" and "name" for this node?');
        }

        // Ns
        $elm->registerXPathNamespace('mapping', 'http://piku235.github.io/JungiThemeBundle/schema/theme-mapping');

        return new Theme(
            (string) $elm['name'],
            $this->locator->locate((string) $elm['path']),
            $this->parseDetails($elm),
            $this->parseTags($elm)
        );
    }

    /**
     * Parses a details about a theme
     *
     * @param \SimpleXMLElement $elm An element
     *
     * @return Details
     *
     * @throws \InvalidArgumentException If a detail node has not defined attr "name"
     * @throws \RuntimeException         When something goes wrong while parsing details node
     */
    private function parseDetails(\SimpleXMLElement $elm)
    {
        $collection = array();
        foreach ($elm->xpath('mapping:details/mapping:detail') as $detail) {
            if (!isset($detail['name'])) {
                throw new \InvalidArgumentException('The detail node has not defined attribute "name". Have you forgot about that?');
            }

            $collection[(string) $detail['name']] = (string) $detail;
        }

        try {
            return new Details($collection);
        } catch (\LogicException $e) {
            throw new \RuntimeException('An exception has occurred while parsing the details node, see the previous exception', null, $e);
        }
    }

    /**
     * Parses a theme tags from a given dom element
     *
     * @param \SimpleXMLElement $elm An element
     *
     * @return TagCollection
     */
    private function parseTags(\SimpleXMLElement $elm)
    {
        $tags = array();
        foreach ($elm->xpath('mapping:tags/mapping:tag') as $tag) {
            $tags[] = $this->parseTag($tag);
        }

        return new TagCollection($tags);
    }

    /**
     * Parses a theme tags from a given dom element
     *
     * @param \SimpleXMLElement $tag A tag element
     *
     * @return TagInterface
     *
     * @throws \InvalidArgumentException If a tag node has not defined attr "type"
     * @throws \RuntimeException         If a tag is not exist
     */
    private function parseTag(\SimpleXMLElement $tag)
    {
        if (!isset($tag['name'])) {
            throw new \InvalidArgumentException('The tag node has not defined attribute "name". Have you forgot about that?');
        }

        return $this->tagFactory->create((string) $tag['name'], count($tag->children()) ? $this->getArgumentsAsPhp($tag) : (string) $tag);
    }

    /**
     * Loads a xml file data
     *
     * @param string $file A file
     *
     * @return \SimpleXMLElement
     *
     * @throws \RuntimeException When the some problem will occur while parsing a mapping file
     * @throws \RuntimeException If a file is not local
     * @throws \DomainException  If a given file is not supported
     */
    protected function loadFile($file)
    {
        if (!stream_is_local($file)) {
            throw new \RuntimeException(sprintf('The "%s" file is not local.', $file));
        } elseif (!$this->supports($file)) {
            throw new \DomainException(sprintf('The given file "%s" is not supported.', $file));
        }

        try {
            $doc = XmlUtils::loadFile($file, __DIR__ . '/schema/theme-1.0.xsd');
        } catch (\InvalidArgumentException $e) {
            throw new \RuntimeException(sprintf('The problem has occurred while parsing the file "%s", see the previous exception.', $file), null, $e);
        }

        return simplexml_import_dom($doc);
    }
}
