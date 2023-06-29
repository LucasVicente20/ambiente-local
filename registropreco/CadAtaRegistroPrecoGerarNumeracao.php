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
 * @author    Pitang Agile TI <contato@pitang.com>
 * @copyright 2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license   http://www.php.net/license/3_01.txt PHP License 3.01
 */

 #-------------------------------------------------------------------------
# Alterado: Caio Coutinho - Pitang Agile TI
# Data:     14/09/2018
# Objetivo: Tarefa Redmine 201632
#-------------------------------------------------------------------------

// 220038--

if (!@include_once dirname(__FILE__).'/../bootstrap.php') {
    throw new Exception('Error Processing Request - Bootstrap', 1);
}

Seguranca();

if(!empty($_SESSION['mensagemFeedback'])){    //Condição para chegagem de mensagem de erro indevidamente vindo de outra pag. e limpar o campo de mensagem. |Madson|
    if($_SESSION['conferePagina'] == 'carona' || 'participante' || 'selecionar' || 'pesquisar'){
    unset($_SESSION['mensagemFeedback']);
    unset($_SESSION['conferePagina']);
    }
}
/**
 * A camada de Dados conterá o código que irá realizar todo o acesso aos dados.
 */
class RegistroPreco_Dados_CadAtaRegistroPrecoGerarNumeracao extends Dados_Abstrata
{
    public function consultarOrgaoComNumeracaoGerada($corporativo = false)
    {       
        if($corporativo) {
            $resultado = ClaDatabasePostgresql::executarSQL($this->sqlGeralOrgaoAtaGerada());
        } else {
            $resultado = ClaDatabasePostgresql::executarSQL($this->sqlOrgaoAtaGerada());
        }

        ClaDatabasePostgresql::hasError($resultado);

        return $resultado;
    }

    /**
     * Consulta o órgão de acordo com o usuário logado
     *
     * @return string
     */
    private function sqlOrgaoAtaGerada()
    {
        $cgrempcodi = $_SESSION['_cgrempcodi_'];
        $cusupocodi = $_SESSION['_cusupocodi_'];

        $sql = "
            select distinct Orgao.corglicodi, Orgao.eorglidesc from sfpc.tbusuariocentrocusto as UsuarioCusto
            inner join sfpc.tbcentrocustoportal as CentroCusto on UsuarioCusto.ccenposequ = CentroCusto.ccenposequ
	        inner join sfpc.tborgaolicitante as Orgao on Orgao.corglicodi = CentroCusto.corglicodi
            where UsuarioCusto.cgrempcodi = $cgrempcodi and UsuarioCusto.cusupocodi = $cusupocodi and UsuarioCusto.fusucctipo = 'C'
        ";

        return $sql;
    }

    /**
     * Consulta todos os orgãos
     *
     * @return string
     */
    private function sqlGeralOrgaoAtaGerada()
    {
        $sql = "select distinct Orgao.corglicodi, Orgao.eorglidesc from sfpc.tborgaolicitante as Orgao where 1=1 
        order by Orgao.eorglidesc ASC";
        return $sql;
    }

    /**
     * Consulta SQL para listar todos os processos que estão na fase 13 = HOMOLOGACAO
     * e que são do tipo registro de preço (flicporegp = 'S').
     *
     * @param int $ano
     *                   [description]
     * @param int $orgao
     *                   [description]
     *
     * @return string [description]
     */
    private function sqlProcessoRegistroPrecoEmHomologacao($ano, $orgao)
    {
        $licHomologacao = 13;
        $licHomParcial  = 26;

        $sql = "
            SELECT
            DISTINCT ON ( B.ecomlidesc, l.alicpoanop, l.clicpoproc ) l.clicpoproc,
                l.alicpoanop,
                l.ccomlicodi,
                l.cgrempcodi,
                l.corglicodi,
                B.ecomlidesc,
                e.cfasescodi
            FROM
                sfpc.tblicitacaoportal l
                INNER JOIN
                    sfpc.tbcomissaolicitacao B
                        ON l.ccomlicodi = B.ccomlicodi
                INNER JOIN sfpc.tbusuariocomis D
                    ON l.cgrempcodi = D.cgrempcodi
                        AND D.ccomlicodi = l.ccomlicodi
                INNER JOIN sfpc.tbfaselicitacao E
                    ON l.clicpoproc = e.clicpoproc
                        AND l.alicpoanop = e.alicpoanop
                        AND l.cgrempcodi = e.cgrempcodi
                        AND l.ccomlicodi = e.ccomlicodi
                        AND l.corglicodi = e.corglicodi
            WHERE  l.alicpoanop = %d    ";
        
        if($_SESSION['_fperficorp_'] != 'S') {
            $sql .= " AND l.cgrempcodi = ".$_SESSION['_cgrempcodi_'];
        }

        $sql .= " AND l.flicporegp LIKE 'S'
                AND l.corglicodi = %d
                AND e.cfasescodi IN (%d,%d)
            GROUP BY l.clicpoproc, l.alicpoanop, l.ccomlicodi, l.cgrempcodi, l.corglicodi, B.ecomlidesc, e.cfasescodi
            ORDER BY B.ecomlidesc ASC, l.alicpoanop DESC, l.clicpoproc DESC ";

        return sprintf($sql, $ano, $orgao, $licHomologacao, $licHomParcial);
    }

