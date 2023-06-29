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
 */

 // 220038--
 
if (! @require_once dirname(__FILE__) . "/../bootstrap.php") {
    throw new Exception("Error Processing Request - Bootstrap", 1);
}

if(!empty($_SESSION['mensagemFeedback'])){    //Condição para chegagem de mensagem de erro indevidamente vindo de outra pag. e limpar o campo de mensagem. |Madson|
    if($_SESSION['conferePagina'] == 'carona' || 'participante' || 'selecionar' || 'pesquisar'){
    unset($_SESSION['mensagemFeedback']);
    unset($_SESSION['conferePagina']);
    }
}
/**
 * A camada de Dados conterá o código que irá realizar todo o acesso aos dados.
 *
 * @author jfsi
 *
 */
class RegistroPreco_Dados_CadAtaRegistroPrecoInternaManter extends Dados_Abstrata
{

    /**
     * [sqlOrgaoAtaGerada description]
     *
     * @return [type] [description]
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
     * [sqlProcessoRegistroPrecoEmHomologacao description]
     *
     * @param [type] $orgao
     *            [description]
     * @param [type] $ano
     *            [description]
     * @return [type] [description]
     */
    public function sqlProcessoRegistroPrecoEmHomologacao($orgao, $ano)
    {
        $sql = "
            SELECT
            DISTINCT l.clicpoproc,
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
				INNER JOIN sfpc.tbataregistroprecointerna F
					ON l.clicpoproc = F.clicpoproc
						AND l.alicpoanop = F.alicpoanop
						AND l.ccomlicodi = F.ccomlicodi
						AND l.corglicodi = F.corglicodi
						AND l.cgrempcodi = F.cgrempcodi
            WHERE
                l.alicpoanop = %d
                AND l.flicporegp LIKE 'S'
                AND l.corglicodi = %d
                AND e.cfasescodi IN (13, 26)
            ORDER BY
                B.ecomlidesc ASC,
                l.alicpoanop DESC,
                l.clicpoproc DESC";
        
        return sprintf($sql, $ano, $orgao);
    }

    /**
     * [consultarProcessoDoOrgaoNoAno description]
     *
     * @param [type] $orgao
     *            [description]
     * @param [type] $ano
     *            [description]
     * @return [type] [description]
     */
    public static function consultarProcessoDoOrgaoNoAno($orgao, $ano)
    {			
		return ClaDatabasePostgresql::executarSQL(self::sqlProcessoRegistroPrecoEmHomologacao($orgao, $ano));
    }

    /**
     * [consultarOrgaoComNumeracaoGerada description]
     *
     * @return [type] [description]
     */
    public static function consultarOrgaoComNumeracaoGerada($corporativo = false)
    {        
        if($corporativo) {
            $resultado = ClaDatabasePostgresql::executarSQL(self::sqlGeralOrgaoAtaGerada());
        } else {
            $resultado = ClaDatabasePostgresql::executarSQL(self::sqlOrgaoAtaGerada());
        }

        ClaDatabasePostgresql::hasError($resultado);

        return $resultado;
    }
}

/**
 * A camada de Interface Gráfica com o Usuário é a camada que conterá o código que irá implementar a interação
 * do sistema com os usuários (telas, relatórios e troca de dados).
 *
 * Utiliza serviços da camada de Adaptação e Transformação.
 *
 * @author jfsi
 *
 */
class RegistroPreco_UI_CadAtaRegistroPrecoInternaManter extends UI_Abstrata
{

    private $corporativo;

    /**
     * [__construct description]
     */
    public function __construct()
    {
        $this->setTemplate(new TemplatePaginaPadrao("templates/CadAtaRegistroPrecoInternaManter.html", "Registro de Preço > Ata Interna > Manter"));
        $this->setAdaptacao(new RegistroPreco_Adaptacao_CadAtaRegistroPrecoInternaManter());
        $this->corporativo = ($_SESSION['_fperficorp_'] == 'S') ? true : false;
    }

    /**
     */
    public function proccessPrincipal()
    {
        if (isset($_SESSION['mensagemFeedback']) && empty($_SESSION['mensagemFeedback']) === false) {
            $this->blockErro($_SESSION['mensagemFeedback']);
        }
        $anos = HelperPitang::carregarAno();
        $this->getAdaptacao()->plotarBlocoAno($anos, $this);

        $orgaos = RegistroPreco_Dados_CadAtaRegistroPrecoInternaManter::consultarOrgaoComNumeracaoGerada($this->corporativo);
        $this->getAdaptacao()->plotarBlocoOrgao($orgaos, $this);

        $orgao = filter_var($_POST['orgao'], FILTER_SANITIZE_NUMBER_INT);
        $ano = empty($_POST['ano']) ? 0 : $_POST['ano'];
        $processos = RegistroPreco_Dados_CadAtaRegistroPrecoInternaManter::consultarProcessoDoOrgaoNoAno($orgao, $ano);


        $this->getAdaptacao()->plotarBlocoProcesso($processos, $this);
		
        unset($_SESSION['mensagemFeedback']);
    }
}

/**
 * A camada de Adaptação e Transformação conterá o código que tratará a lógica de apresentação dos resultados
 * das requisições dos usuários e a troca de dados com sistemas externos.
 *
 * Utiliza serviços da camada de Negócio.
 *
 * @author jfsi
 *
 */
