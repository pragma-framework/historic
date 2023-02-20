<?php

namespace Pragma\Historic;

class Module
{
    /**
     * Renvoi les routes CLI du module Historic
     * @return array
     */
    public static function getDescription(): array
    {
        return [
            "Pragma-Framework/Historic",
            [
                "index.php historic:purge\tRoute used to empty all or part of the history",
                "\t-d|--days=[nb_days]\tNumber of days of history to keep",
                "\t-s|--skip-confirm\tSkip confirmation (useful with crons)",
            ],
        ];
    }
}
