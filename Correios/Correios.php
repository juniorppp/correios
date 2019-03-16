<?php
/**
 * Rastreamento Correios
 *
 * Copyright 2019 Gelson Junior (junior.ppp@gmail.com).
 * License: New-BSD
 */
 
namespace Correios;

final class Correios
{
	public $codigo;
	
	public function __construct($codigo){
		$this->codigo = $codigo;
	}
	
	public function ConsultaEncomenda(){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "https://www2.correios.com.br/sistemas/rastreamento/resultado_semcontent.cfm");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch,CURLOPT_POSTFIELDS, http_build_query(array("Objetos" => $this->codigo)));
		$html = curl_exec($ch) or die(curl_error($ch));
		$status = curl_getinfo($ch);

		if($status['http_code'] == 404){
			echo json_encode(array("page"=>$this->fanpage,"url"=>"404"));
			exit;
		}
		
		return $html;
	}
	
	private function matchRegex($strContent, $strRegex, $intIndex = NULL) {
        $arrMatches = FALSE;
        preg_match_all($strRegex, $strContent, $arrMatches);
        if ($arrMatches === FALSE)
            return FALSE;
        if ($intIndex != NULL && is_int($intIndex)) {
            if ($arrMatches[$intIndex]) {
                return $arrMatches[$intIndex][0];
            }
            return FALSE;
        }
        return $arrMatches;
    }
	
	private function getScrape() {

        if (!isset($this->_strSource) || $this->_strSource == null || $this->_strSource == "") {
            $this->_strSource = $this->ConsultaEncomenda();
        }
        return $this->_strSource;

    }
	
	private function Limpa($valor){
		return strip_tags(utf8_encode($valor));
	}
	
	public function Dados(){
		$file = $this->matchRegex($this->getScrape(), '~<td class="sroDtEvent"(?:.*)>(.*)</td>~Uis',1);
		$status = $this->matchRegex($this->getScrape(), '~<td class="sroLbEvent"(?:.*)>(.*)</td>~Uis',1);
		$Separa = explode("<br />",$file);

		return array("Data"=>self::Limpa($Separa[0]),"Hora"=>self::Limpa($Separa[1]),"Cidade"=>self::Limpa($Separa[2]),"Status_Objeto"=>self::Limpa($status));
		
	}
	
	
}

//$teste = new Correios("LM206212560CN");
//$teste->Dados();