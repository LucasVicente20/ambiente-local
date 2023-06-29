<?php

/**
 * Portal da DGCO.
 *
 * PHP version 5.2.5
 *
 * LICENSE: This source file is subject to version 3.01 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_01.txt. If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category  Pitang Registro Preço
 *
 * @author    Pitang Agile TI <contato@pitang.com>
 * @copyright 2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license   http://www.php.net/license/3_01.txt PHP License 3.01
 */

# Alterado: Pitang Agile TI - Caio Coutinho
# Data:     07/11/2018
# Objetivo: Tarefa Redmine 205440
#---------------------------------------------------------------------
# Alterado: Pitang Agile TI - Caio Coutinho
# data: 12/02/2019
# Objetivo: Tarefa Redmine 210654
#---------------------------------------------------------------------
# Alterado: Osmar Celestindo
# data: 01/01/2023
# Objetivo: Urgência em Produção Relacionado a Compras.
#---------------------------------------------------------------------

// 220038--

if (! @require_once dirname(__FILE__) . '/../bootstrap.php') {
    throw new Exception('Error Processing Request - Bootstrap', 1);
}

Seguranca();

AddMenuAcesso('/estoques/CadIncluirItem.php');
AddMenuAcesso('/estoques/CadItemDetalhe.php');

/**
 *
 * @author jfsi
 *
 */
class RegistroPreco_Dados_CadVisualizarIntencaoRegistroPreco extends Dados_Abstrata
{

    /**
     *
     * @param integer $sequencialIntencao
     * @param integer $anoIntencao
     * @throws InvalidArgumentException
     * @return string
     */
    public function sqlSelectIntencao($sequencialIntencao, $anoIntencao)
    {
        if (! filter_var($sequencialIntencao, FILTER_VALIDATE_INT)) {
            throw new InvalidArgumentException('Informe $sequencialIntenco do tipo inteiro');
        }
        $sequencialIntencao = filter_var($sequencialIntencao, FILTER_SANITIZE_NUMBER_INT);
        
        if (! filter_var($anoIntencao, FILTER_VALIDATE_INT)) {
            throw new InvalidArgumentException('Informe $anoIntencao do tipo inteiro');
        }
        $anoIntencao = filter_var($anoIntencao, FILTER_SANITIZE_NUMBER_INT);
        
        $sql = ' SELECT DISTINCT a.cintrpsequ,  a.cintrpsano, a.tintrpdlim, a.xintrpobje, ';
        $sql .= '        a.xintrpobse, a.fintrpsitu, a.tintrpdcad, a.cusupocodi, a.tintrpulat ';
        $sql .= ' FROM ';
        $sql .= '        sfpc.tbintencaoregistropreco a ';
        $sql .= ' INNER JOIN SFPC.TBRESPOSTAINTENCAORP RI ';
        $sql .= " 		ON a.cintrpsequ = RI.cintrpsequ AND a.cintrpsano = RI.cintrpsano AND RI.frinrpsitu = 'A' ";
        $sql .= ' WHERE ';
        $sql .= "		a.cintrpsequ = $sequencialIntencao AND a.cintrpsano = $anoIntencao ";
        
        return $sql;
    }

    /**
     *
     * @param integer $sequencialIntencao
     * @param integer $anoIntencao
     * @param string $ordem
     * @throws InvalidArgumentException
     */
    public function sqlSelectOrgaosIntencao($sequencialIntencao, $anoIntencao, $ordem = 'nome')
    {
        if (! filter_var($sequencialIntencao, FILTER_VALIDATE_INT)) {
            throw new InvalidArgumentException('Informe $sequencialIntenco do tipo inteiro');
        }
        $sequencialIntencao = filter_var($sequencialIntencao, FILTER_SANITIZE_NUMBER_INT);
        
        if (! filter_var($anoIntencao, FILTER_VALIDATE_INT)) {
            throw new InvalidArgumentException('Informe $anoIntencao do tipo inteiro');
        }
        $anoIntencao = filter_var($anoIntencao, FILTER_SANITIZE_NUMBER_INT);
        
        $orderBy = ' b.eorglidesc ';
        if ($ordem == 'situacao') {
            $orderBy = ' c.frinrpsitu ';
        }
        
        $sql = ' SELECT a.cintrpsequ, a.cintrpsano, a.corglicodi, b.eorglidesc, c.frinrpsitu, c.trinrpulat ';
        $sql .= ' FROM sfpc.tbintencaorporgao a ';
        $sql .= ' INNER JOIN sfpc.tborgaolicitante b ON a.corglicodi = b.corglicodi ';
        $sql .= ' LEFT OUTER JOIN sfpc.tbrespostaintencaorp c ON a.corglicodi = c.corglicodi ';
        $sql .= " AND c.cintrpsequ = $sequencialIntencao AND c.cintrpsano = $anoIntencao ";
        $sql .= ' WHERE ';
        $sql .= "      a.cintrpsequ = $sequencialIntencao ";
        $sql .= "      AND a.cintrpsano = $anoIntencao ";
        $sql .= " ORDER BY $orderBy ";
        
        return $sql;
    }
}

