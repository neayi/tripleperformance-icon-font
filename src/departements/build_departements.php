<?php


require __DIR__ . '/../vendor/autoload.php';
include_once(__DIR__ . '/../includes/wikibuilder.php');

use SVG\SVG;
use SVG\Writing\SVGWriter;
use SVG\Nodes\Shapes\SVGRect;

define('outFolder', dirname(__DIR__) . '/out/departements/');
define('tempFolder', dirname(__DIR__) . '/temp/departements/');
define('resourceFolder', dirname(__DIR__) . '/resources/departements/');
define('force', false);

try {

    makeFolders();
    makeSmallDepartements();
    makeFranceDepartements();
    makeWiki();

} catch (\Throwable $th) {
	echo 'Exception: ',  $th->getMessage(), "\n";
	echo "Stopping build.\n\n";
	exit(1); // Important: throw an error in order to stop the build
}


function makeSmallDepartements()
{
    echo "Building small images for each departement\n";

    // image with 100x100 viewport
    $image = SVG::fromFile(resourceFolder . 'departements.svg');

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

        $aDepSVG = SVG::fromFile(resourceFolder . 'empty.svg');
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

        $tempSVGFilename = tempFolder . "$depNumber.svg";
        $destPNGFilename = outFolder . "Département $depNumber.png";
        $destSVGFilename = outFolder . "Departement-$depNumber.svg";

        if (file_exists($destPNGFilename) && !force)
            continue;

        @unlink($tempSVGFilename);
        @unlink($destPNGFilename);
        @unlink($destSVGFilename);

        file_put_contents( $tempSVGFilename, $aDepSVG->toXMLString());
        
//        myExec ( 'inkscape --without-gui -w 385 --export-png="'.$destPNGFilename.'" --export-id="complete_map" "'.$tempSVGFilename.'"');
        myExec ( 'inkscape --without-gui --export-plain-svg="'.$destSVGFilename.'" --export-id="departement" "'.$tempSVGFilename.'"');
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

function makeFranceDepartements()
{
    echo "Building a larger image with the French climate for each departement\n";

    // image with 100x100 viewport
    $image = SVG::fromFile(resourceFolder . 'departements-climat.svg');

    $doc = $image->getDocument();

    $departements = $doc->getElementsByClassName('departement');

    foreach ($departements as $dep)
    {
        $classnames = $dep->getAttribute('class');
        
        $matches = array();
        if (!preg_match('@departement([0-9ab]+)@', $classnames, $matches))
            continue;
            
        $depNumber = $matches[1];
            
        createNewFranceMap($depNumber);
    }

    echo "Building a larger image with the French climate for each departement - done\n";
}

function createNewFranceMap($depNumber)
{
    $srcFilename = resourceFolder . 'departements-climat.svg';
    $tempFilename = tempFolder . 'France-Climat-Departement-'.strtoupper($depNumber).'.svg';
    $destFilename = outFolder . 'France-Climat-Departement-'.strtoupper($depNumber).'.png';

    if (file_exists($destFilename) && !force)
        return;

    @unlink($destSVGFilename);

    copy($srcFilename, $tempFilename);

    $image = SVG::fromFile($tempFilename);

    $doc = $image->getDocument();

    $departements = $doc->getElementsByClassName('departement' . $depNumber);
    foreach ($departements as $dep)
    {
        $classnames = $dep->getAttribute('class');

        $classnames .= " highlighted";

        $dep->setAttribute('class', $classnames);

        break;
    }

    file_put_contents($tempFilename, $image->toXMLString());

   // myExec ( 'inkscape --without-gui -w 800 --export-png="'.$destFilename.'" --export-id="complete_map" "'.$tempFilename.'"');
}

function makeFolders()
{
    if (!is_dir(outFolder) && !mkdir(outFolder, 0777, true))
    {
        throw new \RuntimeException("Could not create dir " . outFolder);
    }
    
    if (!is_dir(tempFolder) && !mkdir(tempFolder, 0777, true))
    {
        throw new \RuntimeException("Could not create dir " . tempFolder);
    }            
}

/**
 * 
 */
function makeWiki()
{
    // See https://docs.google.com/spreadsheets/d/1FYgqCwiM2CVvnpULBooeMcb2oJdcHovt4vmVHMEy9o4/edit#gid=43686096 for original
    $srcFilename = resourceFolder . 'Départements.txt';
    $destFilename = outFolder . 'wiki_departements.xml';

    if (file_exists($destFilename))
        unlink($destFilename);
    
    $wikiBuilder = new wikiImportFile($destFilename);
    
    $deps = file($srcFilename);

    foreach ($deps as $depString)
    {
        $data = explode("\t", $depString);

        if (count($data) < 13)
            continue;

        list($numero, $nom, $nomComplet, $chambreagri, 
             $region, $coderegionAgreste, $chefLieu, $superficie, $population,
             $climat, $latitude, $longitude, $production) = $data;

        $icone = "Département $numero.png";
        $cartefrance = "France Climat Département $numero.png";

        if (!preg_match('/[0-9].$/', $numero))
            continue;

        $page = $wikiBuilder->addPage("$nom (département)");

        $page->addContent("{{Département
| Numéro = $numero
| Nom = $nom
| Nom complet = $nomComplet
| Icone = $icone
| Carte France = $cartefrance
| Chambre agriculture = $chambreagri
| Région = $region
| Chef lieu = $chefLieu
| Superficie = $superficie
| Population = $population
| Climat = $climat
| Longitude = $longitude
| Latitude = $latitude
}}");

        $page->close();

        $wikiBuilder->addRedirect("$nom ($numero)", "$nom (département)");
        $wikiBuilder->addRedirect("Département $numero", "$nom (département)");
    }

    $wikiBuilder->close();
}
