<?php
 ini_set('display_errors', 0);
 error_reporting(E_ALL ^ E_NOTICE);
// require_once "./dompdf/dompdf/dompdf_config.inc.php";
require '../dompdf/vendor/autoload.php';
use Dompdf\Dompdf;


$arrayTirar = array(
                        'TEXT("Eliakim Ramos");',
                        '$objQrcode->URL("https://eliakimramos.com.br/portifolio/");',
                        '$objQrcode','->GeraQRCODE','(400,"teste.png");', '?>',
                        '<a href="GeraPdf.php" target="_blank" rel="noopener noreferrer">Print Relatorio</a>',
                        '<!--','<!--?php','require_once "classQrCode.php";','= new QrCode();', '--->', '<?php', '->'
                    );

$dompdf = new DOMPDF();
$dompdf->getOptions()->setChroot(['/var/www/html/secfinancas/portalcompras']);
$dompdf->getOptions()->setIsRemoteEnabled(TRUE);
$html = file_get_contents("index.php");
$html = str_replace($arrayTirar,'',$html);
// $teste = explode("<body>", $html);
// $teste = explode("<\body>", $teste[1]);
//  var_dump($teste[0]);
$dompdf->loadHtml($html);
// $dompdf->load_html('<table style="table-layout: fixed; width: 350px;" border="1">
// <tr>
// <td>Reallyyyyyyyyyy Looooooong cell conteeeent</td>
// <td>short</td>
// <td>Normal cell content</td>
// </tr>
// <tr>
// <td>short</td>
// <td>ReallyyyyyyyyyyLooooooong cell conteeeent</td>
// <td>Normal cell content</td>
// </tr>
// </table>
// <div>
//     <img src="teste.png" alt="" style="width: 50px;height50px;">
// </div>
// ');
$dompdf->render();
$dompdf->stream('relatoriopdf.pdf',array("Attachment" => TRUE));
?>