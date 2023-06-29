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
 * @version   Git: v1.8.0-97-g28abed4
 */

 // 220038--

if (!@require_once dirname(__FILE__)."/../bootstrap.php") {
    throw new Exception("Error Processing Request - Bootstrap", 1);
}
/**
 * A camada de Dados conterá o código que irá realizar todo o acesso aos dados.
 *
 * @author jfsi
 *
 */
class Dados
{
    
    /**
     * [sqlLicitacaoAtaInterna description]
     *
     * @param integer $ano
     *            [description]
     * @param integer $processo
     *            [description]
     * @return string [description]
     */
    public function sqlLicitacaoAtaInterna($ano, $processo, $orgaoUsuario)
    {
        if (empty($processo)) {
            throw new Exception("Error Processing Request", 1);
        }

        $valores = explode('-', $processo);
        $sql = "
            SELECT DISTINCT l.clicpoproc, l.alicpoanop, l.xlicpoobje, l.ccomlicodi, c.ecomlidesc, o.corglicodi,
                o.eorglidesc, m.emodlidesc, l.clicpocodl, l.alicpoanol, l.cgrempcodi
            FROM sfpc.tblicitacaoportal l
            INNER JOIN sfpc.tborgaolicitante o
                ON l.corglicodi = o.corglicodi
            INNER JOIN sfpc.tbcomissaolicitacao c
                ON l.ccomlicodi = c.ccomlicodi
            INNER JOIN sfpc.tbmodalidadelicitacao m
                ON l.cmodlicodi = m.cmodlicodi
            WHERE l.clicpoproc = %d AND l.alicpoanop = %d AND l.cgrempcodi = %d AND l.ccomlicodi = %d AND l.corglicodi = %d
            ";
        $sql = sprintf($sql, $valores[0], $valores[1], $valores[2], $valores[3], $valores[4]);
        
        return $sql;
    }


