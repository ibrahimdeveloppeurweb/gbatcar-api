<?php

namespace App\Utils;

// use Dompdf\Dompdf;
// use Dompdf\Options;
use Symfony\Component\HttpFoundation\Response;

class Printer
{
    /**
     * Page A4
     * @param mixed $html
     * @param boolean $etat
     * @param string|null $titre
     * @param string|null $type
     * @param string|null $print
     * @return void
     */
    public function pageA4($html, bool $etat, ?string $titre = null, ?string $type = 'portrait', ?string $print = 'print')
    {
        // $option = new Options();
        // $option->set('defaultFont', 'Arial');
        // $option->setIsRemoteEnabled(true);
        // $dompdf = new Dompdf($option);
        // $dompdf->loadHtml($html);
        // $dompdf->setPaper('A4', $type); //orientation 'portrait' or 'landscape'
        // $dompdf->render();
        // $font = $dompdf->getFontMetrics()->getFont("Verdana", "");
        // if ($type === 'portrait') {
        //     $dompdf->getCanvas()->page_text(530, 10, "{PAGE_NUM} / {PAGE_COUNT}", $font, 9, array(0, 0, 0));
        // } else {
        //     $dompdf->getCanvas()->page_text(755, 20, "{PAGE_NUM} / {PAGE_COUNT}", $font, 9, array(0, 0, 0));
        // }

        // if(isset($print) && TypeVariable::is_not_null($print) && $print === 'print') {
        //     ob_end_clean();
        //     // Obtenir un version téléchargé du PDF
        //     return $dompdf->stream($titre, ["Attachment" => $etat]);
        // }

        // if(isset($print) && TypeVariable::is_not_null($print) && $print === 'blob'){
        //     // Obtenir le contenu du PDF généré
        //     $output = $dompdf->output();
        //     // Renvoyer le PDF en tant que réponse HTTP
        //     return new Response($output, 200, ['Content-Type' => 'application/pdf', 'Content-Disposition' => 'inline; filename="'.$titre.'.pdf"']);
        // }
    }
}
