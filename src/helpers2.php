<?php

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

if ( ! function_exists('append_config'))
{
	/**
	 * Assign high numeric IDs to a config item to force appending.
	 *
	 * @param  array  $array
	 * @return array
	 */
	function append_config(array $array)
	{
		$start = 9999;

		foreach ($array as $key => $value)
		{
			if (is_numeric($key))
			{
				$start++;

				$array[$start] = array_pull($array, $key);
			}
		}

		return $array;
	}
}

if ( ! function_exists('array_add'))
{
	/**
	 * Add an element to an array using "dot" notation if it doesn't exist.
	 *
	 * @param  array   $array
	 * @param  string  $key
	 * @param  mixed   $value
	 * @return array
	 */
	function array_add($array, $key, $value)
	{
		return Arr::add($array, $key, $value);
	}
}

if ( ! function_exists('array_build'))
{
	/**
	 * Build a new array using a callback.
	 *
	 * @param  array     $array
	 * @param  \Closure  $callback
	 * @return array
	 */
	function array_build($array, Closure $callback)
	{
		return Arr::build($array, $callback);
	}
}

if ( ! function_exists('array_divide'))
{
	/**
	 * Divide an array into two arrays. One with keys and the other with values.
	 *
	 * @param  array  $array
	 * @return array
	 */
	function array_divide($array)
	{
		return Arr::divide($array);
	}
}

if ( ! function_exists('array_dot'))
{
	/**
	 * Flatten a multi-dimensional associative array with dots.
	 *
	 * @param  array   $array
	 * @param  string  $prepend
	 * @return array
	 */
	function array_dot($array, $prepend = '')
	{
		return Arr::dot($array, $prepend);
	}
}

if ( ! function_exists('array_except'))
{
	/**
	 * Get all of the given array except for a specified array of items.
	 *
	 * @param  array  $array
	 * @param  array|string  $keys
	 * @return array
	 */
	function array_except($array, $keys)
	{
		return Arr::except($array, $keys);
	}
}

if ( ! function_exists('array_fetch'))
{
	/**
	 * Fetch a flattened array of a nested array element.
	 *
	 * @param  array   $array
	 * @param  string  $key
	 * @return array
	 */
	function array_fetch($array, $key)
	{
		return Arr::fetch($array, $key);
	}
}

if ( ! function_exists('array_first'))
{
	/**
	 * Return the first element in an array passing a given truth test.
	 *
	 * @param  array     $array
	 * @param  \Closure  $callback
	 * @param  mixed     $default
	 * @return mixed
	 */
	function array_first($array, $callback, $default = null)
	{
		return Arr::first($array, $callback, $default);
	}
}

if ( ! function_exists('array_last'))
{
	/**
	 * Return the last element in an array passing a given truth test.
	 *
	 * @param  array     $array
	 * @param  \Closure  $callback
	 * @param  mixed     $default
	 * @return mixed
	 */
	function array_last($array, $callback, $default = null)
	{
		return Arr::last($array, $callback, $default);
	}
}

if ( ! function_exists('array_flatten'))
{
	/**
	 * Flatten a multi-dimensional array into a single level.
	 *
	 * @param  array  $array
	 * @return array
	 */
	function array_flatten($array)
	{
		return Arr::flatten($array);
	}
}

if ( ! function_exists('array_forget'))
{
	/**
	 * Remove one or many array items from a given array using "dot" notation.
	 *
	 * @param  array  $array
	 * @param  array|string  $keys
	 * @return void
	 */
	function array_forget(&$array, $keys)
	{
		return Arr::forget($array, $keys);
	}
}

if ( ! function_exists('array_get'))
{
	/**
	 * Get an item from an array using "dot" notation.
	 *
	 * @param  array   $array
	 * @param  string  $key
	 * @param  mixed   $default
	 * @return mixed
	 */
	function array_get($array, $key, $default = null)
	{
		return Arr::get($array, $key, $default);
	}
}

