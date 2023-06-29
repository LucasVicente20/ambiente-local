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
 * @category  Pitang_Registro_Preço
 * @package   Registropreco
 * @author    Pitang Agile TI <contato@pitang.com>
 * @copyright 2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license   http://www.php.net/license/3_01.txt PHP License 3.01
 * @version   GIT: 20150209_143500-93-g66fecca
 */

 // 220038--
 
if (!@require_once dirname(__FILE__)."/../bootstrap.php") {
    throw new Exception("Error Processing Request - Bootstrap", 1);
}

class CadAtaRegistroPrecoInternaSelecionarProcessoOrgaoExternoDataAccess
{
    /**
     * [consultarAtasInternas description]
     * @return array collections of stdClass
     */
    public function consultarAtasInternas()
    {
        $colecao = array();
        $atas    = null;
        $resultado = executarSQL(
            ClaDatabasePostgresql::getConexao(),
            "SELECT * FROM  sfpc.tbataregistroprecointerna"
        );
        while ($resultado->fetchInto($atas, DB_FETCHMODE_OBJECT)) {
            $colecao[] = $atas;
        }

        return $colecao;
    }
    /**
     * [consultarOrgaoComNumeracaoGerada description]
     * @return [type] [description]
     */
    public function consultarOrgaoComNumeracaoGerada()
    {
        $resultados = array();
        $orgaos  = null;

        $resultado = executarSQL(
            ClaDatabasePostgresql::getConexao(),
            $this->sqlOrgaoAtaGerada()
        );

        while ($resultado->fetchInto($orgaos, DB_FETCHMODE_OBJECT)) {
            $resultados[] = $orgaos;
        }

        $resultadosAux = array();

        $resultadosAux[0] = null;

        $count = 1;

        foreach ($resultados as $resultadoAux1) {
            $booleanAux = true;
            foreach ($resultadosAux as $resultadoAux2) {
                if ($resultadoAux1->eorglidesc == $resultadoAux2->eorglidesc) {
                    $booleanAux = false;
                }
            }
            if ($booleanAux) {
                $resultadosAux[$count] = $resultadoAux1;
                $count++;
            }
        }

        return $resultadosAux;
    }
    /**
     * [sqlProcessoRegistroPrecoEmHomologacao description]
     * @param  [type] $ano [description]
     * @return [type] [description]
     */
    public function sqlProcessoRegistroPrecoEmHomologacao($ano, $orgao)
    {
        $licHomologacao = 13;

        $sql =  "
            SELECT DISTINCT 
                A.CLICPOPROC, A.ALICPOANOP , A.CCOMLICODI , A.CGREMPCODI , A.CORGLICODI , B.ECOMLIDESC, 
                e.cfasescodi 
            FROM 
                SFPC.TBLICITACAOPORTAL A 
            INNER JOIN 
                SFPC.TBCOMISSAOLICITACAO B ON A.CCOMLICODI = B.CCOMLICODI 
            INNER JOIN 
                SFPC.TBUSUARIOCOMIS D ON A.CGREMPCODI = D.CGREMPCODI AND D.CCOMLICODI = A.CCOMLICODI 
            INNER JOIN 
                SFPC.tbfaselicitacao E ON A.CLICPOPROC = e.clicpoproc AND e.cfasescodi = %d  
            WHERE 
                A.CGREMPCODI = ".$_SESSION['_cgrempcodi_']." AND A.ALICPOANOP = %d AND a.flicporegp LIKE 'S'
        	AND A.CORGLICODI = %d 
            ORDER BY 
                B.ECOMLIDESC ASC , A.ALICPOANOP DESC , A.CLICPOPROC DESC";

        return sprintf($sql, $licHomologacao, $ano, $orgao);
    }
    /**
     * [sqlOrgaoAtaGerada description]
     * @return [type] [description]
     */
    public function sqlOrgaoAtaGerada()
    {
        $sql = "
        SELECT o.corglicodi, o.eorglidesc
        FROM  sfpc.tborgaolicitante o
        INNER JOIN sfpc.tbataregistroprecointerna a ON o.corglicodi = a.corglicodi
        ";

        $sql = "
        SELECT
            o.corglicodi ,
            o.eorglidesc
        FROM
            sfpc.tborgaolicitante o
        WHERE o.forglisitu LIKE 'A'
        ORDER BY o.eorglidesc
        ";

        return $sql;
    }
    /**
     * [consultarProcessoDoOrgaoNoAno description]
     * @param  [type] $orgao [description]
     * @param  [type] $ano   [description]
     * @return [type] [description]
     */
    public function consultarProcessoDoOrgaoNoAno($orgao, $ano)
    {
        $resultados = array();
        $resultado = executarSQL(
            ClaDatabasePostgresql::getConexao(),
            $this->sqlProcessoRegistroPrecoEmHomologacao($ano, $orgao)
        );
        while ($resultado->fetchInto($processos, DB_FETCHMODE_OBJECT)) {
            array_push($resultados, $processos);
        }
        return $resultados;
    }
}

