<?php

include("ods.php");

//ESTA CLASSE APENAS "ENCAPSULA" A CLASSE "ods.php" PARA QUE SEJA POSSIVEL UTILIZA-LA
//NO MESMO FORMATO DAS OUTRAS CLASSES DE EXPORTACAO
class ExportaODS{

	private $nomeArquivo;
	private $tmpPath;
	private $ods;
	private $rowNum;

	function ExportaODS($nomeArquivo, $cabecalho, $linhas) { 
		$this->nomeArquivo = $nomeArquivo;
		$this->tmpPath = '/tmp/'. uniqid() . '.ods';
		$this->rowNum = 0;
		
		$this->ods = newOds();
		$this->adicionaDados(array($cabecalho));
		$this->adicionaDados($linhas);
		
		saveOds($this->ods, $this->tmpPath);		
	}
	
	//falta verificar se vai ser necessario excluir o arquivo do disco apos o download
	function download() {

		#send headers
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Content-Type: application/force-download");
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/download");
		header("Content-Disposition: attachment;filename=".$this->nomeArquivo);
		header("Content-Transfer-Encoding: binary ");

		header('Content-Encoding: UTF-8');
		header('Content-type: application/vnd.oasis.opendocument.spreadsheet; charset=UTF-8');
		
		echo readfile($this->tmpPath);

		exit;
	}

	private function adicionaDados($arrayDados){
		$colNum = 0;
		foreach($arrayDados as $row) {
			foreach($row as $field) {
				if(is_numeric($field)) {
					$this->ods->addCell(0,$this->rowNum,$colNum,$field,'float'); //add a cell to sheet 0, row 0, cell 0, with value 1 and type float
				}
				else
				{
					//CORRIGINDO ERROS CAUSADOS POR ASPAS EM CAMPOS DE TEXTO QUE ESTAVAM ARMAZENADOS NO BD
					//E ESTAVAM QUEBRANDO O XML DO ARQUIVO
					$escapedField = str_replace("&", "&amp;", $field);
					$escapedField = str_replace('"', '&quot;', $escapedField);		
					$escapedField = str_replace("'", "&apos;", $escapedField);
					$escapedField = str_replace("<", "&lt;", $escapedField);
					$escapedField = str_replace(">", "&gt;", $escapedField);
					

					$this->ods->addCell(0,$this->rowNum,$colNum,$escapedField,'string'); 
				}

				$colNum++;
			}
			$this->rowNum++;
			$colNum = 0;
		}
	}
}
