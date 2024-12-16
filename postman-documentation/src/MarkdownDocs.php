<?php

namespace AttractCores\PostmanDocumentation;

use Illuminate\Support\Arr;

/**
 * Class MarkdownDocs
 *
 * @package AttractCores\PostmanDocumentation
 * Date: 17.12.2021
 * Version: 1.0
 * Author: Yure Nery <yurenery@gmail.com>
 */
class MarkdownDocs implements \Stringable
{

    protected array $data = [];

    /**
     * Add new heading into final render.
     *
     * @param string $text
     * @param string $headingType
     *
     * @return \AttractCores\PostmanDocumentation\MarkdownDocs
     */
    public function heading(string $text, string $headingType = 'h1') : MarkdownDocs
    {
        if ( ! in_array($headingType, [ 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ]) ) {
            throw new \BadMethodCallException('Heading can only be called via "h1, h2, h3, h4, h5, h6 parameters"',
                500);
        }

        $headingMarkersCount = (int) substr($headingType, 1, 1);

        $this->data[] = [
            'type'   => 'heading',
            'prefix' => str_repeat('#', $headingMarkersCount),
            'text'   => $text,
        ];

        return $this;
    }

    /**
     * Add new line paragraph into final render.
     *
     * @param string $text
     *
     * @param string $prefix
     *
     * @return \AttractCores\PostmanDocumentation\MarkdownDocs
     */
    public function line(string $text, $prefix = '') : MarkdownDocs
    {
        $type = 'paragraph';
        $this->data[] = compact('type', 'text', 'prefix');

        return $this;
    }

    /**
     * Add new numeric list into final render.
     *
     * @param array $tree
     *
     * @return \AttractCores\PostmanDocumentation\MarkdownDocs
     */
    public function numericList(array $tree) : MarkdownDocs
    {
        $type = 'numeric_list';
        $this->data[] = compact('type', 'tree');

        return $this;
    }

    /**
     * Add new unordered list into final render.
     *
     * @param array $tree
     *
     * @return \AttractCores\PostmanDocumentation\MarkdownDocs
     */
    public function unorderedList(array $tree) : MarkdownDocs
    {
        $type = 'unordered_list';
        $this->data[] = compact('type', 'tree');

        return $this;
    }

    /**
     * Add new quotes into final render.
     *
     * @param mixed $list
     *
     * @return \AttractCores\PostmanDocumentation\MarkdownDocs
     */
    public function quote($list) : MarkdownDocs
    {
        $list = Arr::wrap($list);
        $type = 'quotes';
        $this->data[] = compact('type', 'list');

        return $this;
    }

    /**
     * Add new block of code into final render.
     *
     * @param string $code
     * @param string $lang
     *
     * @return \AttractCores\PostmanDocumentation\MarkdownDocs
     */
    public function block(string $code, string $lang) : MarkdownDocs
    {
        $type = 'block';
        $this->data[] = compact('type', 'code', 'lang');

        return $this;
    }

    /**
     * Add new link into final render.
     *
     * @param string      $url
     * @param string|null $name
     *
     * @return \AttractCores\PostmanDocumentation\MarkdownDocs
     */
    public function link(string $url, string $name = NULL) : MarkdownDocs
    {
        $type = 'link';
        $this->data[] = compact('type', 'url', 'name');

        return $this;
    }

    /**
     * Add new image into final render.
     *
     * @param string      $url
     * @param string|null $alt
     * @param string|null $title
     *
     * @return \AttractCores\PostmanDocumentation\MarkdownDocs
     */
    public function image(string $url, string $alt = NULL, string $title = NULL) : MarkdownDocs
    {
        $type = 'img';
        $this->data[] = compact('type', 'url', 'alt', 'title');

        return $this;
    }

    /**
     * Add new raw string into final render.
     *
     * @param string $raw
     *
     * @return \AttractCores\PostmanDocumentation\MarkdownDocs
     */
    public function raw(string $raw) : MarkdownDocs
    {
        $type = 'raw';
        $this->data[] = compact('type', 'raw');

        return $this;
    }

    /**
     * Compile data into markdown string syntax.
     *
     * @return string
     */
    public function __toString() : string
    {
        $str = '';

        foreach ( $this->data as $datum ) {
            switch ( $datum[ 'type' ] ) {
                case 'heading':
                case 'paragraph':
                    $str .= $this->smartTrim(
                        sprintf("%s %s", $datum[ 'prefix' ], $datum[ 'text' ]),
                        '\s'
                    );
                    break;
                case 'numeric_list':
                case 'unordered_list':
                    $str .= $this->compileGivenList($datum[ 'tree' ], $datum[ 'type' ] == 'numeric_list');
                    break;
                case 'quotes':
                    $str .= implode("\n>\n", array_map(fn(string $item) => '>' . $item, $datum[ 'list' ]));
                    break;
                case 'block':
                    $str .= "```${datum['lang']}\n${datum['code']}\n```";
                    break;
                case 'link':
                    $name = $datum[ 'name' ] ?? $datum[ 'url' ];
                    $str .= "[$name](${datum['url']})";
                    break;
                case 'img':
                    $name = $datum[ 'alt' ] ?? $datum[ 'url' ];
                    $str .= "![$name](${datum['url']} \"${datum['title']}\")";
                    break;
                default:
                    $str .= $datum[ 'raw' ];
                    break;
            }

            $str .= "\n\n";
        }

        $this->data = [];

        return $this->smartTrim($str, '\\n');
    }

    /**
     * Return current instance to interact directly from facade.
     *
     * @return \AttractCores\PostmanDocumentation\MarkdownDocs
     */
    public function new() : MarkdownDocs
    {
        return new static;
    }

    /**
     * Return string interpretation of current object.
     *
     * @return string
     */
    public function toString() : string
    {
        return (string) $this;
    }

    /**
     * Compile given list into markdown syntax.
     *
     * @param array  $tree
     * @param bool   $isNumeric
     * @param string $prefix
     * @param string $stepBase
     *
     * @return string
     */
    protected function compileGivenList(array $tree, bool $isNumeric = false, string $prefix = "", string $stepBase = '') : string
    {
        $compiledTree = '';
        $i = 1;

        foreach ( $tree as $index => $item ) {
            $indexBase = $stepBase . ( $isNumeric ? $i . '.' : '-' );

            $compiledTree .= "$prefix";

            if ( is_string($index) && is_array($item) ) {
                $compiledTree .= sprintf("%s %s", $indexBase, $index);
                $compiledTree .= "\n";
                $compiledTree .= $this->compileGivenList(
                    $item, $isNumeric, "\t" . $prefix . " "
                );
            } else {
                $compiledTree .= sprintf("%s %s", $indexBase, $item);
                $compiledTree .= "\n";
            }

            $i++;
        }

        return $compiledTree;
    }

    /**
     * Smart trim by pattern in both directions.
     *
     * @param string $string
     * @param string $except
     * @param string $replace
     *
     * @return string
     */
    public function smartTrim(string $string, string $except, $replace = '') : string
    {
        return preg_replace('/(^' . $except . '+|(?:' . $except . ')*$)/', $replace, $string);
    }

}
