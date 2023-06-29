<?php
/**
 * Portal da DGCO
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
 * @package   registropreco
 * @author    Pitang Agile TI <contato@pitang.com>
 * @copyright 2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license   http://www.php.net/license/3_01.txt PHP License 3.01
 *
 * @version   GIT: EMPREL-SAD-PORTAL-COMPRAS-REGISTRO-PRECO-BL-FUNC-20160519-1035
 */

#---------------------------------------------------------------------
# Alterado: Pitang Agile TI - Caio Coutinho
# data: 12/03/2019
# Objetivo: Tarefa Redmine 212543
#---------------------------------------------------------------------

// 220038--

if (! @require_once dirname(__FILE__) . '/../bootstrap.php') {
    throw new Exception('Error Processing Request - Bootstrap', 1);
}

Seguranca();
if(!empty($_SESSION['mensagemFeedback'])){    //Condição para chegagem de mensagem de erro indevidamente vindo de outra pag. e limpar o campo de mensagem. |Madson|
    if($_SESSION['conferePagina'] != 'pesquisar'){
    unset($_SESSION['mensagemFeedback']);
    unset($_SESSION['conferePagina']);
    }
}
// Adiciona páginas no MenuAcesso #
AddMenuAcesso('/estoques/CadIncluirItem.php');
AddMenuAcesso('/estoques/CadItemDetalhe.php');

/**
 * A camada de Dados conterá o código que irá realizar todo o acesso aos dados.
 */
class RegistroPreco_Dados_CadRegistroPrecoIntencaoPesquisar extends Dados_Abstrata
{
    /**
     * Seleciona intenção no banco de dados
     *
     * @param integer $sequencialIntencao
     * @param integer $anoIntencao
     * @param string $dataInicioCadastro
     * @param string $dataFimCadastramento
     *
     * @return [type] [description]
     */
    public function selecionarIntencao($sequencialIntencao, $anoIntencao, $dataInicioCadastro, $dataFimCadastro)
    {
        $sql = ' 
        	select
				distinct a.cintrpsequ,
				a.cintrpsano,
				a.tintrpdlim,
				a.xintrpobje,
				a.tintrpdcad,
				a.cusupocodi,
                a.fintrpsitu
			from
				sfpc.tbintencaoregistropreco a 
        		inner join sfpc.tbintencaorporgao b 
        		        on a.cintrpsequ = b.cintrpsequ and a.cintrpsano = b.cintrpsano 
        		inner join sfpc.tborgaolicitante c 
        		        on b.corglicodi = c.corglicodi
           where 1 = 1 ';
        
        if (! is_null($sequencialIntencao)) {
            $sql .= " AND a.cintrpsequ = $sequencialIntencao ";
        }
        
        if (! is_null($anoIntencao)) {
            $sql .= " AND a.cintrpsano = $anoIntencao ";
        }
        
        if (! is_null($dataInicioCadastro) && is_null($dataFimCadastro)) {
            $sql .= " AND to_char(a.tintrpdcad, 'YYYY-MM-DD') >= '$dataInicioCadastro' ";
        }
        
        if (! is_null($dataFimCadastro) && is_null($dataInicioCadastro)) {
            $sql .= " AND to_char(a.tintrpdcad, 'YYYY-MM-DD') <= '$dataFimCadastro' ";
        }
        
        if (! is_null($dataInicioCadastro) && ! is_null($dataFimCadastro)) {
            $sql .= " AND to_char(a.tintrpdcad, 'YYYY-MM-DD') >= '$dataInicioCadastro' ";
            $sql .= " AND to_char(a.tintrpdcad, 'YYYY-MM-DD') <= '$dataFimCadastro' ";
        }
        
        $sql .= " ORDER BY a.cintrpsano DESC, a.cintrpsequ ASC ";
        $sql = stripcslashes($sql);
        
        $res = $this->executarSQL($sql);
        
        $this->hasError($res);
        return $res;
    }
}

class RegistroPreco_Negocio_CadRegistroPrecoIntencaoPesquisar extends Negocio_Abstrata
{
    /**
     *
     * {@inheritDoc}
     *
     * @see Negocio_Abstrata::getDados()
     */
    public function getDados()
    {
        $this->setDados(new RegistroPreco_Dados_CadRegistroPrecoIntencaoPesquisar());
        return parent::getDados();
    }

    /**
     * Filtro para pesquisa seja válida
     *
     * @return [type] [description]
     */
    public function filtroPesquisaValido()
    {
        $dataInicio = filter_var($_POST['DataInicioCadastro'], FILTER_SANITIZE_STRING);
        $dataFim = filter_var($_POST['DataFimCadastro'], FILTER_SANITIZE_STRING);
        
        if (empty($dataInicio) || ! ClaHelper::validationData($dataInicio)) {
            $_SESSION['mensagemFeedback'] = 'Data de início não é válida';
            $_SESSION['conferePagina'] = 'pesquisar';
            return false;
        }
        
        if (empty($dataFim) || ! ClaHelper::validationData($dataFim)) {
            $_SESSION['mensagemFeedback'] = 'Data de fim não é válida';
            $_SESSION['conferePagina'] = 'pesquisar';
            return false;
        }
        $start = ClaHelper::dateTimeFormat($dataInicio);
        $end = ClaHelper::dateTimeFormat($dataFim);
        
        if ($end < $start) {
            $_SESSION['mensagemFeedback'] = 'Data de fim é menor que a Data de início';
            $_SESSION['conferePagina'] = 'pesquisar';
            return false;
        }
        
        return true;
    }

