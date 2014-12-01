<?php namespace Database\Query;

class OutfileClause
{
    protected $type;

    protected $file;

    protected $escapedBy;

    protected $optionallyEnclosedBy;

    protected $fieldsTerminatedBy;

    protected $linesTerminatedBy;

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
     * @return mixed
     */
    public function escapedBy($character)
    {
        return $this->escapedBy = $character;
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
