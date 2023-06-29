<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TemplatePortal.class.php
# Autor:    Ariston Cordeiro
# Data:     08/11/2012
# Objetivo: Classe que representa um template usado pelo portal de compras. 
# 					Qualquer código novo que todos templates precisam ter deve ser implementado aqui.
#----------------------------------------------------------------------

require_once(CAMINHO_SISTEMA."import/Template.class.php");
/**
 * TemplatePortal gera um template genérico. É a implementação específica da classe Template, para o Portal de Compras.
 * */
class TemplatePortal extends Template {
	/**
	 * Construtor.
	 * */
	function __construct($file){
		parent::__construct($file); //caminho é relativo ao arquivo do parent
	}
}
?>