/**
 *
 * @author jfsi
 *
 */
class RegistroPreco_Adaptacao_CadVisualizarIntencaoRegistroPreco extends Adaptacao_Abstrata
{

    /**
     *
     * @param UI_Interface $template
     */
    public function listarOrgaosIntencao(UI_Interface $template)
    {
        $row = null;
        $chaveIntencao = $this->getChaveIntencao();
        $dados = new RegistroPreco_Dados_CadVisualizarIntencaoRegistroPreco();
        $sql = $dados->sqlSelectOrgaosIntencao($chaveIntencao['sequencialIntencao'], $chaveIntencao['anoIntencao']);
        $resultado = executarSQL(ClaDatabasePostgresql::getConexao(), $sql);
        while ($resultado->fetchInto($row, DB_FETCHMODE_OBJECT)) {
            $template->getTemplate()->ORGAO_INTENCAO = $row->eorglidesc;
            $template->getTemplate()->block('BLOCO_ORGAO_INTENCAO');
        }
    }

    /**
     */
    public function getChaveIntencao()
    {
        $chaveIntencao = array(
            'sequencialIntencao' => '',
            'anoIntencao' => ''
        );
        $numeroIntencao = $_GET['numero'];
        
        if (empty($numeroIntencao)) {
            $numeroIntencao = $_POST['NumeroIntencaoAcessada'];
        }
        
        if (! empty($numeroIntencao)) {
            $numeroIntencao = explode('/', $numeroIntencao);
            $sequencialIntencao = (isset($numeroIntencao[0]) && $numeroIntencao[0] != '') ? $numeroIntencao[0] : null;
            $anoIntencao = (isset($numeroIntencao[1]) && $numeroIntencao[1] != '') ? $numeroIntencao[1] : null;
            
            $chaveIntencao['sequencialIntencao'] = (int) $sequencialIntencao;
            $chaveIntencao['anoIntencao'] = (int) $anoIntencao;
        }
        
        return $chaveIntencao;
    }

    public function documentosIntencao($cintrpsequ, $cintrpsano) {
        $sql = " SELECT cintrpsequ, encode(iintraarqu, 'base64') as iintraarqu, cintrpsequ, cintrpsano, eintranome 
                 FROM SFPC.TBINTENCAOREGISTROPRECOANEXO WHERE cintrpsequ = $cintrpsequ AND cintrpsano = $cintrpsano";
        $database = ClaDatabasePostgresql::getConexao();
        $res = &$database->getAll($sql, array(), DB_FETCHMODE_OBJECT);
        ClaDatabasePostgresql::hasError($res);

        return $res;
    }
}

/**
 *
 * @author jfsi
 *
 */
class RegistroPreco_UI_CadVisualizarIntencaoRegistroPreco extends UI_Abstrata
{

    /**
     */
    private function loadIntencao()
    {
        $this->setAdaptacao(new RegistroPreco_Adaptacao_CadVisualizarIntencaoRegistroPreco());
        $chaveIntencao = $this->getAdaptacao()->getChaveIntencao();
        $this->plotarDocumentos($chaveIntencao['sequencialIntencao'], $chaveIntencao['anoIntencao']);
        
        if (! empty($chaveIntencao['sequencialIntencao']) && ! empty($chaveIntencao['anoIntencao'])) {
            $dados = new RegistroPreco_Dados_CadVisualizarIntencaoRegistroPreco();
            $sql = $dados->sqlSelectIntencao($chaveIntencao['sequencialIntencao'], $chaveIntencao['anoIntencao']);
            $resultSet = executarSQL(ClaDatabasePostgresql::getConexao(), $sql);
            
            $this->intencao = $resultSet->fetchRow(DB_FETCHMODE_OBJECT);
        }
        
        if (is_null($this->intencao)) {
            $this->getTemplate()->MENSAGEM_ERRO = ExibeMensStr('Intenção selecionada não existe resposta', 2, 1);
            $this->getTemplate()->block('BLOCO_ERRO', true);
        }
    }

