<?php namespace Database\Exception;

class ExceptionHandler implements ExceptionHandlerInterface
{
    /**
     * @var array
     */
    private $parameters;
    
    private $maxQueryLength = 1000;

    /**
     * @param array $parameters
     */
    public function __construct(array $parameters = array())
    {
        $this->parameters = $parameters;
    }
    
    /**
     * @param array $maxQueryLength
     */
    public function setMaxQueryLength($maxQueryLength)
    {
        $this->maxQueryLength = $maxQueryLength;
    }

    /**
     * @param $query
     * @param array $bindings
     * @param \Exception $previousException
     */
    public function handle($query = '', array $bindings = array(), \Exception $previousException)
    {
        $parameters = $this->parameters;

        if($query){
            $sql = $this->replaceArray('\?', $bindings, $query);
        
            if($this->maxQueryLength && strlen($sql) > $this->maxQueryLength){
                $sql = substr($sql, 0, $this->maxQueryLength);
            }
    
            $parameters['SQL'] = $sql;
        }
    
        $message =  $previousException->getMessage() . PHP_EOL . $this->formatArrayParameters($parameters);

        throw new QueryException($message, $previousException);
    }

    /**
     * @param array $parameters
     * @return string
     */
    private function formatArrayParameters(array $parameters)
    {
        $parameters = $this->flattenArray($parameters);

        foreach($parameters as $name => $value)
        {
            $parameters[$name] = $name . ': ' . $value;
        }

        return implode(PHP_EOL, $parameters);
    }

    /**
     * @param array $array
     * @param string $prepend
     * @return array
     */
    private function flattenArray(array $array, $prepend = '')
    {
        $results = array();

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $results = array_merge($results, $this->flattenArray($value, $prepend . $key . '.'));
            } else {
                $results[$prepend . $key] = $value;
            }
        }

        return $results;
    }

    /**
     * @param $search
     * @param array $replace
     * @param $subject
     * @return mixed
     */
    private function replaceArray($search, array $replace, $subject)
    {
        foreach ($replace as $value) {
            $subject = preg_replace('/' . $search . '/', $value, $subject, 1);
        }

        return $subject;
    }
}
