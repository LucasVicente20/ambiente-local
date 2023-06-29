<?php
/**
 * Portal de Compras
 * 
 * Programa: layout.php
 * ----------------------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     10/05/2019
 * Objetivo: Tarefa Redmine 216521
 * ----------------------------------------------------------------------------------------/
 */
?>

<head>
    <title>Portal DGCO - Prefeitura do Recife</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <style type="text/css">
        #Titulo {position: absolute; z-index: 1; visibility: visible; left: -1; top: 15;}
        #BgMenu{position: absolute; z-index: 0; visibility: visible; left: -12; top: -4;}
    </style>
</head>

<!-- Titulo -->
<?php
if (!$_REQUEST['window']) {
    ?>
    <div id="Titulo" style='width: 100% '>
        <img src="../midia/portalCompra.jpg" border="0" alt="">
        <?php
        if ($_SESSION['_ref_']=="transparencia") {
            ?>
            <div style="position:absolute; top:2px; left:680px;">
                <span class="textonormalAmarelo">Você veio de:</span>
            </div>
            <div style="position:absolute; top:17px; left:1000px;">
                <a href="<?php echo URL_TRANSPARENCIA;?>" >
                    <img src="../midia/portalTransparencia1.png" border="0" width="130px" alt=""  style='margin-left:-400px; ' >
                </a>
            </div>
            <?php
        }
        ?>
        <?php
        if ($_SESSION['_ref_']!="transparencia") {
            $strLocalSistema = str_replace('_',' ',strtoupper2($GLOBALS["LOCAL_SISTEMA"]));
            
            if (strpos($GLOBALS["LOCAL_SISTEMA"], CONST_NOMELOCAL_DESENVOLVIMENTO) !== false) {
                echo "
                    <div style='position:absolute;right:0px;top:0px;text-align:middle;background-color:#fff;height:65px;'>
                        <img src='../midia/desenvolver.JPG' border='0' style ='vertical-align:middle;' />\n
                        <span style='font-weight: bold;font-size: 20px;'>".$strLocalSistema."</span>
                    </div>
                ";
            } elseif ($GLOBALS["LOCAL_SISTEMA"] === CONST_NOMELOCAL_HOMOLOGACAO) {
                echo "
                    <div style='position:absolute;right:0px;top:0px;text-align:middle;background-color:#fff;height:65px;'>
                        <img src='../midia/homologa.JPG' border='0' style ='vertical-align:middle;' />\n
                        <span style='font-weight: bold;font-size: 20px;'>".$strLocalSistema."</span>
                    </div>
                ";
            }
        }
        ?>
    </div>
    <!-- Fim do Titulo -->
    <!-- Bg Menu-->
    <div id="BgMenu">
        <img src="../midia/bg_menu.gif" border="0">
    </div>
    <?php
}
?>
<!-- Fim do Bg Menu-->
<script language="javascript" src="../import/jquery/jquery-1.7.2.min.js" type="text/javascript"></script>
<script language="javascript" src="../import/jquery/jquery.maskmoney.js" type="text/javascript"></script>
<script language="javascript" src="../import/jquery/jquery.maskedinput.js" type="text/javascript"></script>
<script language="javascript" src="../funcoes.js" type="text/javascript"></script>