    /**
     * Comando SQL para consultar a quantidade de Ata de Registro de Preço
     * gerada para a licitação.
     *
     * @param int $ano
     *                      [description]
     * @param int $processo
     *                      [description]
     * @param int $orgao
     *                      [description]
     * @param int $comissao
     *                      [description]
     * @param int $grupo
     *                      [description]
     *
     * @return string [description]
     */
    private function sqlAtaInternaProcesso($ano, $processo, $orgao, $comissao, $grupo)
    {
        $sql = '
        SELECT
            COUNT(*)
        FROM sfpc.tbataregistroprecointerna A
        WHERE
            A.clicpoproc = %d
            AND A.alicpoanop = %d
            AND A.ccomlicodi = %d
            AND A.corglicodi = %d
            AND A.cgrempcodi = %d
        ';

        return sprintf($sql, $processo, $ano, $comissao, $orgao, $grupo);
    }

    /**
     * Pesquisa o processo de registro de preco na situacao de homologacao.
     *
     * @param int $ano
     *                 $param integer $orgao
     *
     * @return array [description]
     */
    public function pesquisarProcessoRegistroPrecoEmHomologacao($ano, $orgao)
    {
        $resultados = array();
        $processo = null;

        $sql = $this->sqlProcessoRegistroPrecoEmHomologacao($ano, $orgao);

        $resultado = executarSQL(ClaDatabasePostgresql::getConexao(), $sql);

        while ($resultado->fetchInto($processo, DB_FETCHMODE_OBJECT)) {
            $resultados[] = $processo;
        }

        return $resultados;
    }

    /**
     * [verificaNumeracaoGerada description].
     *
     * @param [type] $ano
     *                         [description]
     * @param [type] $processo
     *                         [description]
     * @param [type] $orgao
     *                         [description]
     * @param [type] $comissao
     *                         [description]
     * @param [type] $grupo
     *                         [description]
     *
     * @return [type] [description]
     */
    public static function verificaNumeracaoGerada($ano, $processo, $orgao, $comissao, $grupo)
    {
        $contador = null;
        $sql = self::sqlAtaInternaProcesso($ano, $processo, $orgao, $comissao, $grupo);
        $resultado = executarSQL(ClaDatabasePostgresql::getConexao(), $sql);
        $resultado->fetchInto($contador, DB_FETCHMODE_OBJECT);

        return $contador < 0;
    }
}

/**
 * A camada de Adaptação e Transformação conterá o código que tratará a lógica de apresentação dos resultados
 * das requisições dos usuários e a troca de dados com sistemas externos.
 *
 * Utiliza serviços da camada de Negócio.
 */
class RegistroPreco_Adaptacao_CadAtaRegistroPrecoGerarNumeracao extends Adaptacao_Abstrata
{
    public function __construct()
    {
        $this->setNegocio(new RegistroPreco_Negocio_CadAtaRegistroPrecoGerarNumeracao());
    }

