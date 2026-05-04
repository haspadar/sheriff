<?php

return [
    'complexity' => [
        'max_cyclomatic_per_method' => << config(phpmetrics.complexity.max_cyclomatic_per_method)|join("") >>,
        'max_weighted_methods_per_class' => << config(phpmetrics.complexity.max_weighted_methods_per_class)|join("") >>,
    ],
    'size' => [
        'max_loc_per_class' => << config(phpmetrics.size.max_loc_per_class)|join("") >>,
        'max_logical_loc_per_class' => << config(phpmetrics.size.max_logical_loc_per_class)|join("") >>,
        'max_logical_loc_per_method' => << config(phpmetrics.size.max_logical_loc_per_method)|join("") >>,
    ],
    'halstead' => [
        'max_volume_per_method' => << config(phpmetrics.halstead.max_volume_per_method)|join("") >>,
        'max_difficulty_per_method' => << config(phpmetrics.halstead.max_difficulty_per_method)|join("") >>,
        'max_effort_per_method' => << config(phpmetrics.halstead.max_effort_per_method)|join("") >>,
        'max_bugs_per_method' => << config(phpmetrics.halstead.max_bugs_per_method)|join("") >>,
    ],
    'inheritance' => [
        'max_depth' => << config(phpmetrics.inheritance.max_depth)|join("") >>,
    ],
    'structure' => [
        'max_methods_per_class' => << config(phpmetrics.structure.max_methods_per_class)|join("") >>,
    ],
    'coupling' => [
        'max_afferent' => << config(phpmetrics.coupling.max_afferent)|join("") >>,
        'max_efferent' => << config(phpmetrics.coupling.max_efferent)|join("") >>,
    ],
];
