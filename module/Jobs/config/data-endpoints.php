 <?php
return array(
    'dummy' => array(
        'type' => \Minibus\Controller\Process\Service\Connection\EndpointConnectionBuilder::WEB_SERVICE_TYPE,
        'params' => array()
    ),
    
    'fake' => array(
        'type' => \Minibus\Controller\Process\Service\Connection\EndpointConnectionBuilder::DATABASE_TYPE,
        'params' => array(
            'driver' => 'mysql'
        )
    )
);
			