    /**
     *
     * @param array $processo
     * @return string
     */
    public function sqlAtaLicitacao($processo, $orgao, $ano)
    {
        $valores = explode('-', $processo);
        $sql = "
            SELECT
                arpi.carpnosequ,
                arpi.clicpoproc,
                arpi.alicpoanop,
                arpi.aforcrsequ,
                fc.nforcrrazs,
                arpi.aarpinanon,
                arpi.aarpinpzvg,
                arpi.farpinsitu,
                arpi.tarpinulat
                
            FROM
                sfpc.tbataregistroprecointerna arpi
                INNER JOIN sfpc.tbfornecedorcredenciado fc
                        ON arpi.aforcrsequ = fc.aforcrsequ
                

            WHERE 1 = 1
                AND arpi.clicpoproc = %d
                AND arpi.alicpoanop = %d
                AND arpi.cgrempcodi = %d
                AND arpi.ccomlicodi = %d
                AND arpi.corglicodi = %d
           ORDER BY
                arpi.carpnosequ
        ";

        return sprintf($sql, $valores[0], $valores[1], $valores[2], $valores[3], $valores[4]);
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

        //return ClaDatabasePostgresql::executarSQL($sql);
        
        
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
        $sql = "
            SELECT * FROM sfpc.tbataregistroprecointerna WHERE carpnosequ = %d             
        ";

        $sql = sprintf($sql, $carpnosequ);
        
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


    public function sqlAtaParticipanteAta($chaveAta)
    {
        $sql  = "select distinct on (o.eorglidesc) * ";
        $sql .= " from sfpc.tbparticipanteatarp pa ";
        $sql .= " inner join sfpc.tborgaolicitante o on ";
        $sql .= " o.corglicodi = pa.corglicodi  ";
        $sql .= "  where pa.carpnosequ in (" . $chaveAta . " )";
    
        return $sql;
    }




    /*Valores condicionais da atualização da situação*/
    public function sqlAtualizaSituacao($ano, $processo, $orgao, $comissao, $grupo, $numeroAta)
    {
        $sql  = "clicpoproc=".$processo;
        $sql .= " and alicpoanop = ".$ano;
        $sql .= " and cgrempcodi =".$grupo;
        $sql .= " and ccomlicodi=".$comissao;
        $sql .= " and corglicodi=".$orgao;
        $sql .= " and carpnosequ=".$numeroAta;


        return $sql;
    }
    /*Fim Querys Sqls*/

    /**
     * Executa a licitação na qual a ata interna se refere
     * @param  [type] $ano          [description]
     * @param  [type] $processo     [description]
     * @param  [type] $orgaoUsuario [description]
     * @return [type]               [description]
     */
    public static function consultarLicitacaoAtaInterna($ano, $processo, $orgaoUsuario)
    {
        $licitacao = null;

        $resultado = executarSQL(
            ClaDatabasePostgresql::getConexao(),
            self::sqlLicitacaoAtaInterna($ano, $processo, $orgaoUsuario)
        );

        $resultado->fetchInto($licitacao, DB_FETCHMODE_OBJECT);

        return $licitacao;
    }
    /**
     * [consultarLicitacaoAtas description]
     * @param  [type] $processo [description]
     * @param  [type] $orgao    [description]
     * @param  [type] $ano      [description]
     * @return [type]           [description]
     */
    public static function consultarLicitacaoAtas($processo, $orgao, $ano)
    {
        $resultados = array();
        $licitacao = null;
        $resultado = executarSQL(
            ClaDatabasePostgresql::getConexao(),
            self::sqlAtaLicitacao($processo, $orgao, $ano)
        );
        while ($resultado->fetchInto($licitacao, DB_FETCHMODE_OBJECT)) {
            $resultados[] = $licitacao;
        }
        return $resultados;
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
class GUI extends BaseIntefaceGraficaUsuario
{
    /**
     * [__construct description]
     */
    public function __construct()
    {
        $this->setTemplate(
            new TemplatePaginaPadrao(
                "templates/CadAtaRegistroPrecoInternaManterEspecialAtas.html",
                "Registro de Preço > Ata Interna > Manter Especial"
            )
        );

        $this->setAdaptacao(new Adaptacao());
        $this->getAdaptacao()->setTemplate($this->getTemplate());

        $this-> getTemplate()->TITULO_PROGRAMA = "MANTER ESPECIAL - ATA INTERNA";
        $this-> getTemplate()->TITULO_ATAS = "ATA(S) DE REGISTRO DE PREÇO";
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
     * @return [type] [description]
     */
    public function proccessPrincipal()
    {
        $orgao    = filter_input(INPUT_GET, 'orgao');
        $ano      = filter_input(INPUT_GET, 'ano');
        $processo = filter_input(INPUT_GET, 'processo');

        unset($_SESSION['orgaos']);
        unset($_SESSION['item']);

        if ($orgao != null) {
            $_SESSION["orgao"] = $orgao;
            $_SESSION["ano"] = $ano;
            $_SESSION["processo"] = $processo;
        } else {
            $orgao = $_SESSION["orgao"];
            $ano = $_SESSION["ano"];
            $processo = $_SESSION["processo"];
        }
        
        $numProcesso = $processo;

        $licitacao = Dados::consultarLicitacaoAtaInterna($ano, $processo, $orgao);
        

        $atasLicitacao = Dados::consultarLicitacaoAtas($processo, $orgao, $ano);

        $this->getAdaptacao()->plotarBlocoLicitacao($licitacao,$atasLicitacao);
        $this->getAdaptacao()->plotarBlocoAta($atasLicitacao);
    }
    /**
     * [processVoltar description]
     * @return [type] [description]
     */
    public function processVoltar()
    {
        $uri  = 'CadAtaRegistroPrecoInternaManterEspecial.php';
        header('Location: ' . $uri);
        exit();
    }
    /**
     * Redireciona para a página de alteração
     * @return [type] [description]
     */
    public function processAlterar()
    {
        $orgao         = $_SESSION["orgao"];
        $ano           = $_SESSION["ano"];
        $processo      = $_SESSION["processo"];
        $sequencialAta = $this->getAdaptacao()->recuperarSequencialAta();
        $anos          = HelperPitang::carregarAno();

        if (empty($sequencialAta)) {
            $this->blockErro('Selecione uma ata de Registro de Preço');
            $this->proccessPrincipal();
            return;
        }

        $uri  = 'CadAtaRegistroPrecoInternaManterEspecialAtasAlterar.php?ano='.$ano.'&processo='.$processo.'&orgao='.$orgao.'&ata='.$sequencialAta;
        header('Location: ' . $uri);
        exit();
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
class Adaptacao extends AbstractAdaptacao
{
    /**
     * [__construct description]
     */
    public function __construct()
    {
        $this->setNegocio(new Negocio());
    }


    /*Início Funções gerenciadoras/Executoras de query*/
    public function ativarSituacaoAta()
    {
        $orgao = $_SESSION["orgao"];
        $ano = $_SESSION["ano"];
        $processo = $_SESSION["processo"];
        $sequencialAta = $this->recuperarSequencialAta();
        $licitacao =Dados::consultarLicitacaoAtaInterna($ano, $processo, $orgao);


        $this->getNegocio()->atualizarSituacaoAta($ano, $processo, $orgao, $licitacao->ccomlicodi, $licitacao->cgrempcodi, $sequencialAta, 'A');
    }

    /*Método de inativação da ata da licitação*/
    public function inativarSituacaoAta()
    {
        $orgao = $_SESSION["orgao"];
        $ano = $_SESSION["ano"];
        $processo = $_SESSION["processo"];
        $sequencialAta = $this->recuperarSequencialAta();
        $licitacao = Dados::consultarLicitacaoAtaInterna($ano, $processo, $orgao);

        $this->getNegocio()->atualizarSituacaoAta($ano, $processo, $orgao, $licitacao->ccomlicodi, $licitacao->cgrempcodi, $sequencialAta, 'I');
        //$this->reiniciarTela();
    }

    /**
     * [sqlSelectItemIntencao description]
     *
     * @param int $sequencialIntencao
     * @param int $anoIntencao
     * @return string
     */
    /*Início Funções de exibição de Templetes Bloco*/
    public function plotarBlocoLicitacao($processos, $atasLicitacao)
    {
        
        $arrayMontato = null;
        foreach ($atasLicitacao as $key => $value) {
           $arrayMontato[] = $value->carpnosequ;
        }

         
        $stringParticipante = '';
        if($arrayMontato != null){
           $chaveAta = implode(',',$arrayMontato);
           $ataParticipante = $this->getNegocio()->consultarAtaParticipanteChave($chaveAta);
           foreach ($ataParticipante as $key => $valueParti) {
               $stringParticipante .= $valueParti->eorglidesc .'<br />';
           }
        }

        

        $this->getTemplate()->VALOR_COMISSAO      = $processos->ecomlidesc;
        $this->getTemplate()->VALOR_PROCESSO      = str_pad($processos->clicpoproc, 4, '0', STR_PAD_LEFT);
        $this->getTemplate()->VALOR_ANO           = $processos->alicpoanop;
        $this->getTemplate()->VALOR_MODALIDADE    = $processos->emodlidesc;
        $this->getTemplate()->VALOR_LICITACAO     = str_pad($processos->clicpoproc, 4, '0', STR_PAD_LEFT);
        $this->getTemplate()->VALOR_ANO_LICITACAO = $processos->alicpoanop;

        $this->getTemplate()->VALOR_ORG_LIMITE    = $processos->eorglidesc;
        //$this->getTemplate()->VALOR_PARTICIPANTES = $processos->eorglidesc;
        $this->getTemplate()->VALOR_PARTICIPANTES = $stringParticipante;
        $this->getTemplate()->VALOR_OBJETO        = $processos->xlicpoobje;

        $this->getTemplate()->block("BLOCO_LICITACAO");
        $this->getTemplate()->block("BLOCO_RESULTADO_PEQUISA");
    }

     /**
     * Recupera o sequencial da ata a partir do número da ata selecionado
     * @return [type] [description]
     */
    public function recuperarSequencialAta()
    {
        $numeroAta = $_REQUEST['NAta'];

        // $numeroAtaExp = explode(".", $numeroAta);
        // $numeroAtaCodi = explode("/", $numeroAtaExp[1]);
        // $codigo = intval($numeroAtaCodi[0]);

        $codigo = intval($numeroAta);

        return $codigo;
    }
 
    public function plotarBlocoAta($atas)
    {
        if ($atas == null) {
            return;
        }

        foreach ($atas as $ata) {
        
            $ataInterna = Dados::procurar($ata->carpnosequ);
            
            $dto = $this->getNegocio()->consultarDCentroDeCustoUsuario($ataInterna[0]->cgrempcodi, $ataInterna[0]->cusupocodi, $ataInterna[0]->corglicodi);
            $objeto = current($dto);

            $documentosAtas = Dados::consultarTodosDocumentosAta($ata->carpnosequ);


            foreach ($documentosAtas as $key => $documento) {
                $_SESSION['documento'.$ata->carpnosequ.'arquivo'.$key] = $documento->idocatarqu;
                $this->getTemplate()->VALOR_DOCUMENTO_KEY = 'documento'.$ata->carpnosequ.'arquivo'.$key;
                
                $documentoDecodificado = base64_decode($documento->arquivo);

                $this->getTemplate()->HEX_DOCUMENTO  = $documentoDecodificado;

                $this->getTemplate()->VALOR_DOCUMENTO = $documento->edocatnome;

                $this->getTemplate()->block("BLOCO_DOCUMENTOS");
            }
                        
            $numeroAtaFormatado = $objeto->ccenpocorg . str_pad($objeto->ccenpounid, 2, '0', STR_PAD_LEFT);
                        
            $numeroAtaFormatado .= "." . str_pad($ataInterna[0]->carpincodn, 4, "0", STR_PAD_LEFT) . "/" . $ata->aarpinanon;

            $data= date("d/m/Y", strtotime($ata->tarpinulat));

            $this->getTemplate()->VALOR_VIGENCIA            = $ata->aarpinpzvg == null ?"" :$ata->aarpinpzvg." MESES";
            $this->getTemplate()->VALOR_DOCUMENTO           = $ata->edoclinome;            
            $this->getTemplate()->VALOR_SEQ_ATA             = $ata->carpnosequ;
            $this->getTemplate()->VALOR_ATA                 = $numeroAtaFormatado;
            $this->getTemplate()->VALOR_DATA                = $data;
            $this->getTemplate()->VALOR_SITUACAO            = $ata->farpinsitu == "A" ? "ATIVA": "INATIVA";
            $this->getTemplate()->block("BLOCO_RESULTADO_ATAS");
        }
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
class Negocio extends BaseNegocio
{
    /**
     * [__construct description]
     */
    public function __construct()
    {
        $this->setDados(new Dados());
    }

    /*Método chamado ao voltar para tela*/
    private function reiniciarTela()
    {
        $orgao = $_SESSION["orgao"];
        $ano = $_SESSION["ano"];
        $processo = $_SESSION["processo"];

        $licitacao = $this->consultarLicitaçãoAtaInterna($ano, $processo, $orgao);
        $this->plotarBlocoLicitacao($licitacao);


        $atasLicitacao= $this->consultarLicitacaoAtas($processo, $orgao, $ano);
        $this->plotarBlocoAta($atasLicitacao);
    }
    /*fim funcoes de Apoio*/


    /*Atualiza a situação da ata selecionada*/
    public function updateSituacaoAta($database, $valores, $condicao)
    {
        $res = $database->autoExecute(
                'sfpc.tbataregistroprecointerna',
                $valores,
                DB_AUTOQUERY_UPDATE, $condicao
           );

        if (PEAR::isError($res)) {
            ExibeErroBD(__FILE__ . "\nLinha: ".__LINE__."\nSql: " . $database->getMessage());
        }

        return $res;
    }

    //Método organiza os valores tanto de inativa como de ativar e faz o update da sua situacão
    public function atualizarSituacaoAta($ano, $processo, $orgao, $comissao, $grupo, $numeroAta, $situacao)
    {
        $valores_operacao = array(
                'farpinsitu'    => $situacao,
                'tarpinulat'    => 'now()'
        );
        $database =& Conexao();
        $database->autoCommit(false);

        $condicao = $this->getDados()->sqlAtualizaSituacao($ano, $processo, $orgao, $comissao, $grupo, $numeroAta);
        $this->updateSituacaoAta($database, $valores_operacao, $condicao);

        $commited = $database->commit();

        if ($commited instanceof DB_error) {
            $database->rollback();
            return false;
        }
        return true;
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

    public function consultarAtaParticipanteChave($numeroAta)
    {	
        $db = Conexao();
        $sql = $this->getDados()->sqlAtaParticipanteAta($numeroAta);
		
        $res = executarSQL($db, $sql);
        $itens = array();
        $item = null;
        $itemTipo = new stdClass();        
        while ($res->fetchInto($item, DB_FETCHMODE_OBJECT)) {
            $item->tipoItem = $itemTipo->tipoItem;
            $itens[] = $item;            
        }
        return $itens;
    }
}

/**
 * [$app description]
 * @var Negocio
 */
$app = new GUI();

$acao = filter_input(INPUT_POST, 'Botao', FILTER_SANITIZE_STRING);

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
    case 'Principal':
    default:
        $app->proccessPrincipal();
        break;
}

$app->getTemplate()->FORMPOST = 'CadAtaRegistroPrecoInternaManterEspecialAtas';
$app->getTemplate()->ACAO_ALTERAR = 'alterar';
$app->getTemplate()->ENVIAR_ALTERAR =  'Alterar';
$app->getTemplate()->VALUE_ALTERAR = 'Alterar';
$app->getTemplate()->block('BLOCO_BOTAO_ATAS_ALTERAR');
echo $app->getTemplate()->show();
