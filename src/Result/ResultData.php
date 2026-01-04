<?php declare(strict_types=1);

namespace Hanaboso\MongoDataGrid\Result;

use Hanaboso\Utils\Date\DateTimeUtils;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

/**
 * Class ResultData
 *
 * @package Hanaboso\MongoDataGrid\Result
 */
class ResultData
{

    /**
     * ResultData constructor.
     *
     * @param mixed[] $data
     * @param string  $dateFormat
     */
    public function __construct(
        private readonly array $data,
        private readonly string $dateFormat = DateTimeUtils::DATE_TIME_UTC,
    )
    {
    }

    /**
     * @return mixed[]
     */
    public function toArray(): array
    {
        $data = $this->data;

        foreach ($data as $key => $item) {
            foreach ($item as $innerKey => $innerItem) {
                if (is_object($innerItem)) {
                    switch ($innerItem::class) {
                        case ObjectId::class:
                            /** @var ObjectId $tt */
                            $tt               = $innerItem;
                            $data[$key]['id'] = (string) $tt;
                            unset($data[$key][$innerKey]);

                            break;
                        case UTCDateTime::class:
                            /** @var UTCDateTime $tt */
                            $tt                    = $innerItem;
                            $data[$key][$innerKey] = $tt->toDateTime()->format($this->dateFormat);

                            break;
                    }
                } else {
                    switch ($innerKey) {
                        case '_id':
                            $data[$key]['id'] = $data[$key][$innerKey];
                            unset($data[$key][$innerKey]);

                            break;
                    }
                }

            }
        }

        return array_values($data);
    }

}