    public function plotarDocumentos($cintrpsequ, $cintrpsano) {
        // Documentos
        $documentos = $this->getAdaptacao()->documentosIntencao($cintrpsequ, $cintrpsano);
        if(!empty($documentos)) {
            foreach ($documentos as $key => $documento) {
                $documentoDecodificado = base64_decode($documento->iintraarqu);
                $this->getTemplate()->VALOR_DOCUMENTO_KEY = 'documento'.$documento->cintrasequ.'arquivo'.$key;
                $this->getTemplate()->VALOR_DOCUMENTO_NOME = $documento->eintranome;
                $this->getTemplate()->HEX_DOCUMENTO  = $documentoDecodificado;
                $this->getTemplate()->block('BLOCO_DOCUMENTO');
            }
            $this->getTemplate()->block('BLOCO_DOCUMENTOS');
        }
    }

    /**
     */
    private function configInitial()
    {
        $this->loadIntencao();
        
        if (! is_null($this->intencao)) {
            $this->setAdaptacao(new RegistroPreco_Adaptacao_CadVisualizarIntencaoRegistroPreco());
            $this->getAdaptacao()->listarOrgaosIntencao($this);
            $this->getTemplate()->VALOR_NUMERO_INTENCAO = str_pad($this->intencao->cintrpsequ, 4, '0', STR_PAD_LEFT) . '/' . $this->intencao->cintrpsano;
            $this->getTemplate()->VALOR_DATA_CADASTRAMENTO_INTENCAO = ClaHelper::converterDataBancoParaBr($this->intencao->tintrpdcad);
            $this->getTemplate()->VALOR_DATA_LIMITE_INTENCAO = ClaHelper::converterDataBancoParaBr($this->intencao->tintrpdlim);
            $this->getTemplate()->VALOR_OBJETO_INTENCAO = $this->intencao->xintrpobje;
            $this->getTemplate()->VALOR_OBSERVACAO_INTENCAO = $this->intencao->xintrpobse;
            $this->getTemplate()->VALOR_SITUACAO_ATUAL_INTENCAO = $this->intencao->fintrpsitu;
            
            $this->getTemplate()->block('BLOCO_FORMULARIO_MANTER');
        }
    }

    /**
     */
    public function __construct()
    {
        $template = new TemplatePortal('templates/CadVisualizarIntencaoRegistroPreco.html');
        $this->setTemplate($template);
    }

    /**
     */
    public function proccessPrincipal()
    {
        $this->configInitial();
        
        if (! empty($this->variables['post']['NumeroIntencao'])) {
            $this->getTemplate()->VALOR_NUMERO_INTENCAO = $this->variables['post']['NumeroIntencao'];
        }
        
        if (! empty($this->variables['post']['DataInicioCadastro'])) {
            $this->getTemplate()->VALOR_DATA_INICIO_CADASTRO = $this->variables['post']['DataInicioCadastro'];
        }
        
        if (! empty($this->variables['post']['DataFimCadastro'])) {
            $this->getTemplate()->VALOR_DATA_FIM_CADASTRO = $this->variables['post']['DataFimCadastro'];
        }
    }

    /**
     */
    public function processVoltar()
    {
        // Flag que indica o botão voltar
        $_SESSION['voltarPesquisa'] = true;
        
        header('Location: CadIncluirIntencaoRegistroPreco.php');
        exit();
    }

