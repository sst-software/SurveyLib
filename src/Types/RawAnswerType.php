<?php

declare(strict_types=1);

namespace Sst\SurveyLibBundle\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;

class RawAnswerType extends Type
{
    public const RAW_ANSWER_DATA_TYPE = 'rawAnswer';

    public function getName(): string
    {
        return self::RAW_ANSWER_DATA_TYPE;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): mixed
    {
        if ($value === null) {
            return null;
        }

        $value = is_resource($value) ? stream_get_contents($value) : $value;

        set_error_handler(function (int $code, string $message): bool {
            throw ConversionException::conversionFailedUnserialization($this->getName(), $message);
        });

        try {
            return unserialize($value);
        } finally {
            restore_error_handler();
        }
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): mixed
    {
        return serialize($value);
    }

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getClobTypeDeclarationSQL($column);
    }
}
