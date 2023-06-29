<?php
//ARQUIVO MODIFICADO A PARTIR DO CODIGO FONTE DISPINIVEL EM: https://gist.github.com/samatsav/6637984

/*
    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/*
  
	Filename	: export.xls.class.php
	Description	: A small light weight PHP class to allow the creation of simple xls excel spreadsheets from array data.
	Version 	: 1.01
	Author 		: Leenix
	Website		: http://www.leenix.co.uk
*/

/*
	Change Log
		V1 - First Release
 		1.01 - Fixed UTF8 Issue
 */

class ExportaXLS {

	private $nomeArquivo;	//Filename which the excel file will be returned as
	private $cabecalho;	// Array which contains header information
	private $linhas;	// Array with the spreadsheet body
	private $rowNo = 0;	// Keep track of the row numbers


	#Class constructor
	function ExportaXLS($nomeArquivo, $cabecalho, $linhas) { 
		$this->nomeArquivo = $nomeArquivo;
		$this->addHeader($cabecalho);
		$this->addRow($linhas); 
	}


	/*
	-------------------------
	START OF PUBLIC FUNCTIONS
	-------------------------
	*/

	//ALTERADA PARA PRIVATE (APENAS SERA USADA NO CONSTRUTOR)
	private function addHeader($header) {
		#Accepts an array or var which gets added to the top of the spreadsheet as a header.
	
			if(is_array($header)) {
				$this->cabecalho[] = $header;
			}
			else
			{
				$this->cabecalho[][0] = $header;
			}
		}
	
	//ALTERADA PARA PRIVATE (APENAS SERA USADA NO CONSTRUTOR)
	private function addRow($row) {
	#Accepts an array or var which gets added to the spreadsheet body
	
		if(is_array($row)) {
			#check for multi dim array
			if(is_array($row[0])) {
				foreach($row as $key=>$array) {
					$this->linhas[] = $array;
				}
			}
			else
			{
				$this->linhas[] = $row;
			}			
		}
		else
		{
			$this->linhas[][0] = $row;
		}
		
	}

	public function download() {
		#build the xls
		$xls = $this->buildXLS();

		#send headers
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Content-Type: application/force-download");
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/download");
		header("Content-Disposition: attachment;filename=".$this->nomeArquivo);
		header("Content-Transfer-Encoding: binary ");

		//ADICIONADAS ESTAS 2 LINHAS ABAIXO PARA CORRIGIR PROBLEMA NA CODIFICACAO DOS CARACTERES DA PLANILHA
		header('Content-Encoding: UTF-8');
		header('Content-type: application/excel; charset=UTF-8');

		echo $xls;

		exit;
	}


	/*
	--------------------------
	START OF PRIVATE FUNCTIONS
	--------------------------
	*/

	private function buildXLS() {
	# build and return the xls 
	
		#Excel BOF
		$xls = pack("ssssss", 0x809, 0x8, 0x0, 0x10, 0x0, 0x0);

		#build headers
		if(is_array($this->cabecalho)) {
			$xls .= $this->build($this->cabecalho);
		}

		#build body
		if(is_array($this->linhas)) {
			$xls .= $this->build($this->linhas);
		}

		$xls .= pack("ss", 0x0A, 0x00);

		return $xls;
	}

	private function build($array) {
	#build and return the headers 		
		foreach($array as $row) {
			$colNo = 0;
			foreach($row as $field) {
				//$y= print_r($field, true);
				//die($y);
				if(is_numeric($field)) {
					$build .= $this->numFormat($this->rowNo, $colNo, $field);
				}
				else
				{
					$build .= $this->textFormat($this->rowNo, $colNo, $field);
				}

				$colNo++;
			}
			$this->rowNo++;
		}

		return $build;
	}

	private function textFormat($row, $col, $data) {
	# format and return the field as a header
		$data = utf8_decode($data);
		$length = strlen($data);
		$field = pack("ssssss", 0x204, 8 + $length, $row, $col, 0x0, $length);
		$field .= $data;

		return $field; 
	}
		

	private function numFormat($row, $col, $data) {
	# format and return the field as a header
    		$field = pack("sssss", 0x203, 14, $row, $col, 0x0);
    		$field .= pack("d", $data); 
		
		return $field; 
	}
}
?>