    /**
     * [plotarBlocoProcesso description].
     *
     * @param RegistroPreco_GUI_CadAtaRegistroPrecoGerarNumeracao $gui
     *                                                                       [description]
     * @param [type]                                              $processos
     *                                                                       [description]
     *
     * @return [type] [description]
     */
    public static function plotarBlocoProcesso(RegistroPreco_UI_CadAtaRegistroPrecoGerarNumeracao $gui, $processos)
    {
    }

    public function existeAtaRegistroPrecoInterna($processo)
    {
        $clicpoproc = new Negocio_ValorObjeto_Clicpoproc($processo->clicpoproc);
        $alicpoanop = new Negocio_ValorObjeto_Alicpoanop($processo->alicpoanop);
        $cgrempcodi = new Negocio_ValorObjeto_Cgrempcodi($processo->cgrempcodi);
        $ccomlicodi = new Negocio_ValorObjeto_Ccomlicodi($processo->ccomlicodi);
        $corglicodi = new Negocio_ValorObjeto_Corglicodi($processo->corglicodi);

        $repositorio = new Negocio_Repositorio_AtaRegistroPrecoInterna();
        $res = $repositorio->procurarPorProcessoLicitatorio($clicpoproc, $alicpoanop, $cgrempcodi, $ccomlicodi, $corglicodi);

        $entidade = current($res);

        return ($entidade->carpnosequ > 0) ? true : false;
    }
}

/**
 * A camada de Interface Gráfica com o Usuário é a camada que conterá o código que irá implementar a interação
 * do sistema com os usuários (telas, relatórios e troca de dados).
 *
 * Utiliza serviços da camada de Adaptação e Transformação.
 */
class RegistroPreco_UI_CadAtaRegistroPrecoGerarNumeracao extends UI_Abstrata
{

    private $corporativo;

    /**
     * [__construct description].
     */
    public function __construct()
    {
        $this->setTemplate(new TemplatePaginaPadrao('templates/CadAtaRegistroPrecoGerarNumeracao.html', 'Registro Preço > Ata Interna > Gerar Numeração'));
        $this->setAdaptacao(new RegistroPreco_Adaptacao_CadAtaRegistroPrecoGerarNumeracao());
        $this->corporativo = ($_SESSION['_fperficorp_'] == 'S') ? true : false;
    }

    public function plotarBlocoOrgao(array $orgaos)
    {
        $orgaoNumeracao = filter_var($_POST['orgaoGestor'], FILTER_VALIDATE_INT);

        $this->getTemplate()->ORGAO_VALUE       = -1;
        $this->getTemplate()->ORGAO_TEXT        = 'Selecione o órgão';
        $this->getTemplate()->ORGAO_SELECTED    = 'selected';
        $this->getTemplate()->clear('ORGAO_SELECTED');
        $this->getTemplate()->block('BLOCO_ORGAO');

        foreach ($orgaos as $orgao) {
            $this->getTemplate()->ORGAO_VALUE   = $orgao->corglicodi;
            $this->getTemplate()->ORGAO_TEXT    = $orgao->eorglidesc;

            // Vendo se a opção atual deve ter o atributo "selected"
            if ($orgaoNumeracao == $orgao->corglicodi) {
                $this->getTemplate()->ORGAO_SELECTED = 'selected';
            } else {
                $this->getTemplate()->clear('ORGAO_SELECTED');
            }

            $this->getTemplate()->block('BLOCO_ORGAO');
        }
    }

    /**
     * [plotarBlocoAno description].
     *
     * @param [type] $anos
     *                     [description]
     *
     * @return [type] [description]
     */
    public function plotarBlocoAno($anos)
    {
        if (!is_array($anos)) {
            throw new Exception('Error Processing Request', 1);
        }

        $gerarNumeracao = isset($_POST['GerarNumeracaoAno']) ? filter_var($_POST['GerarNumeracaoAno'], FILTER_SANITIZE_NUMBER_INT) : -1;
        $this->getTemplate()->ANO_VALUE = '-1';
        $this->getTemplate()->ANO_TEXT = 'Selecione...';
        $this->getTemplate()->clear('ANO_SELECTED');
        $this->getTemplate()->block('BLOCO_ANO');

        foreach ($anos as $value => $text) {
            $this->getTemplate()->ANO_VALUE = $value;
            $this->getTemplate()->ANO_TEXT = $text;

            $anoAtual = date('Y');

            // Vendo se a opção atual deve ter o atributo "selected"
            if ($gerarNumeracao == $value || $text == $anoAtual) {
                $this->getTemplate()->ANO_SELECTED = 'selected';
            } else {
                // Caso esta não seja a opção atual, limpamos o valor da variável SELECTED
                $this->getTemplate()->clear('ANO_SELECTED');
            }
            $this->getTemplate()->block('BLOCO_ANO');
        }
    }

