<?php
namespace BitPrepared\Bundle\FormazioneBundle\Domain\ValueObject;

use BitPrepared\Bundle\FormazioneBundle\Domain\ValueObject;

/**
 * Generated Class
 */
abstract class TipologiaCampo
{
    /**
     * nome esplicativo della enum
     * @var string
     */
    protected $name;

    /**
     * singleton
     * @var BitPrepared\Bundle\FormazioneBundle\Domain\ValueObject\TipologiaCampo
     */
    protected static $instance;


    /**
     * @return BitPrepared\Bundle\FormazioneBundle\Domain\ValueObject\TipologiaCampo|null
     */
    final public static function parseString($parseString)
    {
        $class_name = 'BitPrepared\Bundle\FormazioneBundle\Domain\ValueObject\TipologiaCampo'.'\\'.$parseString;
        if (class_exists($class_name)) {
            $x = $class_name::instance();
            return $x;
        };
        return null;
    }


    /**
     * @return string
     */
    final public function getName()
    {
        return $this->name;
    }
}
