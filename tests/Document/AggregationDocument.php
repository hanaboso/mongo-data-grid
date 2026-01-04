<?php declare(strict_types=1);

namespace MongoDataGridTests\Document;

use DateTime;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * Class AggregationDocument
 *
 * @package MongoDataGridTests\Document
 */
#[ODM\Document]
final class AggregationDocument
{

    /**
     * @var string
     */
    #[ODM\Id]
    private string $id;

    /**
     * @var string
     */
    #[ODM\Field]
    private string $string;

    /**
     * @var integer
     */
    #[ODM\Field]
    private int $int;

    /**
     * @var float
     */
    #[ODM\Field]
    private float $float;

    /**
     * @var bool
     */
    #[ODM\Field]
    private bool $bool;

    /**
     * @var DateTime
     */
    #[ODM\Field]
    private DateTime $date;

    /**
     * AggregationDocument constructor.
     */
    public function __construct()
    {
        $this->int    = 0;
        $this->float  = 0.0;
        $this->bool   = TRUE;
        $this->string = '';
        $this->date   = new DateTime();
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getString(): string
    {
        return $this->string;
    }

    /**
     * @param string $string
     *
     * @return AggregationDocument
     */
    public function setString(string $string): self
    {
        $this->string = $string;

        return $this;
    }

    /**
     * @return int
     */
    public function getInt(): int
    {
        return $this->int;
    }

    /**
     * @param int $int
     *
     * @return AggregationDocument
     */
    public function setInt(int $int): self
    {
        $this->int = $int;

        return $this;
    }

    /**
     * @return float
     */
    public function getFloat(): float
    {
        return $this->float;
    }

    /**
     * @param float $float
     *
     * @return AggregationDocument
     */
    public function setFloat(float $float): self
    {
        $this->float = $float;

        return $this;
    }

    /**
     * @return bool
     */
    public function isBool(): bool
    {
        return $this->bool;
    }

    /**
     * @param bool $bool
     *
     * @return AggregationDocument
     */
    public function setBool(bool $bool): self
    {
        $this->bool = $bool;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getDate(): DateTime
    {
        return $this->date;
    }

    /**
     * @param DateTime $date
     *
     * @return AggregationDocument
     */
    public function setDate(DateTime $date): self
    {
        $this->date = $date;

        return $this;
    }

}