    /**
     * [plotarBlocoProcesso description].
     *
     * @param [type] $processos
     *                          [description]
     *
     * @return [type] [description]
     */
    public function plotarBlocoProcesso($processos)
    {
        if ($processos == null) {
            return;
        }

        $ultCodComiss = 0;
        $this->getTemplate()->TEXTO_PROCESSO_SELECIONE = 'Selecione...';
        $this->getTemplate()->PROCESSO_VALUE = -1;
        $this->getTemplate()->PROCESSO_SELECTED = 'selected';

        foreach ($processos as $processo) {
            if ($processo != null) {
                if ($ultCodComiss == 0) {
                    $ultCodComiss = $processo->ccomlicodi;
                    $this->getTemplate()->OPTGROUP_INICIO = '<optgroup label="'.$processo->ecomlidesc.'">';
                } elseif ($ultCodComiss != $processo->ccomlicodi) {
                    $this->getTemplate()->block('BLOCK_PROCESSO');
                    $this->getTemplate()->OPTGROUP_FINAL = '</optgroup>';
                    $ultCodComiss = $processo->ccomlicodi;
                    $this->getTemplate()->OPTGROUP_INICIO = '<optgroup label="'.$processo->ecomlidesc.'">';
                }
                $numeroProcesso = str_pad($processo->clicpoproc, 4, '0', STR_PAD_LEFT);
                $textoProcesso = $numeroProcesso.'/'.$processo->alicpoanop;
                if ($this->getAdaptacao()->existeAtaRegistroPrecoInterna($processo)) {
                    $textoProcesso .= ' * ';
                }
                $this->getTemplate()->TEXTO_PROCESSO = $textoProcesso;
                $formataId = implode('-', array(
                    $processo->clicpoproc,
                    $processo->alicpoanop,
                    $processo->cgrempcodi,
                    $processo->ccomlicodi,
                    $processo->corglicodi,
                ));
                $this->getTemplate()->PROCESSO_VALUE = $formataId;
            }

            // Vendo se a opção atual deve ter o atributo "selected"CadAtaRegistroPrecoInternaManter
            if ($processo == null) {
                $this->getTemplate()->PROCESSO_SELECTED = 'selected';
            } else {
                // Caso esta não seja a opção atual, limpamos o valor da variável SELECTED
                $this->getTemplate()->clear('PROCESSO_SELECTED');
            }
            $this->getTemplate()->block('BLOCK_OPTION');
        }
        $this->getTemplate()->block('BLOCK_PROCESSO'); 
    }

    /**
     */
    public function proccessPrincipal()
    {        
        if ($_SESSION['mensagemFeedback'] != null) {
           $this->mensagemSistema($_SESSION['mensagemFeedback'], 1, 0);
        }elseif ($_SESSION['colecaoMensagemErro'] != null) {            
            $this->mensagemSistema(implode(', ', $_SESSION['colecaoMensagemErro']), 1, 1);
        }

        $orgaos = $this->getAdaptacao()
            ->getNegocio()
            ->getDados()
            ->consultarOrgaoComNumeracaoGerada($this->corporativo);

        $this->plotarBlocoOrgao($orgaos);
        $this->plotarBlocoAno(HelperPitang::carregarAno());
        $processos = $this->getAdaptacao()
            ->getNegocio()
            ->filtrarProcessoPorAnoSelecionado();
        $this->plotarBlocoProcesso($processos);
    }

    public function alterarAtual()
    {
        $processos = $this->getAdaptacao()
            ->getNegocio()
            ->filtrarProcessoPorAnoSelecionado();
        $this->plotarBlocoAno(HelperPitang::carregarAno());
        $this->plotarBlocoProcesso($processos);
        $orgaos = $this->getAdaptacao()
            ->getNegocio()
            ->getDados()
            ->consultarOrgaoComNumeracaoGerada($this->corporativo);
        $this->plotarBlocoOrgao($orgaos);
    }

