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

# ------------------------------------------------------------------------------
# Autor:   Caio Coutinho - Pitang Agile TI
# Data :    20/08/2018
# Objetivo: CR 201643 - [REGISTRO DE PREÇOS] Manter - Inibir botões “Ativar” e “Inativar” 
# ------------------------------------------------------------------------------

// 220038--

if (! @require_once dirname(__FILE__) . "/../bootstrap.php") {
    throw new Exception("Error Processing Request - Bootstrap", 1);
}

/**
 * A camada de Dados conterá o código que irá realizar todo o acesso aos dados.
 *
 * @author jfsi
 *
 */
class RegistroPreco_Dados_CadAtaRegistroPrecoInternaManterAtas extends Dados_Abstrata
{
    
    public function sqlConsultarCentroDeCustoUsuario($cgrempcodi, $cusupocodi, $corglicodi)
    {
        $sql = "
            SELECT
                   ccp.ccenpocorg, ccp.ccenpounid, ccp.corglicodi
              FROM sfpc.tbcentrocustoportal ccp
             WHERE 1=1
        ";

        if ($corglicodi != null || $corglicodi != "") {
          $sql .= " AND ccp.corglicodi = %d";
        }

        return sprintf($sql, $corglicodi);
    }

    public function sqlConsultarProcurarAta($carpnosequ)
    {
        $sql = "
            SELECT * FROM sfpc.tbataregistroprecointerna WHERE carpnosequ = %d             
        ";

        return sprintf($sql, $carpnosequ);
    }
    
    public function consultarTodosDocumentosAta($carpnosequ)
    {
        $db = Conexao();
        $sql = "
            SELECT encode(idocatarqu, 'base64') as arquivo, carpnosequ, cdocatsequ, edocatnome, cusupocodi
              FROM sfpc.tbdocumentoatarp
             WHERE carpnosequ = %d
        ";

        $sql = sprintf($sql, $carpnosequ);
        $res = executarSQL($db, $sql);
        
        $documentos = array();
        $documento = null;
        while ($res->fetchInto($documento, DB_FETCHMODE_OBJECT)) {
            $documentos[] = $documento;
        }
        if (PEAR::isError($res)) {
            ExibeErroBD(__FILE__ . "\nLinha: ".__LINE__."\nSql: " . $database->getMessage());
        }
        $db->disconnect();

        return $documentos;
    }

    /**
     */

    /* Valores condicionais da atualização da situação */
    public function sqlAtualizaSituacao($ano, $processo, $orgao, $comissao, $grupo, $numeroAta, $codigos)
    {
        $sql = "clicpoproc=" . $processo;
        $sql .= " and alicpoanop = " . $ano;
        $sql .= " and cgrempcodi =" . $grupo;
        $sql .= " and ccomlicodi=" . $comissao;
        $sql .= " and corglicodi=" . $orgao;
        $sql .= " and carpnosequ=" . $numeroAta;
        $sql .= " and carpincodn=" . $codigos[$numeroAta];

        return $sql;
    }

    /* Fim Querys Sqls */

    /**
     * Executa a licitação na qual a ata interna se refere
     *
     * @param integer $ano
     * @param integer $processo
     * @param integer $orgaoUsuario
     *
     * @return NULL|stdClass
     */
    public static function consultarLicitacaoAtaInterna($ano, $processo, $orgaoUsuario, $comissao, $grupo)
    {
        $sql = Dados_Sql_AtaRegistroPrecoNova::sqlLicitacaoAtaInternaNova($ano, $processo, $orgaoUsuario, $comissao, $grupo);
        return ClaDatabasePostgresql::executarSQL($sql);
    }

    /**
     * Executa a consulta dos órgãos participantes de uma ata.
     *
     * @param integer $processo da ata
     * @param integer $ano da ata
     * @param integer $orgaoGestor da ata
     *
     * @return NULL|stdClass
     */
    public static function consultarOrgaosParticipantesAta($processo, $ano, $orgaoGestor)
    {
        $sql = Dados_Sql_AtaRegistroPrecoNova::sqlOrgaosParticipantesAta($processo, $ano, $orgaoGestor);
        $arrayOrgaos = ClaDatabasePostgresql::executarSQL($sql);

        foreach($arrayOrgaos as $orgao) {
            $strOrgaos .= $orgao->eorglidesc . "<br />";
        }

        return $strOrgaos;
    }

