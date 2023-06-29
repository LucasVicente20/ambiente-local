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
 * @version   Git: $Id:$
 */

 // 220038--

 header('X-XSS-Protection:0');

 if (!@require_once dirname(__FILE__)."/../bootstrap.php") {
    throw new Exception("Error Processing Request - Bootstrap", 1);
}

class CadAtaRegistroPrecoInternaManterEspecialAtasAlterar extends Helper_RegistroPreco
{
    private $template;
    private $variables;
    private $files;
    private static $erroPrograma = 'CadAtaRegistroPrecoInternaManterEspecialAtasAlterar.php';

    private function getTemplate()
    {
        return $this->template;
    }

    private function setTemplate(TemplatePaginaPadrao $template)
    {
        $this->template = $template;
        return $this;
    }


    private function excluir()
    {
       
        $ano            = $this->variables['get']['ano'];
        $processo       = $this->variables['get']['processo'];
        $orgao          = $this->variables['get']['orgao'];
        $ata            = $this->variables['get']['ata'];
        $quebraProcesso = explode("-", $processo);
        $codProcesso    = $quebraProcesso[0];
        $codOrgao       = $quebraProcesso[4];


        if(!$this->validarAtaAlterarNumeracao($ano, $codProcesso, $codOrgao, $ata, $_POST, true)){
            $this->proccessPrincipal();
            return false; 
        }

        $database = Conexao();
        $database->autoCommit(false);
        $database->query("BEGIN TRANSACTION");

        try {                
            $database->query(sprintf("DELETE FROM sfpc.tbparticipanteitematarp WHERE carpnosequ = %d  ", $ata));            
            $database->query(sprintf("DELETE FROM sfpc.tbitemataregistropreconova WHERE carpnosequ = %d", $ata));
            $database->query(sprintf("DELETE FROM sfpc.tbataregistroprecointerna WHERE carpnosequ = %d", $ata));
            $database->query("COMMIT");
            $database->query("END TRANSACTION");
            $_SESSION['mensagemFeedback'] = 'Ata excluída com sucesso';
        } catch (Exception $e) {
            $semerror = false;
            $database->query("ROLLBACK");
            $_SESSION['mensagemFeedback'] = 'Erro ao excluir ata';
            ExibeErroBD(self::$erroPrograma . "\nLinha: ".__LINE__."\nSql: " . $e->getMessage());
        }

        $database->disconnect();
        $_SESSION['mensagemFeedback'] = 'Ata excluída com sucesso';
        
        $this->redirecionarParaInicio();

    }



    private function salvar()
    {
        $ano        = $this->variables['get']['ano'];
        $processo   = $this->variables['get']['processo'];
        $orgao      = $this->variables['get']['orgao'];
        $ata        = $this->variables['get']['ata'];
        $quebraProcesso = explode("-", $processo);
        $codProcesso    = $quebraProcesso[0];
        $codOrgao       = $quebraProcesso[4];

        if(!$this->validarAtaAlterarNumeracao($ano, $codProcesso, $codOrgao, $ata, $_POST, false)){
            $this->proccessPrincipal();
            return false;            
        }else{             
             $resultado = $this->atualizarNumeracaoAnoAta($ata, $_POST);             
        }

      
        $_SESSION['post_itens_armazenar_tela_normais'] = $_REQUEST['itemAta'];

        foreach ($_REQUEST['itemAta'] as $item) {                
            if(!$this->validarItemAta($item)){
                $this->proccessPrincipal();
                return false;                
            }                                
        }

        if(!$this->validarItemAtaOrgao($_POST['itemOrgao'])){
            $this->proccessPrincipal();
            return false;            
        }
        
        $db = Conexao();
        $db->autoCommit(false);
        $db->query("BEGIN TRANSACTION");
        try {
            foreach ($_REQUEST['itemAta'] as $item) {                
                $this->salvarItemAta($db, $ata, $item);                                                 
            }
            $db->query("COMMIT");
            $db->query("END TRANSACTION");

            
        } catch (Exception $e) {
            $db->query("ROLLBACK");
            $_SESSION['mensagemFeedback'] = $e->getMessage();
            $this->proccessPrincipal();
        }

        $db->disconnect();        

        $entidade = new StdClass;
        $entidade->carpnosequ = $ata;
        $entidade->cusupocodi = (int) $_SESSION['_cusupocodi_'];

        if(!empty($_SESSION['orgaos'])) {        
            foreach ($_SESSION['orgaos'] as $key => $value) {
                $entidade->corglicodi = $key;
                //depois Verificar a situacao
                $entidade->fpatrpsitu = 'A';
                $entidade->tpatrpulat = date('Y-m-d H:i:s');

                $db = Conexao();
                $db->autoCommit(false);
                $db->query("BEGIN TRANSACTION");
                             
                $consultarParticipanteAtaOrgao = $this->consultarParticipanteAtaOrgao($db, $entidade->carpnosequ, $entidade->corglicodi);
                
                if($consultarParticipanteAtaOrgao == null){   
                    $sqlParticipanteNovo = $this->sqlAddParticipanteOrgaoAta($entidade);
                    $resultadoAtaNova       = executarTransacao($db, $sqlParticipanteNovo);
                    $commited = $db->commit();            
                }else{                
                    $situacao = $consultarParticipanteAtaOrgao->fpatrpsitu;
                    $sqlParticipanteNovo = $this->sqlUpdateParticipanteOrgaoAta($entidade, $situacao);
                    $resultadoAtaNova       = executarTransacao($db, $sqlParticipanteNovo);
                    $commited = $db->commit();                          
                }

                if ($commited instanceof DB_error) {
                    $db->rollback();
                    $_SESSION['mensagemFeedback'] = 'Erro ao salvar dados';
                    ExibeErroBD(self::$erroPrograma . "\nLinha: " . __LINE__ . "\nSql: " . $e->getMessage());
                    $semerror = false;
                }
                
            }
        }

        
        $database = Conexao();
        $database->autoCommit(false);
        $database->query("BEGIN TRANSACTION");

        try {
            foreach ($_POST['itemOrgao'] as $codigoItem => $item) { 

                $itemParaconsulta = $_REQUEST['itemAta'][$codigoItem];

                $codigoReduzido = $itemParaconsulta['cservpsequ'];
                if(isset($itemParaconsulta['cmatepsequ']) === true) {
                    $codigoReduzido = $itemParaconsulta['cmatepsequ'];
                }

                $itemNoBanco = $this->consultarItemAta($database, $ata, $codigoReduzido, $itemParaconsulta['fgrumstipo']);
                foreach ($item as $codigoOrgao => $itemOrgao) {
                    if(is_array($itemOrgao)){ 
                        $this->salvarItemAtaParticipante($database, $ata, $itemNoBanco->citarpsequ, $codigoOrgao, $itemOrgao); 
                    }                               
                }               
            }

            $database->query("COMMIT");
            $database->query("END TRANSACTION");

            $_SESSION['mensagemFeedback'] = 'Dados salvos com sucesso';
        } catch (Exception $e) {
            $semerror = false;
            $database->query("ROLLBACK");
            $_SESSION['mensagemFeedback'] = 'Erro ao salvar dados';
            ExibeErroBD(self::$erroPrograma . "\nLinha: ".__LINE__."\nSql: " . $e->getMessage());
        }

         $database->disconnect();


        $_SESSION['mensagemFeedback'] = 'Dados salvos com sucesso';
        $this->redirecionarParaInicio();


    }


    private function salvarItemAtaParticipante($db, $ata, $codigoItem, $codigoOrgao, $itemOrgao)
    {
   
        $itemNoBanco = $this->consultarItemAtaParticipante($db, $ata, $codigoItem, $codigoOrgao);
        $resultado = null;
        if ($itemNoBanco == null) {            
            $resultado = $this->inserirItemParticipante($db, $ata, $codigoItem, $codigoOrgao, $itemOrgao);
            //unset($_SESSION['item']);
        } else {
            $situacao = $itemNoBanco->fpiarpsitu;
            $resultado = $this->atualizarItemParticipante($db, $ata, $codigoItem, $codigoOrgao, $itemOrgao, $situacao);            
        }

        if (PEAR::isError($resultado)) {
            throw new RuntimeException($resultado->getMessage());
        }
    }

    private function atualizarItemParticipante($db, $ata, $codigoItem, $codigoOrgao, $itemOrgao, $situacao)
    {        
        $sequencial  = $codigoItem;
        $quatidadeDoParticipante = $itemOrgao['apiarpqtat'] == null ? 0  :moeda2float($itemOrgao['apiarpqtat'], 4);
        $quatidadeUtilizadaDoParticipante = $itemOrgao['apiarpqtut'] == null ? 0  :moeda2float($itemOrgao['apiarpqtut'], 4);
        $situacao = $situacao;        
        $codigoUsuario      = $_SESSION['_cusupocodi_'];
        $tpiarpulat = date('Y-m-d H:i:s');
      
        $sql  = "UPDATE ";
        $sql .= "sfpc.tbparticipanteitematarp SET ";
        
        $sql .= " apiarpqtat  = " . $quatidadeDoParticipante;
        $sql .= " , apiarpqtut  = " . $quatidadeUtilizadaDoParticipante;

        $sql .= " , fpiarpsitu  = " . "'" . $situacao . "'";

        $sql .= " , cusupocodi  = " . $codigoUsuario;

        $sql .= " , tpiarpulat  = " . "'" . $tpiarpulat . "'";

        $sql .= " where ";
        $sql .= " carpnosequ = " . $ata;
        $sql .= " and corglicodi = " . $codigoOrgao;
        $sql .= " and citarpsequ = " . $sequencial;

        $resultado = $db->query($sql);
        return $resultado;
    }


    private function inserirItemParticipante($db, $ata, $codigoItem, $codigoOrgao, $itemOrgao)
    {        
        $sequencial  = $codigoItem;
        $quatidadeDoParticipante = $itemOrgao['apiarpqtat'] == null ? 0  :moeda2float($itemOrgao['apiarpqtat'], 4);
        $quatidadeUtilizadaDoParticipante = $itemOrgao['apiarpqtut'] == null ? 0  :moeda2float($itemOrgao['apiarpqtut'], 4);
        $situacao = 'A';        
        $codigoUsuario      = $_SESSION['_cusupocodi_'];
        $tpiarpulat = date('Y-m-d H:i:s');
      
        $sql  = "INSERT INTO ";
        $sql .= "sfpc.tbparticipanteitematarp ";
        $sql .= "(";
        $sql .= "carpnosequ, ";
        $sql .= "corglicodi, ";
        $sql .= "citarpsequ, ";
        $sql .= "apiarpqtat, ";
        $sql .= "fpiarpsitu, ";
        $sql .= "cusupocodi, ";
        $sql .= "tpiarpulat, ";
        $sql .= "apiarpqtut) ";        

        $sql .= "VALUES ";
        $sql .= "(";
        $sql .= "$ata, ";
        $sql .= "$codigoOrgao, ";
        $sql .= "$sequencial, ";
        $sql .= "$quatidadeDoParticipante, ";
        $sql .= "'" . $situacao . "',";
        $sql .= "$codigoUsuario, ";
        $sql .= "'" . $tpiarpulat . "',";
        $sql .= "$quatidadeUtilizadaDoParticipante ";
       
        $sql .= ")";

        $resultado = $db->query($sql);

        return $resultado;
    }



    private function redirecionarParaInicio()
    {
        header('Location: CadAtaRegistroPrecoInternaManterEspecial.php');
        exit();
    }

    private function getCodigoUsuarioLogado()
    {
        return (integer) $this->variables['session']['_cusupocodi_'];
    }

    private function salvarParticipante($db, $ata, $item)
    {
        foreach ($item->participantes as $participante) {
            $participanteNoBanco = $this->consultarParticipanteAta($db, $ata, $participante);
            $resultado = null;
            $codigoUsuario = $this->getCodigoUsuarioLogado();

            if ($participanteNoBanco == null) {
                $resultado = $this->inserirParticipante($db, $ata, $participante, $codigoUsuario);
            } else {
                $resultado = $this->atualizarParticipante($db, $ata, $participante, $codigoUsuario);
            }

            if (PEAR::isError($resultado)) {
                throw new RuntimeException($resultado->getMessage());
            }

            $this->salvarItemParticipante($db, $ata, $item, $participante);
        }
    }

    private function salvarItemParticipante($db, $ata, $item, $participante)
    {
        $this->validarQuantidades($db, $ata, $item, $participante);

        $itemDoParticipanteNoBanco = $this->consultarItemDoParticipante($db, $ata, $participante->sequencial, $item->sequencial);
        $resultado = null;

        if ($itemDoParticipanteNoBanco == null) {
            $resultado = $this->inserirItemDoParticipante($db, $ata, $participante, $item);
        } else {
            $resultado = $this->atualizarItemDoParticipante($db, $ata, $participante, $item);
        }

        if (PEAR::isError($resultado)) {
            throw new RuntimeException($resultado->getMessage());
        }
    }

    private function inserirItemDoParticipante($db, $ata, $participante, $item)
    {
        $codigoUsuario = $this->getCodigoUsuarioLogado();
        $quantidadeItem = moeda2float($participante->quantidadeItem);

        $sql = "INSERT INTO
    				sfpc.tbparticipanteitematarp
					(carpnosequ, corglicodi, citarpsequ, apiarpqtat, fpiarpsitu, cusupocodi, tpiarpulat)
				VALUES
    				($ata,
    				$participante->sequencial,
    				$item->sequencial,
    				$quantidadeItem,
    				'$participante->situacaoParaItem',
    				$codigoUsuario,
    				now())";

        $resultado = $db->query($sql);
        return $resultado;
    }

