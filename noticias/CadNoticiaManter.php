<?php

#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadNoticiaManter.php
# Objetivo: Programa para manter os links e notícias.
# Autor:    José Almir <jose.almir@pitang.com>
# Data:     07/01/2015
#-------------------------------------------------------------------------

require_once '../funcoes.php';
require_once 'AbstractNoticia.php';

class CadNoticiaManter extends AbstractNoticia
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
	
	private function iniciarDataPesquisa()
	{
		$datas = DataMes();
	
		$dataInicial = $datas[0];
		if (isset($_POST['DataIni'])) {
			$dataInicial = $_POST['DataIni'];
		}
	
		$dataFinal = $datas[1];
		if (isset($_POST['DataFim'])) {
			$dataFinal = $_POST['DataFim'];
		}
	
		$this->getTemplate()->DATA_INICIAL = $dataInicial;
		$this->getTemplate()->DATA_FINAL = $dataFinal;
	}
	
	private function dadosPesquisaValidos($dados)
	{
		$validos = true;
	
		$dataInicial = $dados['DataIni'];
		$dataFinal = $dados['DataFim'];
	
		$MensErro = ValidaData($dataInicial);
		if ($MensErro != "") {
			$GLOBALS['Mensagem'] = ExibeMensStr("<a href='javascript:document.formNoticia.DataIni.focus();' class='titulo2'>Data inicial inválida</a>", 2, 0);
			return false;
		}
	
		$MensErro = ValidaData($dataFinal);
		if ($MensErro != "") {
			$GLOBALS['Mensagem'] = ExibeMensStr("<a href='javascript:document.formNoticia.DataFim.focus();' class='titulo2'>Data final inválida</a>", 2, 0);
			return false;
		}
	
		if (ValidaData($dataInicial) == '' && ValidaData($dataFinal) == '') {
			$MensErro = ValidaPeriodo($dataInicial, $dataFinal, 0, "formNoticia");
			if ($MensErro != "") {
				$GLOBALS['Mensagem'] = ExibeMensStr("<a href='javascript:document.formNoticia.DataIni.focus();' class='titulo2'>Data Final igual ou maior que Data Inicial</a>", 2, 0);
				return false;
			}
		}
	
		return $validos;
	}
	
	private function executarConsulta($titulo = "", $texto = "", $situacao = "", $dataInicial = "", $dataFinal = "", $limite = null)
	{
		$db = Conexao();
		$sql  = " SELECT * FROM SFPC.TBNOTICIAPORTALCOMPRAS ";
		$sql .= " WHERE 1 = 1 ";
		
		if (!empty($dataInicial)) {
			$sql .= " AND TNOTPCDATC >= '$dataInicial' ";
		}
		if (!empty($dataFinal)) {
			$sql .= " AND TNOTPCDATC <= '$dataFinal' ";
		}
		if (!empty($titulo)) {
			$sql .= " AND ENOTPCTITL LIKE '%$titulo%' ";
		}
		if (!empty($texto)) {
			$sql .= " AND ENOTPCTEXT LIKE '%$texto%' ";
		}
		if (!empty($situacao)) {
			$sql .= " AND FNOTPCSITU = '$situacao' ";
		}
		$sql .= " ORDER BY TNOTPCDATC DESC ";
		if (!is_null($limite)) {
			$limite = filter_var($limite, FILTER_SANITIZE_NUMBER_INT);
			$sql .= " LIMIT $limite ";
		}
		
		return executarSQL($db, $sql);
	}
	
	private function pesquisar($titulo, $texto, $situacao, $dataCadastroInicio, $dataCadastroFim)
	{
		$dados = array(
			'titulo' => $titulo, 
			'texto' => $texto, 
			'situacao' => $situacao, 
			'DataIni' => $dataCadastroInicio, 
			'DataFim' => $dataCadastroFim
		);
		$dadosValidos = $this->dadosPesquisaValidos($dados);
	
		if ($dadosValidos) {
			$situacao = filter_var($situacao, FILTER_SANITIZE_STRING);
			$dataInicial = new DataHora(filter_var($dataCadastroInicio, FILTER_SANITIZE_STRING));
			$dataInicial = $dataInicial->formata('Y-m-d') . ' 00:00:00';
			$dataFinal = new DataHora(filter_var($dataCadastroFim, FILTER_SANITIZE_STRING));
			$dataFinal = $dataFinal->formata('Y-m-d') . ' 23:59:59';
			
			$_SESSION['tituloPesquisa'] = $titulo;
			$_SESSION['textoPesquisa'] = $texto;
			$_SESSION['situacaoPesquisa'] = $situacao;
			$_SESSION['DataIni'] = $dataCadastroInicio;
			$_SESSION['DataFim'] = $dataCadastroFim;
			
			$resultado = $this->executarConsulta($titulo, $texto, $situacao, $dataInicial, $dataFinal);
			$this->renderizarResultadoDaPesquisa($resultado);
		} else {
			$this->getTemplate()->MENSAGEM_ERRO = $GLOBALS['Mensagem'];
			$this->getTemplate()->block('BLOCO_ERRO', true);
		}
	
		$this->principal();
	}
	
	private function renderizarResultadoDaPesquisa($resultado)
	{
		while ($resultado->fetchInto($item, DB_FETCHMODE_OBJECT)) {
			$dataCadastro = new DataHora($item->tnotpcdatc);
			$dataCadastro = $dataCadastro->formata('d/m/Y') . ' às ' . $dataCadastro->formata('H:i');
		
			$this->getTemplate()->ID_NOTICIA = $item->cnotpcsequ;
			$this->getTemplate()->TITULO_NOTICIA = $item->enotpctitl;
			$this->getTemplate()->DATA_CADASTRO = $dataCadastro;
			$this->getTemplate()->SITUACAO_NOTICIA = ($item->fnotpcsitu == self::ATIVA) ? 'Ativa' : 'Inativa';
		
			$this->getTemplate()->block("BLOCO_ITEM_RESULTADO");
		}
		
		$bloco = "BLOCO_RESULTADO_PESQUISA";
		
		if ($resultado->numRows() <= 0) {
			$bloco = "BLOCO_PESQUISA_SEM_RESULTADO";
		}
		
		$this->getTemplate()->block($bloco);
	}

	private function removerDadosDaSessao()
	{
		unset(
			$_SESSION['tituloPesquisa'],
			$_SESSION['textoPesquisa'],
			$_SESSION['situacaoPesquisa'],
			$_SESSION['DataIni'],
			$_SESSION['DataFim']
		);
	}
	
	private function principal()
	{
		$this->iniciarDataPesquisa();
		$this->getTemplate()->CHECKED_ATIVA_PESQUISA = "checked";
		
		if ($_SERVER["REQUEST_METHOD"] == "GET") {
			$this->removerDadosDaSessao();
		}
		
		if (isset($_SESSION['tituloPesquisa'])) {
			$this->getTemplate()->TITULO_PESQUISA = $_SESSION['tituloPesquisa'];
		}
		
		if (isset($_SESSION['textoPesquisa'])) {
			$this->getTemplate()->TEXTO_PESQUISA = $_SESSION['textoPesquisa'];
		}
		
		if (isset($_SESSION['situacaoPesquisa'])) {
			$this->getTemplate()->CHECKED_ATIVA_PESQUISA = "";
			$this->getTemplate()->CHECKED_INATIVA_PESQUISA = "";
			
			switch ($_SESSION['situacaoPesquisa']) {
				case self::INATIVA:
					$this->getTemplate()->CHECKED_INATIVA_PESQUISA = "checked";
					break;
				case self::ATIVA:
				default:
					$this->getTemplate()->CHECKED_ATIVA_PESQUISA = "checked";
					break;
			}
		}
		
		if (isset($_SESSION['DataIni'])) {
			$this->getTemplate()->DATA_INICIAL = $_SESSION['DataIni'];
		}
		
		if (isset($_SESSION['DataFim'])) {
			$this->getTemplate()->DATA_FINAL = $_SESSION['DataFim'];
		}
		
		$this->getTemplate()->block("BLOCO_PESQUISA");
	}
	
	private function manter($sequencialNoticia)
	{
		$sequencialNoticia = filter_var($sequencialNoticia, FILTER_SANITIZE_NUMBER_INT);
	
		$db = Conexao();
		$sql  = " SELECT * ";
		$sql .= " FROM SFPC.TBNOTICIAPORTALCOMPRAS ";
		$sql .= " WHERE CNOTPCSEQU = $sequencialNoticia ";
	
		$resultado = executarSQL($db, $sql);
		$item = resultObjetoUnico($resultado);
	
		$dataCadastro = new DataHora($item->tnotpcdatc);
	
		if (!empty($GLOBALS['Mensagem'])) {
			$dataInicial = $_POST['DataIni'];
			$dataFinal = $_POST['DataFim'];
		}
	
		$_SESSION['sequencialNoticiaManter'] = $item->cnotpcsequ;

		$this->getTemplate()->TITULO = $item->enotpctitl;
		$this->iniciarDestinoDaNoticia($item->fnotpcdest);
		$this->getTemplate()->TEXTO = $item->enotpctext;
		$this->iniciarSituacao($item->fnotpcsitu);
		$this->getTemplate()->DATA = $dataCadastro->formata('d/m/Y');
		$this->getTemplate()->HORA = $dataCadastro->formata('H:i');
		$this->getTemplate()->ID_NOTICIA = $item->cnotpcsequ;
		$this->getTemplate()->QTD_CARACTERES_DIGITADOS = strlen($item->enotpctext);
		$this->getTemplate()->block('BLOCO_MANTER');
	}
	
	private function alterar()
	{
		$mensagem = "";
	
		if ($this->dadosValidos()) {
			$titulo = $_POST['titulo'];
			$destino = filter_var($_POST['destino'], FILTER_SANITIZE_STRING);
			$texto = $_POST['texto'];
			$situacao = filter_var($_POST['situacao'], FILTER_SANITIZE_STRING);
			$sequencialNoticia = filter_var($_POST['SequencialNoticia'], FILTER_SANITIZE_NUMBER_INT);
			$data = filter_var($_POST['data'], FILTER_SANITIZE_STRING);
			$hora = filter_var($_POST['hora'], FILTER_SANITIZE_STRING);
			
			$timestampCadastro = new DataHora($data);
			$timestampCadastro = $timestampCadastro->formata('Y-m-d') . " $hora:00";
			$timestampAlteracao = date("Y-m-d H:i:s");

			$db = Conexao();
			$sql  = " UPDATE SFPC.TBNOTICIAPORTALCOMPRAS ";
			$sql .= " SET ENOTPCTITL = '$titulo', ENOTPCTEXT = '$texto', FNOTPCDEST = '$destino', ";
			$sql .= " 	  FNOTPCSITU = '$situacao', TNOTPCDATC = '$timestampCadastro', TNOTPCULAT = '$timestampAlteracao' ";
			$sql .= " WHERE CNOTPCSEQU = $sequencialNoticia";

			$resultado = executarSQL($db, $sql);
			$this->removerDadosDaSessao();
			$_POST = array();
			
			$this->getTemplate()->MENSAGEM_ERRO = ExibeMensStr("Notícia alterada com sucesso", 1, 0);
			$this->getTemplate()->block('BLOCO_ERRO', true);
			$this->principal();
		} else {
			$this->getTemplate()->MENSAGEM_ERRO = $GLOBALS['Mensagem'];
			$this->getTemplate()->block('BLOCO_ERRO', true);
			$this->manter($_POST['SequencialNoticia']);
		}
	}
	
	private function excluir()
	{
		$sequencialNoticia = filter_var($_POST['SequencialNoticia'], FILTER_SANITIZE_NUMBER_INT);
	
		if (!empty($sequencialNoticia)) {
			$db = Conexao();
			$sql  = " DELETE FROM SFPC.TBNOTICIAPORTALCOMPRAS ";
			$sql .= " WHERE CNOTPCSEQU = $sequencialNoticia ";

			$resultado = executarSQL($db, $sql);
			$mensagem = ExibeMensStr("Notícia excluída com sucesso", 1, 0);
			$this->removerDadosDaSessao();
			$_POST = array();
		} else {
			$mensagem = 'Selecione uma notícia para ser excluída';
		}
	
		$this->getTemplate()->MENSAGEM_ERRO = $mensagem;
		$this->getTemplate()->block('BLOCO_ERRO', true);
		$this->principal();
	}
	
	private function voltar()
	{
		$dataInicial = new DataHora($_SESSION['DataIni']);
		$dataInicial = $dataInicial->formata('Y-m-d') . ' 00:00:00';
		$dataFinal = new DataHora($_SESSION['DataFim']);
		$dataFinal = $dataFinal->formata('Y-m-d') . ' 23:59:59';

		$resultado = $this->executarConsulta(
			$_SESSION['tituloPesquisa'], 
			$_SESSION['textoPesquisa'], 
			$_SESSION['situacaoPesquisa'], 
			$dataInicial, 
			$dataFinal
		);
		$this->renderizarResultadoDaPesquisa($resultado);
		$this->principal();
	}

	private function frontController()
	{
		$botao = $_POST['Botao'];

		switch ($botao) {
			case 'Pesquisar':
				$this->pesquisar(
					$_POST['titulo'], 
					$_POST['texto'], 
					$_POST['situacao'], 
					$_POST['DataIni'], 
					$_POST['DataFim']
				);
				break;
			case 'Manter':
				$this->manter($_POST['SequencialNoticia']);
				break;
			case 'Alterar':
				$this->alterar();
				break;
			case 'Excluir':
				$this->excluir();
				break;
			case 'Voltar':
				$this->voltar();
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
		$template = new TemplatePaginaPadrao("templates/CadNoticiaManter.html", "Notícias > Manter");
		$instancia = new CadNoticiaManter($template);
		$instancia->executar();
		unset($instancia);
	}
}

CadNoticiaManter::iniciar();
