<?php
if (!isConnect('admin')) {
    throw new Exception('{{401 - Accès non autorisé}}');
}
// Déclaration des variables
$plugin = plugin::byId('velux');
sendVarToJS('eqType', $plugin->getId());
$hkEqWindows = velux::getHkEqLogics('Window');
$hkEq = [];
foreach (['Window', 'External Cover'] as $model) {
	$hkEq[$model] = [];
	foreach (velux::getHkEqLogics($model) as $hkEqLogic) {
		$hkEq[$model][] = [
			'id' => $hkEqLogic->getId(),
			'humanName' => $hkEqLogic->getHumanName(),
		];
	}
}
sendVarToJs('hkEq',$hkEq);
$eqLogics = eqLogic::byType($plugin->getId());
?>

<div class="row row-overflow">
    <!-- Page d'accueil du plugin -->
    <div class="col-xs-12 eqLogicThumbnailDisplay">
	<div class="row">
	    <div class="col-sm-10">
		<legend><i class="fas fa-cog"></i> {{Gestion}}</legend>
		<!-- Boutons de gestion du plugin -->
		<div class="eqLogicThumbnailContainer">
		    <div class="cursor eqLogicAction logoPrimary" data-action="add">
			<i class="fas fa-plus-circle"></i>
			<br>
			<span>{{Ajouter}}</span>
		    </div>
		    <div class="cursor eqLogicAction logoSecondary" data-action="gotoPluginConf">
			<i class="fas fa-wrench"></i>
			<br>
			<span>{{Configuration}}</span>
		    </div>
		</div>
	    </div>
	    <?php
	    // à conserver
	    // sera afficher uniquement si l'utilisateur est en version 4.4 ou supérieur
	    $jeedomVersion  = jeedom::version() ?? '0';
	    $displayInfoValue = version_compare($jeedomVersion, '4.4.0', '>=');
	    if ($displayInfoValue) {
	    ?>
		<div class="col-sm-2">
		    <legend><i class=" fas fa-comments"></i> {{Community}}</legend>
		    <div class="eqLogicThumbnailContainer">
			<div class="cursor eqLogicAction logoSecondary" data-action="createCommunityPost">
			    <i class="fas fa-ambulance"></i>
			    <br>
			    <span style="color:var(--txt-color)">{{Créer un post Community}}</span>
			</div>
		    </div>
		</div>
	    <?php
	    }
	    ?>
	</div>
	<legend><i class="fas fa-table"></i> {{Mes Velux}}</legend>
	<?php
	if (count($eqLogics) == 0) {
	    echo '<br><div class="text-center" style="font-size:1.2em;font-weight:bold;">{{Aucun équipement Template trouvé, cliquer sur "Ajouter" pour commencer}}</div>';
	} else {
	    // Champ de recherche
	    echo '<div class="input-group" style="margin:5px;">';
	    echo '<input class="form-control roundedLeft" placeholder="{{Rechercher}}" id="in_searchEqlogic">';
	    echo '<div class="input-group-btn">';
	    echo '<a id="bt_resetSearch" class="btn" style="width:30px"><i class="fas fa-times"></i></a>';
	    echo '<a class="btn roundedRight hidden" id="bt_pluginDisplayAsTable" data-coreSupport="1" data-state="0"><i class="fas fa-grip-lines"></i></a>';
	    echo '</div>';
	    echo '</div>';
	    // Liste des équipements du plugin
	    echo '<div class="eqLogicThumbnailContainer">';
	    foreach ($eqLogics as $eqLogic) {
		$opacity = ($eqLogic->getIsEnable()) ? '' : 'disableCard';
		echo '<div class="eqLogicDisplayCard cursor ' . $opacity . '" data-eqLogic_id="' . $eqLogic->getId() . '">';
		echo '<img src="' . $eqLogic->getImage() . '"/>';
		echo '<br>';
		echo '<span class="name">' . $eqLogic->getHumanName(true, true) . '</span>';
		echo '<span class="hiddenAsCard displayTableRight hidden">';
		echo ($eqLogic->getIsVisible() == 1) ? '<i class="fas fa-eye" title="{{Equipement visible}}"></i>' : '<i class="fas fa-eye-slash" title="{{Equipement non visible}}"></i>';
		echo '</span>';
		echo '</div>';
	    }
	    echo '</div>';
	}
	?>
    </div> <!-- /.eqLogicThumbnailDisplay -->

    <!-- Page de présentation de l'équipement -->
    <div class="col-xs-12 eqLogic" style="display: none;">
	<!-- barre de gestion de l'équipement -->
	<div class="input-group pull-right" style="display:inline-flex;">
	    <span class="input-group-btn">
		<!-- Les balises <a></a> sont volontairement fermées à la ligne suivante pour éviter les espaces entre les boutons. Ne pas modifier -->
		<a class="btn btn-sm btn-default eqLogicAction roundedLeft" data-action="configure"><i class="fas fa-cogs"></i><span class="hidden-xs"> {{Configuration avancée}}</span>
		</a><a class="btn btn-sm btn-default eqLogicAction" data-action="copy"><i class="fas fa-copy"></i><span class="hidden-xs"> {{Dupliquer}}</span>
		</a><a class="btn btn-sm btn-success eqLogicAction" data-action="save"><i class="fas fa-check-circle"></i> {{Sauvegarder}}
		</a><a class="btn btn-sm btn-danger eqLogicAction roundedRight" data-action="remove"><i class="fas fa-minus-circle"></i> {{Supprimer}}
		</a>
	    </span>
	</div>
	<!-- Onglets -->
	<ul class="nav nav-tabs" role="tablist">
	    <li role="presentation"><a href="#" class="eqLogicAction" aria-controls="home" role="tab" data-toggle="tab" data-action="returnToThumbnailDisplay"><i class="fas fa-arrow-circle-left"></i></a></li>
	    <li role="presentation" class="active"><a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-tachometer-alt"></i> {{Equipement}}</a></li>
	    <li role="presentation"><a href="#commandtab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-list"></i> {{Commandes}}</a></li>
	</ul>
	<div class="tab-content">
	    <!-- Onglet de configuration de l'équipement -->
	    <div role="tabpanel" class="tab-pane active" id="eqlogictab">
		<!-- Partie gauche de l'onglet "Equipements" -->
		<!-- Paramètres généraux et spécifiques de l'équipement -->
		<form class="form-horizontal">
		    <fieldset>
			<div class="col-lg-6">
			    <legend><i class="fas fa-wrench"></i> {{Paramètres généraux}}</legend>
			    <div class="form-group">
				<label class="col-sm-4 control-label">{{Nom de l'équipement}}</label>
				<div class="col-sm-6">
				    <input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display:none;">
				    <input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom de l'équipement}}">
				</div>
			    </div>
			    <div class="form-group">
				<label class="col-sm-4 control-label">{{Objet parent}}</label>
				<div class="col-sm-6">
				    <select id="sel_object" class="eqLogicAttr form-control" data-l1key="object_id">
					<option value="">{{Aucun}}</option>
					<?php
					$options = '';
					foreach ((jeeObject::buildTree(null, false)) as $object) {
					    $options .= '<option value="' . $object->getId() . '">' . str_repeat('&nbsp;&nbsp;', $object->getConfiguration('parentNumber')) . $object->getName() . '</option>';
					}
					echo $options;
					?>
				    </select>
				</div>
			    </div>
			    <div class="form-group">
				<label class="col-sm-4 control-label">{{Catégorie}}</label>
				<div class="col-sm-6">
				    <?php
				    foreach (jeedom::getConfiguration('eqLogic:category') as $key => $value) {
					echo '<label class="checkbox-inline">';
					echo '<input type="checkbox" class="eqLogicAttr" data-l1key="category" data-l2key="' . $key . '" >' . $value['name'];
					echo '</label>';
				    }
				    ?>
				</div>
			    </div>
			    <div class="form-group">
				<label class="col-sm-4 control-label">{{Options}}</label>
				<div class="col-sm-6">
				    <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked>{{Activer}}</label>
				    <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked>{{Visible}}</label>
				</div>
			    </div>

			    <legend><i class="fas fa-cogs"></i> {{Equipement Homekit associés}}</legend>
			    <div class="form-group">
				<label class="col-sm-4 control-label">{{Fenêtre}}
				    <sup><i class="fas fa-question-circle tooltips" title="{{Fenêtre du plugin Homekit}}"></i></sup>
				</label>
				<div class="col-sm-6">
				    <div class="input-group">
				        <input id="hkWindow" class="eqLogicAttr form-control roundedLeft disabled" data-l1key="configuration" data-l2key="w:hkId">
				    	<a id="selectWindow" class="btn btn-default input-group-addon roundedRight" title="{{Sélection d'une fenêtre}}"><i class="fas fa-list-alt"></i></a>
				    </div>
				</div>
			    </div>
			    <div class="form-group">
				<label class="col-sm-4 control-label">{{Volet roulant}}
				    <sup><i class="fas fa-question-circle tooltips" title="{{Volet roulant du plugin Homekit}}"></i></sup>
				</label>
				<div class="col-sm-6">
				    <div class="input-group">
				        <input id="hkStore" class="eqLogicAttr form-control roundedLeft disabled" data-l1key="configuration" data-l2key="s:hkId">
				    	<a id="selectStore" class="btn btn-default input-group-addon roundedRight" title="{{Sélection d'un volet roulant}}"><i class="fas fa-list-alt"></i></a>
				    </div>
				</div>
			    </div>
			    <legend><i class="fas fa-cogs"></i> {{Positions limites}}</legend>
			    <div class="form-group">
				<label class="col-sm-4 control-label">{{Position ventilation}}
				    <sup><i class="fas fa-question-circle tooltips" title="{{Position de la fenêtre au delà de laquelle le volet roulant<br>ne peux pas être actionné dans la partie inférieure.}}"></i></sup>
				</label>
				<div class="col-sm-6">
				    <input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="windowsLimit" placeHolder="7">
				</div>
			    </div>
			    <div class="form-group">
				<label class="col-sm-4 control-label">{{Limite Volet Roulant}}
				    <sup><i class="fas fa-question-circle tooltips" title="{{Limite de mouvement du volet roulant<br>losrque la fenêtre est ouverte.}}"></i></sup>
				</label>
				<div class="col-sm-6">
				    <input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="shuttersLimit" placeHolder="55">
				</div>
			    </div>
			</div>

			<!-- Partie droite de l'onglet "Équipement" -->
			<!-- Affiche un champ de commentaire par défaut mais vous pouvez y mettre ce que vous voulez -->
			<div class="col-lg-6">
			    <legend><i class="fas fa-info"></i> {{Informations}}</legend>
			    <div class="form-group">
				<label class="col-sm-4 control-label">{{Description}}</label>
				<div class="col-sm-6">
				    <textarea class="form-control eqLogicAttr autogrow" data-l1key="comment"></textarea>
				</div>
			    </div>
			</div>
		    </fieldset>
		</form>
	    </div><!-- /.tabpanel #eqlogictab-->

	    <!-- Onglet des commandes de l'équipement -->
	    <div role="tabpanel" class="tab-pane" id="commandtab">
		<a class="btn btn-default btn-sm pull-right cmdAction" data-action="add_target" style="margin-top:5px;">
			<i class="fas fa-plus-circle"></i>
			{{Ajouter une commande "cible"}}
		</a>
		<br>
		<br>
		<div class="table-responsive">
		    <table id="table_cmd" class="table table-bordered table-condensed">
			<thead>
			    <tr>
				<th class="hidden-xs" style="min-width:50px;width:70px;">ID</th>
				<th style="min-width:200px;width:240px;">{{Nom}}</th>
				<th style="width:80px">{{Type}}</th>
				<th style="width:115px">{{LogicalId}}</th>
				<th>{{Paramètres}}</th>
				<th style="width:260px;">{{Options}}</th>
				<th>{{Etat}}</th>
				<th style="min-width:80px;width:130px;">{{Actions}}</th>
			    </tr>
			</thead>
			<tbody>
			</tbody>
		    </table>
		</div>
	    </div><!-- /.tabpanel #commandtab-->

	</div><!-- /.tab-content -->
    </div><!-- /.eqLogic -->
</div><!-- /.row row-overflow -->

<!-- Inclusion du fichier javascript du plugin (dossier, nom_du_fichier, extension_du_fichier, id_du_plugin) -->
<?php include_file('desktop', 'velux', 'js', 'velux'); ?>
<?php include_file('desktop', 'velux', 'css', 'velux'); ?>
<!-- Inclusion du fichier javascript du core - NE PAS MODIFIER NI SUPPRIMER -->
<?php include_file('core', 'plugin.template', 'js'); ?>
