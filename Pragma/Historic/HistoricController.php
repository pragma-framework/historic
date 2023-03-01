<?php
/**
 * Management controller for classes using the Historisable feature
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
     * Route to clear part or all of the history
	 *
     * @return void
     */
    public static function clean(): void
    {
        TaskLock::check_lock(realpath('.') . '/locks', 'historic-clean');

        $options = \Pragma\Router\Request::getRequest()->parse_params(false);
        if (isset($options['d']) || isset($options['days'])) {
            $days = abs((int)($options['d'] ?? $options['days']));

			if (empty($days) || $days === 0) {
				$days = '';
			}
        } else {
			echo 'Number of days of history to keep (leave blank to delete all history) : ';
			$days = rtrim(fgets(STDIN));
		}

		echo 'You wish to delete all history' . (empty($days) ? '.' : " prior to the last $days days.");
		echo PHP_EOL;

		$go = '';
		if (isset($options['s']) || isset($options['skip-confirm'])) {
			$go = 'Y';
		}

		while (!in_array($go, ['Y', 'N']))  {
			echo 'Do you want to continue ? [Y/n] ';
			$go = strtoupper(rtrim(fgets(STDIN)));
			if ($go === '') {
				$go = 'Y';
			}
		}

		if ($go === 'Y') {
			echo 'Start of the cleaning...' . PHP_EOL;
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
					    INNER JOIN $actionsTableName on {$referencesTableName}.action_id = {$actionsTableName}.id
					$where
				", $params);
				echo "Number of rows $referencesTableName deleted : " . $statement->rowCount() . PHP_EOL;

				$statement = $db->query("
					DELETE $changesTableName
					FROM $changesTableName
					    INNER JOIN $actionsTableName on {$changesTableName}.action_id = {$actionsTableName}.id
					$where
				", $params);
				echo "Number of rows $changesTableName deleted : " . $statement->rowCount() . PHP_EOL;

				$statement = $db->query("
					DELETE FROM $actionsTableName 
					$where
				", $params);
				echo "Number of rows $actionsTableName deleted : " . $statement->rowCount() . PHP_EOL;

				echo 'Cleaning successfully completed' . PHP_EOL;

			} catch (\DBException $e) {
				print_r("DBException : " . $e->getCode() . ' - ' . $e->getMessage() . PHP_EOL);
			}
		} else {
			echo 'Abandoned history cleanup process' . PHP_EOL;
		}

        TaskLock::flush(realpath('.') . '/locks', 'historic-clean');
    }
}
