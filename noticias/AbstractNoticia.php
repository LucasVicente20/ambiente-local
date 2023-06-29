<?php

#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: AbstractNoticia.php
# Objetivo: Programa para alimentar os links e notícias.
# Autor:    José Almir <jose.almir@pitang.com>
# Data:     07/01/2015
#-------------------------------------------------------------------------

require_once '../funcoes.php';
session_start();

abstract class AbstractNoticia
{
	const ATIVA = "A";
	const INATIVA = "I";
	const LINK = "L";
	const POPUP = "P";
	
	public function __construct()	
	{
		$this->inicializar();
	}
	
	private function inicializar()
	{
		global $SimboloConcatenacaoArray, $SimboloConcatenacaoDesc;
	
		Seguranca();
	}
	
	protected function getSequencial()
	{
		$db = Conexao();
		$sql  = " SELECT MAX(CNOTPCSEQU) AS ultimo_sequencial ";
		$sql .= " FROM SFPC.TBNOTICIAPORTALCOMPRAS ";
	
		$resultado = executarSQL($db, $sql);
		$ultimoSequencial = resultValorUnico($resultado);
		$proximoSequencial = (empty($ultimoSequencial)) ? 1 : $ultimoSequencial + 1;
	
		return $proximoSequencial;
	}
	
	protected function dadosValidos()
	{
		$validos = true;
		
		$titulo = $_POST['titulo'];
		$destino = $_POST['destino'];
		$texto = $_POST['texto'];
		$situacao = $_POST['situacao'];
		$data = $_POST['data'];
		$hora = $_POST['hora'];
		
		if (empty($titulo)) {
			$GLOBALS['Mensagem'] = ExibeMensStr("<a href='javascript:document.formNoticia.titulo.focus();' class='titulo2'>Título é obrigatório</a>", 2, 0);
			return false;
		}
		
		if (empty($destino)) {
			$GLOBALS['Mensagem'] = ExibeMensStr("<a href='javascript:document.formNoticia.destino.focus();' class='titulo2'>Destino da notícia é obrigatório</a>", 2, 0);
			return false;
		}
		
		if (empty($texto)) {
			$GLOBALS['Mensagem'] = ExibeMensStr("<a href='javascript:document.formNoticia.texto.focus();' class='titulo2'>Texto é obrigatório</a>", 2, 0);
			return false;
		}
		
		if ($destino == self::LINK && !filter_var($texto, FILTER_VALIDATE_URL)) {
			$GLOBALS['Mensagem'] = ExibeMensStr("<a href='javascript:document.formNoticia.texto.focus();' class='titulo2'>Link informado não está em um formado válido</a>", 2, 0);
			return false;
		}
		
		if (empty($situacao)) {
			$GLOBALS['Mensagem'] = ExibeMensStr("<a href='javascript:document.formNoticia.situacao.focus();' class='titulo2'>Situação é obrigatória</a>", 2, 0);
			return false;
		}
		
		if (empty($data)) {
			$GLOBALS['Mensagem'] = ExibeMensStr("<a href='javascript:document.formNoticia.data.focus();' class='titulo2'>Data é obrigatória</a>", 2, 0);
			return false;
		}
		
		$MensErro = ValidaData($data);
		if ($MensErro != "") {
			$GLOBALS['Mensagem'] = ExibeMensStr("<a href='javascript:document.formNoticia.data.focus();' class='titulo2'>Data de cadastro inválida</a>", 2, 0);
			return false;
		}
		
		if (empty($hora)) {
			$GLOBALS['Mensagem'] = ExibeMensStr("<a href='javascript:document.formNoticia.hora.focus();' class='titulo2'>Hora é obrigatória</a>", 2, 0);
			return false;
		}
		
		if (!$this->horaValida($hora)) {
			$GLOBALS['Mensagem'] = ExibeMensStr("<a href='javascript:document.formNoticia.hora.focus();' class='titulo2'>Hora inválida</a>", 2, 0);
			return false;
		}
		
		return $validos;
	}
	
	protected function horaValida($horaInformada) {
		$horaValida = false;
		$hora = explode(":", $horaInformada);
		
		if (count($hora) == 2) {
			if ((is_numeric($hora[0]) && $hora[0] >= 0  && $hora[0] <= 23) &&
				(is_numeric($hora[1]) && $hora[1] >= 0  && $hora[1] <= 59)) {
				$horaValida = true;
			}
		}

		return $horaValida;
	}
	
	protected function iniciarSituacao($situacao = null)
	{
		$this->removerSituacaoMarcada();
		
		if (isset($_POST["situacao"])) {
			$situacao = $_POST["situacao"];
		}
		
		switch ($situacao) {
			case self::INATIVA:				
				$this->getTemplate()->CHECKED_INATIVA = "checked";
				break;
			case self::ATIVA:
			default:
				$this->getTemplate()->CHECKED_ATIVA = "checked";
				break;
		}
	}
	
	protected function removerSituacaoMarcada()
	{
		$this->getTemplate()->CHECKED_ATIVA = "";
		$this->getTemplate()->CHECKED_INATIVA = "";
	}
	
	protected function iniciarDestinoDaNoticia($destino = null)
	{
		$this->removerDestinoDaNoticiaMarcado();
		
		if (isset($_POST["destino"])) {
			$destino = $_POST["destino"];
		}
		
		switch ($destino) {
			case self::POPUP:
				$this->getTemplate()->CHECKED_POPUP = "checked";
				break;
			case self::LINK:
			default:
				$this->getTemplate()->CHECKED_LINK = "checked";;
				break;
		}
	}
	
	protected function removerDestinoDaNoticiaMarcado()
	{
		$this->getTemplate()->CHECKED_LINK = "";
		$this->getTemplate()->CHECKED_POPUP = "";
	}
	
	protected function iniciarTitulo($titulo = null)
	{
		if (isset($_POST["titulo"])) {
			$titulo = $_POST["titulo"];
		}
		
		$this->getTemplate()->TITULO = $titulo;
	}
	
	protected function iniciarTexto($texto = null)
	{
		if (isset($_POST["texto"])) {
			$texto = $_POST["texto"];
		}
	
		$this->getTemplate()->TEXTO = $texto;
	}
	
	protected function iniciarContadorDeCaractere($valor = 0)
	{
		if (isset($_POST["NCaracteresO"])) {
			$valor = $_POST["NCaracteresO"];
		}
	
		$this->getTemplate()->QTD_CARACTERES_DIGITADOS = $valor;
	}
}