if ( ! function_exists('array_only'))
{
	/**
	 * Get a subset of the items from the given array.
	 *
	 * @param  array  $array
	 * @param  array|string  $keys
	 * @return array
	 */
	function array_only($array, $keys)
	{
		return Arr::only($array, $keys);
	}
}

if ( ! function_exists('array_pluck'))
{
	/**
	 * Pluck an array of values from an array.
	 *
	 * @param  array   $array
	 * @param  string  $value
	 * @param  string  $key
	 * @return array
	 */
	function array_pluck($array, $value, $key = null)
	{
		return Arr::pluck($array, $value, $key);
	}
}

if ( ! function_exists('array_pull'))
{
	/**
	 * Get a value from the array, and remove it.
	 *
	 * @param  array   $array
	 * @param  string  $key
	 * @param  mixed   $default
	 * @return mixed
	 */
	function array_pull(&$array, $key, $default = null)
	{
		return Arr::pull($array, $key, $default);
	}
}

if ( ! function_exists('array_set'))
{
	/**
	 * Set an array item to a given value using "dot" notation.
	 *
	 * If no key is given to the method, the entire array will be replaced.
	 *
	 * @param  array   $array
	 * @param  string  $key
	 * @param  mixed   $value
	 * @return array
	 */
	function array_set(&$array, $key, $value)
	{
		return Arr::set($array, $key, $value);
	}
}

if ( ! function_exists('array_sort'))
{
	/**
	 * Sort the array using the given Closure.
	 *
	 * @param  array     $array
	 * @param  \Closure  $callback
	 * @return array
	 */
	function array_sort($array, Closure $callback)
	{
		return Arr::sort($array, $callback);
	}
}

if ( ! function_exists('array_where'))
{
	/**
	 * Filter the array using the given Closure.
	 *
	 * @param  array     $array
	 * @param  \Closure  $callback
	 * @return array
	 */
	function array_where($array, Closure $callback)
	{
		return Arr::where($array, $callback);
	}
}

if ( ! function_exists('camel_case'))
{
	/**
	 * Convert a value to camel case.
	 *
	 * @param  string  $value
	 * @return string
	 */
	function camel_case($value)
	{
		return Str::camel($value);
	}
}

if ( ! function_exists('class_basename'))
{
	/**
	 * Get the class "basename" of the given object / class.
	 *
	 * @param  string|object  $class
	 * @return string
	 */
	function class_basename($class)
	{
		$class = is_object($class) ? get_class($class) : $class;

		return basename(str_replace('\\', '/', $class));
	}
}

if ( ! function_exists('class_uses_recursive'))
{
	/**
	 * Returns all traits used by a class, it's subclasses and trait of their traits
	 *
	 * @param  string  $class
	 * @return array
	 */
	function class_uses_recursive($class)
	{
		$results = [];

		foreach (array_merge([$class => $class], class_parents($class)) as $class)
		{
			$results += trait_uses_recursive($class);
		}

		return array_unique($results);
	}
}

if ( ! function_exists('data_get'))
{
	/**
	 * Get an item from an array or object using "dot" notation.
	 *
	 * @param  mixed   $target
	 * @param  string  $key
	 * @param  mixed   $default
	 * @return mixed
	 */
	function data_get($target, $key, $default = null)
	{
		if (is_null($key)) return $target;

		foreach (explode('.', $key) as $segment)
		{
			if (is_array($target))
			{
				if ( ! array_key_exists($segment, $target))
				{
					return value($default);
				}

				$target = $target[$segment];
			}
			elseif (is_object($target))
			{
				if ( ! isset($target->{$segment}))
				{
					return value($default);
				}

				$target = $target->{$segment};
			}
			else
			{
				return value($default);
			}
		}

		return $target;
	}
}

if ( ! function_exists('dd'))
{
	/**
	 * Dump the passed variables and end the script.
	 *
	 * @param  mixed
	 * @return void
	 */
	function dd()
	{
		array_map(function($x) { var_dump($x); }, func_get_args()); die;
	}
}

