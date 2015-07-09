 <?php
return array(
    'dummyendpoint' => array(
        'type' => \Minibus\Controller\Process\Service\Connection\EndpointConnectionBuilder::DATABASE_TYPE,
        'params' => array(
            'driver' => 'pgsql'
        )
    ),
    
    'fakeendpoint' => array(
        'type' => \Minibus\Controller\Process\Service\Connection\EndpointConnectionBuilder::DATABASE_TYPE,
        'params' => array(
            'driver' => 'mysql'
        )
    )
)
;
			