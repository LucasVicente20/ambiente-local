<?php

#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadLiberarMovimentacaoIncluir.php
# Objetivo: Programa de inclusão de liberação de movimentação durante o período de bloqueio.
# Autor:    José Almir <jose.almir@pitang.com>
# Data:     17/11/2014
#-------------------------------------------------------------------------

require_once '../funcoes.php';
require_once 'AbstractCadLiberarMovimentacao.php';

class CadLiberarMovimentacaoIncluir extends AbstractCadLiberarMovimentacao
{
	private $template;
	
	public function __construct($template)
	{
		parent::__construct();
		$this->template = $template;
	}
	
	protected function getTemplate()
	{
		return $this->template;
	}
	
	private function run()
	{
		$this->frontController();
		return $this->getTemplate()->show();
	}
	
	private function frontController()
	{
		$botao = $this->variables['post']['Botao'];
	
		switch ($botao) {
			case 'Incluir':
				$this->incluir();
				break;
			case 'Principal':
			default:
				$this->principal();
		}
	}
	
	private function dadosValidos()
	{
		$validos = true;
		
		$dataInicial = $this->variables['post']['DataIni'];
		$dataFinal = $this->variables['post']['DataFim'];
		$sequencialOrgao = $this->variables['post']['Orgao'];
		
		if (empty($sequencialOrgao)) {
			$GLOBALS['Mensagem'] = ExibeMensStr("<a href='javascript:document.liberarMovimentacaoPesquisar.Orgao.focus();' class='titulo2'>Selecione um órgão</a>", 2, 0);
			return false;
		}
		
		$MensErro = ValidaData($dataInicial);
		if ($MensErro != "") {
			$GLOBALS['Mensagem'] = ExibeMensStr("<a href='javascript:document.liberarMovimentacaoPesquisar.DataIni.focus();' class='titulo2'>Data inicial inválida</a>", 2, 0);
			return false;
		}
		
		$MensErro = ValidaData($dataFinal);
		if ($MensErro != "") {
			$GLOBALS['Mensagem'] = ExibeMensStr("<a href='javascript:document.liberarMovimentacaoPesquisar.DataFim.focus();' class='titulo2'>Data final inválida</a>", 2, 0);
			return false;
		}
		
		if (ValidaData($dataInicial) == '' && ValidaData($dataFinal) == '') {
			$MensErro = ValidaPeriodo($dataInicial, $dataFinal, 0, "liberarMovimentacaoPesquisar");
			if ($MensErro != "") {
				$GLOBALS['Mensagem'] = ExibeMensStr("<a href='javascript:document.liberarMovimentacaoPesquisar.DataIni.focus();' class='titulo2'>Data Final igual ou maior que Data Inicial</a>", 2, 0);
				return false;
			}
		}

		if (!$this->dataDentroPeriodoBloqueio($dataInicial, $dataFinal)) {
			$GLOBALS['Mensagem'] = ExibeMensStr("<a href='javascript:document.liberarMovimentacaoPesquisar.DataIni.focus();' class='titulo2'>Período informado está fora do período de bloqueio</a>", 2, 0);
			return false;
		}
		
		if ($this->orgaoComLiberacaoCadastrada($sequencialOrgao)) {
			$GLOBALS['Mensagem'] = ExibeMensStr("<a href='javascript:document.liberarMovimentacaoPesquisar.Orgao.focus();' class='titulo2'>O órgão informado já possui uma liberação cadastrada</a>", 2, 0);
			return false;
		}
		
		return $validos;
	}

	private function incluir()
	{
		$mensagem = "";
		
		if ($this->dadosValidos()) {
			$sequencialOrgao = $this->variables['post']['Orgao'];
			$dataAlteracao = date('Y-m-d H:i:s');
			$sequencialUsuario = $_SESSION['_cusupocodi_'];
			$sequencialLiberacao = $this->getSequencial();
			
			$dataInicial = new DataHora($this->variables['post']['DataIni']);
			$dataInicial = $dataInicial->formata('Y-m-d') . ' 00:00:00';			
			$dataFinal = new DataHora($this->variables['post']['DataFim']);
			$dataFinal = $dataFinal->formata('Y-m-d') . ' 23:59:59';
			
			$db = Conexao();
			$sql  = " INSERT INTO SFPC.TBLIBERACAOMOVIMENTACAO ";
			$sql .= " (CLIBMOSEQU, CORGLICODI, TLIBMODINI, TLIBMODFIN, CUSUPOCODI, TLIBMOULAT) ";
			$sql .= " VALUES ";
			$sql .= " ($sequencialLiberacao, $sequencialOrgao, '$dataInicial', '$dataFinal', $sequencialUsuario, '$dataAlteracao') ";

			$resultado = executarSQL($db, $sql);
			$mensagem = ExibeMensStr("Liberação cadastrada com sucesso", 1, 0);
			
			$this->variables['post'] = array();
		} else {
			$mensagem = $GLOBALS['Mensagem'];
		}
		
		$this->getTemplate()->MENSAGEM_ERRO = $mensagem;
		$this->getTemplate()->block('BLOCO_ERRO', true);
		$this->principal();
	}
	
	private function getSequencial()
	{
		$db = Conexao();
		$sql  = " SELECT MAX(CLIBMOSEQU) AS ultimo_sequencial ";
		$sql .= " FROM SFPC.TBLIBERACAOMOVIMENTACAO ";
		
		$resultado = executarSQL($db, $sql);
		$ultimoSequencial = resultValorUnico($resultado);
		$proximoSequencial = (empty($ultimoSequencial)) ? 1 : $ultimoSequencial + 1;
		
		return $proximoSequencial;
	}
	
	private function principal()
	{
		$_SESSION['sequencialOrgaoManter'] = null;
		$this->carregarListaOrgaos('BLOCO_LISTA_ORGAO', $this->variables['post']['Orgao']);
		$this->iniciarDataLiberacao();
	}

	public static function bootstrap()
	{
		session_start();
		
		$template = new TemplatePaginaPadrao(
			"templates/CadLiberarMovimentacaoIncluir.template.html",
			"Estoques > Inventário > Liberar Movimentação > Incluir"
		);
		
		$app = new CadLiberarMovimentacaoIncluir($template);
		echo $app->run();
		unset($app);
	}
}

CadLiberarMovimentacaoIncluir::bootstrap();