    /**
     */
    public function adicionarItensSessao()
    {
        global $SimboloConcatenacaoArray, $SimboloConcatenacaoDesc;
        $row = null;
        
        $this->setAdaptacao(new RegistroPreco_Adaptacao_CadVisualizarIntencaoRegistroPreco());
        $chaveIntencao = $this->getAdaptacao()->getChaveIntencao();
        //kim 27022020    
        $sql = Helper_Acompanhar_Visualizar::sqlSelectItemIntencao($chaveIntencao['sequencialIntencao'], $chaveIntencao['anoIntencao'], $_SESSION['CentroCusto']);
        $retorno = executarSQL(ClaDatabasePostgresql::getConexao(), $sql);
        
        while ($retorno->fetchInto($row, DB_FETCHMODE_OBJECT)) {     
            // Caso seja CADAgrupar, estamos precisando na verdade a inteção

            $isCadAgrupar = ($_GET['ProgramaOrigem'] == 'CadAgrupar');
            if ($isCadAgrupar) {                
                $_SESSION['intencao'] = $chaveIntencao['sequencialIntencao'] . $SimboloConcatenacaoArray . $chaveIntencao['anoIntencao'];                
                break;
            }
            
            // Quantidade consolidada
            $sqlItemConsolidado = Helper_Acompanhar_Visualizar::sqlQuantidadeConsolidada($row->cintrpsequ, $row->cintrpsano, $row->citirpsequ);
            $resultado = executarSQL(ClaDatabasePostgresql::getConexao(), $sqlItemConsolidado);
            $resultado->fetchInto($resultadoConsolidado, DB_FETCHMODE_OBJECT);
            $row->airirpqtpr = $resultadoConsolidado->consolidado;
                        
            $item = Helper_Acompanhar_Visualizar::unificarChavesItem($row);
            $descricao = RetiraAcentos($item->descricao . $SimboloConcatenacaoDesc . str_replace('"', '”', $item->descricao));
            $tipoItem = ($item->tipo == 'CADUM') ? 'M' : 'S';
            
            $valorUnitario = $item->valorUnitario;
            
            $itemSessao = $descricao . $SimboloConcatenacaoArray;
            $itemSessao .= $item->sequencialItem . $SimboloConcatenacaoArray;
            $itemSessao .= 'undMedidaSigla' . $SimboloConcatenacaoArray;
            $itemSessao .= $tipoItem . $SimboloConcatenacaoArray;
            $itemSessao .= $valorUnitario . $SimboloConcatenacaoArray;
            $itemSessao .= $SimboloConcatenacaoArray;
            $itemSessao .= $SimboloConcatenacaoArray;
            $itemSessao .= converte_valor_estoques($item->quantidadeConsolidada) . $SimboloConcatenacaoArray;
            $itemSessao .= $item->descricaoDetalhada;
            
            $_SESSION['item'][count($_SESSION['item'])] = $itemSessao;
        }

        $sequencialIntencao = intval($chaveIntencao['sequencialIntencao']);
        $anoIntencao = $chaveIntencao['anoIntencao'];
        
        if ($_GET['ProgramaOrigem'] == 'CadAgrupar') {
            echo '<script>opener.document.CadAgrupar.submit()</script>';
            echo '<script>self.close()</script>';
        } else {
            echo '<script>opener.document.CadSolicitacaoCompraIncluirManterExcluir.sequencialIntencao.value=' . $sequencialIntencao . '</script>';
            echo "<script>opener.document.CadSolicitacaoCompraIncluirManterExcluir.anoIntencao.value=$anoIntencao</script>";
            echo '<script>opener.document.CadSolicitacaoCompraIncluirManterExcluir.submit()</script>';
            echo '<script>self.close()</script>';
        }
        
        return;
    }

    // kim: função para colocar os dados da irp na sessão sem os items da irp
    public function ExibeIrpSemItens(){
        global $SimboloConcatenacaoArray, $SimboloConcatenacaoDesc;
        $row = null;
        
        $this->setAdaptacao(new RegistroPreco_Adaptacao_CadVisualizarIntencaoRegistroPreco());
        $chaveIntencao = $this->getAdaptacao()->getChaveIntencao();
        //kim 27022020    
        $isCadAgrupar = ($_GET['ProgramaOrigem'] == 'CadAgrupar');
            if ($isCadAgrupar) {                
                $_SESSION['intencao'] = $chaveIntencao['sequencialIntencao'] . $SimboloConcatenacaoArray . $chaveIntencao['anoIntencao'];                
               exit;
            }

        $sequencialIntencao = intval($chaveIntencao['sequencialIntencao']);
        $anoIntencao = $chaveIntencao['anoIntencao'];
        
        if ($_GET['ProgramaOrigem'] == 'CadAgrupar') {
            echo '<script>opener.document.CadAgrupar.submit()</script>';
            echo '<script>self.close()</script>';
        } else {
            echo '<script>opener.document.CadSolicitacaoCompraIncluirManterExcluir.sequencialIntencao.value=' . $sequencialIntencao . '</script>';
            echo "<script>opener.document.CadSolicitacaoCompraIncluirManterExcluir.anoIntencao.value=$anoIntencao</script>";
            echo '<script>opener.document.CadSolicitacaoCompraIncluirManterExcluir.submit()</script>';
            echo '<script>self.close()</script>';
        }
        
        return;
    }
}

$gui = new RegistroPreco_UI_CadVisualizarIntencaoRegistroPreco();

$botao = ! empty($_POST['Botao']) ? $_POST['Botao'] : 'Principal';

switch ($botao) {
    case 'Voltar':
        $gui->processVoltar();
        break;
    case 'SelecionarIntencao':
        $gui->adicionarItensSessao();
        break;
    case 'Principal':
    default:
        if($_GET['ProgramaOrigem'] == "CadSolicitacaoCompraIncluirManterExcluir"){
            $gui->ExibeIrpSemItens();
        }else{
            $gui->proccessPrincipal();
        }
        break;
}

echo $gui->getTemplate()->show();
