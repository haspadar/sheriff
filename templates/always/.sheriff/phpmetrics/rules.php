<?php

return [
    'complexity' => [
        'max_cyclomatic_per_method' => {% IntText(phpmetrics.complexity.max_cyclomatic_per_method) %},
        'max_weighted_methods_per_class' => {% IntText(phpmetrics.complexity.max_weighted_methods_per_class) %},
    ],
    'size' => [
        'max_loc_per_class' => {% IntText(phpmetrics.size.max_loc_per_class) %},
        'max_logical_loc_per_class' => {% IntText(phpmetrics.size.max_logical_loc_per_class) %},
        'max_logical_loc_per_method' => {% IntText(phpmetrics.size.max_logical_loc_per_method) %},
    ],
    'halstead' => [
        'max_volume_per_method' => {% IntText(phpmetrics.halstead.max_volume_per_method) %},
        'max_difficulty_per_method' => {% IntText(phpmetrics.halstead.max_difficulty_per_method) %},
        'max_effort_per_method' => {% IntText(phpmetrics.halstead.max_effort_per_method) %},
        'max_bugs_per_method' => {% FloatText(phpmetrics.halstead.max_bugs_per_method) %},
    ],
    'inheritance' => [
        'max_depth' => {% IntText(phpmetrics.inheritance.max_depth) %},
    ],
    'structure' => [
        'max_methods_per_class' => {% IntText(phpmetrics.structure.max_methods_per_class) %},
    ],
    'coupling' => [
        'max_afferent' => {% IntText(phpmetrics.coupling.max_afferent) %},
        'max_efferent' => {% IntText(phpmetrics.coupling.max_efferent) %},
    ],
];
