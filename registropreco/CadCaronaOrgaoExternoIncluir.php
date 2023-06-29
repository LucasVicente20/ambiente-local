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
 * @version   Git: v1.8.0-46-g35cfc81
 */

 // 220038--
 
if (! @require_once dirname(__FILE__) . "/../bootstrap.php") {
    throw new Exception("Error Processing Request - Bootstrap", 1);
}

require_once './funcoesRegistroPreco.php';

/**
 * A camada de Dados conterá o código que irá realizar todo o acesso aos dados.
 *
 * @author jfsi
 *
 */
class Dados
{
    public static function consultarItensDaAta($ata)
    {
        $sql = "select * from  sfpc.tbitemataregistropreconova iarpn";
        $sql .= " where iarpn.carpnosequ =" . $ata;
        return $sql;
    }

    public function sqlInsertCarona($codigoCarona, $codigoAta, $orgaoExterno)
    {
        $cusupocodi = $_SESSION['_cusupocodi_'];
        $sql = "INSERT INTO sfpc.tbcaronaorgaoexterno
        (ccaroesequ, carpnosequ, ecaroeorgg,tcaroeincl, cusupocodi, tcaroeulat)
        VALUES ($codigoCarona, $codigoAta, '$orgaoExterno', now(), $cusupocodi, now())";
        
        return $sql;
    }

    public function sqlUltimaCaronaOrgaoExteno()
    {
        $sql = "select max(ccaroesequ) from sfpc.tbcaronaorgaoexterno";
        return $sql;
    }

    public function sqlInsertItemCarona($sequencialAta, $sequecilItenAta, $quantidadeCarona)
    {
        $cusupocodi = $_SESSION['_cusupocodi_'];
        
        $sql = "INSERT INTO sfpc.tbcaronaorgaoexternoitem
       (ccaroesequ, citarpsequ, acoeitqtat, cusupocodi, tcoeitulat)
       VALUES($sequencialAta, $sequecilItenAta, $quantidadeCarona, $cusupocodi, now())";
        
        return $sql;
    }

    public function sqlSelectQtdAta($codigoAta)
    {
        $sql = "select aitarpqtor from sfpc.tbitemataregistropreconova";
        $sql .= " where carpnosequ =" . $codigoAta;
        
        return $sql;
    }
    public function sqlSelectQtdUtilizadaAta($codigoAta, $item, $tipo)
    {
        $sql =  "select sum(isc.aitescqtso) from sfpc.tbcaronaorgaoexterno coe";
        $sql .= " inner join sfpc.tbsolicitacaocompra sc";
        $sql .= " on coe.carpnosequ = sc.carpnosequ";
        $sql .= " inner join sfpc.itemsolicitacaocompra isc";
        $sql .= " on isc.csolcosequ = sc.csolcosequ";
        $sql .= " where coe.carpnosequ =" . $codigoAta;
        
        return $sql;
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
        $template = new TemplatePaginaPadrao("templates/CadAtaRegistroPrecoExternaIncluir.html", "Registro de Preço > Ata Interna > Carona Órgão Externo > Incluir");
        $this->setTemplate($template);
        $this->setAdaptacao(new Adaptacao());
        $this->getAdaptacao()->setTemplate($template);
    }

    public function insertDados()
    {
        $inserido = $this->getAdaptacao()
            ->getNegocio()
            ->insertDados();
        if (! $inserido) {
            $this->blockErro("A quantidade solicitada na carona maior que o permitido");
        } else {
            $this->blockErro("Operação Realizada com Sucesso!");
        }
    }

    /*
     * @param IPrograma $app [description]
     * @return void [description]
     */
    public function acaoVoltar()
    {
        $processo = $_REQUEST['processo'];
        $orgao = $_SESSION["orgao"];
        $ano = $_SESSION["ano"];
        
        $uri = 'CadAtaRegistroPrecoInternaCaronaOrgaoExterno.php?ano=' . $ano . '&processo=' . $processo . '&orgao=' . $orgao;
        header('location: ' . $uri);
    }

