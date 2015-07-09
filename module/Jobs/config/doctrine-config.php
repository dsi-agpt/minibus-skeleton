<?php
return array(
    'driver' => array(
        // defines an annotation driver with two paths, and names it `my_annotation_driver`
        'minibus_annotation_driver' => array(
            'paths' => array(
                __DIR__ . '/../../../module/Jobs/src/Jobs/Model/Entity'
            )
        ),
        // default metadata driver, aggregates all other drivers into a single one.
        // Override `orm_default` only if you know what you're doing
        'orm_default' => array(
            'drivers' => array(
                'Jobs' => 'minibus_annotation_driver'
            )
        )
    )
);