class CadAtaRegistroPrecoInternaSelecionarProcessoOrgaoExternoNegocio
{
    /**
     * [$template description]
     * @var \TemplatePaginaPadrao
     */
    private $template;
    private $dao;

    public function __construct(TemplatePaginaPadrao $template)
    {
        $this->dao = new CadAtaRegistroPrecoInternaSelecionarProcessoOrgaoExternoDataAccess();
        $this->template = $template;
    }
    /**
     * [proccessPrincipal description]
     * @param  IPrograma $app [description]
     * @return [type]         [description]
     */
    public function proccessPrincipal()
    {
        $atasInternas = $this->dao->consultarAtasInternas();
        if ($GLOBALS['REQUEST_METHOD'] == 'POST' && empty($atasInternas)) {
            $mensagem = 'Não existe Ata Interna cadastrada';
            $this->template->MENSAGEM_ERRO = ExibeMensStr($mensagem, 1, 0);
            $this->template->block('BLOCO_ERRO', true);
            return;
        }

        $orgaos = $this->dao->consultarOrgaoComNumeracaoGerada();
        $this->plotarBlocoOrgao($orgaos);
    }
    /**
     * [plotarBlocoAno description]
     * @param  array  $anos [description]
     * @return [type] [description]
     */
    public function plotarBlocoAno(array $anos)
    {
        $gerarNumeracao =  $_POST['valorAno'];
        $contador = 0;        
        foreach ($anos as $value => $text) {
            $contador++;
            
            $this->template->ANO_VALUE = $value;
            $this->template->ANO_TEXT = $text;

            // Vendo se a opção atual deve ter o atributo "selected"

            if ($gerarNumeracao != null) {
                if ($value == $gerarNumeracao) {
                    $this->template->ANO_SELECTED = "selected";
                }
            }

            if ($gerarNumeracao == $value) {
                $this->template->ANO_SELECTED = "selected";
            } else {
                // Caso esta não seja a opção atual, limpamos o valor da variável SELECTED
                $this->template->clear("ANO_SELECTED");
            }

            $this->template->block("BLOCO_ANO");
        }
    }
    /**
     * [plotarBlocoOrgao description]
     * @param  [type] $orgaos [description]
     * @return [type] [description]
     */
    public function plotarBlocoOrgao($orgaos)
    {
        $orgaoNumeracao =  filter_input(INPUT_POST, 'orgaoGestor', FILTER_VALIDATE_INT);
        if ($orgaos == null) {
            return;
        }

        $this->template->ORGAO_VALUE       = -1;
        $this->template->ORGAO_TEXT        = 'Selecione o órgão';
        $this->template->ORGAO_SELECTED    = 'selected';
        $this->template->clear('ORGAO_SELECTED');
        $this->template->block('BLOCO_ORGAO');

        foreach ($orgaos as $orgao) {
            $this->template->ORGAO_VALUE = $orgao->corglicodi;

            $this->template->ORGAO_TEXT = $orgao->eorglidesc;

            // Vendo se a opção atual deve ter o atributo "selected"
            if ($orgaoNumeracao == null && $orgao->corglicodi == null) {
                continue;
            } else if ($orgaoNumeracao == $orgao->corglicodi) {
                $this->template->ORGAO_SELECTED = "selected";
            } else {
                $this->template->clear("ORGAO_SELECTED");
            }

            $this->template->block("BLOCO_ORGAO");
        }
    }
    /**
     * [plotarBlocoProcesso description]
     * @param  [type] $processos [description]
     * @return [type] [description]
     */
    public function plotarBlocoProcesso($processos)
    {
        $processoNumeracao =  $_POST['GerarNumeracaoProcess'];

        if ($processos == null) {
            return;
        }
        
        $ultCodComiss = 0;
        foreach ($processos as $processo) {
            if ($processo != null) {
                if ($ultCodComiss == 0) {
                    $ultCodComiss = $processo->ccomlicodi;
                    $this->template->OPTGROUP_INICIO = '<optgroup label="'.$processo->ecomlidesc.'">';
                } elseif ($ultCodComiss != $processo->ccomlicodi) {
                    $this->template->block("BLOCK_PROCESSO");
                    $this->template->OPTGROUP_FINAL = '</optgroup>';
                    $ultCodComiss = $processo->ccomlicodi;
                    $this->template->OPTGROUP_INICIO = '<optgroup label="'.$processo->ecomlidesc.'">';
                }
//                VAR_DUMP($ultCodComiss);

                $this->template->TEXTO_PROCESSO =       str_pad($processo->clicpoproc, 4, '0', STR_PAD_LEFT) .'/'.$processo->alicpoanop;
                $this->template->PROCESSO_VALUE = $processo->clicpoproc;
            }

            // Vendo se a opção atual deve ter o atributo "selected"CadAtaRegistroPrecoInternaManter
            if ($processo == null) {
                $this->template->PROCESSO_SELECTED = "selected";
            } else {
                // Caso esta não seja a opção atual, limpamos o valor da variável SELECTED
                $this->template->clear("PROCESSO_SELECTED");
            }
            $this->template->block("BLOCK_OPTION");
        }
        $this->template->block("BLOCK_PROCESSO");
//        DIE;
    }
    /**
     * [atualizaAnos description]
     * @return [type] [description]
     */
    public function atualizaAnos()
    {
        $anos = HelperPitang::carregarAno();
        $this->plotarBlocoAno($anos);
    }
    /**
     * [atualizaProcessos description]
     * @return [type] [description]
     */
    public function atualizaProcessos($ano=null)
    {
        $orgao = $_POST['orgaoGestor'];
        if ($ano == null) {
            $ano = $_POST['valorAno'];
        }
        
        $orgaos = $this->dao->consultarOrgaoComNumeracaoGerada();
        $this->plotarBlocoOrgao($orgaos);
        
        $this->atualizaAnos();
        $ano = empty($_POST['valorAno']) ?0 :$_POST['valorAno'];
        
        $anos = HelperPitang::carregarAno();
        
        $processos = $this->dao->consultarProcessoDoOrgaoNoAno($orgao, $anos[$ano]);
        
        $this->plotarBlocoProcesso($processos);
    }
    /**
     * [processSelecionar description]
     * @return [type] [description]
     */
    public function processSelecionar()
    {
        $orgao =  filter_input(INPUT_POST, 'orgaoGestor');
        $ano =  $_POST['valorAno'];
        $processo =  $_POST['GerarNumeracaoProcesso'];
        $anos = HelperPitang::carregarAno();

        $uri  = 'CadAtaRegistroPrecoInternaCaronaOrgaoExterno.php?ano='.$anos[$ano].'&processo='.$processo.'&orgao='.$orgao;
        header('location: '.$uri);
    }
}
/**
 *
 */