    /**
     * [plotarTelaPrincipal description]
     *
     * @return [type] [description]
     */
    public function plotarTelaPrincipal()
    {
        $this->getTemplate()->SEQUENCIAL_ATA = $_SESSION['sequencialAta'];
        $this->getTemplate()->PROCESSO_LICITATORIO = str_pad($_GET['processo'], 4, '0', STR_PAD_LEFT);
    }

    public function exibePaginaInicial()
    {
        $this->plotarTelaPrincipal();
        $codigoAta = filter_input(INPUT_GET, 'ata', FILTER_VALIDATE_INT);
        $itensAta =  $this->getAdaptacao()->getNegocio()->consultarItensDaAta($codigoAta);
        $this->getAdaptacao()->plotarItensAta($itensAta);
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
    public function __construct()
    {
        $this->setNegocio(new Negocio());
    }

    public function plotarItensAta($resultado)
    {
        $valoresCarona = $_POST['ValorAtualUnitario'];
        $processo = $_REQUEST['processo'];
        $orgao = $_SESSION["orgao"];
        $ano = $_SESSION["ano"];
        
        $valorSumarizadoTotal = 0;
        
        $contador = 1;
        if ($resultado == null) {
            return;
        }
        
        $valoresUsados = new View();
        
        foreach ($resultado as $item) {
            $valorItem = $item->cservpsequ == null ? $item->cmatepsequ : $item->cservpsequ;
             
            $valorConsumido = $this->getNegocio()->consultarQuantidadeUtilizadaCarona($_REQUEST['ata'], $valorItem, $item->cmatepsequ);
            //$valorConsumido = $valoresUsados->selecionarValoresUsados(, $item->citarpsequ, $valorItem, $processo, $ano, $orgao);

            $this->getTemplate()->VALOR_PROCESSO = $_REQUEST['processo'];
            $this->getTemplate()->VALOR_ITEM = $item->aitarporde;
            $this->getTemplate()->VALOR_CODIGO_ITEM = $item->citarpsequ;
            $this->getTemplate()->VALOR_CODIGO_ATA = $item->carpnosequ;
            $this->getTemplate()->VALOR_CODIGO_REDUZIDO = $item->cservpsequ == null ? $item->cmatepsequ : $item->cservpsequ;
            $this->getTemplate()->VALOR_DESCRICAO = $item->eitarpdescse;
            $this->getTemplate()->VALOR_UNIDADE = $item->vitarpvori;
            $this->getTemplate()->VALOR_UNIT = $item->vitarpvatu;
            $this->getTemplate()->VALOR_TOTAL_ORIGINAL = $item->vitarpvori * aitarpqtor;
            $this->getTemplate()->VALOR_LOTE = $item->citarpnuml;
            $this->getTemplate()->VALOR_QTD_CARONA = $item->vitarpvori * 5 - $valorConsumido;
            $this->getTemplate()->VALOR_ATUAL_UNIT = converte_valor_licitacao($item->acoeitqtat * $item->vitarpvatu);
            $this->getTemplate()->VALOR_TOTAL_ATUAL = converte_valor_licitacao($item->acoeitqtat * $item->vitarpvatu);
            $this->getTemplate()->VALOR_ORIGINAL_UNIT = $item->vitarpvori;
            $this->getTemplate()->VALOR_QTD_ORIGINAL = $item->vitarpvori;
            $this->getTemplate()->VALOR_QTD_ATUAL = $_POST["ValorAtualSaldo[$contador]"];
            $valorSumarizadoTotal += ($item->acoeitqtat * $item->vitarpvatu);
            $contador ++;
            $this->getTemplate()->block('BLOCO_LISTAGEM_ITEM_CARONA');
        }
        
        $this->getTemplate()->TOTAL_ATA = converte_valor_licitacao($valorSumarizadoTotal);
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
    const VALOR_QUANTIDADE_CARONA = 5;

    public function consultarItensDaAta($ata)
    {
        $resultados = array();
        $sql = Dados::consultarItensDaAta($ata);
        $resultado = executarSQL(ClaDatabasePostgresql::getConexao(), $sql);
        while ($resultado->fetchInto($itensAta, DB_FETCHMODE_OBJECT)) {
            $resultados[] = $itensAta;
        }
        return $resultados;
    }
    
    public function consultarQuantidadeUtilizadaCarona()
    {
        $sql = sqlSelectQtdUtilizadaAta($codigoAta, $item, $tipo);
        $resultado = executarSQL(ClaDatabasePostgresql::getConexao(), $sql);
        $resultado->fetchInto($somatorio, DB_FETCHMODE_OBJECT);
        
        return $somatorio;
    }

    public function inserirCaronaOrgao($codigoCarona, $codigoAta, $orgaoExterno, $database)
    {
        $sql = Dados::sqlInsertCarona($codigoCarona, $codigoAta, $orgaoExterno);
        $resultado = $database->query($sql);
        
        ClaDatabase::hasError($resultado);
    }

    public function insertItemCarona($sequencialAta, $sequecilItenAta, $quantidadeCarona, $database)
    {
        $SQL = DADOS::sqlInsertItemCarona($sequencialAta, $sequecilItenAta, $quantidadeCarona, $database);
        $database->query($SQL);
    }

    public function insertDados()
    {
        $valoresCarona = $_POST['ValorAtualUnitario'];
        $valoresSaldo = $_POST['ValorAtualSaldo'];
        $codigoItens = $_POST['CodigoItem'];
        $codigosAtas = $_POST['CodigoAta'];
        $qtdAta = $_POST['qtdCarona'];
        $itemAtaSeq = $_POST['CodigoItem'];
        $orgaoExterno = $_POST['orgaoCarona'];
        
        $validacaoCarona = new NegocioCarona();
        
        $tamanho = sizeof($valoresCarona);
        for ($i = 0; $i < $tamanho; $i ++) {
            $valido = $validacaoCarona->validacao($valoresCarona[$i], $valoresSaldo[$i], intval($qtdAta[$i]));
            
            if (! $valido) {
                $this->getGUI()->blockErro("A quantidade solicitada na carona maior que o permitido");
                return;
            }
        }
        
        $database = ClaDatabasePostgresql::getConexao();
        $database->autoCommit(false);
        
        if ($valido) {
            $SQLcodigoCarona = Dados::sqlUltimaCaronaOrgaoExteno();
            
            $resultado = executarSQL(ClaDatabasePostgresql::getConexao(), $SQLcodigoCarona);
            
            $resultado->fetchInto($codigoCarona, DB_FETCHMODE_OBJECT);
            
            $this->inserirCaronaOrgao($codigoCarona->max + 1, $codigosAtas[0], $orgaoExterno, $database);
            for ($i = 0; $i < $tamanho; $i ++) {
                $this->insertItemCarona($codigoCarona->max + 1, $itemAtaSeq[$i], intval($qtdAta[$i]), $database);
            }
        } else {
            return false;
        }
        // Caso não ocorra nenhum erro, commita transação
        $database->commit();
        return true;
    }

/**
 * [persistenciaDados description]
 *
 * @param [type] $database
 *            [description]
 * @return [type] [description]
 */
}

$app = new GUI();

$botao = isset($_POST['Botao']) ? filter_input(INPUT_POST, 'Botao', FILTER_SANITIZE_STRING) : 'Principal';

switch ($botao) {
    case 'RetirarItem':
        $app->acaoRetirarItem();
        break;
    case 'Incluir':
        $app->insertDados();
        break;
    case 'Voltar':
        $app->acaoVoltar();
        break;
    case 'Principal':
    default:
        $app->exibePaginaInicial();
        break;
}

$app->getTemplate()->block('BLOCO_CARONA');
$app->getTemplate()->block('BLOCO_LISTAGEM_CARONA');

$app->getTemplate()->ACAO_SALVAR = "Incluir";
$app->getTemplate()->NOME_PROGRAMA = "CadCaronaOrgaoExternoIncluir";
$app->getTemplate()->SUPER_TITULO = "Manter - Carona Órgão Externo";
$app->getTemplate()->block('bloco_voltar');
echo $app->getTemplate()->show();
