<?php

namespace Psecio\PropAuth;

class Resolve
{
    /**
     * Subject for the data search
     *
     * @var mixed
     */
    private $subject;

    /**
     * Init the object with the subject provided
     *
     * @param mixed $subject
     */
    public function __construct($subject)
    {
        $this->subject = $subject;
    }

    /**
     * Execute the data search using the path provided
     *
     * @param string|array $path Path to locate the data
     * 
     * @return void
     */
    public function execute($path)
    {
        $parts = (!is_array($path)) ? explode('.', $path) : $path;        
        $source = $this->subject;

        foreach ($parts as $index => $part) {
            $source = $this->resolve($part, $source);
        }
        return $source;
    }

    /**
     * Using the path part given, locate the data on the current source
     *
     * @param string $part Path "part"
     * @param mixed $source Partial data from the subject
     * 
     * @return mixed Result of resolving the part path on the source data
     */
    public function resolve($part, $source)
    {
        if (is_array($source)) {
            $source = $this->handleArray($part, $source);
        } elseif (is_object($source)) {
            $source = $this->handleObject($part, $source);
        }

        return $source;
    }

    /**
     * Handle the location of the value when the source is an object
     *
     * @param string $path Part of the path to locate
     * @param mixed $subject Source to search
     * 
     * @return mixed Result of the search on the object
     */
    public function handleObject($path, $subject)
    {
        $subject = $this->getPropertyValue($path, $subject);
        return $subject;
    }

    /**
     * Handle the location of the value when the source is an array
     *
     * @param string $path Part of the path to locate
     * @param mixed $subject Source to search
     * 
     * @return mixed Result of the search on the array
     */
    public function handleArray($path, $subject)
    {
        $set = [];
        foreach ($subject as $subj) {
            $result = $this->resolve($path, $subj);
            if (is_array($result)) {
                $set = array_merge($set, $result);
            } else {
                $set = $result;
            }
        }
        return $set;
    }

    /**
     * Type a few options to get the property value for evaluation
     * NOTE: This is a duplicate of what's in the Enforcer class, same method name
     *
     * @param string $type Type of check being performed
     * @param object $subject Object to get the property value from
     * @return mixed Either the found property value or null if not found
     */
    public function getPropertyValue($type, $subject)
    {
        $method = 'get'.ucwords(strtolower($type));
        $propertyValue = null;

        if (($type !== 'closure' && $type !== 'method') && (isset($subject->$type) && $subject->$type !== null)) {
            $propertyValue = $subject->$type;
        } elseif (method_exists($subject, $method)) {
            $propertyValue = $subject->$method();
        } elseif (method_exists($subject, 'getProperty')) {
            $propertyValue = $subject->getProperty($type);
        }

        return $propertyValue;
    }
}
