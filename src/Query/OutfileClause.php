<?php namespace Database\Query;

class OutfileClause
{
    /**
     * @var string
     */
    public $type;

    /**
     * @var string
     */
    public $file;

    /**
     * @var string
     */
    public $escapedBy;

    /**
     * @var string
     */
    public $enclosedBy;

    /**
     * @var bool
     */
    public $optionallyEnclosedBy;

    /**
     * @var string
     */
    public $fieldsTerminatedBy;

    /**
     * @var string
     */
    public $linesTerminatedBy;

    /**
     * Create a new outfile clause instance.
     *
     * @param $file
     */
    public function __construct($file, $type)
    {
        $this->file = $file;

        $this->type = $type;
    }

    /**
     * @param $character
     * @return $this
     */
    public function escapedBy($character)
    {
        $this->escapedBy = $character;

        return $this;
    }

    /**
     * @param $character
     * @param bool $optionally
     * @return $this
     */
    public function enclosedBy($character, $optionally = false)
    {
        $this->optionallyEnclosedBy = $optionally;

        $this->enclosedBy = $character;

        return $this;
    }

    /**
     * @param $character
     * @return $this
     */
    public function fieldsTerminatedBy($character)
    {
        $this->fieldsTerminatedBy = $character;

        return $this;
    }

    /**
     * @param $character
     * @return $this
     */
    public function linesTerminatedBy($character)
    {
        $this->linesTerminatedBy = $character;

        return $this;
    }

}
