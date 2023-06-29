<?php

/** Classe excessão raiz do Portal. Todos objetos usados em throw em funções ou classes devem ser inâncias de classes derivadas desta classe. */
class Excecao extends Exception {
	function toString(){
		$msg = "Seguem dados do Exception:\n\n";
		$msg .= 'Mensagem: '.$this->getMessage()."\n\n";
		//$msg .= 'Código: '.$this->getCode()."\n\n";
		//$msg .= 'Arquivo: '.$this->getFile()."\n\n";
		$msg .= 'Trace: '."\n".$this->getTraceAsString()."";
		return $msg;
	}
}
?>