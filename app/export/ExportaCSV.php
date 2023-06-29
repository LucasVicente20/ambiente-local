<?php
class ExportaCSV{
	
	protected $nomeArquivo;
	protected $delimitador;
	protected $cabecalho;
	protected $linhas;	

	public function __construct($nomeArquivo, $delimitador, $cabecalho, $linhas){
		$this->nomeArquivo = $nomeArquivo;
		$this->delimitador = $delimitador;
		$this->cabecalho = $cabecalho;
		$this->linhas = $linhas;
	}

	public function download(){
		header('Content-Type: application/csv');
		header('Content-Disposition: attachment; filename="'.$this->nomeArquivo.'";');

		// open the "output" stream
		// see http://www.php.net/manual/en/wrappers.php.php#refsect2-wrappers.php-unknown-unknown-unknown-descriptioq
		$f = fopen('php://output', 'w');
		
		fputcsv($f, $this->cabecalho, $this->delimitador);

		foreach ($this->linhas as $linha) {
			fputcsv($f, $linha, $this->delimitador);
		}

		fclose($f);

    	// flush buffer
    	ob_flush();

    	// use exit to get rid of unexpected output afterward
    	exit();
	}
	
}
