<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TemplatePaginaPadrao.class.php
# Autor:    Ariston Cordeiro
# Data:     08/11/2012
# Objetivo: Template de uma página padrão do portal de compras (cria o menu de acesso e layout padrão, E preenche automaticamente mensagens de erro). 
# 					O arquivo do template informado em $file deve um html contendo apenas o corpo do html, pois será colocado dentro de <body>.
#						O template também deve possuir a variável MENSAGEM_ERRO, para receber as mensagens de erro para o usuário.
#----------------------------------------------------------------------
# Alterado: Caio Coutinho - Pitang Agile TI
# Data:     18/12/2018
# Objetivo: Tarefa Redmine 211230
#----------------------------------------------------------------------------------

require_once(CAMINHO_SISTEMA."geral/TemplatePortal.class.php");
/**
* Gera uma página no padrão do Portal de compras (cria o menu de acesso e layout padrão, E preenche automaticamente mensagens de erro)
* O arquivo do template informado em $file deve um html contendo apenas o corpo do html, pois será colocado dentro de <body>.
* */
class TemplateNovaJanela extends TemplatePortal {
	/**
	 * Construtor da classe.
	 * atributos: 
	 * $file- caminho do template. Deve ser relativo ao ao diretório onde está o arquivo da classe Template
	 * */
	function __construct($file, $acesso, $respinsivo = false){
	    $html = ($respinsivo) ? 'PaginaNovaJanelaResponsivo.template.html' : 'PaginaNovaJanela.template.html';

		parent::__construct(CAMINHO_SISTEMA."geral/templates/" .  $html);

		$this->addFile("CORPO", $file);

		//ob_start(); //iniciando caso tenha finalizado
		$this->LAYOUT = ob_get_clean ();
		
		#variáveis de erro
		$Mens = $GLOBALS["Mens"];
		$Mensagem = $GLOBALS["Mensagem"];
		$Tipo = $GLOBALS["Tipo"];
		
		if ( $Mens == 1 ) {
			$this->MENSAGEM_ERRO = ExibeMensStr($Mensagem,$Tipo,1);
			$this->block("BLOCO_ERRO");
		}
	}
}

?>