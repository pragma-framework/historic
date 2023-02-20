<?php
/**
 * Controller de gestion pour les classes utilisant le trait Historisable
 *
 * @package Pragma\Historic
 */

namespace Pragma\Historic;

use Pragma\DB\DB;
use Pragma\Exceptions\DBException;
use Pragma\Helpers\TaskLock;

class HistoricController
{
    /**
     * Route pour vider une partie ou tout l'historique
	 *
     * @return void
     */
    public static function purge(): void
    {
        TaskLock::check_lock(realpath('.') . '/locks', 'historic-purge');

        $options = \Pragma\Router\Request::getRequest()->parse_params(false);
        if (isset($options['d']) || isset($options['days'])) {
            $days = abs((int)($options['d'] ?? $options['days']));

			if (empty($days) || $days === 0) {
				$days = '';
			}
        } else {
			echo 'Nombre de jours d\'historique à conserver (laissez vide pour suprimer tous l\'historique) : ';
			$days = rtrim(fgets(STDIN));
		}

		echo 'Vous souhaitez supprimer tout l\'historique' . (empty($days) ? '.' : " antérieur aux $days derniers jours.");
		echo PHP_EOL;

		$go = '';
		if (isset($options['s']) || isset($options['skip-confirm'])) {
			$go = 'O';
		}

		while (!in_array($go, ['O', 'N']))  {
			echo 'Souhaitez-vous continuer ? [O/n] ';
			$go = strtoupper(rtrim(fgets(STDIN)));
			if ($go === '') {
				$go = 'O';
			}
		}

		if ($go === 'O') {
			echo 'Début du nettoyage...' . PHP_EOL;
			try {
				$db = DB::getDB();

				$actionsTableName = Action::getTableName();
				$changesTableName = Change::getTableName();
				$referencesTableName = Reference::getTableName();

				$where = '';
				$params = [];
				if (!empty($days)) {
					$date = new \DateTime();
					$date->sub(new \DateInterval("P{$days}D"));
					$where = "WHERE {$actionsTableName}.created_at < ?";
					$params[] = $date->format('Y-m-d');
				}

				$statement = $db->query("
					DELETE $referencesTableName
					FROM $referencesTableName
					    LEFT JOIN $actionsTableName on {$referencesTableName}.action_id = {$actionsTableName}.id
					$where
				", $params);
				echo "Nombre de lignes $referencesTableName supprimées : " . $statement->rowCount() . PHP_EOL;

				$statement = $db->query("
					DELETE $changesTableName
					FROM $changesTableName
					    LEFT JOIN $actionsTableName on {$changesTableName}.action_id = {$actionsTableName}.id
					$where
				", $params);
				echo "Nombre de lignes $changesTableName supprimées : " . $statement->rowCount() . PHP_EOL;

				$statement = $db->query("
					DELETE FROM $actionsTableName 
					$where
				", $params);
				echo "Nombre de lignes $actionsTableName supprimées : " . $statement->rowCount() . PHP_EOL;

				echo 'Nettoyage terminé avec succès' . PHP_EOL;

			} catch (\DBException $e) {
				print_r("DBException : " . $e->getCode() . ' - ' . $e->getMessage() . PHP_EOL);
			}
		} else {
			echo 'Processus de nettoyage de l\'historique abandonné' . PHP_EOL;
		}

        TaskLock::flush(realpath('.') . '/locks', 'historic-purge');
    }
}
