<?php

namespace GeminiLabs\SiteReviews\HelperTraits;

trait Arr
{
    /**
     * @return bool
     */
    public function compareArrays(array $arr1, array $arr2)
    {
        sort($arr1);
        sort($arr2);
        return $arr1 == $arr2;
    }

    /**
     * @param mixed $array
     * @return array
     */
    public function consolidateArray($array)
    {
        return is_array($array) || is_object($array)
            ? (array) $array
            : [];
    }

    /**
     * @return array
     */
    public function convertDotNotationArray(array $array)
    {
        $results = [];
        foreach ($array as $path => $value) {
            $results = $this->dataSet($results, $path, $value);
        }
        return $results;
    }

    /**
     * @param string $string
     * @param mixed $callback
     * @return array
     */
    public function convertStringToArray($string, $callback = null)
    {
        $array = array_map('trim', explode(',', $string));
        return $callback
            ? array_filter($array, $callback)
            : array_filter($array);
    }

    /**
     * Get a value from an array of values using a dot-notation path as reference.
     * @param array $data
     * @param string $path
     * @param mixed $fallback
     * @return mixed
     */
    public function dataGet($data, $path = '', $fallback = '')
    {
        $data = $this->consolidateArray($data);
        $keys = explode('.', $path);
        foreach ($keys as $key) {
            if (!isset($data[$key])) {
                return $fallback;
            }
            $data = $data[$key];
        }
        return $data;
    }

    /**
     * Set a value to an array of values using a dot-notation path as reference.
     * @param string $path
     * @param mixed $value
     * @return array
     */
    public function dataSet(array $data, $path, $value)
    {
        $token = strtok($path, '.');
        $ref = &$data;
        while (false !== $token) {
            $ref = $this->consolidateArray($ref);
            $ref = &$ref[$token];
            $token = strtok('.');
        }
        $ref = $value;
        return $data;
    }

    /**
     * @param bool $flattenValue
     * @param string $prefix
     * @return array
     */
    public function flattenArray(array $array, $flattenValue = false, $prefix = '')
    {
        $result = [];
        foreach ($array as $key => $value) {
            $newKey = ltrim($prefix.'.'.$key, '.');
            if ($this->isIndexedFlatArray($value)) {
                if ($flattenValue) {
                    $value = '['.implode(', ', $value).']';
                }
            } elseif (is_array($value)) {
                $result = array_merge($result, $this->flattenArray($value, $flattenValue, $newKey));
                continue;
            }
            $result[$newKey] = $value;
        }
        return $result;
    }

    /**
     * @param string $key
     * @param string $position
     * @return array
     */
    public function insertInArray(array $array, array $insert, $key, $position = 'before')
    {
        $keyPosition = intval(array_search($key, array_keys($array)));
        if ('after' == $position) {
            ++$keyPosition;
        }
        if (false !== $keyPosition) {
            $result = array_slice($array, 0, $keyPosition);
            $result = array_merge($result, $insert);
            return array_merge($result, array_slice($array, $keyPosition));
        }
        return array_merge($array, $insert);
    }

    /**
     * @param mixed $array
     * @return bool
     */
    public function isIndexedFlatArray($array)
    {
        if (!is_array($array) || array_filter($array, 'is_array')) {
            return false;
        }
        return wp_is_numeric_array($array);
    }

    /**
     * @param bool $prefixed
     * @return array
     */
    public function prefixArrayKeys(array $values, $prefixed = true)
    {
        $trim = '_';
        $prefix = $prefixed
            ? $trim
            : '';
        $prefixed = [];
        foreach ($values as $key => $value) {
            $key = trim($key);
            if (0 === strpos($key, $trim)) {
                $key = substr($key, strlen($trim));
            }
            $prefixed[$prefix.$key] = $value;
        }
        return $prefixed;
    }

    /**
     * @return array
     */
    public function removeEmptyArrayValues(array $array)
    {
        $result = [];
        foreach ($array as $key => $value) {
            if (!$value) {
                continue;
            }
            $result[$key] = is_array($value)
                ? $this->removeEmptyArrayValues($value)
                : $value;
        }
        return $result;
    }

    /**
     * @return array
     */
    public function unprefixArrayKeys(array $values)
    {
        return $this->prefixArrayKeys($values, false);
    }
}