    private function consultarParticipanteAta($db, $ata, $participante)
    {
        $sql = "SELECT
    				carpnosequ, corglicodi, fpatrpexcl, cusupocodi, tpatrpulat
				FROM
    				sfpc.tbparticipanteatarp
    			WHERE
    				carpnosequ = $ata
    				AND corglicodi = $participante->sequencial";

        $resultado = executarSQL($db, $sql);
        $resultado->fetchInto($participanteDaAta, DB_FETCHMODE_OBJECT);

        return $participanteDaAta;
    }

    private function atualizarItemDoParticipante($db, $ata, $participante, $item)
    {
        $codigoUsuario = $this->getCodigoUsuarioLogado();
        $quantidadeItem = moeda2float($participante->quantidadeItem);

        $sql = "UPDATE
    				sfpc.tbparticipanteitematarp
				SET
    				apiarpqtat=$quantidadeItem,
    				fpiarpsitu='$participante->situacaoParaItem',
    				cusupocodi=$codigoUsuario,
    				tpiarpulat=now()
				WHERE
    				carpnosequ=$ata
    				AND corglicodi=$participante->sequencial
    				AND citarpsequ=$item->sequencial";

        $resultado = $db->query($sql);
        return $resultado;
    }

    private function consultarItemDoParticipante($db, $ata, $sequencialParticipante, $sequencialItem)
    {
        $sql = "SELECT
    				carpnosequ, corglicodi, citarpsequ, apiarpqtat, fpiarpsitu, cusupocodi, tpiarpulat
				FROM
    				sfpc.tbparticipanteitematarp
    			WHERE
    				carpnosequ = $ata
    				AND corglicodi = $sequencialParticipante
    				AND citarpsequ = $sequencialItem";

        $resultado = executarSQL($db, $sql);
        $resultado->fetchInto($itemDoParticipante, DB_FETCHMODE_OBJECT);

        return $itemDoParticipante;
    }

    private function inserirParticipante($db, $ata, $participante, $codigoUsuario)
    {
        $sequencialOrgao = $participante->sequencial;
        $excluido = $participante->inativo;

        $sql = "INSERT INTO
    				sfpc.tbparticipanteatarp
					(carpnosequ, corglicodi, fpatrpexcl, cusupocodi, tpatrpulat)
				VALUES
    				($ata, $sequencialOrgao, '$excluido', $codigoUsuario, now())";

        $resultado = $db->query($sql);
    }

    private function atualizarParticipante($db, $ata, $participante, $codigoUsuario)
    {
        $sequencialOrgao = $participante->sequencial;
        $excluido = $participante->inativo;

        $sql = "UPDATE
    				sfpc.tbparticipanteatarp
				SET
					fpatrpexcl='$excluido', cusupocodi=$codigoUsuario, tpatrpulat=now()
				WHERE
    				carpnosequ=$ata AND corglicodi=$sequencialOrgao";

        $resultado = $db->query($sql);
    }

    private function salvarItemAta($db, $ata, $item)
    {

        $codigoReduzido = $item['cservpsequ'];
        if(isset($item['cmatepsequ']) === true) {
            $codigoReduzido = $item['cmatepsequ'];
        }

        $itemNoBanco = $this->consultarItemAta($db, $ata, $codigoReduzido, $item['fgrumstipo']);

        //$itemNoBanco = $this->consultarItemAta($db, $ata, $item->codigoReduzido, $item->tipo);
        $resultado = null;

        if ($itemNoBanco == null) {            
            $resultado = $this->inserirItem($db, $ata, $item);
        } else {
            $resultado = $this->atualizarItem($db, $ata, $item);
        }

        

        if (PEAR::isError($resultado)) {
            throw new RuntimeException($resultado->getMessage());
        }
    }

    private function validarItemAta($item){ 
       
        $retorno = true;
        $erros = false;
        $valores = '';

        if( (moeda2float($item['valor_qtd_original']) <= 0 || $item['valor_qtd_original'] == null)){
            $erros = true;
            $valores .= "<a href=\"javascript:document.getElementById('itemAta[" . $item['valorOrdemItem'] . "][valor_qtd_original]').focus();\" class='titulo2'>QUANTIDADE ORIGINAL; </a> ";
        }

        if( (moeda2float($item['valor_original_unit']) <= 0 || $item['valor_original_unit'] == null)){
            $erros = true;
            $valores .= ' ';
            $valores .= "<a href=\"javascript:document.getElementById('itemAta[" . $item['valorOrdemItem'] . "][valor_original_unit]').focus();\" class='titulo2'>VALOR ORIGINAL UNIT.; </a> ";
        }

        /*if( (moeda2float($item['quantidade_total']) <= 0 || $item['quantidade_total'] == null)){
            $erros = true;
            $valores .= ' QTD. TOTAL DA ATA; ';
        }*/

        /*if( (moeda2float($item['valor_unitario_atual']) <= 0 || $item['valor_unitario_atual'] == null)){
            $erros = true;
            $valores .= ' VALOR UNITÁRIO ATUAL; ';
        }*/

        if($erros){
            $_SESSION['mensagemFeedback'] = 'Todos os campos do item devem ser preenchidos: ('. $valores .')';
            $this->getTemplate()->MENSAGEM_ERRO = ExibeMensStr($_SESSION['mensagemFeedback'], 1, 0);
            $this->getTemplate()->block('BLOCO_ERRO', true);
            unset($_SESSION['mensagemFeedback']);
            $retorno = false;
        }

        return $retorno;
    }


    private function validarAtaAlterarNumeracao($ano, $codProcesso, $codOrgao, $ata, $postForm, $flagExclusao){ 
       
        $retorno                = true;
        $erros                  = false;
        $valores                = '';
        $mensagemPassada        = "";
        $ata                    = $this->consultarAtaPorChave($ano, $codProcesso, $codOrgao, $ata);
        $numeroAtaConsultada    = str_pad($ata->carpincodn, 4, "0", STR_PAD_LEFT);
        $anoAtaConsultada       = $ata->aarpinanon;        

        if($postForm['VALOR_ATA'] == ""){
            $erros = true;
            $mensagemPassada .= "<a href=\"javascript:document.getElementById('valorAta').focus();\" class='titulo2'>O campo de número da ata é obrigatório; </a> ";
        }

        if($postForm['ANO_ATA'] == ""){
            $erros = true;
            $mensagemPassada .= "<a href=\"javascript:document.getElementById('anoAta').focus();\" class='titulo2'>O campo de ano da ata é obrigatório; </a> ";
        }

        if(!$erros){
            if(( ($anoAtaConsultada != $postForm['ANO_ATA']) || ($numeroAtaConsultada != $postForm['VALOR_ATA']) ) || $flagExclusao ){
                $contemCaronaExternaOuScc = $this->consultarExisteSccOuCaronaExterna($ata->carpnosequ);
                if($contemCaronaExternaOuScc){
                    $erros = true;
                    if($flagExclusao){
                        $mensagemPassada = 'Não é possível excluir a ata, pois esta ata já está relacionada com uma Solicitação de Compra do tipo SARP ou Carona Externa';
                    }else{
                        $mensagemPassada = 'Não é possível alterar a numeração, pois esta ata já está relacionada com uma Solicitação de Compra do tipo SARP ou Carona Externa';
                    }
                }            
            }

            if(!$flagExclusao){
                if($this->consultarExisteAtaInternaAnoNumeracaoOrgao($ata->carpnosequ, $ata->corglicodi, $postForm['ANO_ATA'], $postForm['VALOR_ATA'])){
                    $erros = true;
                    $mensagemPassada = 'Não é possível alterar a numeração, pois esta nova numeração já foi cadastrada para este órgão';
                }
            }
        }
       
        if($erros){
            $_SESSION['mensagemFeedback'] = $mensagemPassada;
            $this->getTemplate()->MENSAGEM_ERRO = ExibeMensStr($_SESSION['mensagemFeedback'], 1, 0);
            $this->getTemplate()->block('BLOCO_ERRO', true);
            unset($_SESSION['mensagemFeedback']);
            $retorno = false;
        }

        return $retorno;
    }


    public function validarItemAtaOrgao($itensOrgao)
    {
       
        $_SESSION['post_itens_armazenar_tela'] = $itensOrgao;

        foreach ($itensOrgao as $codigoItem => $item) { 
            $somatorioDaVez = 0;
            foreach ($item as $codigoOrgao => $itemOrgao) {
                if(is_array($itemOrgao)){
                    $itemOrgao['apiarpqtut'] = ($itemOrgao['apiarpqtut'] == null) ? 0 : $itemOrgao['apiarpqtut'];
                    $itemOrgao['apiarpqtat'] = ($itemOrgao['apiarpqtat'] == null) ? 0 : $itemOrgao['apiarpqtat'];                    
                    /*if($itemOrgao['apiarpqtut'] == null || $itemOrgao['apiarpqtat'] == null){
                        $_SESSION['mensagemFeedbackTipo']   = 1;
                        $_SESSION['mensagemFeedback'] = 'A Quantidade Utilizada ou Quantidade Total do Item de Ordº ' . $codigoItem . ' não pode ser nulo';
                        return false;
                    }*/

                    if(moeda2float($itemOrgao['apiarpqtut']) > moeda2float($itemOrgao['apiarpqtat'])){
                        $_SESSION['mensagemFeedbackTipo']   = 1;
                        $_SESSION['mensagemFeedback'] = 'A Quantidade Utilizada do Item de Lote '. $itemOrgao['lote']. ' e Ordº ' . $itemOrgao['ordem'] . ' não pode ser superior a Quantidade Total do Item para o Participante ' . $itemOrgao['orgao'];
                        return false;
                    }
                    $somatorioDaVez += moeda2float($itemOrgao['apiarpqtat']);
                    $somatorioDaVez = number_format((float)$somatorioDaVez, 2, '.', '');
                }
            } 
            
            $item['qtd_total_item'] = ($_REQUEST['itemAta'][$codigoItem]['quantidade_total'] != 0) ? $_REQUEST['itemAta'][$codigoItem]['quantidade_total'] : $_REQUEST['itemAta'][$codigoItem]['valor_qtd_original'];
            $qtd_total_item_tmp = number_format((float)$item['qtd_total_item'], 2, '.', '');
            //verificando se o saldo do item é menor que a soma de todas as quantidades para o item
            // if(moeda2float($item['qtd_total_item']) < $somatorioDaVez){ $qtd_total_item_tmp
            if($qtd_total_item_tmp < $somatorioDaVez){ 
                $_SESSION['mensagemFeedbackTipo']   = 1;
                $_SESSION['mensagemFeedback'] = 'A soma das Quantidades Totais do Item do Lote '. $_REQUEST['itemAta'][$codigoItem]['citelpnuml'] .' e Ordº ' . $_REQUEST['itemAta'][$codigoItem]['aitelporde'] . ' de todos os Participantes não pode ser superior que a Quantidade Total da Ata';
                return false;
            }
          
            
        }

        return true;
    }

    private function inserirItem($db, $ata, $item)
    {
        $sequencial         = ($item['citelpsequ'] == null || $item['citelpsequ'] == 0) ? $this->obterProximoNumeroItem() : $item['citelpsequ'];
        $ordem              = $item['aitelporde'] == null ? 'null' : $item['aitelporde'];
        $sequencialMaterial = 'null';
        $sequencialServico  = 'null';

        $quantidadeOriginal = $item['aitelpqtso'] == null ? 'null'  :moeda2float($item['aitelpqtso'], 4);
        $valorUnitarioOriginal = $item['vitelpvlog'] == null ? 'null'  :moeda2float($item['vitelpvlog'], 4);

        if(isset($item['valor_qtd_original'])){
            if($item['valor_qtd_original'] != ''){
                $quantidadeOriginal = moeda2float($item['valor_qtd_original'], 4);
            }
        }

        if(isset($item['valor_original_unit'])){
            if($item['valor_original_unit'] != ''){
                $valorUnitarioOriginal = moeda2float($item['valor_original_unit'], 4);
            }
        }

        $quantidadeAtual        = $item['quantidade_total'] == null ? 0  : moeda2float($item['quantidade_total'], 4);        
        $valorUnitarioAtual     = $item['valor_unitario_atual'] == null ? 0  :moeda2float($item['valor_unitario_atual'], 4); 
        $lote                   = $item['citelpnuml'] == null ? 0 : $item['citelpnuml'];        
        $situacaoItem           = $item['situacao'];
        $incluidoDiretamente    = $item['fitarpincl'];
        $excluidoDiretamente    = $item['fitarpexcl'];      
        $marca                  = $item['eitelpmarc'] == null ? 'null' : $item['eitelpmarc'];
        $modelo                 = $item['eitelpmode'] == null ? 'null' : $item['eitelpmode'];        
        $codigoUsuario          = $_SESSION['_cusupocodi_'];        

        if ($item['fgrumstipo'] == 'CADUM') {
            $sequencialMaterial = $item['cmatepsequ'] == null ? 'null' : $item['cmatepsequ'];
        } else {
            $sequencialServico = $item['cservpsequ'] == null ? 'null' : $item['cservpsequ'];
        }

        if(strlen($item['fgrumstipo']) > 1){
            $colunaSequencialItem = ($item['fgrumstipo'] == 'CADUM') ? 'eitarpdescmat' : 'eitarpdescse';
        }else{
            $colunaSequencialItem = ($item['fgrumstipo'] == 'M') ? 'eitarpdescmat' : 'eitarpdescse';    
        }

        $sql  = "INSERT INTO ";
        $sql .= "sfpc.tbitemataregistropreconova ";
        $sql .= "(";
        $sql .= "carpnosequ, ";
        $sql .= "citarpsequ, ";
        $sql .= "aitarporde, ";
        $sql .= "cmatepsequ, ";
        $sql .= "cservpsequ, ";
        $sql .= "aitarpqtor, ";
        $sql .= "aitarpqtat, ";
        $sql .= "vitarpvori, ";
        $sql .= "vitarpvatu, ";
        $sql .= "citarpnuml, ";
        $sql .= "fitarpsitu, ";
        $sql .= "fitarpincl, ";
        $sql .= "fitarpexcl, ";
        $sql .= "titarpincl, ";
        $sql .= "cusupocodi, ";
        $sql .= "titarpulat, ";
        $sql .= "eitarpmarc, ";

        //FIXME: logica para modificar quando for processo licitatorio
        if(isset($item['valor_descricao_detalhada'])){
            $sql .= $colunaSequencialItem . ", ";
        }

        $sql .= "eitarpmode ";
        $sql .= ")";
        $sql .= "VALUES ";
        $sql .= "(";
        $sql .= "$ata, ";
        $sql .= "$sequencial, ";
        $sql .= "$ordem, ";
        $sql .= "$sequencialMaterial, ";
        $sql .= "$sequencialServico, ";
        $sql .= "$quantidadeOriginal, ";
        $sql .= "$quantidadeAtual, ";
        $sql .= "$valorUnitarioOriginal, ";
        $sql .= "$valorUnitarioAtual, ";
        $sql .= "$lote, ";
        $sql .= "'$situacaoItem', ";
        $sql .= "'$incluidoDiretamente', ";
        $sql .= "'$excluidoDiretamente', ";
        $sql .= "now(), ";
        $sql .= "$codigoUsuario, ";
        $sql .= "now(), ";
        $sql .= "'$marca', ";

        if(isset($item['valor_descricao_detalhada'])){
            $sql .= " '" . strtoupper($item['valor_descricao_detalhada']) ."', "; 
        }

        $sql .= "'$modelo' ";
        $sql .= ")";

        $resultado = $db->query($sql);
        return $resultado;
    }

