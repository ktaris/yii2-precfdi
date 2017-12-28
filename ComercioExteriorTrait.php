<?php

/**
 * @copyright Copyright (c) 2017 Carlos Ramos
 * @package ktaris-cfdi
 * @version 0.1.0
 */

namespace ktaris\precfdi;

trait ComercioExteriorTrait
{
    protected function agregarNamespaceDeComercioExterior()
    {
        $this->agregarNamespace('cce11', 'http://www.sat.gob.mx/ComercioExterior11');

        $this->agregarUbicacion('http://www.sat.gob.mx/ComercioExterior11');
        $this->agregarUbicacion('http://www.sat.gob.mx/sitio_internet/cfd/ComercioExterior11/ComercioExterior11.xsd');
    }

    protected function crearNodoComercioExterior($nodoXml, $cfdiObj)
    {
        $nodo = $nodoXml->addChild('cce11:ComercioExterior', null, 'cce11');
        $this->vaciarAtributos($nodo, $cfdiObj);

        if (!empty($cfdiObj->Emisor)) {
            $this->crearNodoComercioExteriorEmisor($nodo, $cfdiObj->Emisor);
        }

        if (!empty($cfdiObj->Receptor)) {
            $this->crearNodoComercioExteriorReceptor($nodo, $cfdiObj->Receptor);
        }

        if (!empty($cfdiObj->Mercancias)) {
            $this->crearNodoComercioExteriorMercancias($nodo, $cfdiObj->Mercancias);
        }
    }

    protected function crearNodoComercioExteriorEmisor($nodoXml, $cfdiObj)
    {
        $nodo = $nodoXml->addChild('cce11:Emisor');
        $this->vaciarAtributos($nodo, $cfdiObj);

        if (!empty($cfdiObj->Domicilio)) {
            $this->crearNodoComercioExteriorDomicilio($nodo, $cfdiObj->Domicilio);
        }
    }

    protected function crearNodoComercioExteriorDomicilio($nodoXml, $cfdiObj)
    {
        $nodo = $nodoXml->addChild('cce11:Domicilio');
        $this->vaciarAtributos($nodo, $cfdiObj);
    }

    protected function crearNodoComercioExteriorReceptor($nodoXml, $cfdiObj)
    {
        $nodo = $nodoXml->addChild('cce11:Receptor');
        $this->vaciarAtributos($nodo, $cfdiObj);

        if (!empty($cfdiObj->Domicilio)) {
            $this->crearNodoComercioExteriorDomicilio($nodo, $cfdiObj->Domicilio);
        }
    }

    protected function crearNodoComercioExteriorMercancias($nodoXml, $cfdiObj)
    {
        $nodo = $nodoXml->addChild('cce11:Mercancias');
        foreach ($cfdiObj as $model) {
            $this->crearNodoComercioExteriorMercancia($nodo, $model);
        }
    }

    protected function crearNodoComercioExteriorMercancia($nodoXml, $cfdiObj)
    {
        $nodo = $nodoXml->addChild('cce11:Mercancia');
        $this->vaciarAtributos($nodo, $cfdiObj);
    }
}