class CadAtaRegistroPrecoInternaSelecionarProcessoOrgaoExterno implements IPrograma
{
    /**
     * [$template description]
     * @var \TemplatePaginaPadrao
     */
    private $template;
    /**
     * [$negocio description]
     * @var CadAtaRegistroPrecoIternaTrocarFornecedorNegocio
     */
    private $negocio;
    /**
     * Gets the value of template.
     *
     * @return mixed
     */
    public function getTemplate()
    {
        return $this->template;
    }
    /**
     * Sets the value of template.
     *
     * @param TemplatePaginaPadrao $template the template
     *
     * @return self
     */
    public function setTemplate(TemplatePaginaPadrao $template)
    {
        $this->template = $template;

        return $this;
    }
    /**
     * [getNegocio description]
     * @return [type] [description]
     */
    public function getNegocio()
    {
        return $this->negocio;
    }
    /**
     * [__construct description]
     * @param TemplatePaginaPadrao $template [description]
     * @param ArrayObject          $session  [description]
     */
    public function __construct(TemplatePaginaPadrao $template = null)
    {
        if (is_null($template)) {
            $template = new TemplatePaginaPadrao(
                "templates/CadAtaRegistroPrecoInternaTrocarFornecedor.html",
                "Registro de Preço > Ata Interna > Carona Orgão Externo > Incluir"
            );
        }
        
        

        $this->setTemplate($template);
        $this->getTemplate()->TITULO_SUPERIOR = "INCLUIR - CARONA ÓRGÃO EXTERNO";
        $this->getTemplate()->DESCRICAO_REGRAS_PAG = "Selecione o Gestor,
    			 o Ano e o Processo Licitatório que já possui
    			 a numeração das Atas Internas e clique no botão “Selecionar”.";
        $this->getTemplate()->NAME_PROGRAMA = "CadAtaRegistroPrecoInternaSelecionarProcessoOrgaoExterno";
        
        $this->negocio = new CadAtaRegistroPrecoInternaSelecionarProcessoOrgaoExternoNegocio($this->getTemplate());
    }
    /**
     * [frontController description]
     * @return [type] [description]
     */
    public static function frontController()
    {
        $app = new CadAtaRegistroPrecoInternaSelecionarProcessoOrgaoExterno();
        $botao = isset($_POST['Botao'])
            ? filter_input(INPUT_POST, 'Botao', FILTER_SANITIZE_STRING)
            : 'Principal';

        switch ($botao) {
            case 'Voltar':
                $app->getNegocio()->processVoltar();
                break;
            case 'Selecionar':
                $app->getNegocio()->processSelecionar();
                break;
            case 'AtualizaAnos':
                $app->getNegocio()->atualizaProcessos();
                break;
            case 'AtualizaProcessos':
                    $app->getNegocio()->atualizaProcessos();
                    break;
            case 'Principal':
            default:
                $app->getNegocio()->proccessPrincipal();
                break;
        }
        return $app->getTemplate()->show();
    }
}

echo CadAtaRegistroPrecoInternaSelecionarProcessoOrgaoExterno::frontController();
