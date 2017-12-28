<?php
/**
 * @copyright Copyright (c) 2017 Carlos Ramos
 * @package ktaris-csd
 * @version 0.1.0
 */

namespace ktaris\precfdi;

/**
 * Excepción que representa un error en la lectura del XML del CFDI.
 *
 * @author Carlos Ramos <carlos@ramoscarlos.com>
 */
class PreCfdiException extends \Exception
{
    /**
     * @return string nombre bonito de la excepción
     */
    public function getName()
    {
        return 'Error en lectura de datos para PreCFDI';
    }
}
