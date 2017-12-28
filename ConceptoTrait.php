<?php

/**
 * @copyright Copyright (c) 2017 Carlos Ramos
 * @package ktaris-cfdi
 * @version 0.1.0
 */

namespace ktaris\precfdi;

trait ConceptoTrait
{
    use ComplementoConceptoTrait;

    /**
     * Crea el nodo del Conceptos.
     *
     * @param SimpleXMLElement $nodoXml nodo de XML.
     * @param array            $cfdiObj arreglo con los conceptos.
     */
    protected function crearNodoConceptos($nodoXml, $cfdiObj)
    {
        $nodo = $nodoXml->addChild('cfdi:Conceptos');
        foreach ($cfdiObj as $model) {
            $nodoConcepto = $nodo->addChild('cfdi:Concepto');
            $this->crearNodoConcepto($nodoConcepto, $model);
        }
    }

    /**
     * Crea el nodo de un Concepto.
     *
     * @param SimpleXMLElement            $nodoXml nodo de XML.
     * @param {ktaris\cfdi\base\Concepto} $cfdiObj concepto con datos.
     */
    protected function crearNodoConcepto($nodoXml, $cfdiObj)
    {
        $this->vaciarAtributos($nodoXml, $cfdiObj);
        // Impuestos
        if ($cfdiObj->tieneImpuestos) {
            $nodoImpuestos = $nodoXml->addChild('cfdi:Impuestos');
            // Traslados
            if ($cfdiObj->tieneTraslados) {
                $this->crearNodoTraslados($nodoImpuestos, $cfdiObj);
            }
            // Retenciones
            if ($cfdiObj->tieneRetenciones) {
                $this->crearNodoRetenciones($nodoImpuestos, $cfdiObj);
            }
        }
        // InformacionAduanera
        if (!empty($cfdiObj->InformacionAduanera)) {
            $this->crearNodoInformacionAduanera($nodoXml, $cfdiObj->InformacionAduanera);
        }
        // ComplementoConcepto
        if (!empty($cfdiObj->ComplementoConcepto) && $cfdiObj->ComplementoConcepto->tieneComplemento) {
            $this->crearNodoConceptoComplemento($nodoXml, $cfdiObj->ComplementoConcepto);
        }
    }

    protected function crearNodoInformacionAduanera($nodoXml, $cfdiObj)
    {
        foreach ($cfdiObj as $model) {
            $nodo = $nodoXml->addChild('cfdi:InformacionAduanera');
            $this->vaciarAtributos($nodo, $model);
        }
    }
}