    private function atualizarItem($db, $ata, $item)
    {        
        $valorUnitarioOriginal = null;
        $quantidadeOriginal = null;
        if(isset($item['valor_qtd_original']) && isset($item['valor_original_unit'])){            
            $valorUnitarioOriginal  = (float) moeda2float($item['valor_original_unit'],4);
            $quantidadeOriginal     = (float) moeda2float($item['valor_qtd_original'],4);

        }

        $aitarpqtat         = $item['quantidade_total'] == null ? 0 : (float) moeda2float($item['quantidade_total'], 4);
        $vitarpvatu         = $item['valor_unitario_atual'] == null ? 0 : (float) moeda2float($item['valor_unitario_atual'], 4);
        $situacao           = $item['situacao'];        
        $codigoUsuario      = $_SESSION['_cusupocodi_'];
        $marca              = $item['eitelpmarc'] == null ? 'null' : $item['eitelpmarc'];
        $modelo             = $item['eitelpmode'] == null ? 'null' : $item['eitelpmode'];                
        $citelpsequ         = ($item['citelpsequ'] == null || $item['citelpsequ'] == 0) ? $item['citarpsequ'] : $item['citelpsequ'];

        if(strlen($item['fgrumstipo']) > 1){
            $colunaSequencialItem = ($item['fgrumstipo'] == 'CADUM') ? 'eitarpdescmat' : 'eitarpdescse';
        }else{
            $colunaSequencialItem = ($item['fgrumstipo'] == 'M') ? 'eitarpdescmat' : 'eitarpdescse';    
        }
       
        $sql = "UPDATE
                    sfpc.tbitemataregistropreconova
                SET
                    aitarpqtat=$aitarpqtat, fitarpsitu='$situacao',
                    vitarpvatu=$vitarpvatu, cusupocodi=$codigoUsuario, ";
        $sql .= " eitarpmarc = '" . $marca . "', ";
        $sql .= " eitarpmode ='" . $modelo . "', ";

        if($valorUnitarioOriginal != null && $quantidadeOriginal != null){
            $sql .= " vitarpvori =" . $valorUnitarioOriginal . ", ";
            $sql .= " aitarpqtor =" . $quantidadeOriginal . ", ";
        }
        
        //FIXME: logica para modificar quando for processo licitatorio
        if(isset($item['valor_descricao_detalhada'])){
            $sql .= $colunaSequencialItem . " = '" . strtoupper($item['valor_descricao_detalhada']) ."', "; 
        }
        
        $sql .= "   titarpulat=now()
        WHERE
        carpnosequ = $ata
        
        AND citarpsequ=$citelpsequ";
        
        $resultado = $db->query($sql);
        return $resultado;
    }

    private function consultarItemAta($db, $ata, $codigoReduzido, $tipo)
    {
        $colunaSequencialItem = "";
        if(strlen($tipo) > 1){
            $colunaSequencialItem = ($tipo == 'CADUM') ? 'cmatepsequ' : 'cservpsequ';
        }else{
            $colunaSequencialItem = ($tipo == 'M') ? 'cmatepsequ' : 'cservpsequ';    
        }

        $sql = "SELECT
        iarpn.carpnosequ, iarpn.$colunaSequencialItem, aitarpqtor, aitarpqtat, fitarpsitu, vitarpvatu, citarpsequ
        FROM
        sfpc.tbitemataregistropreconova iarpn
        WHERE
        iarpn.carpnosequ = $ata
        AND iarpn.$colunaSequencialItem = ".$codigoReduzido;
       
        $resultado = executarSQL($db, $sql);
        $resultado->fetchInto($item, DB_FETCHMODE_OBJECT);

        return $item;
    }


    /**
     *
     * @param integer $ata
     * @param integer $codigoItem
     * @param integer $codigoOrgao
     */
    public function consultarItemAtaParticipante($db, $ata, $codigoItem, $codigoOrgao)
    {     
        $item = null;
        
        if(!is_null($codigoItem)) {
            $sql  = "SELECT ipia.carpnosequ, ipia.corglicodi, ipia.citarpsequ, ipia.apiarpqtat,  ";
            $sql .= "       ipia.apiarpqtut, ipia.fpiarpsitu, ipia.cusupocodi, ipia.tpiarpulat  ";
            $sql .= "   FROM sfpc.tbparticipanteitematarp ipia  ";
            $sql .= "   WHERE ipia.carpnosequ = " . $ata;
            $sql .= "     AND ipia.citarpsequ = " . $codigoItem;
            $sql .= "     AND ipia.corglicodi = " . $codigoOrgao;
            
            $resultado = executarSQL($db, $sql);
            $resultado->fetchInto($item, DB_FETCHMODE_OBJECT);
        }

        return $item;
    }

    /**
     *
     * @param integer $ata
     * @param integer $codigoItem
     * @param integer $codigoOrgao
     */
    public function consultarParticipanteAtaOrgao($db, $ata, $codigoOrgao)
    {
        
        $sql  = "select * ";
        $sql .= " from sfpc.tbparticipanteatarp pa ";
        $sql .= " inner join sfpc.tborgaolicitante o on ";
        $sql .= " o.corglicodi = pa.corglicodi  ";
        $sql .= "  where pa.carpnosequ = " . $ata;  

        $sql .= "  and pa.corglicodi = " . $codigoOrgao;     

        $resultado = executarSQL($db, $sql);
        $resultado->fetchInto($item, DB_FETCHMODE_OBJECT);

        return $item;
    }

    /**
     *
     * @param unknown $valores
     */
    public function sqlAddParticipanteOrgaoAta($valores)
    {
        $sql = "INSERT INTO sfpc.tbparticipanteatarp";
        $sql .= "(carpnosequ, corglicodi, cusupocodi, tpatrpulat,fpatrpsitu)";        
        $sql .= " VALUES($valores->carpnosequ, $valores->corglicodi, $valores->cusupocodi, '$valores->tpatrpulat', '$valores->fpatrpsitu')";
        
        return $sql;
    }

    /**
     *
     * @param unknown $valores
     */
    public function sqlUpdateParticipanteOrgaoAta($valores, $situacao)
    {
        $sql = "UPDATE sfpc.tbparticipanteatarp SET";
        $sql .= " cusupocodi = " . $valores->cusupocodi;
        $sql .= " , tpatrpulat = '" . $valores->tpatrpulat . "'";
        $sql .= " , fpatrpsitu = '" . $situacao . "'";
        $sql .= " where ";
        $sql .= " carpnosequ = ". $valores->carpnosequ;
        $sql .= " and corglicodi = ". $valores->corglicodi;
        
        return $sql;
    }


    public function obterProximoNumeroItem()
    {
        $sql = $this->sqlConsultarMaiorItem();
        $resultado = executarSQL(ClaDatabasePostgresql::getConexao(), $sql);
        $resultado->fetchInto($valorMaximo, DB_FETCHMODE_OBJECT);

        $valorAtual = intval($valorMaximo->max) + 1;

        return $valorAtual;
    }

    /**
     *
     * @return string
     */
    public function sqlConsultarMaiorItem()
    {
        $sql = "select max(i.citarpsequ) from sfpc.tbitemataregistropreconova i";
        return $sql;
    }

    private function validarQuantidades($db, $ata, $item, $participante)
    {
        $colunaTipoItem = null;

        if ($item->tipo == 'CADUM') {
            $colunaTipoItem = 'cmatepsequ';
        } else {
            $colunaTipoItem = 'cservpsequ';
        }

        $sql = "SELECT
				    isc.$colunaTipoItem AS codigo,
				    isc.aitescqtso AS quantidade,
				    (
				    	CASE
							WHEN isc.cmatepsequ IS NOT NULL THEN 'material'
				        	WHEN isc.cservpsequ IS NOT NULL THEN 'servico'
				     	END
				    ) AS tipo
				FROM
				    sfpc.tbsolicitacaocompra sc
				    INNER JOIN sfpc.tbitemsolicitacaocompra isc
				    	ON isc.csolcosequ = sc.csolcosequ
				    	AND isc.$colunaTipoItem = $item->codigoReduzido
				WHERE
				    sc.carpnosequ = $ata
				    AND sc.fsolcorpcp IS NOT NULL
					AND sc.csitsocodi != 10
					AND sc.corglicodi = $participante->sequencial";

        $resultado = executarSQL($db, $sql);
        $resultado->fetchInto($itemConsumido, DB_FETCHMODE_OBJECT);

        if ($itemConsumido != null) {
            if (moeda2float($participante->quantidadeItem) < $itemConsumido->quantidade) {
                $msg = 'Quantidade a ser diminuída para o item "%s" é menor que o saldo da quantidade do órgão "%s"';
                $msgFormatada = sprintf($msg, $item->descricao, $participante->descricao);

                throw new RuntimeException($msgFormatada);
            }
        }

        return $itemConsumido;
    }

    private function retirarItem()
    {
        $seqItemAta = $this->variables['post']['idItem'];
        $seqAta     = $this->variables['get']['ata'];
        
        if (!isset($_POST['idItem'])){
            $_SESSION['mensagemFeedback'] = 'Item não informado';
            return;
        }
        
        $radio = explode("|", $seqItemAta);
        if (isset($_POST['idItem']) && count($radio) == 2) {
            $pos = $radio[1];
            unset($_SESSION['item'][$pos]);                    
            return;
        } else {
            $seqItemAta = $radio[0];
        }        

        $database = Conexao();
        $database->autoCommit(false);
        $database->query("BEGIN TRANSACTION");

        try {                
            $database->query(sprintf("DELETE FROM sfpc.tbparticipanteitematarp WHERE carpnosequ = %d  AND citarpsequ = %d", $_REQUEST['ata'], $seqItemAta));            
            $database->query(sprintf("DELETE FROM sfpc.tbitemcaronainternaatarp WHERE carpnosequ = %d AND  citarpsequ = %d", $_REQUEST['ata'], $seqItemAta));
            $database->query(sprintf("DELETE FROM sfpc.tbitemataregistropreconova WHERE carpnosequ = %d AND  citarpsequ = %d", $_REQUEST['ata'], $seqItemAta));
            $database->query("COMMIT");
            $database->query("END TRANSACTION");
            $_SESSION['mensagemFeedback'] = 'Item retirado com sucesso';
        } catch (Exception $e) {
            $semerror = false;
            $database->query("ROLLBACK");
            $_SESSION['mensagemFeedback'] = 'Erro ao retirar o item';
            ExibeErroBD(self::$erroPrograma . "\nLinha: ".__LINE__."\nSql: " . $e->getMessage());
        }

        return;
    }

    private function inativarParticipante()
    {
        $semerror = true;
        $columnOrgao = $this->variables['post']['columnOrgao'];
        $seqAta = $this->variables['get']['ata'];

        if (empty($columnOrgao)) {
            $_SESSION['mensagemFeedback'] = 'Selecione um Órgão Participante';
            return;
        }

        foreach ($columnOrgao as $keyCodigo => $orgao) {

            $podeInativarParticipante = $this->podeInativarParticipante($seqAta, $keyCodigo);
            if (!$podeInativarParticipante) {
                $semerror = false;
                break;
            }

            if(!$this->inativarItemOrgao($keyCodigo, $seqAta)){
                $semerror = false;
                break;
            }

            if(!$this->inativarParticipanteOrgao($keyCodigo, $seqAta)){
                $semerror = false;
                break;
            }

        }

        return;
    }

    /**
     * Negócio. Ativar Participante
     *
     * @return void
     */
    public function ativarParticipante()
    {
        
        $semerror = true;
        $columnOrgao = $this->variables['post']['columnOrgao'];
        $seqAta = $this->variables['get']['ata'];

        if (empty($columnOrgao)) {
            $_SESSION['mensagemFeedback'] = 'Selecione um Órgão Participante';
            return;
        }

        foreach ($columnOrgao as $keyCodigo => $orgao) {

            if(!$this->ativarItemOrgao($keyCodigo, $seqAta)){
                $semerror = false;
                break;
            }

            if(!$this->ativarParticipanteOrgao($keyCodigo, $seqAta)){
                $semerror = false;
                break;
            }

        }
                      
        return $semerror;
    }