class RegistroPreco_Adaptacao_CadAtaRegistroPrecoInternaManter extends Adaptacao_Abstrata
{

    /**
     *
     * @param array $anos
     * @param UI_Interface $template
     */
    public function plotarBlocoAno(array $anos, UI_Interface $template)
    {
        $gerarNumeracao = filter_input(INPUT_POST, 'ano', FILTER_VALIDATE_INT);

        $template->getTemplate()->ANO_VALUE = - 1;
        $template->getTemplate()->ANO_TEXT = "Selecione um ano";
        $template->getTemplate()->block("BLOCO_ANO");

        foreach ($anos as $value => $text) {
            $template->getTemplate()->ANO_VALUE = $text;
            $template->getTemplate()->ANO_TEXT = $text;

            // Vendo se a opção atual deve ter o atributo "selected"
            if ($gerarNumeracao == $text || $text == date(' Y')) {
                $template->getTemplate()->ANO_SELECTED = "selected";
            } else {
                // // Caso esta não seja a opção atual, limpamos o valor da variável SELECTED
                $template->getTemplate()->clear("ANO_SELECTED");
            }

            $template->getTemplate()->block("BLOCO_ANO");
        }
    }

    /**
     *
     * @param array $orgaos
     * @param UI_Interface $template
     */
    public function plotarBlocoOrgao(array $orgaos, UI_Interface $template)
    {
        $orgaoNumeracao = filter_input(INPUT_POST, 'orgao', FILTER_VALIDATE_INT);

        $template->getTemplate()->ORGAO_VALUE = - 1;
        $template->getTemplate()->ORGAO_TEXT = "Selecione um órgão";
        $template->getTemplate()->block("BLOCO_ORGAO");

        if ($orgaos == null) {
            return;
        }

        $orgaoNumeracao = filter_input(INPUT_POST, 'orgao', FILTER_VALIDATE_INT);
        $gerarNumeracao = filter_input(INPUT_POST, 'ano', FILTER_VALIDATE_INT);		

        foreach ($orgaos as $orgao) {
			
            $template->getTemplate()->ORGAO_VALUE = $orgao->corglicodi;
            $template->getTemplate()->ORGAO_TEXT = $orgao->eorglidesc;

            // Vendo se a opção atual deve ter o atributo "selected"
            if ($orgaoNumeracao == $orgao->corglicodi) {
                $template->getTemplate()->ORGAO_SELECTED = "selected";
            } else {
                // Caso esta não seja a opção atual, limpamos o valor da variável SELECTED
                $template->getTemplate()->clear("ORGAO_SELECTED");
            }

            $template->getTemplate()->block("BLOCO_ORGAO");
        }
    }

    /**
     *
     * @param unknown $processos
     */
    public function plotarBlocoProcesso(array $processos, UI_Interface $template)
    {
        $ultCodComiss = 0;
        $template->getTemplate()->TEXTO_PROCESSO_SELECIONE = "Selecione o processo";
        

        foreach ($processos as $processo) {
            if ($processo != null) {
                if ($ultCodComiss == 0) {
                    $ultCodComiss = $processo->ccomlicodi;
                    $template->getTemplate()->OPTGROUP_INICIO = '<optgroup label="' . $processo->ecomlidesc . '">';
                } elseif ($ultCodComiss != $processo->ccomlicodi) {
                    $template->getTemplate()->block("BLOCK_PROCESSO");
                    $template->getTemplate()->OPTGROUP_FINAL = '</optgroup>';
                    $ultCodComiss = $processo->ccomlicodi;
                    $template->getTemplate()->OPTGROUP_INICIO = '<optgroup label="' . $processo->ecomlidesc . '">';
                }

                $template->getTemplate()->TEXTO_PROCESSO = str_pad($processo->clicpoproc, 4, '0', STR_PAD_LEFT) . '/' . $processo->alicpoanop;
                //$template->getTemplate()->PROCESSO_VALUE = $processo->clicpoproc;
                $formataId = implode('-', array(
                    $processo->clicpoproc,
                    $processo->alicpoanop,
                    $processo->cgrempcodi,
                    $processo->ccomlicodi,
                    $processo->corglicodi,
                ));
                $template->getTemplate()->PROCESSO_VALUE = $formataId;
            }

            // Vendo se a opção atual deve ter o atributo "selected"CadAtaRegistroPrecoInternaManter
            if ($processo == null) {
                $template->getTemplate()->PROCESSO_SELECTED = "selected";
            } else {
                // Caso esta não seja a opção atual, limpamos o valor da variável SELECTED
                $template->getTemplate()->clear("PROCESSO_SELECTED");
            }
            $template->getTemplate()->block("BLOCK_OPTION");
        }
        $template->getTemplate()->block("BLOCK_PROCESSO");
    }
}

$app = new RegistroPreco_UI_CadAtaRegistroPrecoInternaManter();
$acao = filter_var($_REQUEST['Botao'], FILTER_SANITIZE_SPECIAL_CHARS);

switch ($acao) {
    case 'Principal':
    default:
        $app->proccessPrincipal();
        break;
}

echo $app->getTemplate()->show();
