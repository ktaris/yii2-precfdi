<?php

/**
 * @copyright Copyright (c) 2017 Carlos Ramos
 * @package ktaris-cfdi
 * @version 0.1.0
 */

namespace ktaris\precfdi;

use yii\base\Model;
use ktaris\cfdi\CFDI;
use ktaris\cadenaoriginal\CadenaOriginal;

class PreCFDI extends Model
{
    use ConceptoTrait, ComplementoTrait;

    /**
     * @var SimpleXMLElement objeto que genera el XML del PreCFDI.
     */
    private $_xmlObj;
    /**
     * @var array espacios de nombres utilizados en el documento.
     * Se pueden añadir más por medio de Complemento o ConceptoComplemento.
     */
    private $_namespaces;
    /**
     * @var array similar a $_namespaces, son ubicaciones de los esquemas
     * utilizados por el cfdi posteriormente.
     */
    private $_locations;

    public function init()
    {
        $this->_namespaces = [
            'cfdi' => 'http://www.sat.gob.mx/cfd/3',
            'xsi' => 'http://www.w3.org/2001/XMLSchema-instance',
        ];

        $this->_locations = [
            'http://www.sat.gob.mx/cfd/3',
            'http://www.sat.gob.mx/sitio_internet/cfd/3/cfdv33.xsd',
        ];
    }

    // ==================================================================
    //
    // API pública.
    //
    // ------------------------------------------------------------------

    public function generarConSello($cfdiObj, $csdObj)
    {
        $this->generar($cfdiObj);
        return $this->sellar($csdObj);
    }

    /**
     * Genera el PreCFDI en base al arreglo de datos o a un objeto
     * {ktaris\cfdi\CFDI}.
     * @param  mixed $cfdiObj datos, en objeto o arreglo.
     * @return string         PreCFDI generado.
     */
    public function generar($cfdiObj)
    {
        $cfdiObj = $this->leerDatos($cfdiObj);

        $xmlDoc = $this->crearNodoComprobante();
        $this->vaciarDatosComprobante($xmlDoc, $cfdiObj);
        $this->crearNodoCfdiRelacionados($xmlDoc, $cfdiObj);
        $this->crearNodoEmisor($xmlDoc, $cfdiObj->Emisor);
        $this->crearNodoReceptor($xmlDoc, $cfdiObj->Receptor);
        $this->crearNodoConceptos($xmlDoc, $cfdiObj->Conceptos);
        $this->crearNodoImpuestos($xmlDoc, $cfdiObj);

        if ($cfdiObj->Complemento->tieneComplemento) {
            $this->crearNodoComplemento($xmlDoc, $cfdiObj->Complemento);
        }

        $this->_xmlObj = $xmlDoc;

        return $this->xml;
    }

    public function sellar($csdObj)
    {
        // 1. Obtener el "NoCertificado", dado que forma parte de la
        // cadena original. Aquí también agregamos el atributo
        // "Certificado", aprovechando la lectura.
        if (empty($this->_xmlObj['NoCertificado'])) {
            $this->_xmlObj->addAttribute('NoCertificado', $csdObj->getNoCertificado());
        }
        if (empty($this->_xmlObj['Certificado'])) {
            $this->_xmlObj->addAttribute('Certificado', $csdObj->getCertificado());
        }

        // 2. Generar la cadena original ahora que se han agregado
        // los datos del certificado necesarios para dicha cadena.
        $cadenaOriginal = CadenaOriginal::obtener($this->xml);

        // 3. Obtener el sello del precfdi con el CSD
        $sello = $csdObj->generarSelloConSha256($cadenaOriginal);

        // 4. Agregar el atributo "Sello" al XML.
        if (empty($this->_xmlObj['Sello'])) {
            $this->_xmlObj->addAttribute('Sello', $sello);
        }

        return $this->xml;
    }

    public function getXml()
    {
        return $this->_xmlObj->asXml();
    }

    // ==================================================================
    //
    // Funciones relativas a la creación del documento.
    //
    // ------------------------------------------------------------------

    /**
     * Crea el nodo principal del documento en base a los datos recibidos.
     *
     * @return SimpleXMLElement elemento principal.
     */
    protected function crearNodoComprobante()
    {
        $header = '<?xml version="1.0" encoding="UTF-8" ?>';
        $namespaces = $this->generarCadenaDeNamespaces();
        $locations = 'xsi:schemaLocation="'.implode(' ', $this->_locations).'"';
        $nodoPrincipal = "<cfdi:Comprobante {$namespaces} {$locations} />";
        $documentoXml = new \SimpleXMLElement($header.$nodoPrincipal);

        return $documentoXml;
    }

    protected function vaciarDatosComprobante($nodoXml, $cfdiObj)
    {
        $this->vaciarAtributos($nodoXml, $cfdiObj);
    }

    protected function crearNodoCfdiRelacionados($nodoXml, $cfdiObj)
    {
        if (!$cfdiObj->CfdiRelacionados) {
            return;
        }

        $nodo = $nodoXml->addChild('cfdi:CfdiRelacionados');
        $this->vaciarAtributos($nodo, $cfdiObj->CfdiRelacionados);
        foreach ($cfdiObj->CfdiRelacionados->CfdiRelacionados as $model) {
            $nodoTmp = $nodo->addChild('cfdi:CfdiRelacionado');
            $this->vaciarAtributos($nodoTmp, $model);
        }
    }