    /**
     * Negócio. Retirar Participante
     *
     * @return void
     */
    public function retirarParticipante()
    {
        
        $semerror = true;
        $columnOrgao = $this->variables['post']['columnOrgao'];
        $seqAta = $this->variables['get']['ata'];
        if (empty($columnOrgao)) {
            $_SESSION['mensagemFeedback'] = 'Selecione um Órgão Participante';
            return;
        }
        
        foreach ($columnOrgao as $keyCodigo => $orgao) {

            if(!$this->removerItemOrgao($keyCodigo, $seqAta)){
                $semerror = false;
                break;
            }

            if(!$this->removerParticipanteOrgao($keyCodigo, $seqAta)){
                $semerror = false;
                break;
            }

            unset($_SESSION['orgaos'][$keyCodigo]);

        }
        
        return $semerror;
    }

    /**
     *
     * @param unknown $codigoOrgao $cogidoAta
     */
    public function sqlInativarItemOrgaoParticipante($codigoOrgao, $cogidoAta)
    {
        $sql = "    UPDATE sfpc.tbparticipanteitematarp SET fpiarpsitu = 'I'  ";
        $sql .= "   WHERE  carpnosequ =  $cogidoAta  ";        
        $sql .= "   AND    corglicodi = $codigoOrgao  ";
        
        return $sql;
    }

    /**
     *
     * @param unknown $codigoOrgao $cogidoAta
     */
    public function sqlInativarOrgaoParticipante($codigoOrgao, $cogidoAta)
    {
        $sql = "    UPDATE sfpc.tbparticipanteatarp SET fpatrpsitu = 'I'  ";
        $sql .= "   WHERE  carpnosequ =  $cogidoAta  ";        
        $sql .= "   AND    corglicodi = $codigoOrgao  ";
        
        return $sql;
    }

    /**
     *
     * @param unknown $codigoOrgao $cogidoAta
     */
    public function sqlAtivarItemOrgaoParticipante($codigoOrgao, $cogidoAta)
    {
        $sql = "    UPDATE sfpc.tbparticipanteitematarp SET fpiarpsitu = 'A'  ";
        $sql .= "   WHERE  carpnosequ =  $cogidoAta  ";        
        $sql .= "   AND    corglicodi = $codigoOrgao  ";
        
        return $sql;
    }

    /**
     *
     * @param unknown $codigoOrgao $cogidoAta
     */
    public function sqlRemoverItemOrgaoParticipante($codigoOrgao, $cogidoAta)
    {
        $sql = "    DELETE FROM sfpc.tbparticipanteitematarp  ";
        $sql .= "   WHERE  carpnosequ =  $cogidoAta  ";        
        $sql .= "   AND    corglicodi = $codigoOrgao  ";
                
        return $sql;
    }

    /**
     *
     * @param unknown $codigoOrgao $cogidoAta
     */
    public function sqlRemoverOrgaoParticipante($codigoOrgao, $cogidoAta)
    {
        $sql = "    DELETE FROM sfpc.tbparticipanteatarp  ";
        $sql .= "   WHERE  carpnosequ =  $cogidoAta  ";        
        $sql .= "   AND    corglicodi = $codigoOrgao  ";
        
        return $sql;
    }

     /**
     *
     * @param unknown $codigoOrgao $cogidoAta
     */
    public function sqlAtivarOrgaoParticipante($codigoOrgao, $cogidoAta)
    {
        $sql = "    UPDATE sfpc.tbparticipanteatarp SET fpatrpsitu = 'A'  ";
        $sql .= "   WHERE  carpnosequ =  $cogidoAta  ";        
        $sql .= "   AND    corglicodi = $codigoOrgao  ";
        
        return $sql;
    }


    private function inativarItemOrgao($keyCodigo, $ata){

        $db = Conexao();
        $db->autoCommit(false);
        $db->query("BEGIN TRANSACTION");

        $sqlRemoverOrgaoItemParticipante = $this->sqlInativarItemOrgaoParticipante($keyCodigo, $ata);
        $resultadoAtaNova       = executarTransacao($db, $sqlRemoverOrgaoItemParticipante);
        $commited = $db->commit();                

        if ($commited instanceof DB_error) {
            $db->rollback();
            $_SESSION['mensagemFeedback'] = 'Erro ao salvar dados';
            ExibeErroBD(self::$erroPrograma . "\nLinha: " . __LINE__ . "\nSql: " . $e->getMessage());
            return false;
        }

        return true;
    }

    private function ativarItemOrgao($keyCodigo, $ata){

        $db = Conexao();
        $db->autoCommit(false);
        $db->query("BEGIN TRANSACTION");

        $sqlRemoverOrgaoItemParticipante = $this->sqlAtivarItemOrgaoParticipante($keyCodigo, $ata);
        $resultadoAtaNova       = executarTransacao($db, $sqlRemoverOrgaoItemParticipante);
        $commited = $db->commit();                

        if ($commited instanceof DB_error) {
            $db->rollback();
            $_SESSION['mensagemFeedback'] = 'Erro ao salvar dados';
            ExibeErroBD(self::$erroPrograma . "\nLinha: " . __LINE__ . "\nSql: " . $e->getMessage());
            return false;
        }

        return true;
    }

    private function removerItemOrgao($keyCodigo, $ata){

        $db = Conexao();
        $db->autoCommit(false);
        $db->query("BEGIN TRANSACTION");

        $sqlRemoverOrgaoItemParticipante = $this->sqlRemoverItemOrgaoParticipante($keyCodigo, $ata);
        $resultadoAtaNova       = executarTransacao($db, $sqlRemoverOrgaoItemParticipante);
        $commited = $db->commit();                

        if ($commited instanceof DB_error) {
            $db->rollback();
            $_SESSION['mensagemFeedback'] = 'Erro ao salvar dados';
            ExibeErroBD(self::$erroPrograma . "\nLinha: " . __LINE__ . "\nSql: " . $e->getMessage());
            return false;
        }

        return true;
    }

    private function removerParticipanteOrgao($keyCodigo, $ata){

        $db = Conexao();
        $db->autoCommit(false);
        $db->query("BEGIN TRANSACTION");

        $sqlRemoverOrgaoParticipante = $this->sqlRemoverOrgaoParticipante($keyCodigo, $ata);
        $resultadoAtaNova       = executarTransacao($db, $sqlRemoverOrgaoParticipante);
        $commited = $db->commit();                

        if ($commited instanceof DB_error) {
            $db->rollback();
            $_SESSION['mensagemFeedback'] = 'Erro ao salvar dados';
            ExibeErroBD(self::$erroPrograma . "\nLinha: " . __LINE__ . "\nSql: " . $e->getMessage());
            return false;
        }

        return true;
    }


    private function inativarParticipanteOrgao($keyCodigo, $ata){

        $db = Conexao();
        $db->autoCommit(false);
        $db->query("BEGIN TRANSACTION");

        $sqlRemoverOrgaoItemParticipante = $this->sqlInativarOrgaoParticipante($keyCodigo, $ata);
        $resultadoAtaNova       = executarTransacao($db, $sqlRemoverOrgaoItemParticipante);
        $commited = $db->commit();                

        if ($commited instanceof DB_error) {
            $db->rollback();
            $_SESSION['mensagemFeedback'] = 'Erro ao salvar dados';
            ExibeErroBD(self::$erroPrograma . "\nLinha: " . __LINE__ . "\nSql: " . $e->getMessage());
            return false;
        }

        return true;
    }

    private function ativarParticipanteOrgao($keyCodigo, $ata){

        $db = Conexao();
        $db->autoCommit(false);
        $db->query("BEGIN TRANSACTION");

        $sqlRemoverOrgaoItemParticipante = $this->sqlAtivarOrgaoParticipante($keyCodigo, $ata);
        $resultadoAtaNova       = executarTransacao($db, $sqlRemoverOrgaoItemParticipante);
        $commited = $db->commit();                

        if ($commited instanceof DB_error) {
            $db->rollback();
            $_SESSION['mensagemFeedback'] = 'Erro ao salvar dados';
            ExibeErroBD(self::$erroPrograma . "\nLinha: " . __LINE__ . "\nSql: " . $e->getMessage());
            return false;
        }

        return true;
    }

    private function podeInativarParticipante($seqAta, $seqParticipante)
    {
        $podeInativarParticipante = true;
        $seqOrgaoParticipante = null;

        if (empty($seqParticipante)) {
            $_SESSION['mensagemFeedback'] = 'Participante não selecionado';
            $podeInativarParticipante = false;
        } else {
            $seqOrgaoParticipante = $seqParticipante;
        }

        if (!empty($seqOrgaoParticipante) && $this->participanteComSarpGerada($seqAta, $seqOrgaoParticipante)) {
            $_SESSION['mensagemFeedback'] = 'Não é possível inativar o participante. Participante com solicitação de compra do tipo SARP gerada';
            $podeInativarParticipante = false;
        }

        return $podeInativarParticipante;
    }

    private function redirecionarParaPaginaDeManutencao()
    {
        $ano = $this->variables['get']['ano'];
        $processo = $this->variables['get']['processo'];
        $orgao = $this->variables['get']['orgao'];
        $ata = $this->variables['get']['ata'];

        $uri = "CadAtaRegistroPrecoInternaManterEspecialAtasAlterar.php";
        $parametros = "?ano=$ano&processo=$processo&orgao=$orgao&ata=$ata";

        header('Location: ' . $uri . $parametros);
        exit();
    }

    private function participanteComSarpGerada($sequencialAta, $sequencialParticipante)
    {
        $sql = "SELECT
				    COUNT(sc.corglicodi)
				FROM
				    sfpc.tbataregistroprecointerna arpi
				    INNER JOIN sfpc.tblicitacaoportal lp
				        ON lp.clicpoproc = arpi.clicpoproc
					    AND lp.alicpoanop = arpi.alicpoanop
					    AND lp.cgrempcodi = arpi.cgrempcodi
					    AND lp.ccomlicodi = arpi.ccomlicodi
					INNER JOIN sfpc.tbsolicitacaolicitacaoportal slp
				        ON slp.clicpoproc = arpi.clicpoproc
					    AND slp.alicpoanop = arpi.alicpoanop
					    AND slp.cgrempcodi = arpi.cgrempcodi
					    AND slp.ccomlicodi = arpi.ccomlicodi
					INNER JOIN sfpc.tbsolicitacaocompra sc
				        ON sc.csolcosequ = slp.csolcosequ
				    	AND sc.fsolcorpcp IS NOT NULL
					    AND sc.csitsocodi != 10
					    AND sc.corglicodi = $sequencialParticipante
					    AND sc.carpnosequ = $sequencialAta
				WHERE
				    arpi.carpnosequ = $sequencialAta";

        $db = Conexao();
        $resultado = executarSQL($db, $sql);
        $sarpGerada = (boolean) resultValorUnico($resultado);

        return $sarpGerada;
    }

    private function proccessPrincipal()
    {
        //unset($_SESSION['orgaos']);
        $orgao          = $this->variables['get']['orgao'];
        $ano            = $this->variables['get']['ano'];
        $processo       = $this->variables['get']['processo'];
        $ata            = $this->variables['get']['ata'];
        $quebraProcesso = explode("-", $processo);
        $codProcesso    = $quebraProcesso[0];
        $codOrgao       = $quebraProcesso[4];
        $orgao          = $codOrgao;        
        
        $this->plotarBlocoBotao($ano, $orgao, $processo, $ata);        
        
        $atas       = $this->consultarAtaPorChave($ano, $codProcesso, $codOrgao, $ata);
        $licitacao  = $this->consultarLicitacaoAtaInterna($ano, $processo, $orgao);
        
        $this->plotarBlocoLicitacao($licitacao, $atas, null, null);
        
        if(isset($atas->carpnosequ)){ 
            $itensAta = $this->consultarItensAtaParticipante($atas->carpnosequ);

            if($itensAta == null || $itensAta == '' || empty($itensAta)){     
                $itensAta = $this->consultarItensAtaN($atas->aarpinanon,  $atas->carpnosequ);
            }
            
            $ataParticipante = $this->consultarAtaParticipanteChave($atas);
            
            foreach ($ataParticipante as $orgao) {                                                                            
                $_SESSION['orgaos'][$orgao->corglicodi] = $orgao->eorglidesc;                                        
            }                
        }
        
        if(isset($_REQUEST['itemAta'])){
            $_SESSION['post_itens_armazenar_tela_normais'] = $_REQUEST['itemAta'];
        }
        
        if(isset($_POST['itemOrgao'])){
            $_SESSION['post_itens_armazenar_tela'] = $_POST['itemOrgao'];
        }

        $this->plotarBlocoItemAta($itensAta, $atas);                                       

        $quebraProcesso = explode("-", $processo);
        $codProcesso    = $quebraProcesso[0];
        $codOrgao       = $quebraProcesso[4];
       
        $this->getTemplate()->ANO = $ano;
        $this->getTemplate()->PROCESSO = $processo;
        $this->getTemplate()->ORGAO = $orgao;
        $this->getTemplate()->ATA = $ata;

        if (isset($_SESSION['mensagemFeedback'])) {
            $this->getTemplate()->MENSAGEM_ERRO = ExibeMensStr($_SESSION['mensagemFeedback'], 2, 0);
            $this->getTemplate()->block('BLOCO_ERRO', true);
            unset($_SESSION['mensagemFeedback']);
        }

        
    }