    /**
     * [pesquisar description]
     *
     * @return [type] [description]
     */
    public function pesquisar()
    {
        
        // Número intenção
        $numeroIntencao = explode('/', $_POST['NumeroIntencao']);
        
        $sequencialIntencao = null;
        if (isset($numeroIntencao[0]) && $numeroIntencao[0] != "") {
            $sequencialIntencao = $numeroIntencao[0];
        }
        
        $anoIntencao = null;
        if (isset($numeroIntencao[1]) && $numeroIntencao[1] != "") {
            $anoIntencao = $numeroIntencao[1];
        }
        
        // Data inicial
        $dataInicioCadastro = filter_var($_POST['DataInicioCadastro'], FILTER_SANITIZE_STRING);
        if (! empty($dataInicioCadastro)) {
            $dataInicioCadastro = ClaHelper::converterDataBrParaBanco($dataInicioCadastro);
        }
        
        // Data final
        $dataFimCadastro = filter_var($_POST['DataFimCadastro'], FILTER_SANITIZE_STRING);
        if (! empty($dataFimCadastro)) {
            $dataFimCadastro = ClaHelper::converterDataBrParaBanco($dataFimCadastro);
        }
        
        $dao = new RegistroPreco_Dados_CadRegistroPrecoIntencaoPesquisar();
        
        return $dao->selecionarIntencao($sequencialIntencao, $anoIntencao, $dataInicioCadastro, $dataFimCadastro);
    }
}

/**
 * A camada de Adaptação e Transformação conterá o código que tratará a lógica de apresentação dos resultados
 * das requisições dos usuários e a troca de dados com sistemas externos.
 *
 * Utiliza serviços da camada de Negócio.
 */
class RegistroPreco_Adaptacao_CadRegistroPrecoIntencaoPesquisar extends Adaptacao_Abstrata
{
    /**
     *
     * {@inheritDoc}
     *
     * @see Adaptacao_Abstrata::getNegocio()
     */
    public function getNegocio()
    {
        $this->setNegocio(new RegistroPreco_Negocio_CadRegistroPrecoIntencaoPesquisar());
        return parent::getNegocio();
    }

    /**
     * Configuration Initial
     */
    public function configInitial()
    {
        
        // Caso o usuário clicou em voltar na tela de manter recarrega a pesquisa
        if (isset($_SESSION['voltarPesquisa'])) {
            $ultimoFiltro = $_SESSION['ultimoFiltro'];
            
            $_POST['NumeroIntencao'] = $ultimoFiltro['NumeroIntencao'];
            $_POST['DataInicioCadastro'] = $ultimoFiltro['DataInicioCadastro'];
            $_POST['DataFimCadastro'] = $ultimoFiltro['DataFimCadastro'];
            
            unset($_SESSION['voltarPesquisa']);
            
            $this->pesquisar();
        }
    }
}

/**
 * A camada de Interface Gráfica com o Usuário é a camada que conterá o código que irá implementar a interação
 * do sistema com os usuários (telas, relatórios e troca de dados).
 *
 * Utiliza serviços da camada de Adaptação e Transformação.
 */
class RegistroPreco_UI_CadRegistroPrecoIntencaoPesquisar extends UI_Abstrata
{
    const CAMINHO_TEMPLATE = "templates/CadRegistroPrecoIntencaoPesquisar.html";

    /**
     *
     * {@inheritDoc}
     *
     * @see UI_Abstrata::getAdaptacao()
     */
    public function getAdaptacao()
    {
        $this->setAdaptacao(new RegistroPreco_Adaptacao_CadRegistroPrecoIntencaoPesquisar());
        return parent::getAdaptacao();
    }

    /**
     */
    public function recuperarDadosInformados()
    {
        if (isset($_POST['NumeroIntencao'])) {
            $this->getTemplate()->VALOR_NUMERO_INTENCAO = $_POST['NumeroIntencao'];
        }
        
        if (isset($_POST['DataInicioCadastro'])) {
            $this->getTemplate()->VALOR_DATA_INICIO_CADASTRO = $_POST['DataInicioCadastro'];
        }
        
        if (isset($_POST['DataFimCadastro'])) {
            $this->getTemplate()->VALOR_DATA_FIM_CADASTRO = $_POST['DataFimCadastro'];
        }
    }

