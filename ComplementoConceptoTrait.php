<?php

/**
 * @copyright Copyright (c) 2017 Carlos Ramos
 * @package ktaris-cfdi
 * @version 0.1.0
 */

namespace ktaris\precfdi;

trait ComplementoConceptoTrait
{
    protected function agregarNamespaceDeComplementoConcepto($cfdiObj)
    {
        if ($cfdiObj->tieneInstitucionesPrivadas) {
            $this->agregarNamespaceDeInstitucionesEducativas();
        }
    }

    protected function crearNodoConceptoComplemento($nodoXml, $cfdiObj)
    {
        $nodo = $nodoXml->addChild('cfdi:ComplementoConcepto');

        if ($cfdiObj->tieneInstitucionesEducativas) {
            $this->crearNodoConceptoComplementoInstitucionesEducativas($nodo, $cfdiObj->InstitucionesEducativas);
        }
    }

    // ==================================================================
    //
    // Intituciones Educativas
    //
    // ------------------------------------------------------------------

    protected function agregarNamespaceDeInstitucionesEducativas()
    {
        $this->agregarNamespace('iedu', 'http://www.sat.gob.mx/iedu');

        $this->agregarUbicacion('http://www.sat.gob.mx/iedu');
        $this->agregarUbicacion('http://www.sat.gob.mx/sitio_internet/cfd/iedu/iedu.xsd');
    }

    protected function crearNodoConceptoComplementoInstitucionesEducativas($nodoXml, $cfdiObj)
    {
        $nodo = $nodoXml->addChild('iedu:instEducativas', null, 'iedu');
        $this->vaciarAtributos($nodo, $cfdiObj);
    }
}