if ( ! function_exists('e'))
{
	/**
	 * Escape HTML entities in a string.
	 *
	 * @param  string  $value
	 * @return string
	 */
	function e($value)
	{
		return htmlentities($value, ENT_QUOTES, 'UTF-8', false);
	}
}

if ( ! function_exists('ends_with'))
{
	/**
	 * Determine if a given string ends with a given substring.
	 *
	 * @param  string  $haystack
	 * @param  string|array  $needles
	 * @return bool
	 */
	function ends_with($haystack, $needles)
	{
		return Str::endsWith($haystack, $needles);
	}
}

if ( ! function_exists('head'))
{
	/**
	 * Get the first element of an array. Useful for method chaining.
	 *
	 * @param  array  $array
	 * @return mixed
	 */
	function head($array)
	{
		return reset($array);
	}
}

if ( ! function_exists('last'))
{
	/**
	 * Get the last element from an array.
	 *
	 * @param  array  $array
	 * @return mixed
	 */
	function last($array)
	{
		return end($array);
	}
}

if ( ! function_exists('object_get'))
{
	/**
	 * Get an item from an object using "dot" notation.
	 *
	 * @param  object  $object
	 * @param  string  $key
	 * @param  mixed   $default
	 * @return mixed
	 */
	function object_get($object, $key, $default = null)
	{
		if (is_null($key) || trim($key) == '') return $object;

		foreach (explode('.', $key) as $segment)
		{
			if ( ! is_object($object) || ! isset($object->{$segment}))
			{
				return value($default);
			}

			$object = $object->{$segment};
		}

		return $object;
	}
}

if ( ! function_exists('preg_replace_sub'))
{
	/**
	 * Replace a given pattern with each value in the array in sequentially.
	 *
	 * @param  string  $pattern
	 * @param  array   $replacements
	 * @param  string  $subject
	 * @return string
	 */
	function preg_replace_sub($pattern, &$replacements, $subject)
	{
		return preg_replace_callback($pattern, function($match) use (&$replacements)
		{
			return array_shift($replacements);

		}, $subject);
	}
}

if ( ! function_exists('snake_case'))
{
	/**
	 * Convert a string to snake case.
	 *
	 * @param  string  $value
	 * @param  string  $delimiter
	 * @return string
	 */
	function snake_case($value, $delimiter = '_')
	{
		return Str::snake($value, $delimiter);
	}
}

if ( ! function_exists('starts_with'))
{
	/**
	 * Determine if a given string starts with a given substring.
	 *
	 * @param  string  $haystack
	 * @param  string|array  $needles
	 * @return bool
	 */
	function starts_with($haystack, $needles)
	{
		return Str::startsWith($haystack, $needles);
	}
}

if ( ! function_exists('str_contains'))
{
	/**
	 * Determine if a given string contains a given substring.
	 *
	 * @param  string  $haystack
	 * @param  string|array  $needles
	 * @return bool
	 */
	function str_contains($haystack, $needles)
	{
		return Str::contains($haystack, $needles);
	}
}

if ( ! function_exists('str_finish'))
{
	/**
	 * Cap a string with a single instance of a given value.
	 *
	 * @param  string  $value
	 * @param  string  $cap
	 * @return string
	 */
	function str_finish($value, $cap)
	{
		return Str::finish($value, $cap);
	}
}

if ( ! function_exists('str_is'))
{
	/**
	 * Determine if a given string matches a given pattern.
	 *
	 * @param  string  $pattern
	 * @param  string  $value
	 * @return bool
	 */
	function str_is($pattern, $value)
	{
		return Str::is($pattern, $value);
	}
}

if ( ! function_exists('str_limit'))
{
	/**
	 * Limit the number of characters in a string.
	 *
	 * @param  string  $value
	 * @param  int     $limit
	 * @param  string  $end
	 * @return string
	 */
	function str_limit($value, $limit = 100, $end = '...')
	{
		return Str::limit($value, $limit, $end);
	}
}