    /**
     * [consultarLicitacaoAtas description]
     *
     * @param [type] $processo
     *            [description]
     * @param [type] $orgao
     *            [description]
     * @param [type] $ano
     *            [description]
     * @return [type] [description]
     */
    public static function consultarLicitacaoAtas($processo, $orgao, $ano, $situacao = 'A')
    {
        $processo = filter_var($processo, FILTER_SANITIZE_NUMBER_INT);
        $orgao = filter_var($orgao, FILTER_SANITIZE_NUMBER_INT);
        $ano = filter_var($ano, FILTER_SANITIZE_NUMBER_INT);

        
        //$sql = Dados_Sql_AtaRegistroPrecoNova::sqlAtaLicitacao($processo, $orgao, $ano);
        $sql = Dados_Sql_AtaRegistroPrecoNova::sqlAtaLicitacaoList($processo, $orgao, $ano, $situacao);
        return ClaDatabasePostgresql::executarSQL($sql);
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
class RegistroPreco_UI_CadAtaRegistroPrecoInternaManterAtas extends UI_Abstrata
{

    /**
     * [__construct description]
     */
    public function __construct()
    {
        $this->setTemplate(new TemplatePaginaPadrao("templates/CadAtaRegistroPrecoInternaManterAtas.html", "Registro de Preço > Ata Interna > Manter"));

        $this->setAdaptacao(new RegistroPreco_Adaptacao_CadAtaRegistroPrecoInternaManterAtas());

        $this->getTemplate()->TITULO_PROGRAMA = "MANTER ATA INTERNA ";
        $this->getTemplate()->TITULO_ATAS = "ATA(S) DE REGISTRO DE PREÇO";
    }

    public function inativarSituacaoAta()
    {
        $sequencialAta = $this->getAdaptacao()->recuperarSequencialAta();
        if (empty($sequencialAta)) {
            $this->blockErro('Selecione uma ata de Registro de Preço');
            return;
        }

        $this->getAdaptacao()->inativarSituacaoAta();
    }

    public function ativarSituacaoAta()
    {
        $sequencialAta = $this->getAdaptacao()->recuperarSequencialAta();
        if (empty($sequencialAta)) {
            $this->blockErro('Selecione uma ata de Registro de Preço');
            return;
        }
        $this->getAdaptacao()->ativarSituacaoAta();
    }

    /**
     * [proccessPrincipal description]
     *
     * @return [type] [description]
     */
    public function proccessPrincipal()
    {
        $orgao      = isset($_REQUEST['orgao']) ? filter_var($_REQUEST['orgao'], FILTER_SANITIZE_NUMBER_INT) : $_SESSION['orgao'];
        $ano        = isset($_REQUEST['ano']) ? filter_var($_REQUEST['ano'], FILTER_SANITIZE_NUMBER_INT) : $_SESSION['ano'];
        $processo   = isset($_REQUEST['processo']) ? filter_var($_REQUEST['processo'], FILTER_SANITIZE_NUMBER_INT) : $_SESSION['processo'];
        $data       = explode('-', $processo);
        $comissao   = $data[3];
        $grupo      = $data[2];
        
        $_SESSION["orgao"]      = $orgao;
        $_SESSION["ano"]        = $ano;
        $_SESSION["processo"]   = $processo;
        $_SESSION["comissao"]   = $comissao;
        $_SESSION["grupo"]      = $grupo;

        if ($orgao < 0) {
            $_SESSION['mensagemFeedback'] .= "Selecione um órgao";
            $this->processVoltar();
        }

        if ($ano < 0) {
            $_SESSION['mensagemFeedback'] .= "Selecione um ano";
            $this->processVoltar();
        }

        if (is_null($processo)) {
            $_SESSION['mensagemFeedback'] .= "Selecione um processo";
            $this->processVoltar();
        }

        
        $codProcesso = explode("-", $processo);
        

        $licitacao = RegistroPreco_Dados_CadAtaRegistroPrecoInternaManterAtas::consultarLicitacaoAtaInterna($ano, $codProcesso[0], $orgao, $comissao, $grupo);

        $this->plotarBlocoLicitacao($licitacao);

        $situacao = ($_SESSION['_fperficorp_'] == 'S') ? 'A' : 'I';

        $atasLicitacao = RegistroPreco_Dados_CadAtaRegistroPrecoInternaManterAtas::consultarLicitacaoAtas($processo, $orgao, $ano, $situacao);

       
        $_SESSION['NATA_SEQ'] = $atasLicitacao->carpnosequ;

        $this->plotarBlocoAta($atasLicitacao);
    }

    /**
     * [processVoltar description]
     *
     * @return [type] [description]
     */
    public function processVoltar()
    {
        $uri = 'CadAtaRegistroPrecoInternaManter.php';
        header('Location: ' . $uri);
        exit();
    }

    /**
     * Redireciona para a página de alteração
     *
     * @return [type] [description]
     */
    public function processAlterar()
    {
        $orgao          = $_SESSION["orgao"];
        $ano            = $_SESSION["ano"];
        $processo       = $_SESSION["processo"];
        $sequencialAta  = $this->getAdaptacao()->recuperarSequencialAta();

        if (empty($sequencialAta)) {
            $this->blockErro('Selecione uma ata de Registro de Preço');
            $this->proccessPrincipal();
            return;
        }

        $uri = 'CadAtaRegistroPrecoInternaManterAtasAlterar.php?ano=' . $ano . '&processo=' . $processo . '&orgao=' . $orgao . '&ata=' . $sequencialAta;
        header('Location: ' . $uri);
        exit();
    }

    /* Redireciona para a página de visualização */
    public function processvisualizar()
    {
        $orgao = $_SESSION["orgao"];
        $ano = $_SESSION["ano"];
        $processo = $_SESSION["processo"];
        $sequencialAta = $this->getAdaptacao()->recuperarSequencialAta();

        if (empty($sequencialAta)) {
            $this->blockErro('Selecione uma ata de Registro de Preço');
            $this->proccessPrincipal();
            return;
        }

        $uri = 'CadAtaRegistroPrecoInternaManterAtasVisualizar.php?ano=' . $ano . '&processo=' . $processo . '&orgao=' . $orgao . '&ata=' . $sequencialAta;
        header('Location: ' . $uri);
        exit();
    }

    /**
     * [sqlSelectItemIntencao description]
     *
     * @param int $sequencialIntencao
     * @param int $anoIntencao
     * @return string
     */
    /* Início Funções de exibição de Templetes Bloco */
    public function plotarBlocoLicitacao($processos)
    {
        $processos = current($processos);

        $this->getTemplate()->VALOR_COMISSAO = $processos->ecomlidesc;
        $this->getTemplate()->VALOR_PROCESSO = str_pad($processos->clicpoproc, 4, '0', STR_PAD_LEFT);
        $this->getTemplate()->VALOR_ANO = $processos->alicpoanop;
        $this->getTemplate()->VALOR_MODALIDADE = $processos->emodlidesc;
        $this->getTemplate()->VALOR_LICITACAO = str_pad($processos->clicpocodl, 4, '0', STR_PAD_LEFT);
        $this->getTemplate()->VALOR_ANO_LICITACAO = $processos->alicpoanol;
        $this->getTemplate()->VALOR_ORG_LIMITE = $processos->eorglidesc;
        $this->getTemplate()->VALOR_PARTICIPANTES = RegistroPreco_Dados_CadAtaRegistroPrecoInternaManterAtas::
            consultarOrgaosParticipantesAta($processos->clicpoproc, $processos->alicpoanop, $processos->corglicodi);
        $this->getTemplate()->VALOR_OBJETO = $processos->xlicpoobje;

        $this->getTemplate()->block("BLOCO_LICITACAO");
        $this->getTemplate()->block("BLOCO_RESULTADO_PEQUISA");
    }

    public function plotarBlocoAta($atas)
    {
        
        
        foreach ($atas as $ata) {            
            
            
            $dto = $this->getAdaptacao()->getNegocio()->consultarDCentroDeCustoUsuario($ata->cgrempcodi, $ata->cusupocodi, $ata->corglicodi);
            $objeto = current($dto);

            $ataInterna = current($this->getAdaptacao()->getNegocio()->procurar((int)$ata->carpnosequ));

            $numeroAtaFormatado = $objeto->ccenpocorg . str_pad($objeto->ccenpounid, 2, '0', STR_PAD_LEFT);
            $numeroAtaFormatado .= "." . str_pad($ataInterna->carpincodn, 4, "0", STR_PAD_LEFT) . "/" . $ata->aarpinanon;

            $this->getTemplate()->KEY = $ata->carpnosequ;
            $this->getTemplate()->CODN = $ataInterna->carpincodn;
            $this->getTemplate()->VALOR_SEQ_ATA = $ata->carpnosequ;
            $this->getTemplate()->VALOR_ATA = $numeroAtaFormatado;
            date_default_timezone_set('America/Recife');
            $data = date("d/m/Y", strtotime($ata->tarpinulat));

            $this->getTemplate()->VALOR_VIGENCIA = $ata->aarpinpzvg == null ? "" : $ata->aarpinpzvg . " MESES";
            $documentosAtas = $this->getAdaptacao()->listarTodosDocumentos($ata->carpnosequ);

            foreach ($documentosAtas as $key => $documento) {
                $_SESSION['documento'.$ata->carpnosequ.'arquivo'.$key] = $documento->idocatarqu;
                $this->getTemplate()->VALOR_DOCUMENTO_KEY = 'documento'.$ata->carpnosequ.'arquivo'.$key;
                
                $documentoDecodificado = base64_decode($documento->arquivo);

                $this->getTemplate()->HEX_DOCUMENTO  = $documentoDecodificado;

                $this->getTemplate()->VALOR_DOCUMENTO = $documento->edocatnome;

                $this->getTemplate()->block("BLOCO_DOCUMENTOS");
            }
            $this->getTemplate()->VALOR_DATA      = $data;
            $this->getTemplate()->VALOR_SITUACAO  = $ata->farpinsitu == "A" ? "ATIVA" : "INATIVA";

            $this->getTemplate()->block("BLOCO_RESULTADO_ATAS");
        }
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
class RegistroPreco_Adaptacao_CadAtaRegistroPrecoInternaManterAtas extends Adaptacao_Abstrata
{

    /**
     * [__construct description]
     */
    public function __construct()
    {
        $this->setNegocio(new RegistroPreco_Negocio_CadAtaRegistroPrecoInternaManterAtas());
    }

    public function listarTodosDocumentos($carpnosequ)
    {
        return $this->getNegocio()->consultarTodosDocumentosAta($carpnosequ);
    }

    /* Início Funções gerenciadoras/Executoras de query */
    public function ativarSituacaoAta()
    {
        $orgao      = (int) $_SESSION["orgao"];
        $ano        = (int) $_SESSION["ano"];
        $processo   = (int) $_SESSION["processo"];
        $comissao   = (int) $_SESSION["comissao"];
        $grupo      = (int) $_SESSION["grupo"];

        $sequencialAta = $this->recuperarSequencialAta();
        $licitacao = RegistroPreco_Dados_CadAtaRegistroPrecoInternaManterAtas::consultarLicitacaoAtaInterna($ano, $processo, $orgao, $comissao, $grupo);
        $licitacao = current($licitacao);
        $this->getNegocio()->atualizarSituacaoAta($ano, $processo, $orgao, $licitacao->ccomlicodi, $licitacao->cgrempcodi, $sequencialAta, 'A');
    }

    /* Método de inativação da ata da licitação */
    public function inativarSituacaoAta()
    {
        $orgao      = (int) $_SESSION["orgao"];
        $ano        = (int) $_SESSION["ano"];
        $processo   = (int) $_SESSION["processo"];
        $comissao   = (int) $_SESSION["comissao"];
        $grupo      = (int) $_SESSION["grupo"];

        $sequencialAta = $this->recuperarSequencialAta();

        $licitacao = RegistroPreco_Dados_CadAtaRegistroPrecoInternaManterAtas::consultarLicitacaoAtaInterna($ano, $processo, $orgao, $comissao, $grupo);
        $licitacao = current($licitacao);
        $this->getNegocio()->atualizarSituacaoAta($ano, $processo, $orgao, $licitacao->ccomlicodi, $licitacao->cgrempcodi, $sequencialAta, 'I');
    }

    /**
     * Recupera o sequencial da ata a partir do número da ata selecionado
     *
     * @return [type] [description]
     */
    public function recuperarSequencialAta()
    {
        $numeroAta = $_REQUEST['NAta'];

        return $numeroAta;
    }
}

/**
 * A camada de Negócio conterá o código que irá implementar todas as regras de negócio do sistema.
 *
 * Utiliza serviços da camada de Dados.
 *
 * @author jfsi
 *
 */
class RegistroPreco_Negocio_CadAtaRegistroPrecoInternaManterAtas extends Negocio_Abstrata
{

    /**
     * [__construct description]
     */
    public function __construct()
    {
        $this->setDados(new RegistroPreco_Dados_CadAtaRegistroPrecoInternaManterAtas());
    }

    public function consultarDCentroDeCustoUsuario($cgrempcodi, $cusupocodi, $corglicodi)
    {   
        $db = Conexao();
        $sql = $this->getDados()->sqlConsultarCentroDeCustoUsuario($cgrempcodi, $cusupocodi, $corglicodi);
        
        $res = executarSQL($db, $sql);
        
        $itens = array();
        $item = null;
        while ($res->fetchInto($item, DB_FETCHMODE_OBJECT)) {
            $itens[] = $item;
        }
        if (PEAR::isError($res)) {
            ExibeErroBD(__FILE__ . "\nLinha: ".__LINE__."\nSql: " . $database->getMessage());
        }
        $db->disconnect();
        return $itens;
    }

    public function procurar($carpnosequ)
    {   
        $db = Conexao();
        $sql = $this->getDados()->sqlConsultarProcurarAta($carpnosequ);
        
        $res = executarSQL($db, $sql);
        
        $itens = array();
        $item = null;
        while ($res->fetchInto($item, DB_FETCHMODE_OBJECT)) {
            $itens[] = $item;
        }
        if (PEAR::isError($res)) {
            ExibeErroBD(__FILE__ . "\nLinha: ".__LINE__."\nSql: " . $database->getMessage());
        }
        $db->disconnect();
        return $itens;
    }

    public function consultarTodosDocumentosAta($carpnosequ)
    {
        return $this->getDados()->consultarTodosDocumentosAta($carpnosequ);
    }

    /* Método chamado ao voltar para tela */
    private function reiniciarTela()
    {
        $orgao = $_SESSION["orgao"];
        $ano = $_SESSION["ano"];
        $processo = $_SESSION["processo"];

        $licitacao = $this->consultarLicitaçãoAtaInterna($ano, $processo, $orgao);
        $this->plotarBlocoLicitacao($licitacao);

        $atasLicitacao = $this->consultarLicitacaoAtas($processo, $orgao, $ano);
        $this->plotarBlocoAta($atasLicitacao);
    }

    /* fim funcoes de Apoio */

    /* Atualiza a situação da ata selecionada */
    public function updateSituacaoAta($database, $valores, $condicao)
    {
        $res = $database->autoExecute('sfpc.tbataregistroprecointerna', $valores, DB_AUTOQUERY_UPDATE, $condicao);

        if (PEAR::isError($res)) {
            ExibeErroBD(__FILE__ . "\nLinha: " . __LINE__ . "\nSql: " . $database->getMessage());
        }

        return $res;
    }

    // Método organiza os valores tanto de inativa como de ativar e faz o update da sua situacão
    public function atualizarSituacaoAta($ano, $processo, $orgao, $comissao, $grupo, $numeroAta, $situacao)
    {
        $valores_operacao = array(
            'farpinsitu' => $situacao,
            'tarpinulat' => 'now()'
        );
        $database = & Conexao();
        $database->autoCommit(false);
        $codigos = $_POST['codigo'];
        $condicao = $this->getDados()->sqlAtualizaSituacao($ano, $processo, $orgao, $comissao, $grupo, $numeroAta, $codigos);
        $this->updateSituacaoAta($database, $valores_operacao, $condicao);

        $commited = $database->commit();

        if ($commited instanceof DB_error) {
            $database->rollback();
            return false;
        }
        return true;
    }
}

Seguranca();

$app = new RegistroPreco_UI_CadAtaRegistroPrecoInternaManterAtas();
$acao = filter_var($_REQUEST['Botao'], FILTER_SANITIZE_STRING);

switch ($acao) {
    case 'Voltar':
        $app->processVoltar();
        break;
    case 'Ativar':
        $app->ativarSituacaoAta();
        $app->proccessPrincipal();
        break;
    case 'Inativar':
        $app->inativarSituacaoAta();
        $app->proccessPrincipal();
        break;
    case 'Alterar':
        $app->processAlterar();
        break;
    case 'Visualizar':
        $app->processvisualizar();
        break;
    case 'Principal':
    default:
        $app->proccessPrincipal();
        break;
}

$valor_ativ_inat = '';
if($_SESSION['_fperficorp_'] == 'S' && false) {
    $valor_ativ_inat = '<input type="button" name="ativar" value="Ativar" class="botao" onclick="javascript:enviar(\'Ativar\')" > ';
    $valor_ativ_inat .= '<input type="button" name="{inativar}" value="Inativar" class="botao" onclick="javascript:enviar(\'Inativar\')" > ';
}

$app->getTemplate()->VALOR_ATIVAR_INATIVAR = $valor_ativ_inat;

$app->getTemplate()->ACAO_VISUALIZAR = 'Visualizar';
$app->getTemplate()->ENVIAR_VISUALIZAR = 'Visualizar';
$app->getTemplate()->VALUE_VISUALIZAR = 'Visualizar';
$app->getTemplate()->block('BLOCO_BOTAO_ATAS_VISUALIZAR');

$app->getTemplate()->block('BLOCO_ATIVAR_INATIVAR');

$app->getTemplate()->FORMPOST = 'CadAtaRegistroPrecoInternaManterAtas';
$app->getTemplate()->ACAO_ALTERAR = 'alterar';
$app->getTemplate()->ENVIAR_ALTERAR = 'Alterar';
$app->getTemplate()->VALUE_ALTERAR = 'Alterar';
$app->getTemplate()->block('BLOCO_BOTAO_ATAS_ALTERAR');

echo $app->getTemplate()->show();
