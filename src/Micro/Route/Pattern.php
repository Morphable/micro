<?php

namespace Morphable\Micro\Route;

class Pattern
{
    const ARGUMENT_PREFIX = ':';
    const ARGUMENT_OPTIONAL = '?:';

    /** @var string */
    protected $pattern;

    /** @var array */
    protected $arguments = [];

    /** @var array */
    protected $params = [];

    /** @var string */
    protected $regex = '';

    /** @var bool */
    protected $isParsed = false;

    /**
     * construct
     *
     * @param string $pattern
     */
    public function __construct(string $pattern)
    {
        $this->pattern = self::normalize($pattern);
    }

    /**
     * normalize path
     *
     * @param string $pattern
     * @return string
     */
    public static function normalize($pattern = '')
    {
        return trim($pattern, '/');
    }

    /**
     * get route pattern
     *
     * @return string
     */
    public function getPattern()
    {
        return $this->pattern;
    }

    /**
     * add prefix to pattern
     *
     * @param string $prefix
     * @return self
     */
    public function addPrefix($prefix)
    {
        $this->pattern = self::normalize('/' . trim($prefix, '/') . '/' . trim($this->pattern));
        return $this;
    }
    
    /**
     * parse pattern
     *
     * @return self
     */
    protected function parse()
    {
        if (!$this->isParsed) {
            $this->params = self::pathToParams($this->pattern);

            foreach ($this->params as $index => $param) {
                // match on anything
                if ($param == '') {
                    $this->regex .= "\/";
                    continue;
                }

                // optional, saves as argument
                if (substr($param, 0, 2) == self::ARGUMENT_OPTIONAL) {
                    $this->regex .= "(\/[^\/]*)?";
                    $this->arguments[substr($param, 2)] = $index;
                    continue;
                }

                // match on anything and save as argument
                if ($param[0] === self::ARGUMENT_PREFIX) {
                    $this->regex .= "\/[^\/]*";
                    $this->arguments[substr($param, 1)] = $index;
                    continue;
                }

                // match on exact name
                $this->regex .= strtolower("\/{$param}");
            }

            $this->regex = "/^{$this->regex}$/";
            $this->isParsed = true;
        }

        return $this;
    }

    /**
     * get pattern
     *
     * @return string
     */
    public function getRegex()
    {
        $this->parse();

        return $this->regex;
    }

    public function getArguments()
    {
        $this->parse();

        return $this->arguments;
    }

    /**
     * create params from a path
     *
     * @param string $path
     * @return array
     */
    public static function pathToParams($path)
    {
        return explode('/', self::normalize($path));
    }
}
