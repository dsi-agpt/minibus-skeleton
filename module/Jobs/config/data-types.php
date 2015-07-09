
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
                                'lastname' => 'Last Name',
                                'firstname' => 'First Name'
                            )
                            
                        )
                    ),
                    'sources' => array(
                        'dummyendpoint' => array(
                            'label' => 'Dummy Endpoint',
                            'control' => 'defaultProcessControl',
                            'dataTransferAgent' => 'acquisition-bar-dummyendpoint',
                            'locks' => array(
                                Jobs\Controller\Exclusion\Locks::BAR => LOCK_EX
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
                    'cibles' => array(
                        'fakeendpoint' => array(
                            'label' => 'FakeEndPoint',
                            'control' => 'defaultProcessControl',
                            'dataTransferAgent' => 'export-bar-fakeendpoint',
                            'locks' => array(
                                Jobs\Controller\Exclusion\Locks::BAR => LOCK_SH
                            ),
                            'options' => array(
                                'display_button' => array(
                                    'synchronize' => true,
                                    'resync' => true,
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