    private function consultarMaiorSequencialItemAta($ata)
    {
        $sql = "SELECT
				    MAX(citarpsequ)
				FROM
				    sfpc.tbitemataregistropreconova
				WHERE
				    carpnosequ = $ata";

        $db = Conexao();
        $resultado = executarSQL($db, $sql);
        $maiorSequencial = resultValorUnico($resultado);

        return $maiorSequencial;
    }

    private function adicionarNovoItem()
    {

        if (!empty($_SESSION['item'])) {
            $sequencialAta = $this->variables['get']['ata'];
            sort($_SESSION['item']);
            $pos = count($_SESSION['itens']);

            for ($i = 0; $i < count($_SESSION['item']); $i ++) {
                $DadosSessao = explode($this->variables['separatorArray'], $_SESSION['item'][$i]);
                $ItemCodigo = $DadosSessao[1];
                $ItemTipo   = $DadosSessao[3];

                $itemJaExiste = false;
                $qtdeServicos = count($_SESSION['itens']);

                for ($i2 = 0; $i2 < $qtdeServicos; $i2++) {
                    if ($ItemCodigo == $_SESSION['itens'][$i2]->codigoReduzido) {
                        $itemJaExiste = true;
                    }
                }

                if (!empty($DadosSessao[2])) {
                    if (!$itemJaExiste) {
                        $sql = "select
	    							m.ematepdesc, u.eunidmsigl
	                            from
	    							sfpc.TBmaterialportal m, SFPC.TBunidadedemedida u
	                            where
	    							m.cmatepsequ = ".$ItemCodigo."
									and u.cunidmcodi = m.cunidmcodi";

                        $database = Conexao();
                        $res = $database->query($sql);

                        if (PEAR::isError($res)) {
                            EmailErroSQL("Erro em SQL", __FILE__, __LINE__, "Erro em SQL", $sql, $res);
                        }

                        $Linha = $res->fetchRow();
                        $MaterialDescricao = $Linha[0];
                        $MaterialUnidade = $Linha[1];

                        $materiais = new stdClass;
                        $materiais->ordem = $pos + 1;
                        $materiais->sequencial = $this->consultarMaiorSequencialItemAta($sequencialAta) + 1;
                        $materiais->descricao = $MaterialDescricao;
                        $materiais->tipo = 'CADUM';
                        $materiais->codigoReduzido = $ItemCodigo;
                        $materiais->lote = 1;
                        $materiais->siglaUnidade =  $MaterialUnidade;
                        $materiais->quantidadeTotal = converte_valor_estoques(0);
                        $materiais->participantes = $this->getOrgaosPorAta($sequencialAta);
                        $materiais->situacao = 'A';
                        $materiais->valorUnitario   = $DadosSessao[4];
                        $materiais->marca           = $DadosSessao[5];
                        $materiais->modelo          = $DadosSessao[6];

                        $_SESSION['itens'][] = $materiais;
                    }
                } else {
                    if (!$itemJaExiste) {
                        $sql = " select m.eservpdesc
    							from SFPC.TBservicoportal m
    							where m.cservpsequ = ".$ItemCodigo."";

                        $database = Conexao();
                        $res = $database->query($sql);

                        if (PEAR::isError($res)) {
                            EmailErroSQL("Erro em SQL", __FILE__, __LINE__, "Erro em SQL", $sql, $res);
                        }

                        $Linha = $res->fetchRow();
                        $Descricao = $Linha[0];

                        $servicos = new stdClass;
                        $servicos->ordem = $pos + 1;
                        $servicos->sequencial = $this->consultarMaiorSequencialItemAta($sequencialAta) + 1;
                        $servicos->descricao = $Descricao;
                        $servicos->tipo = 'CADUS';
                        $servicos->codigoReduzido = $ItemCodigo;
                        $servicos->lote = 1;
                        $servicos->siglaUnidade =  'UN';
                        $servicos->quantidadeTotal = converte_valor_estoques(0);
                        $servicos->participantes = $this->getOrgaosPorAta($sequencialAta);
                        $servicos->situacao = 'A';
                        $materiais->valorUnitario   = $DadosSessao[4];

                        $_SESSION['itens'][] = $servicos;
                    }
                }
            }

            unset($_SESSION['item']);
        }
    }

    private function consultarItensAta($numeroAta, $anoAta)
    {
        $resultados = array();
        $db = Conexao();
        $sql = $this->sqlItemAtaNova($numeroAta, $anoAta);
        $resultado = executarSQL($db, $sql);

        while ($resultado->fetchInto($item, DB_FETCHMODE_OBJECT)) {
            $resultados[] =$item;
        }

        return $resultados;
    }

    private function plotarBlocoBotao($ano, $orgao, $processo, $ata)
    {
        $this->getTemplate()->VALOR_ANO_SESSAO = $ano;
        $this->getTemplate()->VALOR_ORGAO_SESSAO = $orgao;
        $this->getTemplate()->VALOR_PROCESSO_SESSAO = $processo;
        $this->getTemplate()->VALOR_ATA_SESSAO = $ata;
        $this->getTemplate()->block("BLOCO_BOTAO");
    }

    private function getNumeroAtaInterna($ata)
    {
        $numeroAta = getNumeroSolicitacaoCompra(ClaDatabasePostgresql::getConexao(), $ata->csolcosequ);
        $valoresExploded = explode(".", $numeroAta);
        $valorUnidadeOrc = substr($valoresExploded[0], 2, 2);

        $valorAta = str_pad($ata->corglicodi, 2, '0', STR_PAD_LEFT);
        $valorAta .= $valorUnidadeOrc . '.';
        $valorAta .= str_pad($ata->carpnosequ, 4, '0', STR_PAD_LEFT) . '/';
        $valorAta .= $ata->alicpoanop;

        return $valorAta;
    }

    /**
     *
     * @param stdClass $licitacao
     * @param stdClass $ata
     * @param unknown $dataInformada
     * @param unknown $vigenciaInformada
     */
    private function plotarBlocoLicitacao($licitacao, $ata, $dataInformada, $vigenciaInformada)
    {
        $dataHota = new DataHora($ata->tarpindini);       
        
       
        $dto = $this->consultarDCentroDeCustoUsuario($ata->cgrempcodi, $ata->cusupocodi, $ata->corglicodi);
        $objeto = current($dto);
        
        $numeroAtaFormatado = "";
        $numeroAtaFormatado .= $objeto->ccenpocorg . str_pad($objeto->ccenpounid, 2, '0', STR_PAD_LEFT);

        $this->getTemplate()->VALOR_ORGAO_UNIDADE   = $numeroAtaFormatado;
        
                
        $this->getTemplate()->VALOR_ATA             = str_pad($ata->carpincodn, 4, "0", STR_PAD_LEFT);
        $this->getTemplate()->ANO_ATA               = $ata->aarpinanon;         
        $this->getTemplate()->PROCESSO_LICITATORIO  =  substr($licitacao->clicpoproc + 10000, 1);
        $this->getTemplate()->VALOR_ANO             = $licitacao->alicpoanop;        
        $this->getTemplate()->FORNECEDOR_ORIGINAL   = $this->getDadosFornecedorOriginal($ata);
        $this->getTemplate()->VALOR_COMISSAO        = $licitacao->ecomlidesc;
        // $this->getTemplate()->FORNECEDOR_ATUAL      = $this->getDadosFornecedorAtual($ata);        
        $this->getTemplate()->VALOR_DATA            = $dataHota->formata('d/m/Y');
        $this->getTemplate()->VALOR_VIGENCIA        = $ata->aarpinpzvg;

        // $this->plotarBlocoDocumentos($ata);
        $this->getTemplate()->block("BLOCO_LICITACAO");
    }



    private function getDadosFornecedorOriginal($ata)
    {
        $numeroInscricaoFornecedorOriginal = (!empty($ata->aforcrccgc)) ? $ata->aforcrccgc : $ata->aforcrccpf;
        $dadosFornecedorOriginal = Helper_RegistroPreco::montarDadosDoFornecedorDaAta(
            $numeroInscricaoFornecedorOriginal,
            $ata->nforcrrazs,
            $ata->eforcrlogr,
            $ata->aforcrnume,
            $ata->eforcrbair,
            $ata->nforcrcida,
            $ata->cforcresta
        );

        return $dadosFornecedorOriginal;
    }

