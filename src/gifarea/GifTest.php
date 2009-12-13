<?php
require_once "./lib/jpgraph/src/jpgraph.php";

require_once "./lib/jpgraph/src/jpgraph_utils.inc";
require_once "./lib/jpgraph/src/jpgraph_canvas.php";

// Setup a basic canvas we can work
$g = new CanvasGraph (10,10, 'auto');
$g->SetMargin( 1,2,1 ,2);
$g->img->SetTransparent("white");
$g->img->SetColor("blue");
$g->img->Rectangle(0,0,5,5);

// Setup a basic canvas we can work
$g1 = new CanvasGraph (30,30, 'auto');
$g1->SetMargin( 1,2,1 ,2);
$g1->initFrame();
$g1->img->SetTransparent("white");
$g1->img->SetColor("yellow");
$g1->img->FilledRectangle(10,10,20,20);


// Setup a basic canvas we can work
$g2 = new CanvasGraph (30,30, 'auto');
$g2->SetMargin( 1,2,1 ,2);
$g2->initFrame();
$g2->img->SetTransparent("white");
$g2->img->SetColor("red");
$g2->img->FilledRectangle(20,20,30,30);




// We need to stroke the plotarea and margin before we add the
// text since we otherwise would overwrite the text.

// Draw a text box in the middle
$txt="This is a TEXT!!!";
$t = new Text( $txt,0,0 );
$t->SetFont( FF_ARIAL, FS_BOLD,40);
// How should the text box interpret the coordinates?
$t->Align( 'left','top');

// How should the paragraph be aligned?
$t->ParagraphAlign( 'left');

// Add a box around the text, white fill, black border and gray shadow
//$t->SetBox( "white", "black","gray");

// Stroke the text
//$t->Stroke( $g->img);

//$basegif->Add($t);

$mgraph = new MGraph(500,500);
$mgraph->SetFillColor('orange');
$mgraph->Add($g,0,0);
$mgraph->Add($g1,0,0);
$mgraph->Add($g2,0,0);
$mgraph->Stroke();

?>