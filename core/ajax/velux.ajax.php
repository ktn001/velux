<?php
/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

try {
    require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
    include_file('core', 'authentification', 'php');

    if (!isConnect('admin')) {
        throw new Exception(__('401 - Accès non autorisé', __FILE__));
    }

  /* Fonction permettant l'envoi de l'entête 'Content-Type: application/json'
    En V3 : indiquer l'argument 'true' pour contrôler le token d'accès Jeedom
    En V4 : autoriser l'exécution d'une méthode 'action' en GET en indiquant le(s) nom(s) de(s) action(s) dans un tableau en argument
  */
    ajax::init();

    $action = init('action');

    if ($action == 'getCmdAssociationPropositions') {
	$hkEq_id = init('hkEq_id');
	ajax::success(json_encode(velux::getCmdAssociationPropositions($hkEq_id)));
    }

    if ($action == 'saveHkCmdSelections') {
	$hkEq_id = init('hkEq_id');
	$values = init('values');
	config::save('hkCmds_' . $hkEq_id, $values, 'velux');
	ajax::success();
    }

    if ($action == 'getCmdConfigs') {
	$cmdFile = __DIR__ . '/../config/cmds.json';
	$configs = json_decode(file_get_contents($cmdFile),true);
	foreach (array_keys($configs) as $logicalId) {
		$configs[$logicalId]['name']=translate::exec($configs[$logicalId]['name'],$cmdFile);
		$configs[$logicalId]['logicalId'] = $logicalId;
	}
	ajax::success(json_encode($configs));
    }

    throw new Exception(__('Aucune méthode correspondante à', __FILE__) . ' : ' . init('action'));
    /*     * *********Catch exeption*************** */
}
catch (Exception $e) {
    ajax::error(displayException($e), $e->getCode());
}