    private function getDadosFornecedorAtual($ata)
    {
        $dadosFornecedorAtual = null;

        if (isset($ata->razaofornecedoratual) && !empty($ata->razaofornecedoratual)) {
            $numeroInscricaoFornecedorAtual = (!empty($ata->cgcfornecedoratual)) ? $ata->cgcfornecedoratual : $ata->cpffornecedoratual;

            $dadosFornecedorAtual = Helper_RegistroPreco::montarDadosDoFornecedorDaAta(
                $numeroInscricaoFornecedorAtual,
                $ata->razaofornecedoratual,
                $ata->logradourofornecedoratual,
                $ata->numeroenderecofornecedoratual,
                $ata->bairrofornecedoratual,
                $ata->cidadefornecedoratual,
                $ata->estadofornecedoratual
            );
        } else {
            $dadosFornecedorAtual = $this->getDadosFornecedorOriginal($ata);
        }

        return $dadosFornecedorAtual;
    }

    
    private function plotarBlocoItemAta($itens, $ata)
    {
       
        global $SimboloConcatenacaoArray, $SimboloConcatenacaoDesc;
        $itensDaSessao = $_SESSION['item'];                
                
        $quantidadeItemquantidadeItem = $_REQUEST['quantidadeItem'];
        if ($itens == null && isset($itensDaSessao) === false) {
            return;
        }

        $_itens[] = $itens;

        $ordenacaoMaior = 0;
        foreach ($_itens[0] as $item) {
            if($ordenacaoMaior <= $item->aitarporde){
                $ordenacaoMaior = $item->aitarporde;
            }
        }

        if($ordenacaoMaior != 0){
            $ordenacaoMaior += 1;
            if(count($_itens[0]) + 1 == $ordenacaoMaior){
                $ordenacaoMaior += 1;
            }
        }
       
        

        if (isset($itensDaSessao) === true) {
            foreach ($itensDaSessao as $key => $value) {
               
                $dadosSessaoItemQuebra = explode($SimboloConcatenacaoArray, $value);

                $idItemParaConsultar    = $dadosSessaoItemQuebra[1];
                $tipoDaVez              = $dadosSessaoItemQuebra[3];
                $tipoGene               = $dadosSessaoItemQuebra[4];
                $dataBase = Conexao();
                $itemConsultadoCompleto = $this->consultarItemCompleto($dataBase, $idItemParaConsultar, $tipoDaVez);

                $novoArray = new StdClass;

                $novoArray->aitarporde      = count($_itens[0]) + 2;
                $novoArray->cmatepsequ      = $itemConsultadoCompleto->cmatepsequ;
                $novoArray->cservpsequ      = $itemConsultadoCompleto->cservpsequ;

                $novoArray->ematepdesc      = $itemConsultadoCompleto->ematepdesc;
                $novoArray->eservpdesc      = $itemConsultadoCompleto->eservpdesc;
                $novoArray->eunidmsigl      = $itemConsultadoCompleto->eunidmsigl;

                $novoArray->eitelpdescmat   = '';
                $novoArray->eitelpdescse    = '';
                
                $novoArray->fmatepgene      = $tipoGene;
                $novoArray->posItem         = $key;
                $novoArray->aitelpqtso      = '';
                $novoArray->vitelpvlog      = '';
                $novoArray->vitelpunit      = '';
                
                $novoArray->citelpnuml      = 0;
                $novoArray->cmatepsitu      = '';
                $novoArray->cservpsitu      = '';
                
                array_push($_itens[0], $novoArray);                 

            }
        }

        $this->getTemplate()->TR_LAYOUT = '';
        //Colunas Orgaos
        if (!empty($_SESSION['orgaos'])) {                   
            foreach ($_SESSION['orgaos'] as $key => $orgao) {
                if($key != ''){
                    $this->getTemplate()->ID_ORGAO_COLUMN  = $key;     
                    $this->getTemplate()->NOME_ORGAO  = $orgao;
                                       

                    $statusOrgao = $this->consultarAtaParticipanteAtaOrgao($ata->carpnosequ, $key);

                    $valor = 'ATIVO';
                    if($statusOrgao != null){
                        if($statusOrgao[0]->fpatrpsitu != 'A'){
                            $valor = 'INATIVO';
                        }
                    }

                    $this->getTemplate()->STATUS  = $valor;                                                  
                    $this->getTemplate()->block("BLOCO_ORGAO_ITEM_COLUNA"); 
                    $this->getTemplate()->block("BLOCO_ORGAO_ITEM_COLUNA_2"); 
                }
            }
        }else{            
            $this->getTemplate()->TR_LAYOUT  = '<tr></tr>';
        }
        
        foreach ($_itens[0] as $keyArray => $item) {
            
                // CADUM = material e CADUS = serviço
                $tipo = 'material';
                if (is_null($item->cmatepsequ) == true) {
                    $tipo = 'servico';
                }

                // Código do item
                $valorCodigo = $item->cmatepsequ;
                if ($tipo == 'servico') {
                    $valorCodigo = $item->cservpsequ;
                }

                // Descrição do item
                $valorDescricao = $item->ematepdesc;
                if ($tipo === 'servico') {
                    $valorDescricao = $item->eservpdesc;
                }
               
                $valorDescricaoDetalhada = isset($_SESSION['post_itens_armazenar_tela_normais']) ? $_SESSION['post_itens_armazenar_tela_normais'][$keyArray]['valor_descricao_detalhada'] : $item->eitarpdescmat;
                $textarea = ' -<textarea style="display:none" name="itemAta['.$keyArray.'][valor_descricao_detalhada]"></textarea>';; 
                if ($tipo === 'servico') {
                    $valorDescricaoDetalhada = isset($_SESSION['post_itens_armazenar_tela_normais']) ? $_SESSION['post_itens_armazenar_tela_normais'][$keyArray]['valor_descricao_detalhada'] : $item->eitarpdescse;
                    $textarea = '<textarea style="text-transform: uppercase;" name="itemAta['.$keyArray.'][valor_descricao_detalhada]" cols="30"
											rows="4" class="textonormal">'.$valorDescricaoDetalhada.'</textarea>';
                } else if($tipo === 'material' && $item->fmatepgene == 'S') {
                    $textarea = '<textarea style="text-transform: uppercase;" name="itemAta['.$keyArray.'][valor_descricao_detalhada]" cols="30"
											rows="4" class="textonormal">'.$valorDescricaoDetalhada.'</textarea>';
                }

                // Situação do item
                $situacao = $item->cmatepsitu;
                if ($tipo === 'servico') {
                    $situacao = $item->cservpsitu;
                }

                // Valor total
                $valorTotal = ($item->aitelpqtso * $item->vitelpvlog);

                // Marca
                $valor_marca = isset($_SESSION['post_itens_armazenar_tela_normais']) ? $_SESSION['post_itens_armazenar_tela_normais'][$keyArray]['eitelpmarc'] : $item->eitelpmarc;
                if($item->eitelpmarc == null && !isset($_SESSION['post_itens_armazenar_tela_normais'])) {
                    $valor_marca = $item->eitarpmarc;
                }
                $input_marca = '<textarea style="text-transform: uppercase;" id="itemAta['.$keyArray.'][eitelpmarc]" name="itemAta['.$keyArray.'][eitelpmarc]" cols="10" rows="4" class="textonormal">'.strtoupper($valor_marca).'</textarea>';
                
                // Modelo
                $valor_modelo = isset($_SESSION['post_itens_armazenar_tela_normais']) ? $_SESSION['post_itens_armazenar_tela_normais'][$keyArray]['eitelpmode'] : $item->eitarpmode;
                if($item->eitarpmode == null && !isset($_SESSION['post_itens_armazenar_tela_normais'])) {
                    $valor_modelo = $item->eitelpmode;
                }
                $input_modelo = '<textarea style="text-transform: uppercase;" id="itemAta['.$keyArray.'][eitelpmode]" name="itemAta['.$keyArray.'][eitelpmode]" cols="10" rows="4" class="textonormal">'.strtoupper($valor_modelo).'</textarea>';

                $tipoFinal = ($tipo == 'material') ? 'CADUM' : 'CADUS';
                $situacao                                   = $item->fitarpsitu;
                $ordenacao = $item->aitarporde;               


                $this->getTemplate()->VALOR_SEQITEM         = !empty($item->citarpsequ) ? $item->citarpsequ : $keyArray; 
                $this->getTemplate()->VALOR_MARCA           = $input_marca;
                $this->getTemplate()->VALOR_MODELO          = $input_modelo;
                $this->getTemplate()->VALOR_ORD             = $ordenacao;   
                $this->getTemplate()->VALOR_ORD_ITEM        = $keyArray;
                $this->getTemplate()->VALOR_TIPO            = $tipoFinal;         // Código Sequencial do Material OU                 
                $this->getTemplate()->VALOR_CADUS           = $valorCodigo;         // Código Sequencial do Material OU Código sequencial do serviço
                $this->getTemplate()->VALOR_DESCRICAO       = $valorDescricao;      // Descrição do material ou serviço
                $this->getTemplate()->VALOR_DESCRICAO_DETALHADA = $textarea;
                $this->getTemplate()->VALOR_UND             = $item->eunidmsigl;
                $this->getTemplate()->VALOR_LOTE            = $item->citarpnuml;            

                
                $this->getTemplate()->QTD_PARTICIPANTE_ITEM = '';
                $this->getTemplate()->QTD_PARTICIPANTE_ITEM_UTILIZADA = '';
                $this->getTemplate()->QTD_SALDO_BLOCO = '';

                
                if(isset($_SESSION['post_itens_armazenar_tela_normais'])){                    
                    $item->vitarpvatu   = moeda2float($_SESSION['post_itens_armazenar_tela_normais'][$keyArray]['valor_unitario_atual']);                
                    $item->aitarpqtat   = moeda2float($_SESSION['post_itens_armazenar_tela_normais'][$keyArray]['quantidade_total']);
                    $item->aitarpqtor   = moeda2float($_SESSION['post_itens_armazenar_tela_normais'][$keyArray]['valor_qtd_original']);
                    $item->vitarpvori   = moeda2float($_SESSION['post_itens_armazenar_tela_normais'][$keyArray]['valor_original_unit']);
                    $situacao           = moeda2float($_SESSION['post_itens_armazenar_tela_normais'][$keyArray]['situacao']);
                }

                $saldoQuantidadeTotal                       = ($item->aitarpqtat != 0) ? $item->aitarpqtat : $item->aitarpqtor;
                $calculoQuantidateUtilizada                 = 0;
                $calculoSaldoAta                            = 0;

                //Pegar campo Novo contendo a descricao                    
                $this->getTemplate()->VALOR_QTD_ORIGINAL    = "<input value='".converte_valor_estoques($item->aitarpqtor)."' id='itemAta[".$keyArray."][valor_qtd_original]' name='itemAta[".$keyArray."][valor_qtd_original]' class='dinheiro4casas' size='5' onblur="."javascript:AtualizarValorTotal('itemAta[".$keyArray."][valor_qtd_original]','itemAta[".$keyArray."][valor_original_unit]','totalValorItem[".$keyArray."]');"."  >";
                $this->getTemplate()->VALOR_ORIGINAL_UNIT    = "<input value='".converte_valor_estoques($item->vitarpvori)."' id='itemAta[".$keyArray."][valor_original_unit]' name='itemAta[".$keyArray."][valor_original_unit]' class='dinheiro4casas' size='5' onblur="."javascript:AtualizarValorTotal('itemAta[".$keyArray."][valor_qtd_original]','itemAta[".$keyArray."][valor_original_unit]','totalValorItem[".$keyArray."]');"."  >";
                $valorTotal = ($item->aitarpqtor * $item->vitarpvori);
                $this->getTemplate()->VALOR_TOTAL           = converte_valor_estoques($valorTotal);                            
                $this->getTemplate()->VALOR_UNITARIO_ATUAL = converte_valor_estoques($item->vitarpvatu);
                $this->getTemplate()->QTDATUAL_X_VLUNITARIOATUAL = converte_valor_estoques($item->aitarpqtat * $item->vitarpvatu);

                if ($situacao === 'A') {
                    $this->getTemplate()->VALOR_SITUACAO_ATIVO      = 'selected';
                    $this->getTemplate()->VALOR_SITUACAO_INATIVO    = '';
                } else {
                    $this->getTemplate()->VALOR_SITUACAO_INATIVO    = 'selected';
                    $this->getTemplate()->VALOR_SITUACAO_ATIVO      = '';
                }

                $this->getTemplate()->VALOR_SEQITEM = isset($item->citarpsequ) ? $item->citarpsequ : '0|'.$item->posItem;
                $this->getTemplate()->VALOR_ID_ITEM = $valorCodigo;
               

                if (!empty($_SESSION['orgaos'])) {                    
                    foreach ($_SESSION['orgaos'] as $key => $orgao) {

                        if($key != ''){
                            //if ($item->tipoItem == "ITEMPARTICIPANTE") {
                                if(isset($_SESSION['post_itens_armazenar_tela'])){
                                    $this->getTemplate()->QTD_PARTICIPANTE_ITEM = ($_SESSION['post_itens_armazenar_tela'][$keyArray][$key]['apiarpqtat'] != null) ? $_SESSION['post_itens_armazenar_tela'][$keyArray][$key]['apiarpqtat'] : '';
                                    $this->getTemplate()->QTD_PARTICIPANTE_ITEM_UTILIZADA = ($_SESSION['post_itens_armazenar_tela'][$keyArray][$key]['apiarpqtut'] != null) ? $_SESSION['post_itens_armazenar_tela'][$keyArray][$key]['apiarpqtut'] : '';
                                    $this->getTemplate()->QTD_SALDO_BLOCO = ($_SESSION['post_itens_armazenar_tela'][$keyArray][$key]['apiarpqtat'] != null && $_SESSION['post_itens_armazenar_tela'][$keyArray][$key]['apiarpqtut'] != null) ? converte_valor_estoques($_SESSION['post_itens_armazenar_tela'][$keyArray][$key]['apiarpqtat'] - $_SESSION['post_itens_armazenar_tela'][$keyArray][$key]['apiarpqtut']) : '';
                                }else{
                                    $this->getTemplate()->QTD_PARTICIPANTE_ITEM = '';
                                    $this->getTemplate()->QTD_PARTICIPANTE_ITEM_UTILIZADA = '';
                                    $this->getTemplate()->QTD_SALDO_BLOCO = '';
                                }
                                if(isset($item->tipoItemValores)){
                                    foreach ($item->tipoItemValores as $keyConstrucao => $value) {
                                        foreach ($value as $keyOrgaoInteno => $valueOrgao) { 
                                    

                                            $chaveVerificacao = null;

                                            if(is_int($keyOrgaoInteno)){
                                                $chaveVerificacao = $keyOrgaoInteno;
                                            }else{
                                                $chaveVerificacao = $keyConstrucao;
                                            }

                                                                                
                                            if($chaveVerificacao == $key){ 

                                                if(is_int($keyOrgaoInteno)){
                                                    $calculoQuantidateUtilizada   += $valueOrgao['apiarpqtat'];
                                                    if(isset($_SESSION['post_itens_armazenar_tela'])){
                                                        $this->getTemplate()->QTD_PARTICIPANTE_ITEM = ($_SESSION['post_itens_armazenar_tela'][$keyArray][$key]['apiarpqtat'] != null) ? $_SESSION['post_itens_armazenar_tela'][$keyArray][$key]['apiarpqtat'] : '';
                                                        $this->getTemplate()->QTD_PARTICIPANTE_ITEM_UTILIZADA = ($_SESSION['post_itens_armazenar_tela'][$keyArray][$key]['apiarpqtut'] != null) ? $_SESSION['post_itens_armazenar_tela'][$keyArray][$key]['apiarpqtut'] : '';
                                                        $this->getTemplate()->QTD_SALDO_BLOCO = ($_SESSION['post_itens_armazenar_tela'][$keyArray][$key]['apiarpqtat'] != null && $_SESSION['post_itens_armazenar_tela'][$keyArray][$key]['apiarpqtut'] != null) ? converte_valor_estoques($_SESSION['post_itens_armazenar_tela'][$keyArray][$key]['apiarpqtat'] - $_SESSION['post_itens_armazenar_tela'][$keyArray][$key]['apiarpqtut']) : '';
                                                    }else{
                                                        $this->getTemplate()->QTD_PARTICIPANTE_ITEM = converte_valor_estoques($valueOrgao['apiarpqtat']);
                                                        $this->getTemplate()->QTD_PARTICIPANTE_ITEM_UTILIZADA = converte_valor_estoques($valueOrgao['apiarpqtut']);
                                                        $this->getTemplate()->QTD_SALDO_BLOCO = converte_valor_estoques($valueOrgao['apiarpqtat'] - $valueOrgao['apiarpqtut']);
                                                    }
                                                }else{
                                                    $calculoQuantidateUtilizada   += $value['apiarpqtat'];
                                                    if(isset($_SESSION['post_itens_armazenar_tela'])){
                                                        $this->getTemplate()->QTD_PARTICIPANTE_ITEM = ($_SESSION['post_itens_armazenar_tela'][$keyArray][$key]['apiarpqtat'] != null) ? $_SESSION['post_itens_armazenar_tela'][$keyArray][$key]['apiarpqtat'] : '';
                                                        $this->getTemplate()->QTD_PARTICIPANTE_ITEM_UTILIZADA = ($_SESSION['post_itens_armazenar_tela'][$keyArray][$key]['apiarpqtut'] != null) ? $_SESSION['post_itens_armazenar_tela'][$keyArray][$key]['apiarpqtut'] : '';
                                                        $this->getTemplate()->QTD_SALDO_BLOCO = ($_SESSION['post_itens_armazenar_tela'][$keyArray][$key]['apiarpqtat'] != null && $_SESSION['post_itens_armazenar_tela'][$keyArray][$key]['apiarpqtut'] != null) ? converte_valor_estoques($_SESSION['post_itens_armazenar_tela'][$keyArray][$key]['apiarpqtat'] - $_SESSION['post_itens_armazenar_tela'][$keyArray][$key]['apiarpqtut']) : '';
                                                    }else{
                                                        $this->getTemplate()->QTD_PARTICIPANTE_ITEM = converte_valor_estoques($value['apiarpqtat']);
                                                        $this->getTemplate()->QTD_PARTICIPANTE_ITEM_UTILIZADA = converte_valor_estoques($value['apiarpqtut']);
                                                        $this->getTemplate()->QTD_SALDO_BLOCO = converte_valor_estoques($value['apiarpqtat'] - $value['apiarpqtut']);
                                                    }
                                                }                                            
                                            }else{                                            
                                                continue;                                            
                                            }
                                        }                                
                                    } 
                                }
                                               
                            //}
                            $this->getTemplate()->ID_ORGAO  = $key;                                                                        
                            $this->getTemplate()->block("BLOCO_ORGAO_ITEM"); 
                        }                                                 
                    }
                }  

                $calculoSaldoAta = $saldoQuantidadeTotal - $calculoQuantidateUtilizada;
                $this->getTemplate()->VALOR_QTD_TOTAL       = converte_valor_estoques($item->aitarpqtat);
                $this->getTemplate()->VALOR_QTD_UTILIZADA    = converte_valor_estoques($calculoQuantidateUtilizada);                
                $this->getTemplate()->SALDO                 = converte_valor_estoques($calculoSaldoAta);                
                $this->getTemplate()->block("BLOCO_ITEM");
                $this->getTemplate()->block("BLOCO_RESULTADO_ATAS");
                $this->getTemplate()->block("BLOCO_ITEM_TOTAL");                                    
        }
    }