if ( ! function_exists('str_plural'))
{
	/**
	 * Get the plural form of an English word.
	 *
	 * @param  string  $value
	 * @param  int     $count
	 * @return string
	 */
	function str_plural($value, $count = 2)
	{
		return Str::plural($value, $count);
	}
}

if ( ! function_exists('str_random'))
{
	/**
	 * Generate a more truly "random" alpha-numeric string.
	 *
	 * @param  int  $length
	 * @return string
	 *
	 * @throws \RuntimeException
	 */
	function str_random($length = 16)
	{
		return Str::random($length);
	}
}

if ( ! function_exists('str_replace_array'))
{
	/**
	 * Replace a given value in the string sequentially with an array.
	 *
	 * @param  string  $search
	 * @param  array   $replace
	 * @param  string  $subject
	 * @return string
	 */
	function str_replace_array($search, array $replace, $subject)
	{
		foreach ($replace as $value)
		{
			$subject = preg_replace('/'.$search.'/', $value, $subject, 1);
		}

		return $subject;
	}
}

if ( ! function_exists('str_singular'))
{
	/**
	 * Get the singular form of an English word.
	 *
	 * @param  string  $value
	 * @return string
	 */
	function str_singular($value)
	{
		return Str::singular($value);
	}
}

if ( ! function_exists('studly_case'))
{
	/**
	 * Convert a value to studly caps case.
	 *
	 * @param  string  $value
	 * @return string
	 */
	function studly_case($value)
	{
		return Str::studly($value);
	}
}

if ( ! function_exists('trait_uses_recursive'))
{
	/**
	 * Returns all traits used by a trait and its traits
	 *
	 * @param  string  $trait
	 * @return array
	 */
	function trait_uses_recursive($trait)
	{
		$traits = class_uses($trait);

		foreach ($traits as $trait)
		{
			$traits += trait_uses_recursive($trait);
		}

		return $traits;
	}
}

if ( ! function_exists('value'))
{
	/**
	 * Return the default value of the given value.
	 *
	 * @param  mixed  $value
	 * @return mixed
	 */
	function value($value)
	{
		return $value instanceof Closure ? $value() : $value;
	}
}

if ( ! function_exists('with'))
{
	/**
	 * Return the given object. Useful for chaining.
	 *
	 * @param  mixed  $object
	 * @return mixed
	 */
	function with($object)
	{
		return $object;
	}
}

<?php

define('DATE_SQL',"Y-m-d H:i:s");

if ( ! function_exists('date_between'))
{
    /**
     * Create a folder in a thread safe way
     * Between 'is_dir' and 'mkdir' another thread could have created a folder.
     * This can cause the system to raise an unwarrented error
     *
     * Returns TRUE if folder was created, NULL if folder already exists
     *
     * Throws exception on any other error
     *
     * @param  array   $array
     * @param  string  $key
     * @param  mixed   $value
     * @return array
     */
    function date_between($date, $start, $end)
    {
        $date instanceof DateTime or $date = new DateTime($date);
        $start instanceof DateTime or $start = new DateTime($start);
        $end instanceof DateTime or $end = new DateTime($end);

        return $date >= $start && $date <= $end;
    }
}

if ( ! function_exists('mkdir_thread_safe'))
{
    /**
     * Create a folder in a thread safe way
     * Between 'is_dir' and 'mkdir' another thread could have created a folder.
     * This can cause the system to raise an unwarrented error
     *
     * Returns TRUE if folder was created, NULL if folder already exists
     *
     * Throws exception on any other error
     *
     * @param  array   $array
     * @param  string  $key
     * @param  mixed   $value
     * @return array
     */
    function mkdir_thread_safe($dir, $permissions = 0777, $recursive = false)
    {
        if(is_dir($dir)) return;

        try {
            mkdir($dir, $permissions, $recursive);
        }
        catch (\ErrorException $e){

            if(is_dir($dir)) return;

            throw $e;
        }

        return true;
    }
}

