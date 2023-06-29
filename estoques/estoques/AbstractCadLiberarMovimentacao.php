<?php

#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: AbstractCadLiberarMovimentacao.php
# Objetivo: Classe abstrata do programa de liberação de movimentação durante o período de bloqueio.
# Autor:    José Almir <jose.almir@pitang.com>
# Data:     18/11/2014
#-------------------------------------------------------------------------

require_once '../funcoes.php';

abstract class AbstractCadLiberarMovimentacao
{
	protected $variables;
	
	public function __construct()	
	{
		$this->inicializar();
	}
	
	protected function iniciarDataLiberacao()
	{
		$periodo = $this->getPeriodoBloqueio();
		
		$dataInicial = $periodo['dataInicial'];
		if (isset($this->variables['post']['DataIni'])) {
			$dataInicial = $this->variables['post']['DataIni'];
		}
		
		$dataFinal = $periodo['dataFinal'];
		if (isset($this->variables['post']['DataFim'])) {
			$dataFinal = $this->variables['post']['DataFim'];
		}
		
		$this->iniciarDatas($dataInicial, $dataFinal);
	}

	protected function orgaoComLiberacaoCadastrada($sequencialOrgao)
	{
		$sql = "SELECT CORGLICODI FROM SFPC.TBLIBERACAOMOVIMENTACAO WHERE CORGLICODI = $sequencialOrgao";
		
		$db = Conexao();
		$resultado = executarSQL($db, $sql);
		$sequencialOrgaoLiberado = resultValorUnico($resultado);

		$proibirManipulacao = true;
		$sequencialOrgaoEmEdicao = $_SESSION['sequencialOrgaoManter'];
		$_SESSION['sequencialOrgaoManter'] = null;

		if (is_null($sequencialOrgaoLiberado) || $sequencialOrgaoLiberado == $sequencialOrgaoEmEdicao) {
			$proibirManipulacao = false;
		}
		
		return $proibirManipulacao;
	}
	
	protected function getPeriodoBloqueio()
	{
		$sql  = " SELECT EPARGEDATI, EPARGEDATF ";
		$sql .= " FROM SFPC.TBPARAMETROSGERAIS ";
	
		$db = Conexao();
		$resultado = executarSQL($db, $sql);
		$periodo = resultObjetoUnico($resultado);
	
		$dataInicial = $periodo->epargedati;
		if (!empty($dataInicial)) {
			$dataInicial = new DataHora($dataInicial);
			$dataInicial = $dataInicial->formata('d/m/Y');
		}
	
		$dataFinal = $periodo->epargedatf;
		if (!empty($dataFinal)) {
			$dataFinal = new DataHora($dataFinal);
			$dataFinal = $dataFinal->formata('d/m/Y');
		}
	
		return array(
			'dataInicial' => $dataInicial,
			'dataFinal' => $dataFinal
		);
	}
	
	protected function dataDentroPeriodoBloqueio($dataInicial, $dataFinal)
	{
		$periodoBloqueio = $this->getPeriodoBloqueio();
		$dataInicialBloqueio = new DataHora($periodoBloqueio['dataInicial']);
		$dataFinalBloqueio = new DataHora($periodoBloqueio['dataFinal']);
		$timestampInicioBloqueio = strtotime($dataInicialBloqueio->formata('Y-m-d'));
		$timestampFimBloqueio = strtotime($dataFinalBloqueio->formata('Y-m-d'));
	
		$dataInicialInformada = new DataHora($dataInicial);
		$dataFinalInformada = new DataHora($dataFinal);
		$timestampInicioInformado = strtotime($dataInicialInformada->formata('Y-m-d'));
		$timestampFimInformado = strtotime($dataFinalInformada->formata('Y-m-d'));
	
		if ($timestampInicioInformado >= $timestampInicioBloqueio && $timestampFimInformado <= $timestampFimBloqueio) {
			return true;
		}
	
		return false;
	}

	protected function carregarListaOrgaos($bloco, $sequencialOrgao = '')
	{
		$orgaos = $this->getOrgaos();

		$this->getTemplate()->ID_ORGAO = "";
		$this->getTemplate()->ORGAO_SELECIONADO = "";
		$this->getTemplate()->NOME_ORGAO = "Todos";
		$this->getTemplate()->block("BLOCO_LISTA_ORGAO");

		foreach ($orgaos as $item) {
			$this->getTemplate()->ID_ORGAO = $item->corglicodi;
			$this->getTemplate()->NOME_ORGAO = $item->eorglidesc;
			
			$selecioando = "";
			if (!empty($sequencialOrgao) && $sequencialOrgao == $item->corglicodi) {
				$selecioando = 'selected="selected"';
			}
			
			$this->getTemplate()->ORGAO_SELECIONADO = $selecioando;
			$this->getTemplate()->block($bloco);
		}
	}
	
	protected function getOrgaos()
	{
		$db = Conexao();
		$sql  = " SELECT DISTINCT A.CORGLICODI, B.EORGLIDESC ";
		$sql .= " FROM SFPC.TBCENTROCUSTOPORTAL A, SFPC.TBORGAOLICITANTE B ";
		$sql .= " WHERE A.CORGLICODI = B.CORGLICODI ";
		$sql .= "       AND A.FCENPOSITU <> 'I' ";
		$sql .= " ORDER BY B.EORGLIDESC";
		
		$resultado = executarSQL($db, $sql);
		
		$listaOrgaos = array();
		while ($resultado->fetchInto($item, DB_FETCHMODE_OBJECT)) {
			$listaOrgaos[] = $item;
		}
		
		return $listaOrgaos;
	}
	
	protected function iniciarDatas($dataInicialPadrao = '', $dataFinalPadrao = '')
	{
		$dataMes = DataMes();
		$dataInicial = $dataMes[0];
		$dataFinal = $dataMes[1];
		
		if (!empty($dataInicialPadrao) && !empty($dataFinalPadrao)) {
			$dataInicial = $dataInicialPadrao;
			$dataFinal = $dataFinalPadrao;
		}

		$this->getTemplate()->DATA_INICIAL = $dataInicial;
		$this->getTemplate()->DATA_FINAL = $dataFinal;
	}
	
	private function inicializar()
	{
		global $SimboloConcatenacaoArray, $SimboloConcatenacaoDesc;

		Seguranca();
		
		$arrayGlobals = array();
		$arrayGlobals['server'] = $_SERVER;
		$arrayGlobals['separatorArray'] = $SimboloConcatenacaoArray;
		$arrayGlobals['separatorDesc'] = $SimboloConcatenacaoDesc;
		
		if ($arrayGlobals['server']['REQUEST_METHOD'] == "POST") {
			$arrayGlobals['post'] = $_POST;
		}
		
		if ($arrayGlobals['server']['REQUEST_METHOD'] == 'GET') {
			$arrayGlobals['get'] = $_GET;
		}
		
		$this->variables = $arrayGlobals;
	}
}