    private function getItensDaAta($sequencialIntencao, $anoIntencao)
    {
        $tipoRequisicao = $_SERVER['REQUEST_METHOD'];

        if ($tipoRequisicao == "GET") {
            $this->adicionarItensNaSecao($sequencialIntencao, $anoIntencao);
        }

        if ($tipoRequisicao == "POST") {
            $this->atualizarItensDaSecao();
        }

        return $_SESSION['itens'];
    }

    private function adicionarItensNaSecao($sequencialIntencao, $anoIntencao)
    {
        $itens = $this->consultarItensAta($sequencialIntencao, $anoIntencao);
        if ($itens == null) {
            return;
        }

        $itensSessao = array();

        foreach ($itens as $item) {
            $itemSessao = new stdClass();
            $itemSessao->ordem = $item->aitarporde;
            $itemSessao->sequencial = $item->citarpsequ;
            $itemSessao->descricao = ($item->cmatepsequ == null) ? $item->eservpdesc : $item->ematepdesc;
            $itemSessao->tipo = ($item->cmatepsequ == null) ? 'CADUS' : 'CADUM';
            $itemSessao->codigoReduzido = ($item->cmatepsequ == null) ? $item->cservpsequ : $item->cmatepsequ;
            $itemSessao->lote = $item->citarpnuml;
            $itemSessao->siglaUnidade = ($item->cmatepsequ == null) ? 'UN' : $item->eunidmsigl;
            $itemSessao->quantidadeTotal = converte_valor_estoques($item->aitarpqtor);
            $itemSessao->participantes = $this->getOrgaosPorItem($sequencialIntencao, $item->citarpsequ);
            $itemSessao->situacao = $item->fitarpsitu;
            $itemSessao->valorUnitario = converte_valor_estoques($item->vitarpvori);

            $itensSessao[] = $itemSessao;
        }

        $_SESSION['itens'] = $itensSessao;
    }

    private function atualizarItensDaSecao()
    {
        $itensOrgao = $this->variables['post']['itemOrgao'];
        $itemGeralAta = $this->variables['post']['quantidadeItem'];
        $valorUnitarioItem = $this->variables['post']['valorUnitarioItem'];

        foreach ($_SESSION['itens'] as $itemAta) {
            if ($itemGeralAta == $itemAta->codigoReduzido) {
                $itemAta->quantidadeTotal = $itemGeralAta[$itemAta->codigoReduzido];
                $itemAta->valorUnitario = $valorUnitarioItem[$itemAta->codigoReduzido];
            }

            foreach ($itemAta->participantes as $orgao) {
                foreach ($itensOrgao as $ordemItem => $itemOrgao) {
                    foreach ($itemOrgao as $seqOrgao => $qdtItemOrgao) {
                        if ($seqOrgao == $orgao->sequencial && $ordemItem == $itemAta->ordem) {
                            $orgao->quantidadeItem = $qdtItemOrgao;
                        }
                    }
                }
            }
        }

        $this->adicionarNovoItem();
    }

    private function getOrgaosPorItem($sequencialIntencao, $sequencialItem)
    {
        $database = Conexao();
        $sql = $this->sqlSelectOrgaosPorItem($sequencialIntencao, $sequencialItem);
        $resultado = executarSQL($database, $sql);
        $orgao = null;

        $orgaos = array();

        while ($resultado->fetchInto($orgao, DB_FETCHMODE_OBJECT)) {
            if ($orgao->fpiarpsitu == 'A') {
                $orgaoSessao = new stdClass();
                $orgaoSessao->sequencial = $orgao->corglicodi;
                $orgaoSessao->descricao = $orgao->eorglidesc;
                $orgaoSessao->quantidadeItem = converte_valor_estoques($orgao->apiarpqtat);
                $orgaoSessao->inativo = $orgao->fpatrpexcl;
                $orgaoSessao->situacaoParaItem = $orgao->fpiarpsitu;

                $orgaos[] = $orgaoSessao;
            }
        }

        return $orgaos;
    }


    public function consultarDCentroDeCustoUsuario($cgrempcodi, $cusupocodi, $corglicodi)
    {   
        $db = Conexao();
        $sql = $this->sqlConsultarCentroDeCustoUsuario($cgrempcodi, $cusupocodi, $corglicodi);
        
        $res = executarSQL($db, $sql);
        
        $itens = array();
        $item = null;
        while ($res->fetchInto($item, DB_FETCHMODE_OBJECT)) {
            $itens[] = $item;
        }        
        $db->disconnect();
        return $itens;
    }

    private function sqlConsultarCentroDeCustoUsuario($cgrempcodi, $cusupocodi, $corglicodi)
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

    private function getOrgaosPorAta($sequencialAta)
    {
        $database = Conexao();
        $sql = "SELECT
    				ol.corglicodi,
				    ol.eorglidesc,
				    piarp.apiarpqtat    				
				FROM
				    sfpc.tbparticipanteitematarp piarp
				    INNER JOIN sfpc.tborgaolicitante ol
				    	ON piarp.corglicodi = ol.corglicodi
    				INNER JOIN sfpc.tbparticipanteatarp parp
    					ON parp.corglicodi = piarp.corglicodi
				    INNER JOIN sfpc.tbataregistroprecointerna arpi
				    	ON piarp.carpnosequ = arpi.carpnosequ
				WHERE
				    piarp.carpnosequ = $sequencialAta";

        $resultado = executarSQL($database, $sql);
        $orgao = null;

        $orgaos = array();

        while ($resultado->fetchInto($orgao, DB_FETCHMODE_OBJECT)) {
            if ($orgao->fpatrpexcl != 'S') {
                $orgaoSessao = new stdClass();
                $orgaoSessao->sequencial = $orgao->corglicodi;
                $orgaoSessao->descricao = $orgao->eorglidesc;
                $orgaoSessao->quantidadeItem = converte_valor_estoques(0);
                $orgaoSessao->inativo = $orgao->fpatrpexcl;
                $orgaoSessao->situacaoParaItem = 'A';

                $orgaos[] = $orgaoSessao;
            }
        }

        return $orgaos;
    }

    private function sqlSelectOrgaosPorItem($sequencialAta, $sequencialItemAta)
    {
        $sql = "SELECT
    				ol.corglicodi,
				    ol.eorglidesc,
				    piarp.apiarpqtat,    				
    				piarp.fpiarpsitu
				FROM
				    sfpc.tbparticipanteitematarp piarp
				    INNER JOIN sfpc.tborgaolicitante ol
				    	ON piarp.corglicodi = ol.corglicodi
    				INNER JOIN sfpc.tbparticipanteatarp parp
    					ON parp.corglicodi = piarp.corglicodi    					
				    INNER JOIN sfpc.tbataregistroprecointerna arpi
				    	ON piarp.carpnosequ = arpi.carpnosequ
				WHERE
				    piarp.carpnosequ = %d
				    AND piarp.citarpsequ = %d
    				AND piarp.fpiarpsitu = 'A'";


        return sprintf($sql, $sequencialAta, $sequencialItemAta);
    }

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

  
    private function consultarAtaPorChave($processo, $orgao, $ano, $numeroAta)
    {
        $db = Conexao();
        $sql = $this->sqlAtaPorchave($processo, $orgao, $ano, $numeroAta);
        $resultado = executarSQL($db, $sql);
        $resultado->fetchInto($ata, DB_FETCHMODE_OBJECT);
        return $ata;
    }

    private function consultarExisteSccOuCaronaExterna($chaveAtaCod)
    {
        $retorno = false;
        $db = Conexao();
        $sql = $this->sqlExisteCaronaExterna($chaveAtaCod);
        $resultado = executarSQL($db, $sql);
        $resultado->fetchInto($resultadoCountCarona, DB_FETCHMODE_OBJECT);

        $db->disconnect();

        $db = Conexao();

        $sql = $this->sqlExisteScc($chaveAtaCod);
        $resultado = executarSQL($db, $sql);
        $resultado->fetchInto($resultadoCountScc, DB_FETCHMODE_OBJECT);

        $db->disconnect();
               
        if(($resultadoCountCarona->count != 0) || ( $resultadoCountScc->count != 0)){
            $retorno = true;
        }

        return $retorno;
    }

    private function consultarExisteAtaInternaAnoNumeracaoOrgao($ataCod, $orgao, $ano, $numeracao)
    {
        $retorno = false;
        $db = Conexao();
        $sql = $this->sqlExisteAtaInternaAnoNumeracaoOrgao($ataCod, $orgao, $ano, $numeracao);
        $resultado = executarSQL($db, $sql);
        $resultado->fetchInto($resultadoCount, DB_FETCHMODE_OBJECT);

        $db->disconnect();
       
        if($resultadoCount->count != 0){
            $retorno = true;
        }

        return $retorno;
    }