if ( ! function_exists('symlink_thread_safe'))
{
    /**
     * Create a folder in a thread safe way
     * Between 'is_dir' and 'mkdir' another thread could have created a folder.
     * This can cause the system to raise an unwarrented error
     *
     * Returns TRUE if folder was created, NULL if folder already exists
     *
     * Throws exception on any other error
     *
     * @param  array   $array
     * @param  string  $key
     * @param  mixed   $value
     * @return array
     */
    function symlink_thread_safe($target, $link)
    {
        if(is_link($link)) return;

        try {
            symlink($target, $link);
        }
        catch (\ErrorException $e){

            if(is_link($link)) return;

            throw $e;
        }

        return true;
    }
}

if ( ! function_exists('rmdir_thread_safe'))
{
    /**
     * Unlink a file in a thread safe way
     * Between 'file_exists' and 'unlink' another thread could have removed the folder.
     * This can cause the system to raise an unwarrented error
     *
     * Returns TRUE if file was delete, NULL if file didn't exist
     *
     * Throws exception on any other error
     *
     * @param  string   $file The file name
     * @return array
     */
    function rmdir_thread_safe($file)
    {
        if(!is_dir($file)) return;

        try {
            rmdir($file);
        }
        catch (\ErrorException $e){

            if(!is_dir($file)) return;

            throw $e;
        }

        return true;
    }
}

if ( ! function_exists('unlink_thread_safe'))
{
    /**
     * Unlink a file in a thread safe way
     * Between 'file_exists' and 'unlink' another thread could have removed the folder.
     * This can cause the system to raise an unwarrented error
     *
     * Returns TRUE if file was delete, NULL if file didn't exist
     *
     * Throws exception on any other error
     *
     * @param  string   $file The file name
     * @return array
     */
    function unlink_thread_safe($file)
    {
        if(!file_exists($file)) return;

        try {
            unlink($file);
        }
        catch (\ErrorException $e){

            if(!file_exists($file)) return;

            throw $e;
        }

        return true;
    }
}

if ( ! function_exists('rrmdir_thread_safe'))
{
    /**
     * Unlink a file in a thread safe way
     * Between 'file_exists' and 'unlink' another thread could have removed the folder.
     * This can cause the system to raise an unwarrented error
     *
     * Returns TRUE if file was delete, NULL if file didn't exist
     *
     * Throws exception on any other error
     *
     * @param  string   $file The file name
     * @return array
     */
    function rrmdir_thread_safe($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (filetype($dir."/".$object) == "dir") rmdir_thread_safe($dir."/".$object); else unlink_thread_safe($dir."/".$object);
                }
            }
            reset($objects);
            rmdir_thread_safe($dir);
        }
    }
}

if ( ! function_exists('array_add'))
{
    /**
     * Add an element to an array if it doesn't exist.
     *
     * @param  array   $array
     * @param  string  $key
     * @param  mixed   $value
     * @return array
     */
    function array_add($array, $key, $value)
    {
        if ( ! isset($array[$key])) $array[$key] = $value;

        return $array;
    }
}

if ( ! function_exists('array_build'))
{
    /**
     * Build a new array using a callback.
     *
     * @param  array  $array
     * @param  \Closure  $callback
     * @return array
     */
    function array_build($array, Closure $callback)
    {
        $results = array();

        foreach ($array as $key => $value)
        {
            list($innerKey, $innerValue) = call_user_func($callback, $key, $value);

            $results[$innerKey] = $innerValue;
        }

        return $results;
    }
}

if ( ! function_exists('array_divide'))
{
    /**
     * Divide an array into two arrays. One with keys and the other with values.
     *
     * @param  array  $array
     * @return array
     */
    function array_divide($array)
    {
        return array(array_keys($array), array_values($array));
    }
}

