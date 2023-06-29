<?php
/**
 * Autor: João Madson Felix
 * Data: 09 de Abril de 2021
 * Objetivo: Realizar a inserção de itens em ata que estagvam com erro
 *==========================================================================
 * Alterado: Eliakim Ramos
 * Data:     16/08/2022
 * Objetivo: CR #267552
 */

if (! @include_once dirname(__FILE__) . '/../bootstrap.php') {
    throw new Exception('Error Processing Request - Bootstrap', 1);
}

    require_once dirname(__FILE__) . '/../funcoes.php';
    session_start();
    Seguranca();

    $template = new TemplatePaginaPadrao('templates/InsertItensAta.html', 'Registro de Preço > Ata Externa > inserir Item ata');
    

    if($_SERVER['REQUEST_METHOD'] == "GET"){
        $template->MENSAGEM = "";
        $template->show();
    }


 
    function getCSV($name) {
        $file = fopen($name, "r");
        $result = array();
        $i = 0;
        while (!feof($file)):
           if (substr(($result[$i] = fgets($file)), 0, 10) !== ';;;;;;;;') :
              $i++;
           endif;
        endwhile;
        fclose($file);
        return $result;
    }
    function GetMaxSeq($carpnosequ,$db){
        $sql = "select max(citarpsequ) as sequencial from sfpc.tbitemataregistropreconova where carpnosequ =".$carpnosequ;
        $result = executarSQL($db, $sql);
        $retorno = $result->fetchRow(DB_FETCHMODE_OBJECT);
        return $retorno->sequencial+1;
    }

    function clear($idB){
        $id = utf8_encode($idB);
        $LetraProibi = Array(" ",",",".","'","\"","&","|","!","#","$","¨","*","(",")","`","<",">","=","+","§","{","}","[","]","^","~","?","%");
        $special = Array('Á','È','ô','Ç','á','è','Ò','ç','Â','Ë','ò','â','ë','Ø','Ñ','À','Ð','ø','ñ','à','ð','Õ','Å','õ','Ý','å','Í','Ö','ý','Ã','í','ö','ã',
           'Î','Ä','î','Ú','ä','Ì','ú','Æ','ì','Û','æ','Ï','û','ï','Ù','®','É','ù','©','é','ó','Ü','Þ','Ê','ó','ü','þ','ê','Ô','ß','"','”','„');
        $clearspc = Array('a','e','o','c','a','e','o','c','a','e','o','a','e','o','n','a','d','o','n','a','o','o','a','o','y','a','i','o','y','a','i','o','a',
           'i','a','i','u','a','i','u','a','i','u','a','i','u','i','u','','e','u','c','e','o','u','p','e','o','u','b','e','o','b','','','','','','');
        $newStringA = str_replace($special, $clearspc, $id);
        $newString = str_replace($LetraProibi, "", trim($newStringA));

        return strtoupper2($newString);
     }
    function clear2($idB){
        $id = utf8_encode($idB);
        $LetraProibi = Array("'","\"","|","!","#","$","¨","*","(",")","`","<",">","§","{","}","[","]","^","~","?","\n","<br />","^l");
        $special = Array('Á','È','ô','Ç','á','è','Ò','ç','Â','Ë','ò','â','ë','Ø','Ñ','À','Ð','ø','ñ','à','ð','Õ','Å','õ','Ý','å','Í','Ö','ý','Ã','í','ö','ã',
           'Î','Ä','î','Ú','ä','Ì','ú','Æ','ì','Û','æ','Ï','û','ï','Ù','®','É','ù','©','é','ó','Ü','Þ','Ê','ó','ü','þ','ê','Ô','ß','"','”','„');
        $clearspc = Array('a','e','o','c','a','e','o','c','a','e','o','a','e','o','n','a','d','o','n','a','o','o','a','o','y','a','i','o','y','a','i','o','a',
           'i','a','i','u','a','i','u','a','i','u','a','i','u','i','u','','e','u','c','e','o','u','p','e','o','u','b','e','o','b');
        $newStringA = str_replace($special, $clearspc, $id);
        $newStringB = str_replace($LetraProibi, "", $newStringA);
        $newString = preg_replace("/\r|\n/",'',$newStringB);

        return strtoupper2($newString);
     }

    if($_SERVER['REQUEST_METHOD'] == "POST"){
        if($_POST['op'] == "AnoProcesso"){
            $anos = HelperPitang::carregarAno();
            $htmlOptionsAno = "<option>Selecione um ano...</option>";
            foreach ($anos as $text) {
                $htmlOptionsAno .= '<option value="'.$text.'">'.$text.'</option>';
            }
            print($htmlOptionsAno);
            exit();
        }

        if($_POST['op'] == "Processo" && !empty($_POST['Ano'])){
            $htmlOptionsProcesso = "<option>Selecione um processo...</option>";
            $sql = new Dados_Sql_AtaRegistroPrecoExterna();
            $resultado = ClaDatabasePostgresql::executarSQL($sql->sqlSelecionaTodasAtasPeloAno($_POST['Ano']));
            //ClaDatabasePostgresql::hasError($resultado);
            foreach ($resultado as $processo) {
                $fornecedor = !empty($processo->atual) ? $processo->atual : $processo->original;
                $htmlOptionsProcesso .= '<option value="'.$processo->carpnosequ.'">'.$processo->earpexproc . " - " . $fornecedor.'</option>';
            }
            print($htmlOptionsProcesso);
            exit();
        }

        if($_POST['op'] == "GetAtaRegistroPrecoAtiva" && !empty($_POST['ano']) && !empty($_POST['processo'])){
            $repositorio = new Negocio_Repositorio_AtaRegistroPrecoExterna();
            $DadosAtaRegistroPrecoAtiva = $repositorio->consultarAtaRegistroPrecoAtiva(new Negocio_ValorObjeto_Carpnosequ($_POST['processo']), new Negocio_ValorObjeto_Aarpexanon($_POST['ano']));
            $response = "";
            $arrayDados = array();
            foreach($DadosAtaRegistroPrecoAtiva as $ataRegistroPrecoAtiva ){
                $arrayDados = array(
                                    "NUMERO_ATA_EXTERNA" => str_pad($ataRegistroPrecoAtiva->carpexcodn, 4, '0', STR_PAD_LEFT),
                                    "ANO_ATA_EXTERNA" => $ataRegistroPrecoAtiva->aarpexanon,
                                    "PROCESSO_ATA_EXTERNA" => strtoupper2($ataRegistroPrecoAtiva->earpexproc),
                                    "SEQ_PROCESSO" => $ataRegistroPrecoAtiva->carpnosequ,
                                    "SITUACAO_ATA_EXTERNA" => $ataRegistroPrecoAtiva->farpexsitu,
                                    "ORGAO_ATA_EXTERNA" => $ataRegistroPrecoAtiva->earpexorgg,
                                    "OBJETO_ATA_EXTERNA" => $ataRegistroPrecoAtiva->earpexobje,
                                 );
            }
            $response = json_encode($arrayDados);
            print($response);
            exit();
        }

        $db = conexao();
        $dados = getCSV($_FILES['arquivo']['tmp_name'], 'r');
        $quantDados = count($dados);
        $chave = false;
        $carpnosequ = !empty($_POST["SEQ_PROCESSO"]) ? $_POST["SEQ_PROCESSO"] : 12345;
        $fitarpsitu = "A";
        $sequencial = GetMaxSeq($carpnosequ,$db);
        $posCitarpnuml = $posAitarpqtor = $posCservpsequ = $posCmatepsequ = $posValor1 = $posValor2 = $posEitarpdescse= 0;
        for($i=0;$i<$quantDados;$i++){
            if($i == 0 ){
                $linha = explode(";", clear($dados[$i]));
                $posAitarpqtor = array_search("QUANT", $linha);
                $posCitarpnuml = array_search("ITEM", $linha);
                $posCservpsequ = array_search("CADUS", $linha);
                $posCmatepsequ = array_search("CADUM", $linha);
                $posValor1 = array_search("VALORUNIT", $linha);
                $posValor2 = array_search("VALORUNITCOMBDI", $linha);
                $posEitarpdescse = (array_search("DESCRICAO", $linha)) ? array_search("DESCRICAO", $linha) : array_search("DESCRICO", $linha);
                continue;
            }else {
                $linha = explode(";", $dados[$i]);
            }
            $arrayRetiraAspas = array('"','""',"'","''");
            $citarpnuml = $linha[$posCitarpnuml];
            $eitarpdescse = htmlspecialchars(str_replace($arrayRetiraAspas,'',$linha[$posEitarpdescse]));
            $aitarpqtor = $linha[$posAitarpqtor];
            $cservpsequ = "";
            if(!empty($posCservpsequ)){
                if(!empty($linha[$posCservpsequ])){
                    $cservpsequ = is_int($linha[$posCservpsequ]) ? $linha[$posCservpsequ] : 430;
                }else{
                    $cservpsequ = NULL;
                }
            }else{
                $cservpsequ = NULL;
            }

            if(!empty($posCmatepsequ)){

                if(!empty($linha[$posCmatepsequ])){
                    $cmatepsequ = is_int($linha[$posCmatepsequ])? $cmatepsequ : 430;
                }else{
                    $cmatepsequ = NULL;
                }
            }else{
                $cmatepsequ = NULL;
            }

            if(!empty($linha[2]) ){
                if(strpos($linha[$posValor2],',')){
                    $linha[$posValor1] = str_replace(".", '', $linha[$posValor1]);
                    $linha[$posValor2] = str_replace(".", '', $linha[$posValor2]);
                    $auxVal = explode(",", trim($linha[$posValor2]));
                    $valor = str_pad(str_replace('"','',$auxVal[0]).".".str_replace('"','',$auxVal[1]),6,"00",STR_PAD_RIGHT);
                }else {
                    $valor = strpos($linha[$posValor2],'.') ? str_pad(trim($linha[$posValor2]), 6, "00", STR_PAD_RIGHT) : str_pad(trim($linha[$posValor2]), 6, ".0000", STR_PAD_RIGHT);
                }
                //echo $eitarpdescse."<br/>";
                if(!empty($aitarpqtor)){
                    //var_dump($valor);
                    $sql = "INSERT INTO sfpc.tbitemataregistropreconova
                    (carpnosequ,citarpsequ,aitarporde,cmatepsequ,cservpsequ,aitarpqtor,aitarpqtat,vitarpvori,vitarpvatu,citarpnuml,eitarpmarc,eitarpmode,eitarpdescmat,eitarpdescse,fitarpsitu,fitarpincl,fitarpexcl,titarpincl,cusupocodi,titarpulat) 
                    VALUES (".$carpnosequ.",".$sequencial.",".$sequencial.",".(empty($cmatepsequ)?'NULL':$cmatepsequ).",".(empty($cservpsequ)?'NULL':$cservpsequ).",'".$aitarpqtor."','00000','$valor','0.0000',".$citarpnuml.",'.','.',NULL,'".$eitarpdescse."','".$fitarpsitu."','S','S','NOW()',".$_SESSION['_cusupocodi_'].",'NOW()')";    
                }else{
                        //var_dump($valor);
                        $sql = "INSERT INTO sfpc.tbitemataregistropreconova
                        (carpnosequ,citarpsequ,aitarporde,cmatepsequ,cservpsequ,aitarpqtor,aitarpqtat,vitarpvori,vitarpvatu,citarpnuml,eitarpmarc,eitarpmode,eitarpdescmat,eitarpdescse,fitarpsitu,fitarpincl,fitarpexcl,titarpincl,cusupocodi,titarpulat) 
                        VALUES (".$carpnosequ.",".$sequencial.",".$sequencial.",".(empty($cmatepsequ)?'NULL':$cmatepsequ).",".(empty($cservpsequ)?'NULL':$cservpsequ).",'".$aitarpqtor."','00000','$valor','0.0000',".$citarpnuml.",'.','.',NULL,'".$eitarpdescse."','".$fitarpsitu."','S','S','NOW()',".$_SESSION['_cusupocodi_'].",'NOW()')";
                        var_dump($sql);die;
                }
                $resultado = executarSQL($db, $sql);
                //($sql);
                $sequencial++;
            }

        }
        //die;
        $template->MENSAGEM = "Inserção Concluida!";
        $template->show();

    }
?>

