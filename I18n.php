<?php
namespace Ket;

/**
 * Internationalization (i18n) class. Provides language loading and translation
 * methods without dependencies on [gettext](http://php.net/gettext).
 *
 * Adapted from [Kohana](https://github.com/kohana/core) i18n class for standalone use
 *
 */

class I18n {

	/**
	 * @var  string   translation tables path
	 */
	protected $translationsPath = null;

	/**
	 * @var  string   target language: en-us, es-es, zh-cn, etc
	 */
	protected $target = 'en-gb';

	/**
	 * @var  string  source language: en-us, es-es, zh-cn, etc
	 */
	protected $source = 'en-gb';

	/**
	 * @var  array  cache of loaded languages
	 */
	protected $cache = array();
    
    /**
     * constructor
     *
     * @param string $source            source language
     * @param string $target            target language
     * @param string $translationsPath  path to translation table files
     */
    public function __construct($source = null, $target = null, $translationsPath = null)
    {
        if ($source) {
            $this->source($source);
        }
        if ($target) {
            $this->target($target);
        }
        if ($translationsPath) {
            $this->translationsPath($translationsPath);
        }
    }
    
    /**
     * set path to translation table files
     *
     * @param string $path 
     * @return void
     */
    public function translationsPath($path)
    {
        $this->translationsPath = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }

	/**
	 * translation function. The PHP function
	 * [strtr](http://php.net/strtr) is used for replacing parameters.
	 *
	 *    \Ket\I18n::translate('Welcome back, :user', array(':user' => $username));
	 *
	 * [!!] The target language is defined by [\Ket\I18n::$lang].
	 *
	 * @uses    \Ket\I18n::get
	 * @param   string  $string text to translate
	 * @param   array   $values values to replace in the translated text
	 * @param   string  $source   source language
	 * @return  string
	 */
	public function translate($string, array $values = NULL, $source = null)
	{
        if ( ! $source) {
            $source = $this->source;
        }

		if ($source !== $this->target)
		{
			// The message and target languages are different
			// Get the translation for this message
			$string = $this->get($string);
		}

		return empty($values) ? $string : strtr($string, $values);
	}
	
	/**
	 * Get and set the source language.
	 *
	 *     // Get the source language
	 *     $lang = \Ket\I18n::source();
	 *
	 *     // Change the source language to Spanish
	 *     \Ket\I18n::source('es-es');
	 *
	 * @param   string  $language   new language setting
	 * @return  string
	 */
	public function source($language = NULL)
	{
		if ($language)
		{
			// Normalize the language
			$this->source = $this->normalizeLanguage($language);
		}

		return $this->source;
	}

	/**
	 * Get and set the target language.
	 *
	 *     // Get the current language
	 *     $lang = \Ket\I18n::lang();
	 *
	 *     // Change the current language to Spanish
	 *     \Ket\I18n::lang('es-es');
	 *
	 * @param   string  $language   new language setting
	 * @return  string
	 */
	public function target($language = NULL)
	{
		if ($language)
		{
			// Normalize the language
			$this->target = $this->normalizeLanguage($language);
		}

		return $this->target;
	}

    protected function normalizeLanguage($language)
    {
        return strtolower(str_replace(array(' ', '_'), '-', $language));
    }

	/**
	 * Returns translation of a string. If no translation exists, the original
	 * string will be returned. No parameters are replaced.
	 *
	 *     $hello = \Ket\I18n::get('Hello friends, my name is :name');
	 *
	 * @param   string  $string text to translate
	 * @param   string  $target   target language
	 * @return  string
	 */
	public function get($string, $target = NULL)
	{
		if ( ! $target)
		{
			// Use the global target language
			$target = $this->target;
		}

		// Load the translation table for this language
		$table = $this->load($target);

		// Return the translated string if it exists
		return isset($table[$string]) ? $table[$string] : $string;
	}

	/**
	 * Returns the translation table for a given language.
	 *
	 *     // Get all defined Spanish messages
	 *     $messages = \Ket\I18n::load('es-es');
	 *
	 * @param   string  $language   language to load
	 * @return  array
	 */
	public function load($language)
	{
		if (isset($this->cache[$language]))
		{
			return $this->cache[$language];
		}

		// New translation table
		$table = array();
        $file = $this->translationsPath . $language . '.php';
        
        if (is_file($file)) {
            $table = include $file;
        }

		// Cache the translation table locally
		return $this->cache[$language] = $table;
	}
}