    /**
     * [processSelecionar description].
     *
     * @return [type] [description]
     */
    public function processSelecionar()
    {

        $arrValidacao = $this->validaFormulario();

        if ($arrValidacao) {            
            $this->proccessPrincipal();
            return;
        }else{

            $anoAtual = filter_input(INPUT_POST, 'GerarNumeracaoAno', FILTER_SANITIZE_NUMBER_INT);
            $processoAtual = filter_var($_POST['GerarNumeracaoProcess'], FILTER_SANITIZE_NUMBER_INT);
            $anos = HelperPitang::carregarAno();
            $uri = 'CadRegistroPrecoLicitacaoAtas.php?processo='.$processoAtual.'&ano='.$anos[$anoAtual];
            header('Location: '.$uri);
            exit();
        }
    }


    /**
     * [validaFormulario description].
     *
     * @return [type] [description]
     */
    private function validaFormulario()
    {
        
        $anoAtual = filter_input(INPUT_POST, 'GerarNumeracaoAno', FILTER_SANITIZE_NUMBER_INT);
        $processoAtual = filter_var($_POST['GerarNumeracaoProcess'], FILTER_SANITIZE_NUMBER_INT);
        $error = false;
        if (($processoAtual == null || $processoAtual == '') || ($anoAtual == null || $anoAtual == '') ) {
            $_SESSION['colecaoMensagemErro'][] = 'Todos os campos são obrigatórios';
            $error = true;
            
        }
        

        return $error;
    }
}

/**
 * A camada de Negócio conterá o código que irá implementar todas as regras de negócio do sistema.
 *
 * Utiliza serviços da camada de Dados.
 */
class RegistroPreco_Negocio_CadAtaRegistroPrecoGerarNumeracao extends Negocio_Abstrata
{
    public function __construct()
    {
        $this->setDados(new RegistroPreco_Dados_CadAtaRegistroPrecoGerarNumeracao());
    }

    /**
     * Filtra processo por ano selecionada.
     *
     * @return [type] [description]
     */
    public function filtrarProcessoPorAnoSelecionado()
    {
        $anos = HelperPitang::carregarAno();
        $gerarNumeracaoAno = filter_var($_POST['GerarNumeracaoAno'], FILTER_SANITIZE_NUMBER_INT) ? (int) $_POST['GerarNumeracaoAno'] : 0;
        $anoSelecionado = $anos[$gerarNumeracaoAno];
        $orgaoGestor = filter_var($_POST['orgaoGestor'], FILTER_SANITIZE_NUMBER_INT) ? (int) $_POST['orgaoGestor'] : 0;
        if ($orgaoGestor == 0) {
            return false;
        }
        $processos = $this->getDados()->pesquisarProcessoRegistroPrecoEmHomologacao($anoSelecionado, $orgaoGestor);

        return $processos;
    }

    /**
     * [alterarAtualProcesso description].
     *
     * @return [type] [description]
     */
    public function alterarAtualProcesso()
    {
        $botao = isset($_POST['Botao']) ? filter_input(INPUT_POST, 'Botao', FILTER_SANITIZE_STRING) : 'Principal';

        $this->atualProcesso = intval($botao);
    }

    /**
     * [alterarAtual description].
     *
     * @return [type] [description]
     */




}

$app = new RegistroPreco_UI_CadAtaRegistroPrecoGerarNumeracao();

$acao = filter_input(INPUT_POST, 'Botao', FILTER_SANITIZE_STRING);

//alteracao dfs2 prefeitura;
switch ($acao) {
    case 'Selecionar':
        $app->processSelecionar();
        break;
    case 'Processo':
        $app->getAdaptacao()
            ->getNegocio()
            ->filtrarProcessoPorAnoSelecionado();
        break;
    case 'Imprimir':
        $app->getAdaptacao()
            ->getNegocio()
            ->processImprimir();
        break;
    case 'AlterarProcesso':
        $app->alterarAtual();
        break;
    case 'AlterarOrgao':
        $app->proccessPrincipal();
        break;
    case 'Principal':
    default:
        $app->proccessPrincipal();
        break;
}

echo $app->getTemplate()->show();
