## HeadingTriple Performance Icon font
This project is dedicated at providing the Triple Performance icon font.
Do not hesitate to use it if you have a need.

The public URL for the font is: 
https://neayi.github.io/tripleperformance-icon-font/style.css

To use, just add the following code to your page:

    <link rel="stylesheet" href="[style.css](https://neayi.github.io/tripleperformance-icon-font/style.css)"></head>

https://neayi.github.io/tripleperformance-icon-font/demo.html

## Adding icons to the font
The icons are designed in AI files. Just add icons in the file and export them to `\profile_icons\svg_output_from_ai` (as single SVG assets). You may need to add a transparent circle around your icon to make sure the relative size of the icon fits with the others (see existing icons).
If you want your icon to be customizable in terms of color, you need to make sure the fill or stroke color is either #15a072, #414a64 or #404964.
After your icons are exported, please run:

    php src/profile_icons/stripSvgColors.php
This will copy each SVG to `icomoon\svg` which is the source for icomoon. The script will rename each file so that it can be safely used as a classname, and remove all fill information from within the 3 colors further up.
Once your new icons are in the `icomoon/svg` folder, you are ready to upload them to your icomoon project. The JSON source for the project is in icomoon (you might need to upload it back before). Then add the icons and download the iconfont in the docs folder. Don't forget to download and commit the new JSON file for the project too.

## Départements Français
The font also includes a list of French departements, that are generated from the script in the `src/departements` folder. You shouldn't need to rebuild them but in case the script just creates a new SVG file for each departement in the icomoon folder.

## Sources icons
Most icons come from [Nounproject](https://thenounproject.com/).
