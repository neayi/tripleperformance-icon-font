<?php


require __DIR__ . '/../../vendor/autoload.php';

use SVG\SVG;
use SVG\Writing\SVGWriter;
use SVG\Nodes\Shapes\SVGRect;

try {

    makeSmallDepartements();

} catch (\Throwable $th) {
	echo 'Exception: ',  $th->getMessage(), "\n";
	echo "Stopping build.\n\n";
	exit(1); // Important: throw an error in order to stop the build
}

function makeSmallDepartements()
{
    echo "Building small images for each departement\n";

    // image with 100x100 viewport
    $image = SVG::fromFile(__DIR__ . '/departements.svg');

    $doc = $image->getDocument();

    $departements = $doc->getElementsByClassName('land');

    foreach ($departements as $dep)
    {
        $classnames = $dep->getAttribute('class');

        // Maintenant on créé un nouvel SVG pour chaque département :

        // Récupère le numéro du département :
        // land departement90
        $depNumber = str_replace('land departement', '', $classnames);

        // Make sure we have 2A and not 2a
        $depNumber = strtoupper($depNumber);

        $aDepSVG = SVG::fromFile(__DIR__ . '/empty.svg');
        $dep->setAttribute('id', 'departement');

        $writer = new SVGWriter(false);
        $writer->writeNode($dep);

        $strNode = $writer->getString();

        // Get each points:
        $matches = array();
        if (preg_match_all('/([0-9.]+),([0-9.]+)/', $strNode, $matches))
        {
            $xs = $matches[1];
            $ys = $matches[2];

            $minx = min($xs);
            $maxx = max($xs);

            $miny = min($ys);
            $maxy = max($ys);
            
            $dx = ($maxx-$minx)/2+$minx-50;
            $dy = ($maxy-$miny)/2+$miny-50;

            // Now add a transform attribute:
            $dep->setAttribute('transform', "translate(-$dx,-$dy)");
        }

        $depdoc = $aDepSVG->getDocument();

        $map = $depdoc->getElementById('complete_map');

        $map->addChild($dep);

        $rect = new SVGRect(0, 0, 100, 100);
        $rect->setStyle("fill", "none");
        $map->addChild($rect);

        $tempSVGFilename = __DIR__ . "/../../temp/$depNumber.svg";
        file_put_contents( $tempSVGFilename, $aDepSVG->toXMLString());

        $destSVGFilename = __DIR__ . "/../../icomoon/svg/Departement-$depNumber.svg";
        @unlink($destSVGFilename);

        // Create a new svg with only the image, cropped to it's max dimmensions
        myExec ( 'inkscape --export-plain-svg="'.$destSVGFilename.'" --export-id="departement" "'.$tempSVGFilename.'"');
        
        // $destPNGFilename = outFolder . "Département $depNumber.png";
        // @unlink($destPNGFilename);
        // myExec ( 'inkscape --without-gui -w 385 --export-png="'.$destPNGFilename.'" --export-id="complete_map" "'.$tempSVGFilename.'"');
    }

    echo "Building small images for each departement - done\n";
}

function myExec($cmd)
{
    $output = array();
    $return_var = 0;

    exec ($cmd, $output, $return_var);

    if ($return_var !== 0)
    {
        throw new \RuntimeException("Could not run $cmd.");
    }    
}
