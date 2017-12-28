<?php

/**
 * @copyright Copyright (c) 2017 Carlos Ramos
 * @package ktaris-cfdi
 * @version 0.1.0
 */

namespace ktaris\precfdi;

trait ComplementoTrait
{
    use ComercioExteriorTrait;

    protected function agregarNamespaceDeComplemento($cfdiObj)
    {
        if ($cfdiObj->tieneComercioExterior) {
            $this->agregarNamespaceDeComercioExterior();
        }
    }

    protected function crearNodoComplemento($nodoXml, $cfdiObj)
    {
        $nodo = $nodoXml->addChild('cfdi:Complemento');

        if ($cfdiObj->tieneComercioExterior) {
            $this->crearNodoComercioExterior($nodo, $cfdiObj->ComercioExterior);
        }
    }
}