if ( ! function_exists('array_dot'))
{
    /**
     * Flatten a multi-dimensional associative array with dots.
     *
     * @param  array   $array
     * @param  string  $prepend
     * @return array
     */
    function array_dot($array, $prepend = '')
    {
        $results = array();

        foreach ($array as $key => $value)
        {
            if (is_array($value))
            {
                $results = array_merge($results, array_dot($value, $prepend.$key.'.'));
            }
            else
            {
                $results[$prepend.$key] = $value;
            }
        }

        return $results;
    }
}

if ( ! function_exists('array_except'))
{
    /**
     * Get all of the given array except for a specified array of items.
     *
     * @param  array  $array
     * @param  array  $keys
     * @return array
     */
    function array_except($array, $keys)
    {
        return array_diff_key($array, array_flip((array) $keys));
    }
}

if ( ! function_exists('array_fetch'))
{
    /**
     * Fetch a flattened array of a nested array element.
     *
     * @param  array   $array
     * @param  string  $key
     * @return array
     */
    function array_fetch($array, $key)
    {
        foreach (explode('.', $key) as $segment)
        {
            $results = array();

            foreach ($array as $value)
            {
                $value = (array) $value;

                $results[] = $value[$segment];
            }

            $array = array_values($results);
        }

        return array_values($results);
    }
}

if ( ! function_exists('array_first'))
{
    /**
     * Return the first element in an array passing a given truth test.
     *
     * @param  array    $array
     * @param  Closure  $callback
     * @param  mixed    $default
     * @return mixed
     */
    function array_first($array, $callback, $default = null)
    {
        foreach ($array as $key => $value)
        {
            if (call_user_func($callback, $key, $value)) return $value;
        }

        return value($default);
    }
}

if ( ! function_exists('array_flatten'))
{
    /**
     * Flatten a multi-dimensional array into a single level.
     *
     * @param  array  $array
     * @return array
     */
    function array_flatten($array)
    {
        $return = array();

        array_walk_recursive($array, function($x) use (&$return) { $return[] = $x; });

        return $return;
    }
}

if ( ! function_exists('array_forget'))
{
    /**
     * Remove an array item from a given array using "dot" notation.
     *
     * @param  array   $array
     * @param  string  $key
     * @return void
     */
    function array_forget(&$array, $key)
    {
        $keys = explode('.', $key);

        while (count($keys) > 1)
        {
            $key = array_shift($keys);

            if ( ! isset($array[$key]) or ! is_array($array[$key]))
            {
                return;
            }

            $array =& $array[$key];
        }

        unset($array[array_shift($keys)]);
    }
}

if ( ! function_exists('array_get'))
{
    /**
     * Get an item from an array using "dot" notation.
     *
     * @param  array   $array
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    function array_get($array, $key, $default = null)
    {
        if (is_null($key)) return $array;

        if (isset($array[$key])) return $array[$key];

        foreach (explode('.', $key) as $segment)
        {
            if ( ! is_array($array) or ! array_key_exists($segment, $array))
            {
                return value($default);
            }

            $array = $array[$segment];
        }

        return $array;
    }
}

if ( ! function_exists('array_only'))
{
    /**
     * Get a subset of the items from the given array.
     *
     * @param  array  $array
     * @param  array  $keys
     * @return array
     */
    function array_only($array, $keys)
    {
        return array_intersect_key($array, array_flip((array) $keys));
    }
}

if ( ! function_exists('array_pluck'))
{
    /**
     * Pluck an array of values from an array.
     *
     * @param  array   $array
     * @param  string  $value
     * @param  string  $key
     * @return array
     */
    function array_pluck($array, $value, $key = null)
    {
        $results = array();

        foreach ($array as $item)
        {
            $itemValue = is_object($item) ? $item->{$value} : $item[$value];

            // If the key is "null", we will just append the value to the array and keep
            // looping. Otherwise we will key the array using the value of the key we
            // received from the developer. Then we'll return the final array form.
            if (is_null($key))
            {
                $results[] = $itemValue;
            }
            else
            {
                $itemKey = is_object($item) ? $item->{$key} : $item[$key];

                $results[$itemKey] = $itemValue;
            }
        }

        return $results;
    }
}

