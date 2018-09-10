<?php

namespace Kyoushu\NorthDevonGovData\Model;

class BinCollection
{

    const TYPE_BLACK_BIN = 'black_bin';
    const TYPE_GREEN_BIN = 'green_bin';
    const TYPE_RECYCLING = 'recycling';

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $text;

    /**
     * @var \DateTimeInterface
     */
    protected $date;

    public function __construct(string $type, string $text, \DateTimeInterface $date)
    {
        $this->type = $type;
        $this->text = $text;
        $this->date = $date;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function getDate(): \DateTimeInterface
    {
        return $this->date;
    }



}