<?php
// Desenvolvido por Joel - WHMCS.RED || Modificações de search inteligente feita por Luciano - WHMCS.RED
// Pegar Session
use WHMCS\Session;
// Pegar Conexão com Banco de Dados
use WHMCS\Database\Capsule;
// Bloqueia o acesso direto ao arquivo
if (!defined("WHMCS")){
	die("Acesso restrito!");
}
// Monta o PIN
function montar_pin($id){
	$limite = 6;
	$montar = md5($id);
	$montar = preg_replace("/[^0-9]/", "", $montar);
	$resultado = substr($montar, $limite, $limite);
	return $resultado;
}
// Página de Administrador
add_hook("AdminAreaClientSummaryPage", 1, function($vars){
	return "<div class='alert alert-success'><strong>PIN: ".montar_pin($vars["userid"])."</strong></div>";
});
// Página do Cliente
add_hook("ClientAreaHomepagePanels", 1, function($homePagePanels){
    $newPanel = $homePagePanels->addChild(
        "whmcsred-pin",
        array(
            "name" => "PIN",
            "label" => "PIN",
            "icon" => "fa-key",
            "order" => "99",
            "extras" => array(
                "color" => "green"
            )
        )
    );
    $newPanel->addChild(
        "whmcsred-pin-1",
        array(
            "label" => "<span style='font-size:15px;'>PIN para Atendimento: <strong><kbd style='background-color:#5cb85c;'>".montar_pin($_SESSION["uid"])."</kbd></strong><span>",
            "order" => 10
        )
    );
    $newPanel->addChild(
        "whmcsred-pin-2",
        array(
            "label" => "Você deverá informar esse PIN no Atendimento Online assim que for solicitado por um dos nossos atendentes.",
            "order" => 11
        )
    );
});
// Adicionando função de pesquisa do PIN
add_hook("IntelligentSearch", 1, function($vars){
	$pesquisa = array();
	foreach (capsule::table("tblclients")->get() as $clientes){
		$resultado = montar_pin($clientes->id);
		if($resultado == $vars["searchTerm"]){
			$idcliente = $clientes->id;
			$pin = $resultado;
		}
	}
	foreach (capsule::table("tblclients")->WHERE("id", $idcliente)->get() as $cliente){
		$pesquisa[] = '
		<div class="searchresult">
			<a href="clientssummary.php?userid='.$cliente->id.'">
				<strong>'.$cliente->firstname.' '.$cliente->lastname.'</strong>
				(PIN: '.$pin.')<br />
				<span class="desc">' . $cliente->email . '</span>
			</a>
		</div>';
	}
	return $pesquisa;
});
// Adiciona string para os templates de email
add_hook("EmailPreSend", 1, function($vars){
	$pinstring = array();
	$pinstring["pin"] = montar_pin($vars["userid"]);
	return $pinstring;
});

use WHMCS\View\Menu\Item as MenuItem;
use Illuminate\Database\Capsule\Manager as Capsule;

# Add Sidebar PIN Atendimento
add_hook('ClientAreaSecondarySidebar', 1, function(MenuItem $primarySidebar){
	

    $primarySidebar->addChild('Client-PINATD', array(
        'label' => "PIN Atendimento",
        'uri' => '#',
        'order' => '1',
        'icon' => 'fa-key'
    ));
    
    # Retrieve the panel we just created.
    $PinATDPanel = $primarySidebar->getChild('Client-PINATD');
    
    // Move the panel to the end of the sorting order so it's always displayed
    // as the last panel in the sidebar.
    $PinATDPanel->moveToBack();
    $PinATDPanel->setOrder(0);
    
    # Exibi o PIN.
    $balancePanel->addChild('pin-atd', array(
        'uri' => '#',
        'label' => '<h4 style="text-align:center;">'.montar_pin($_SESSION["uid"]).'</h4>',
        'order' => 1
    ));
    

});