    /**
     * Crea el nodo del Emisor.
     *
     * @param SimpleXMLElement          $nodoXml nodo de XML.
     * @param {ktaris\cfdi\base\Emisor} $cfdiObj objeto de datos.
     */
    protected function crearNodoEmisor($nodoXml, $cfdiObj)
    {
        $nodo = $nodoXml->addChild('cfdi:Emisor');
        $this->vaciarAtributos($nodo, $cfdiObj);
    }

    /**
     * Crea el nodo del Receptor.
     *
     * @param SimpleXMLElement          $nodoXml nodo de XML.
     * @param {ktaris\cfdi\base\Receptor} $cfdiObj objeto de datos.
     */
    protected function crearNodoReceptor($nodoXml, $cfdiObj)
    {
        $nodo = $nodoXml->addChild('cfdi:Receptor');
        $this->vaciarAtributos($nodo, $cfdiObj);
    }

    /**
     * Crea el nodo de Impuestos.
     *
     * @param SimpleXMLElement $nodoXml nodo de XML.
     * @param {ktaris\cfdi\base\Impuestos} $cfdiObj impuestos.
     */
    protected function crearNodoImpuestos($nodoXml, $cfdiObj)
    {
        $nodo = $nodoXml->addChild('cfdi:Impuestos');
        if (!$cfdiObj->tieneImpuestos) {
            return;
        }

        $this->vaciarAtributos($nodo, $cfdiObj->Impuestos);

        // Retenciones
        if ($cfdiObj->tieneRetenciones) {
            $this->crearNodoRetenciones($nodo, $cfdiObj);
        }
        // Traslados
        if ($cfdiObj->tieneTraslados) {
            $this->crearNodoTraslados($nodo, $cfdiObj);
        }
    }

    /**
     * Crea el nodo de Traslados, y sus hijos.
     *
     * @param SimpleXMLElement $nodoXml nodo de XML.
     * @param mixed            $cfdiObj puede recibir los datos de los traslados
     * de un concepto, o del comprobante, funciona para cualquiera de los dos.
     */
    protected function crearNodoTraslados($nodoXml, $cfdiObj)
    {
        $nodo = $nodoXml->addChild('cfdi:Traslados');
        foreach ($cfdiObj->Traslados as $model) {
            $nodoTmp = $nodo->addChild('cfdi:Traslado');
            $this->vaciarAtributos($nodoTmp, $model);
        }
    }

    /**
     * Crea el nodo de Retenciones, y sus hijos.
     *
     * @param SimpleXMLElement $nodoXml nodo de XML.
     * @param mixed            $cfdiObj puede recibir los datos de las retenciones
     * de un concepto, o del comprobante, funciona para cualquiera de los dos.
     */
    protected function crearNodoRetenciones($nodoXml, $cfdiObj)
    {
        $nodo = $nodoXml->addChild('cfdi:Retenciones');
        foreach ($cfdiObj->Retenciones as $model) {
            $nodoTmp = $nodo->addChild('cfdi:Retencion');
            $this->vaciarAtributos($nodoTmp, $model);
        }
    }

    // ==================================================================
    //
    // Funciones protegidas de procesamiento interno, para generar XML.
    //
    // ------------------------------------------------------------------

    protected function leerDatos($cfdiObj)
    {
        // Primero revisamos si es instancia de un CFDI.
        // Si es así, regresamos el objeto nada más.
        if ($cfdiObj instanceof CFDI) {
            return $cfdiObj;
        }

        // Revisa si la entrada es un arreglo y, si es así,
        // crea un objeto CFDI.
        if (is_array($cfdiObj)) {
            $cfdiObjTmp = new CFDI;
            $cfdiObjTmp->load($cfdiObj);

            return $cfdiObjTmp;
        }

        // Si no es arreglo ni un objeto CFDI, arrojamos una excepción.
        throw new PreCfdiException('No se recibieron datos válidos para el PreCFDI.');
    }

    /**
     * Vacía los atributos del componente CFDI al documento en XML.
     *
     * @param SimpleXMLElement $nodoXml   nodo de XML al que se le añadirán los atributos.
     * @param mixed            $modelo    componente con datos
     */
    protected function vaciarAtributos($nodoXml, $modelo)
    {
        // Asegurar que los filtro se ejecutaron sobre el modelo.
        // Principalmente necesario para eliminar espacios vacíos.
        $modelo->validate();

        foreach ($modelo->atributosDeNodo as $attr => $value) {
            $nodoXml->addAttribute($attr, $value);
        }
    }

    // ==================================================================
    //
    // Procesamiento interno para lidiar con namespaces.
    //
    // ------------------------------------------------------------------

    /**
     * Agregamos un espacio de nombres al documento, con su prefijo
     * y la ubicación donde reside su xsd o algo así.
     * @param  string $prefijo prefijo del namespace
     * @param  string $url     url del xsd o definición.
     */
    protected function agregarNamespace($prefijo, $url)
    {
        $this->_namespaces[$prefijo] = $url;
    }

    /**
     * Agregamos ubicación del xsd y de otras definiciones, para anexar
     * al XML del PreCFDI.
     * @param  string $ubicacion url a ser integrada.
     */
    protected function agregarUbicacion($ubicacion)
    {
        $this->_locations[] = $ubicacion;
    }

    /**
     * Recibe el arreglo de namespaces y lo convierte en cadena.
     *
     * @return string cadena con namespaces.
     */
    protected function generarCadenaDeNamespaces()
    {
        $cadena = '';
        foreach ($this->_namespaces as $ns => $url) {
            $cadena .= "xmlns:{$ns}=\"{$url}\" ";
        }

        return trim($cadena);
    }
}
