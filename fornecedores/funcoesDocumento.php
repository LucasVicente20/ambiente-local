<?php
#-------------------------------------------------------------------------
# Portal de Compras
# Programa: funcoesDocumento.php
# Objetivo: funções com regras do módulo Documentos
# Autor:    Ernesto Ferreira
#-----------------------

require_once("../funcoes.php");

# Abrindo Conexão
if (!isset($db)) {
    $db = Conexao();
}

/**
 * Verificar erro nas consultas
 *
 * @param $res
 *
 * @return bool
 */
function isError($res) {
    if (PEAR::isError($res)) {
        $CodErroEmail  = $res->getCode();
        $DescErroEmail = $res->getMessage();
        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
    }

    return false;
}

function getFornecedores(){
    // retorna todos os fornecedores
    $db = $GLOBALS["db"];

    $sql = ' select A.aprefosequ, A.AFORCRSEQU, A.NFORCRRAZS, A.AFORCRCCGC, A.AFORCRCCPF 
    from SFPC.TBFORNECEDORCREDENCIADO A  ';
    $res  = $db->query($sql);

    if(!isError($res)) {
        $array = array();
        while ($Linha = $res->fetchRow()) {
            $array[] = $Linha;
        }

        return $array;
    }


}


function getPreFornecedores(){
    // retorna todos os fornecedores
    $db = $GLOBALS["db"];

    $sql = ' select B.aprefosequ, A.AFORCRSEQU, B.npreforazs, B.aprefoccgc, B.aprefoccpf
    from SFPC.TBPREFORNECEDOR B
    left join SFPC.TBFORNECEDORCREDENCIADO A on B.aprefosequ = A.aprefosequ  ';
    $res  = $db->query($sql);

    if(!isError($res)) {
        $array = array();
        while ($Linha = $res->fetchRow()) {
            $array[] = $Linha;
        }

        return $array;
    }


}


function getDadosFornecedor($chave, $arrFornecedores, $tipo){
    $result = array();
    if($tipo == 2){
        foreach ($arrFornecedores as $linha) {
       
            if($linha[1]==$chave){

                if($linha[3]){
                    $cpfCnpj = FormataCNPJ($linha[3]);
                }else{
                    $cpfCnpj = FormataCPF($linha[4]);
                }

                $result = array($linha[1], $linha[2], $cpfCnpj);

            }
            
        }
    }else{
        foreach ($arrFornecedores as $linha) {
       
            if($linha[0]==$chave){

                if($linha[3]){
                    $cpfCnpj = FormataCNPJ($linha[3]);
                }else{
                    $cpfCnpj = FormataCPF($linha[4]);
                }

                if($linha[1]){
                    $result = array($linha[1], $linha[2], $cpfCnpj, 'cred');
                }else{
                    $result = array($linha[0], $linha[2], $cpfCnpj, 'pre');
                }

            }
            
        }

    }
    return $result;

}


function formatarDataHora($data){

    $dataHora = substr($data, 0,19);

    $arrDataHora = explode(' ',$dataHora);
    $arrData = explode('-',$arrDataHora[0]);

    $dataformatada = $arrData[2].'/'.$arrData[1].'/'.$arrData[0];

    return $dataformatada.' '.$arrDataHora[1];
}



function dadosParametrosGerais($db = null){
    if(!$db){
        $db = $GLOBALS["db"];
    }
    
    $sql = ' select qpargetmaobjeto, qpargetmajustificativa, qpargedescse,
                        epargesubelemespec, qpargeqmac, qpargeqmac, epargetdov
             from sfpc.tbparametrosgerais ';
    $res  = $db->query($sql);

    if(!isError($res)) {
        $array = array();
        while ($Linha = $res->fetchRow()) {
            $array = $Linha;
        }

        return $array;
    }
}

