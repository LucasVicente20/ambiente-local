<?php

#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadNoticiaIncluir.php
# Objetivo: Programa para alimentar os links e notícias.
# Autor:    José Almir <jose.almir@pitang.com>
# Data:     07/01/2015
#-------------------------------------------------------------------------

require_once '../funcoes.php';
require_once 'AbstractNoticia.php';

class CadNoticiaIncluir extends AbstractNoticia
{
	private $template;
	private $instancia;

	public function __construct($template)
	{
		parent::__construct();
		$this->template = $template;
	}

	protected function getTemplate()
	{
		return $this->template;
	}

	private function incluir()
	{
		$mensagem = "";

		if ($this->dadosValidos()) {	
			$titulo = $_POST['titulo'];
			$destino = filter_var($_POST['destino'], FILTER_SANITIZE_STRING);
			$texto = $_POST['texto'];
			$situacao = filter_var($_POST['situacao'], FILTER_SANITIZE_STRING);
			$data = filter_var($_POST['data'], FILTER_SANITIZE_STRING);
			$hora = filter_var($_POST['hora'], FILTER_SANITIZE_STRING);
			$timestampCadastro = date_transform($data) . " $hora:00";
			$timestampAlteracao = date("Y-m-d H:i:s");
			
			$sequencialNoticia = $this->getSequencial();
			$sequencialUsuario = $_SESSION['_cusupocodi_'];

			$db = Conexao();
			$sql  = " INSERT INTO SFPC.TBNOTICIAPORTALCOMPRAS ";
			$sql .= " (CNOTPCSEQU, ENOTPCTITL, ENOTPCTEXT, FNOTPCDEST, FNOTPCSITU, TNOTPCDATC, CUSUPOCODI, TNOTPCULAT) ";
			$sql .= " VALUES ";
			$sql .= " ($sequencialNoticia, '$titulo', '$texto', '$destino', '$situacao', '$timestampCadastro', $sequencialUsuario, '$timestampAlteracao') ";

			$resultado = executarSQL($db, $sql);
			$mensagem = ExibeMensStr("Notícia cadastrada com sucesso", 1, 0);
			$_POST = array();
		} else {
			$mensagem = $GLOBALS['Mensagem'];
		}

		$this->getTemplate()->MENSAGEM_ERRO = $mensagem;
		$this->getTemplate()->block('BLOCO_ERRO', true);
		$this->principal();
	}

	private function principal()
	{
		$this->iniciarTitulo();
		$this->iniciarDestinoDaNoticia();
		$this->iniciarTexto();
		$this->iniciarContadorDeCaractere();
		$this->iniciarSituacao();
	}
	
	private function frontController()
	{
		$botao = $_POST['Botao'];
	
		switch ($botao) {
			case 'Incluir':
				$this->incluir();
				break;
			case 'Principal':
			default:
				$this->principal();
		}
	}

	private function executar()
	{
		$this->frontController();
		$this->getTemplate()->show();
	}
	
	public static function iniciar()
	{
		$template = new TemplatePaginaPadrao("templates/CadNoticiaIncluir.html", "Notícias > Incluir");
		$instancia = new CadNoticiaIncluir($template);
		$instancia->executar();
	}
}

CadNoticiaIncluir::iniciar();
