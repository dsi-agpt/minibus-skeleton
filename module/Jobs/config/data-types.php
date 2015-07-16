
<?php
return array(
    'foo' => array(
        'label' => 'Foo',
        'children' => array(
            'bar' => array(
                'label' => 'Bar',
                'annualize' => false,
                'configuration' => array(
                    'browse' => array(
                        'general' => array(
                            'control' => 'defaultBrowseControl',
                            'datatable-formatter' => 'Jobs\Model\Browse\Formatters\Foo\Bar\DataTableFormatter',
                            'url' => 'rest/data/bar',
                            'columns' => array(
                                'title' => 'Title',
                                'record_label' => 'Record label',
                                'release_date' => 'Release date',
                                'primary_artist' => 'Primary artist'
                            )
                        )
                    ),
                    'sources' => array(
                        'dummy' => array(
                            'label' => 'Dummy Endpoint',
                            'process-description' => 'This process retrieves products containing word "piano" on ebay API and rejects those non belonging to Music/CD category',
                            'control' => 'defaultProcessControl',
                            'dataTransferAgent' => 'acquisition-bar-dummy',
                            'locks' => array(
                                Jobs\Controller\Exclusion\Locks::PRODUCTS => LOCK_EX
                            ),
                            'options' => array(
                                'display_button' => array(
                                    'synchronize' => true,
                                    'control' => false,
                                    'stop' => true,
                                    'clear' => true
                                )
                            )
                        )
                    ),
                    'targets' => array(
                        'fake' => array(
                            'label' => 'FakeEndPoint',
                            'process-description' => 'This process is not implemented, it is placed here as an illustration of how Minibus handles errors. ',
                            
                            'control' => 'defaultProcessControl',
                            'dataTransferAgent' => 'export-bar-fake',
                            'locks' => array(
                                Jobs\Controller\Exclusion\Locks::PRODUCTS => LOCK_SH
                            ),
                            'options' => array(
                                'display_button' => array(
                                    'synchronize' => true,
                                    'resync' => false,
                                    'control' => true,
                                    'stop' => true,
                                    'clear' => false
                                )
                            )
                        )
                    )
                )
            )
        )
    )
);
