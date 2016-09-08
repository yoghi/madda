<?php

namespace BitPrepared\Bundle\FormazioneBundle\Domain\ValueObject;

/**
 * Tipologia di campo CFM/CFT/CCG/CAM
 */
abstract class TipologiaCampo
{
    /**
     * nome esplicativo della enum
     *
     * @var string
     */
    protected $name;

    /**
     * Singleton instance for enum
     *
     * @var BitPrepared\Bundle\FormazioneBundle\Domain\ValueObject\TipologiaCampo
     */
    protected static $instance;

    /**
     * @return string
     */
    final public function getName()
    {
        return $this->name;
    }

    /**
     * @return BitPrepared\Bundle\FormazioneBundle\Domain\ValueObject\TipologiaCampo|null
     */
    final public static function parseString($parseString)
    {
        $className = 'BitPrepared\Bundle\FormazioneBundle\Domain\ValueObject\TipologiaCampo'.'\\'.$parseString;
        if (class_exists($className)) {
            $enumClass = $className::instance();

            return $enumClass;
        }
    }
}
