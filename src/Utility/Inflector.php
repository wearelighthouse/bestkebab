<?php

namespace BestKebab\Utility;

if (!defined('ABSPATH')) {
    exit;
}

class Inflector
{
    private static $_plural = [
        '/(quiz)$/i' => "$1zes",
        '/^(ox)$/i' => "$1en",
        '/([m|l])ouse$/i' => "$1ice",
        '/(matr|vert|ind)ix|ex$/i' => "$1ices",
        '/(x|ch|ss|sh)$/i' => "$1es",
        '/([^aeiouy]|qu)y$/i' => "$1ies",
        '/(hive)$/i' => "$1s",
        '/(?:([^f])fe|([lr])f)$/i' => "$1$2ves",
        '/(shea|lea|loa|thie)f$/i' => "$1ves",
        '/sis$/i' => "ses",
        '/([ti])um$/i' => "$1a",
        '/(tomat|potat|ech|her|vet)o$/i' => "$1oes",
        '/(bu)s$/i' => "$1ses",
        '/(alias)$/i' => "$1es",
        '/(octop)us$/i' => "$1i",
        '/(ax|test)is$/i' => "$1es",
        '/(us)$/i' => "$1es",
        '/s$/i' => "s",
        '/$/' => "s"
    ];
    
    private static $_singular = [
        '/(quiz)zes$/i' => "$1",
        '/(matr)ices$/i' => "$1ix",
        '/(vert|ind)ices$/i' => "$1ex",
        '/^(ox)en$/i' => "$1",
        '/(alias)es$/i' => "$1",
        '/(octop|vir)i$/i' => "$1us",
        '/(cris|ax|test)es$/i' => "$1is",
        '/(shoe)s$/i' => "$1",
        '/(o)es$/i' => "$1",
        '/(bus)es$/i' => "$1",
        '/([m|l])ice$/i' => "$1ouse",
        '/(x|ch|ss|sh)es$/i' => "$1",
        '/(m)ovies$/i' => "$1ovie",
        '/(s)eries$/i' => "$1eries",
        '/([^aeiouy]|qu)ies$/i' => "$1y",
        '/([lr])ves$/i' => "$1f",
        '/(tive)s$/i' => "$1",
        '/(hive)s$/i' => "$1",
        '/(li|wi|kni)ves$/i' => "$1fe",
        '/(shea|loa|lea|thie)ves$/i' => "$1f",
        '/(^analy)ses$/i' => "$1sis",
        '/((a)naly|(b)a|(d)iagno|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/i' => "$1$2sis",
        '/([ti])a$/i' => "$1um",
        '/(n)ews$/i' => "$1ews",
        '/(h|bl)ouses$/i' => "$1ouse",
        '/(corpse)s$/i' => "$1",
        '/(us)es$/i' => "$1",
        '/s$/i' => ""
    ];
    
    private static $_irregular = [
        'move' => 'moves',
        'foot' => 'feet',
        'goose' => 'geese',
        'sex' => 'sexes',
        'child' => 'children',
        'man' => 'men',
        'tooth' => 'teeth',
        'person' => 'people'
    ];
    
    private static $_uncountable = [
        'sheep',
        'fish',
        'deer',
        'series',
        'species',
        'money',
        'rice',
        'information',
        'equipment'
    ];

    /**
     * Performs a serious of inflections on a string
     *
     * @param string $string The string to inflect
     * @param array $functions The array of function names
     * @return string
     */
    public static function batch($string, array $functions)
    {
        foreach ($functions as $function) {
            $string = self::$function($string);
        }
        return $string;
    }

    /**
     * Returns the class from a class with namespace
     *
     * @param string $string The string to classify
     * @return string
     */
    public static function classify($string)
    {
        $string = explode('\\', $string);
        return end($string);
    }

    /**
     * Returns the input CamelCase as 'Camel Case'.
     *
     * @param string $string String to be humanized
     * @return string
     */
    public static function humanize($string)
    {
        return ucwords(str_replace('_', ' ', $string));
    }
    
    /**
     * Returns the plural inflection of a string
     *
     * @param string $string The string to pluralize
     * @return string
     */
    public static function pluralize($string)
    {
        if (in_array(strtolower($string), self::$_uncountable)) {
            return $string;
        }

        foreach (self::$_irregular as $pattern => $result) {
            $pattern = '/' . $pattern . '$/i';
            
            if (preg_match($pattern, $string)) {
                return preg_replace($pattern, $result, $string);
            }
        }
        
        foreach (self::$_plural as $pattern => $result) {
            if (preg_match($pattern, $string)) {
                return preg_replace($pattern, $result, $string);
            }
        }
        
        return $string;
    }
    
    /**
     * Returns the post type of a given controller class
     *
     * @param string $string The controller class
     * @return string
     */
    public static function postTypify($string)
    {
        return ucfirst(static::singularize(str_replace('Controller', '', static::classify($string))));
    }

    /**
     * Returns the singular inflection of a string
     *
     * @param string $string The string to sigularize
     * @return string
     */
    public static function singularize($string)
    {
        if (in_array(strtolower($string), self::$_uncountable)) {
            return $string;
        }

        foreach (self::$_irregular as $result => $pattern) {
            $pattern = '/' . $pattern . '$/i';
            
            if (preg_match($pattern, $string)) {
                return preg_replace($pattern, $result, $string);
            }
        }
        
        foreach (self::$_singular as $pattern => $result) {
            if (preg_match($pattern, $string)) {
                return preg_replace($pattern, $result, $string);
            }
        }
        
        return $string;
    }

    /**
     * Returns a string with all spaces converted to dashes
     *
     * @param string $string The string you want to slug
     * @return string
     */
    public static function slugify($string)
    {
        $quotedReplacement = preg_quote('-', '/');

        $map = [
            '/[^\s\p{Zs}\p{Ll}\p{Lm}\p{Lo}\p{Lt}\p{Lu}\p{Nd}]/mu' => ' ',
            '/[\s\p{Zs}]+/mu' => $replacement,
            sprintf('/^[%s]+|[%s]+$/', $quotedReplacement, $quotedReplacement) => '',
        ];

        return strtolower(preg_replace(array_keys($map), array_values($map), $string));
    }

    /**
     * Returns the input CamelCasedString as an underscored_string
     *
     * @param string $string The string to be underscored
     * @return string
     */
    public static function underscore($string)
    {
        $string = str_replace('-', '_', $string);
        return strtolower(preg_replace('/(?<=\\w)([A-Z])/', '_' . '\\1', $string));
    }
}