    /**
     * [__construct description]
     */
    public function __construct()
    {
        $this->setTemplate(new TemplatePaginaPadrao(self::CAMINHO_TEMPLATE, "Registro de Preço > Intenção > Manter"));
        
        $dataMes = DataMes();
        
        $this->getTemplate()->VALOR_NUMERO_INTENCAO = '';
        $this->getTemplate()->VALOR_DATA_INICIO_CADASTRO = $dataMes[0];
        $this->getTemplate()->VALOR_DATA_FIM_CADASTRO = $dataMes[1];
    }

    /**
     * [plotarTelaPesquisa description]
     *
     * @param [type] $resultSet
     *            [description]
     * @return [type] [description]
     */
    public function plotarTelaPesquisa($resultSet)
    {
        unset($_SESSION['colecaoMensagemErro'], $_SESSION['mensagemFeedback']);
        $this->getTemplate()->MENSAGEM_ERRO = null;
        $this->getTemplate()->clear('MENSAGEM_ERRO');
        
        $this->recuperarDadosInformados();
        
        if ($this->getAdaptacao()
            ->getNegocio()
            ->filtroPesquisaValido()) {
            $resultSet = $this->getAdaptacao()
                ->getNegocio()
                ->pesquisar();
            $row = null;
            foreach ($resultSet as $row) {
                $this->getTemplate()->VALOR_NUMERO_INTENCAO_ITEM = substr($row->cintrpsequ + 10000, 1) . '/' . $row->cintrpsano;
                $tintrpdcad = new DateTime($row->tintrpdcad);
                $this->getTemplate()->VALOR_DATA_CADASTRO_ITEM = $tintrpdcad->format('d/m/Y');
                $this->getTemplate()->VALOR_OBJETO_ITEM = $row->xintrpobje;
                $this->getTemplate()->VALOR_SITUACAO_ITEM = ($row->fintrpsitu == 'I') ? 'INATIVO' : 'ATIVO';
                
                $this->getTemplate()->block("BLOCO_LISTAGEM_ITEM");
            }
            
            if (count($resultSet) > 0) {
                $this->getTemplate()->block("BLOCO_HEADER_LISTAGEM_ITEM");
                $this->getTemplate()->block("BLOCO_RESULTADO_PEQUISA");
            } else {
                $this->mensagemSistema('Não existem dados para os filtros informados', 1, 1);
                $this->getTemplate()->block('BLOCO_ERRO', true);
            }
        }
    }
}

class CadRegistroPrecoIntencaoPesquisar extends ProgramaAbstrato
{
    /**
     * [plotarTelaInicial description]
     *
     * @return [type] [description]
     */
    private function plotarTelaInicial()
    {
        if (isset($_SESSION['mensagemFeedback'])) {
            $mensagemErro = $_SESSION['mensagemFeedback'];
            if (is_array($_SESSION['mensagemFeedback'])) {
                $mensagemErro = '';
                foreach ($_SESSION['mensagemFeedback'] as $mensagem) {
                    $mensagemErro .= $mensagem;
                }
            }
            
            $this->getUI()->getTemplate()->MENSAGEM_ERRO = ExibeMensStr($mensagemErro, 1, 0);
            $this->getUI()
                ->getTemplate()
                ->block('BLOCO_ERRO', true);
            
            unset($_SESSION['mensagemFeedback']);
        }
    }

    private function pesquisar()
    {
        $this->getUI()->recuperarDadosInformados();
        $resultado = array();
        if (! $this->getUI()
            ->getAdaptacao()
            ->getNegocio()
            ->filtroPesquisaValido()) {
            $this->plotarTelaInicial();
            return;
        }
        
        $resultado = $this->getUI()
            ->getAdaptacao()
            ->getNegocio()
            ->pesquisar();
        
        $this->getUI()->plotarTelaPesquisa($resultado);
        // Último filtro realizado
        $_SESSION['ultimoFiltro'] = array(
            'NumeroIntencao' => $_POST['NumeroIntencao'],
            'DataInicioCadastro' => $_POST['DataInicioCadastro'],
            'DataFimCadastro' => $_POST['DataFimCadastro']
        );
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see ProgramaAbstrato::configuracao()
     */
    protected function configuracao()
    {
        $this->setUI(new RegistroPreco_UI_CadRegistroPrecoIntencaoPesquisar());
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see ProgramaAbstrato::frontController()
     */
    protected function frontController()
    {
        // Quando o programa for chamado
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            $_GET = filter_var_array($_GET, FILTER_SANITIZE_STRING);
            $this->plotarTelaInicial();
        }
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_var_array($_POST, FILTER_SANITIZE_STRING);
            switch ($_POST['Botao']) {
                case 'Pesquisar':
                    $this->pesquisar();
                    break;
                default:
                    $this->plotarTelaInicial();
                    break;
            }
        }
    }
    
    public function __destruct()
    {
        unset($_SESSION['ultimoFiltro']);
    }
}

ProgramaAbstrato::iniciar(new CadRegistroPrecoIntencaoPesquisar());
