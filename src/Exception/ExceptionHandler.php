<?php namespace Database\Exception;

class ExceptionHandler implements ExceptionHandlerInterface
{
    /**
     * @var array
     */
    private $parameters;

    /**
     * @param array $parameters
     */
    public function __construct(array $parameters = array())
    {
        $this->parameters = $parameters;
    }

    /**
     * @param $query
     * @param array $bindings
     * @param \Exception $previousException
     */
    public function handle($query, array $bindings = array(), \Exception $previousException)
    {
        $parameters = $this->parameters;

        $parameters['SQL'] = $this->replaceArray('\?', $bindings, $query);

        $message =  $previousException->getMessage() . PHP_EOL . $this->formatArrayParameters($parameters);

        throw new QueryException($message, $previousException);
    }

    /**
     * @param array $parameters
     * @return string
     */
    private function formatArrayParameters(array $parameters)
    {
        foreach($parameters as $name => $value)
        {
            $parameters[$name] = $name . ': ' . $value;
        }

        return implode(PHP_EOL, $parameters);
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
