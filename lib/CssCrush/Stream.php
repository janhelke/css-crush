<?php
/**
 *
 *  Stream sugar.
 *
 */
namespace CssCrush;

class Stream
{
    public function __construct($str)
    {
        $this->raw = $str;
    }

    public function __toString()
    {
        return $this->raw;
    }

    public static function endsWith($haystack, $needle)
    {
        return substr($haystack, -strlen($needle)) === $needle;
    }

    public function update($str)
    {
        $this->raw = $str;

        return $this;
    }

    public function substr($start, $length = null)
    {
        if (! isset($length)) {

            return substr($this->raw, $start);
        }
        else {

            return substr($this->raw, $start, $length);
        }
    }

    public function matchAll($patt, $offset = 0)
    {
        return Regex::matchAll($patt, $this->raw, $offset);
    }

    public function restore($types, $release = false, $callback = null)
    {
        $this->raw = Crush::$process->tokens->restore($this->raw, $types, $release, $callback);

        return $this;
    }

    public function replaceHash($replacements)
    {
        if ($replacements) {
            $this->raw = str_replace(
                array_keys($replacements),
                array_values($replacements),
                $this->raw);
        }
        return $this;
    }

    public function pregReplaceHash($replacements)
    {
        if ($replacements) {
            $this->raw = preg_replace(
                array_keys($replacements),
                array_values($replacements),
                $this->raw);
        }
        return $this;
    }

    public function pregReplaceCallback($patt, $callback)
    {
        $this->raw = preg_replace_callback($patt, $callback, $this->raw);
        return $this;
    }

    public function append($append)
    {
        $this->raw .= $append;
        return $this;
    }

    public function prepend($prepend)
    {
        $this->raw = $prepend . $this->raw;
        return $this;
    }

    public function splice($replacement, $offset, $length = null)
    {
        $this->raw = substr_replace($this->raw, $replacement, $offset, $length);
        return $this;
    }

    public function trim()
    {
        $this->raw = trim($this->raw);
        return $this;
    }

    public function rTrim()
    {
        $this->raw = rtrim($this->raw);
        return $this;
    }

    public function lTrim()
    {
        $this->raw = ltrim($this->raw);
        return $this;
    }

    public function captureDirectives($directive, $parse_options = array())
    {
        $directive = ltrim($directive, '@');
        $parse_options += array(
            'keyed' => true,
            'lowercase_keys' => true,
            'ignore_directives' => true,
            'singles' => false,
            'flatten' => false,
        );

        if ($parse_options['singles']) {
            $patt = Regex::make('~@' . $directive . '(?:\s*{{ block }}|\s+(?<name>{{ ident }})\s+(?<value>[^;]+)\s*;)~iS');
        }
        else {
            $patt = Regex::make('~@' . $directive . '\s*{{ block }}~iS');
        }

        $captured_directives = array();
        $this->pregReplaceCallback($patt, function ($m) use (&$captured_directives, $parse_options) {
            if (isset($m['name'])) {
                $name = $parse_options['lowercase_keys'] ? strtolower($m['name']) : $m['name'];
                $captured_directives[$name] = $m['value'];
            }
            else {
                $captured_directives = DeclarationList::parse($m['block_content'], $parse_options) + $captured_directives;
            }
            return '';
        });

        return $captured_directives;
    }
}