if ( ! function_exists('array_pull'))
{
    /**
     * Get a value from the array, and remove it.
     *
     * @param  array   $array
     * @param  string  $key
     * @return mixed
     */
    function array_pull(&$array, $key)
    {
        $value = array_get($array, $key);

        array_forget($array, $key);

        return $value;
    }
}

if ( ! function_exists('array_set'))
{
    /**
     * Set an array item to a given value using "dot" notation.
     *
     * If no key is given to the method, the entire array will be replaced.
     *
     * @param  array   $array
     * @param  string  $key
     * @param  mixed   $value
     * @return array
     */
    function array_set(&$array, $key, $value)
    {
        if (is_null($key)) return $array = $value;

        $keys = explode('.', $key);

        while (count($keys) > 1)
        {
            $key = array_shift($keys);

            // If the key doesn't exist at this depth, we will just create an empty array
            // to hold the next value, allowing us to create the arrays to hold final
            // values at the correct depth. Then we'll keep digging into the array.
            if ( ! isset($array[$key]) or ! is_array($array[$key]))
            {
                $array[$key] = array();
            }

            $array =& $array[$key];
        }

        $array[array_shift($keys)] = $value;

        return $array;
    }
}

if ( ! function_exists('dd'))
{
    /**
     * Dump the passed variables and end the script.
     *
     * @param  dynamic  mixed
     * @return void
     */
    function dd()
    {
        array_map(function($x) { var_dump($x); }, func_get_args()); die;
    }
}

if ( ! function_exists('e'))
{
    /**
     * Escape HTML entities in a string.
     *
     * @param  string  $value
     * @return string
     */
    function e($value)
    {
        return htmlentities($value, ENT_QUOTES, 'UTF-8', false);
    }
}

if ( ! function_exists('ends_with'))
{
    /**
     * Determine if a given string ends with a given needle.
     *
     * @param string $haystack
     * @param string|array $needle
     * @return bool
     */
    function ends_with($haystack, $needles)
    {
        foreach ((array) $needles as $needle)
        {
            if ($needle == substr($haystack, strlen($haystack) - strlen($needle))) return true;
        }

        return false;
    }
}

if ( ! function_exists('first'))
{
    /**
     * Get the first element of an array. Useful for method chaining.
     *
     * @param  array  $array
     * @return mixed
     */
    function first($array)
    {
        return reset($array);
    }
}


if ( ! function_exists('last'))
{
    /**
     * Get the last element from an array.
     *
     * @param  array  $array
     * @return mixed
     */
    function last($array)
    {
        return end($array);
    }
}


if ( ! function_exists('object_get'))
{
    /**
     * Get an item from an object using "dot" notation.
     *
     * @param  object  $object
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    function object_get($object, $key, $default = null)
    {
        if (is_null($key)) return $object;

        foreach (explode('.', $key) as $segment)
        {
            if ( ! is_object($object) or ! isset($object->{$segment}))
            {
                return value($default);
            }

            $object = $object->{$segment};
        }

        return $object;
    }
}

if ( ! function_exists('preg_replace_sub'))
{
    /**
     * Replace a given pattern with each value in the array in sequentially.
     *
     * @param  string  $pattern
     * @param  array   $replacements
     * @param  string  $subject
     * @return string
     */
    function preg_replace_sub($pattern, &$replacements, $subject)
    {
        return preg_replace_callback($pattern, function($match) use (&$replacements)
        {
            return array_shift($replacements);

        }, $subject);
    }
}