    public function consultarItensAtaN($alicpoanop, $carpnosequ)
    {
        $db = Conexao();
        $sql = $this->sqlConsultarItensAta($alicpoanop, $carpnosequ);
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

    private function sqlConsultarItensAta($alicpoanop, $carpnosequ)
    {
        $sql  = "SELECT * ";                    
        $sql .= "         FROM ";
        $sql .= "             sfpc.tbitemataregistropreconova i ";
        $sql .= "             INNER JOIN sfpc.tbataregistroprecointerna arpi ";
        $sql .= "                 ON arpi.carpnosequ = i.carpnosequ ";
        $sql .= "                     AND arpi.aarpinanon = %d ";
        $sql .= "             LEFT OUTER JOIN sfpc.tbmaterialportal m ON i.cmatepsequ = m.cmatepsequ ";
        $sql .= "             LEFT OUTER JOIN sfpc.tbunidadedemedida ump ON ump.cunidmcodi = m.cunidmcodi ";
        $sql .= "             LEFT OUTER JOIN sfpc.tbservicoportal s ON i.cservpsequ = s.cservpsequ ";       
        $sql .= "         WHERE ";
        $sql .= "             i.carpnosequ = %d ";
        
        return sprintf($sql, $alicpoanop, $carpnosequ);

    }//end sqlConsultarItensAta()


    /**
     * Dados. Consultar itens da ata.
     *
     * @param $carpnosequ Código do Processo Licitatório    
     */
    public function consultarItensAtaParticipante($carpnosequ)
    {
        $db = Conexao();
        $sql = $this->sqlConsultarItensAtaParticipante($carpnosequ);
        $res = executarSQL($db, $sql);
        $itens = array();
        $item = null;
        $itemTipo = new stdClass();   
        $itemTipo->tipoItemValores = array();        
        $itemTipo->tipoItem = "ITEMPARTICIPANTE";
        $carpnosequAnterior = '';
        $corglicodiAnterior = '';
        $citarpsequAnterior = '';
            
        while ($res->fetchInto($item, DB_FETCHMODE_OBJECT)) {
            
            $varArray = array($item->corglicodi => array('apiarpqtat' => $item->apiarpqtat, 'apiarpqtut' => $item->apiarpqtut));
            
            if($citarpsequAnterior != $item->citarpsequ){                
                $citarpsequAnterior = $item->citarpsequ;                
                $item->tipoItem = $itemTipo->tipoItem;  

                $item->tipoItemValores = array();

                if($itemTipo->tipoItemValores != null){
                    $itemTipo->tipoItemValores = array();       
                }

                
                //if(sizeof($itens) > 0){                    
                array_push($item->tipoItemValores, $varArray);  
                //}else{
                //array_push($itemTipo->tipoItemValores, $varArray);                
                //}

                
                $itens[] = $item;
            }else{
                $endItem = end($itens);

                //array_push($itemTipo->tipoItemValores, $varArray); 

                array_push($endItem->tipoItemValores, $varArray);                  
               
            }

            
                       
        }

        return $itens;

    }//end consultarItensAta()

  
    public function consultarAtaParticipanteChave($numeroAta)
    {	
        $db = Conexao();
        $sql = $this->sqlAtaParticipanteAta($numeroAta);
		
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

    public function consultarAtaParticipanteAtaOrgao($numeroAta, $numeroOrgao)
    {	
        $db = Conexao();
        $sql = $this->sqlAtaParticipanteAtaOrgao($numeroAta, $numeroOrgao);
		
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


    /**
     *
     * @param integer $ata
     * @param integer $codigoReduzido
     * @param integer $tipo
     */
    public function consultarItemCompleto($db, $codigoReduzido, $tipo)
    {
        
        $colunaSequencialItem = "";
        if(strlen($tipo) > 1){
            $colunaSequencialItem = ($tipo == 'CADUM') ? 'cmatepsequ' : 'cservpsequ';
        }else{
            $colunaSequencialItem = ($tipo == 'M') ? 'cmatepsequ' : 'cservpsequ';    
        }

        $sql = " SELECT
        *
        FROM ";

        if ($tipo == 'M') {
            $sql .= " sfpc.tbmaterialportal i left join SFPC.TBunidadedemedida um ON
            um.cunidmcodi = i.cunidmcodi   ";
        }else{
            $sql .= " sfpc.tbservicoportal i ";
        }
        
        $sql .= " WHERE 
        i.$colunaSequencialItem = ".$codigoReduzido;

        $resultado = executarSQL($db, $sql);
        $resultado->fetchInto($item, DB_FETCHMODE_OBJECT);

        return $item;
    }



    private function atualizarNumeracaoAnoAta($ataCod, $postValores)
    {
        $db = Conexao();
        $sql = $this->sqlAtualizarNumeracaoAnoAta($ataCod, $postValores);
        $resultado = executarSQL($db, $sql);        
        $db->disconnect();
        if (PEAR::isError($resultado)) {
            EmailErroSQL("Erro em SQL", __FILE__, __LINE__, "Erro em SQL", $sql, $res);
        }
        return $resultado;
    }


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


    // private function sqlLicitacaoAtaInterna($ano, $processo, $orgaoUsuario)
    // {
    //     $sql = "select distinct l.clicpoproc,";
    //     $sql .= " l.alicpoanop,";
    //     $sql .= " l.xlicpoobje,";
    //     $sql .= " l.ccomlicodi,";
    //     $sql .= " c.ecomlidesc,";
    //     $sql .= " o.corglicodi,";
    //     $sql .= " o.eorglidesc,";
    //     $sql .= " m.emodlidesc,";
    //     $sql .= " l.clicpocodl,";
    //     $sql .= " l.alicpoanol";
    //     $sql .= " from sfpc.tblicitacaoportal l";
    //     $sql .= " inner join sfpc.tborgaolicitante o";
    //     $sql .= " on o.corglicodi=".$orgaoUsuario;
    //     $sql .= " and l.corglicodi = o.corglicodi";
    //     $sql .= " inner join sfpc.tbcomissaolicitacao c";
    //     $sql .= " on l.ccomlicodi = c.ccomlicodi";
    //     $sql .= " inner join sfpc.tbmodalidadelicitacao m";
    //     $sql .= " on l.cmodlicodi = m.cmodlicodi";
    //     $sql .= " where l.alicpoanop =".$ano;
    //     $sql .= " and l.clicpoproc =".$processo;
    //     return $sql;
    // }

    private function sqlAtaPorchave($processo, $orgao, $ano, $chaveAta)
    {
        $sql  = "select a.carpincodn, a.earpinobje, a.aarpinanon, a.aarpinpzvg, a.tarpindini, a.cgrempcodi, a.cusupocodi, f.nforcrrazs, d.edoclinome,";
        $sql .= " a.corglicodi, a.carpnosequ, a.alicpoanop, s.csolcosequ, a.aarpinanon, carpnoseq1, ";

        $sql .= " f.nforcrrazs, f.aforcrccgc, f.aforcrccpf, f.eforcrlogr, ";
        $sql .= " f.aforcrnume, f.eforcrbair, f.nforcrcida, f.cforcresta, ";

        $sql .= " fa.nforcrrazs as razaoFornecedorAtual, fa.aforcrccgc as cgcFornecedorAtual, fa.aforcrccpf as cpfFornecedorAtual, fa.eforcrlogr as logradouroFornecedorAtual, ";
        $sql .= " fa.aforcrnume as numeroEnderecoFornecedorAtual, fa.eforcrbair as bairroFornecedorAtual, fa.nforcrcida as cidadeFornecedorAtual, fa.cforcresta as estadoFornecedorAtual ";

        $sql .= " from sfpc.tbataregistroprecointerna a";

        $sql .= " left outer join sfpc.tbsolicitacaolicitacaoportal s";
        $sql .= " on (s.clicpoproc = a.clicpoproc";
        $sql .= " and s.alicpoanop = a.alicpoanop";
        $sql .= " and s.ccomlicodi = a.ccomlicodi";
        $sql .= " and s.corglicodi = a.corglicodi)";

        $sql .= " left outer join sfpc.tbfornecedorcredenciado f";
        $sql .= " on f.aforcrsequ = a.aforcrsequ";

        $sql .= " left outer join sfpc.tbfornecedorcredenciado fa";
        $sql .= " on fa.aforcrsequ = (select afa.aforcrsequ from sfpc.tbataregistroprecointerna afa where afa.carpnosequ = a.carpnoseq1)";

        $sql .= " left outer join sfpc.tbdocumentolicitacao d";
        $sql .= " on d.clicpoproc = a.clicpoproc";
        $sql .= " and d.clicpoproc = " . $processo;
        $sql .= " and d.corglicodi = " . $orgao;
        $sql .= " and d.alicpoanop = " . $ano;

        $sql .= " where a.carpnosequ = " . $chaveAta;

        return $sql;
    }



    private function sqlExisteCaronaExterna($ataCod)
    {
        $sql  = " SELECT count(*) ";
        $sql .= " FROM sfpc.tbcaronaorgaoexterno car ";

        $sql .= " WHERE 1=1 ";
        $sql .= " AND car.carpnosequ = ".$ataCod;

        return $sql;
    }


    private function sqlExisteAtaInternaAnoNumeracaoOrgao($ataCod, $orgao, $ano, $numeracao)
    {
        $sql  = " SELECT count(*) ";
        $sql .= " FROM sfpc.tbataregistroprecointerna atai ";

        $sql .= " WHERE 1=1 ";
        $sql .= " AND atai.aarpinanon = ".$ano;
        $sql .= " AND atai.carpincodn = ".$numeracao;
        $sql .= " AND atai.corglicodi = ".$orgao;

        $sql .= " AND atai.carpnosequ <> ".$ataCod;

        return $sql;
    }


    private function sqlAtualizarNumeracaoAnoAta($ataCod, $postValores)
    {

        $carpincodn = $postValores['VALOR_ATA'];
        $aarpinanon = $postValores['ANO_ATA'];

        $sql = " UPDATE sfpc.tbataregistroprecointerna ";
        //Nº da Ata Interna*
        $sql .= " SET carpincodn = " . $carpincodn . ", ";
        //Ano da Ata Interna*
        $sql .= " aarpinanon =  ". $aarpinanon . ", ";
        
        $sql .= " tarpinulat = now()";        
        $sql .= " where carpnosequ = " . $ataCod;

        return $sql;
    }



    private function sqlExisteScc($ataCod)
    {
        $sql  = " SELECT count(*) ";
        $sql .= " FROM sfpc.tbsolicitacaocompra sol ";

        $sql .= " WHERE 1=1 ";
        $sql .= " and sol. carpnosequ = ".$ataCod;
        $sql .= "   and sol.csitsocodi <> 10 ";

        return $sql;
    }


    /**
     * SQL consultar itens da ata Participante.
     *
     * @param $carpnosequ Código do Processo Licitatório     
     */
    private function sqlConsultarItensAtaParticipante($carpnosequ)
    {
        $sql  = "SELECT * ";                    
        $sql .= "         FROM ";
        $sql .= "             sfpc.tbparticipanteatarp arpi ";
        $sql .= "             inner join sfpc.tbparticipanteitematarp ipa on  ";
        $sql .= "               ipa.carpnosequ = arpi.carpnosequ  ";
        $sql .= "               and ipa.corglicodi = arpi.corglicodi ";
        $sql .= "             inner join sfpc.tbitemataregistropreconova i on ";
        $sql .= "               i.carpnosequ = arpi.carpnosequ ";
        $sql .= "               and i.citarpsequ = ipa.citarpsequ ";   
        $sql .= "             left outer join sfpc.tbmaterialportal m on ";   
        $sql .= "               i.cmatepsequ = m.cmatepsequ  ";   
        $sql .= "             left outer join sfpc.tbunidadedemedida ump on ";   
        $sql .= "               ump.cunidmcodi = m.cunidmcodi  ";   
        $sql .= "             left outer join sfpc.tbservicoportal s on ";   
        $sql .= "               i.cservpsequ = s.cservpsequ ";   
        $sql .= "             inner join sfpc.tborgaolicitante o on ";   
        $sql .= "               o.corglicodi = arpi.corglicodi ";   

        $sql .= "         WHERE ";
        $sql .= "             i.carpnosequ = %d ";
        $sql .= "         order by ipa.citarpsequ, ipa.corglicodi asc  ";
        
        return sprintf($sql, $carpnosequ);

    }//end sqlConsultarItensAta()

    private function sqlAtaParticipanteAta($chaveAta)
    {
        $sql  = "select * ";
        $sql .= " from sfpc.tbparticipanteatarp pa ";
        $sql .= " inner join sfpc.tborgaolicitante o on ";
        $sql .= " o.corglicodi = pa.corglicodi  ";
        $sql .= "  where pa.carpnosequ = " . $chaveAta->carpnosequ;

        return $sql;
    }

    private function sqlAtaParticipanteAtaOrgao($chaveAta, $numeroOrgao)
    {
        $sql  = "select * ";
        $sql .= " from sfpc.tbparticipanteatarp pa ";
        $sql .= " inner join sfpc.tborgaolicitante o on ";
        $sql .= " o.corglicodi = pa.corglicodi  ";
        $sql .= "  where pa.carpnosequ = " . $chaveAta;  

        $sql .= "  and pa.corglicodi = " . $numeroOrgao;         

        return $sql;
    }

    private function sqlItemAtaNova($numeroAta, $anoAta)
    {
        $sql = "SELECT
				    i.citarpsequ,
				    i.aitarporde,
				    i.aitarpqtor,
				    i.vitarpvori,
				    i.aitarpqtat,
				    i.vitarpvatu,
				    i.citarpnuml,
				    i.fitarpsitu,
				    i.eitarpdescmat,
				    i.cservpsequ,
				    i.cmatepsequ,
				    i.eitarpdescse,
				    m.ematepdesc,
				    s.eservpdesc,
    				ump.eunidmsigl
				FROM
				    sfpc.tbitemataregistropreconova i
				    INNER JOIN sfpc.tbataregistroprecointerna arpi
				    	ON arpi.carpnosequ = i.carpnosequ
				    		AND arpi.aarpinanon = %d
				    LEFT OUTER JOIN sfpc.tbmaterialportal m ON i.cmatepsequ = m.cmatepsequ
    				LEFT OUTER JOIN sfpc.tbunidadedemedida ump ON ump.cunidmcodi = m.cunidmcodi
				    LEFT OUTER JOIN sfpc.tbservicoportal s ON i.cservpsequ = s.cservpsequ
				WHERE
				    i.carpnosequ = %d
    				AND i.fitarpsitu = 'A'";

        return sprintf($sql, $anoAta, $numeroAta);
    }
 
    private function sqlCodigoMaximoDocumento($processo, $orgao, $ano, $grupo)
    {
        $sql="select max(d.cdoclicodi) from sfpc.tbdocumentolicitacao d";
        $sql.="where d.clicpoproc =".$processo;
        $sql.="and d.cgrempcodi =".$grupo;
        $sql.="and d.corglicodi =".$orgao;
        $sql.="and d.alicpoanop =".$ano;

        return $sql;
    }

    private function sqlInsereDocumento($valores)
    {
        $sql="INSERT INTO sfpc.tbdocumentolicitacao (clicpoproc,alicpoanop,cgrempcodi,ccomlicodi,corglicodi,";
        $sql.= "cdoclicodi,edoclinome,tdoclidata,cusupocodi,tdocliulat)";
        $sql.=" VALUES (".$valores.")";
    }

    private function processVoltar()
    {
        $uri  = 'CadAtaRegistroPrecoInternaManterEspecial.php';
        header('location: ' . $uri);
    }

    private function frontController()
    {
        $botao = isset($this->variables['post']['Botao'])
            ? $this->variables['post']['Botao']
            : 'Principal'; 
        switch ($botao) {
            case 'Voltar':
                $this->processVoltar();
                break;
            case 'Excluir':
                $this->excluir();
                break;
            case 'Salvar':                      
                $this->salvar();
                break;
            case 'RetirarParticipante':
                $this->retirarParticipante(); 
                $this->proccessPrincipal();       
                break;
            case 'AtivarParticipante':
                $this->ativarParticipante(); 
                $this->proccessPrincipal();       
                break;
            case 'InativarParticipante':
                $this->inativarParticipante();
                $this->proccessPrincipal();
                break;
            case 'RetirarItem':
                $this->retirarItem();
                $this->proccessPrincipal();
                break;
            case 'Principal':
            default:
                $this->proccessPrincipal();
        }
    }

    public function __construct(TemplatePaginaPadrao $template, ArrayObject $variablesGlobals)
    {
        $this->setTemplate($template);
        $this->variables = $variablesGlobals;
        $this->frontController();
    }

    public function run()
    {
        return $this->getTemplate()->show();
    }
}

/**
 * Bootstrap application
 */
function bootstrap()
{
    global $SimboloConcatenacaoArray, $SimboloConcatenacaoDesc;

    $template = new TemplatePaginaPadrao(
        "templates/CadAtaRegistroPrecoInternaManterEspecialAtasAlterar.html",
        "Registro de Preço > Ata Interna > Manter Especial"
    );
    $template->NOMEPROGRAMA = 'CadAtaRegistroPrecoInternaManterEspecialAtasAlterar';

    $arrayGlobals = new ArrayObject();
    $arrayGlobals['session'] = $_SESSION;
    $arrayGlobals['server'] = $_SERVER;
    $arrayGlobals['get'] = $_GET;
    $arrayGlobals['separatorArray'] = $SimboloConcatenacaoArray;
    $arrayGlobals['separatorDesc'] = $SimboloConcatenacaoDesc;

    if ($arrayGlobals['server']['REQUEST_METHOD'] == "POST") {
        $arrayGlobals['post'] = $_POST;
    }

    if ($arrayGlobals['server']['REQUEST_METHOD'] == "GET") {
        unset($_SESSION['itens']);
    }

    $app = new CadAtaRegistroPrecoInternaManterEspecialAtasAlterar($template, $arrayGlobals);
    echo $app->run();

    unset($app, $template, $arrayGlobals);
}

bootstrap();