if ( ! function_exists('starts_with'))
{
    /**
     * Determine if a string starts with a given needle.
     *
     * @param  string  $haystack
     * @param  string|array  $needle
     * @return bool
     */
    function starts_with($haystack, $needles)
    {
        foreach ((array) $needles as $needle)
        {
            if (strpos($haystack, $needle) === 0) return true;
        }

        return false;
    }
}

if ( ! function_exists('str_contains'))
{
    /**
     * Determine if a given string contains a given sub-string.
     *
     * @param  string        $haystack
     * @param  string|array  $needle
     * @return bool
     */
    function str_contains($haystack, $needle)
    {
        foreach ((array) $needle as $n)
        {
            if (strpos($haystack, $n) !== false) return true;
        }

        return false;
    }
}

if ( ! function_exists('str_finish'))
{
    /**
     * Cap a string with a single instance of a given value.
     *
     * @param  string  $value
     * @param  string  $cap
     * @return string
     */
    function str_finish($value, $cap)
    {
        return rtrim($value, $cap).$cap;
    }
}

if ( ! function_exists('str_is'))
{
    /**
     * Determine if a given string matches a given pattern.
     *
     * @param  string  $pattern
     * @param  string  $value
     * @return bool
     */
    function str_is($pattern, $value)
    {
        if ($pattern == $value) return true;

        $pattern = preg_quote($pattern, '#');

        // Asterisks are translated into zero-or-more regular expression wildcards
        // to make it convenient to check if the strings starts with the given
        // pattern such as "library/*", making any string check convenient.
        if ($pattern !== '/')
        {
            $pattern = str_replace('\*', '.*', $pattern).'\z';
        }
        else
        {
            $pattern = '/$';
        }

        return (bool) preg_match('#^'.$pattern.'#', $value);
    }
}


if ( ! function_exists('str_random'))
{
    /**
     * Generate a "random" alpha-numeric string.
     *
     * Should not be considered sufficient for cryptography, etc.
     *
     * @param  int     $length
     * @return string
     */
    function str_random($length = 16)
    {
        if (function_exists('openssl_random_pseudo_bytes'))
        {
            $bytes = openssl_random_pseudo_bytes($length * 2);

            if ($bytes === false)
            {
                throw new \RuntimeException('Unable to generate random string.');
            }

            return substr(str_replace(array('/', '+', '='), '', base64_encode($bytes)), 0, $length);
        }

        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        return substr(str_shuffle(str_repeat($pool, 5)), 0, $length);
    }
}

if ( ! function_exists('str_replace_array'))
{
    /**
     * Replace a given value in the string sequentially with an array.
     *
     * @param  string  $search
     * @param  array  $replace
     * @param  string  $subject
     * @return string
     */
    function str_replace_array($search, array $replace, $subject)
    {
        foreach ($replace as $value)
        {
            $subject = preg_replace('/'.$search.'/', $value, $subject, 1);
        }

        return $subject;
    }
}

if ( ! function_exists('value'))
{
    /**
     * Return the default value of the given value.
     *
     * @param  mixed  $value
     * @return mixed
     */
    function value($value)
    {
        return $value instanceof Closure ? $value() : $value;
    }
}

if ( ! function_exists('detect_environment'))
{
    /**
     * Detect the application's current environment.
     *
     * @param  array|string  $environments
     * @return string
     */
    function detect_environment($environments)
    {

        // If the given environment is just a Closure, we will defer the environment check
        // to the Closure the developer has provided, which allows them to totally swap
        // the webs environment detection logic with their own custom Closure's code.
        if ($environments instanceof Closure)
        {
            return call_user_func($environments);
        }

        foreach($environments as $environment => $hosts)
        {

            // To determine the current environment, we'll simply iterate through the possible
            // environments and look for the host that matches the host for this request we
            // are currently processing here, then return back these environment's names.
            foreach ((array) $hosts as $host)
            {
                if (str_is($host, gethostname()))
                {
                    return $environment;
                }
            }
        }
    }
}
