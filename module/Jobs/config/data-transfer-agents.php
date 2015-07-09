
<?php
return array(
    'acquisition-bar-dummyendpoint' => array(
        'class' => 'Jobs\Model\Process\DataTransfer\Acquisition\Foo\Bar\DummyEndpoint\TransferAgent',
        'converter' => 'Jobs\Model\Process\DataTransfer\Acquisition\Foo\Bar\DummyEndpoint\Converter'
    ),
    'export-bar-fakeendpoint' => array(
        'class' => 'Jobs\Model\Process\DataTransfer\Export\Foo\Bar\FakeEndpoint\TransferAgent',
        'converter' => 'Jobs\Model\Process\DataTransfer\Export\Foo\Bar\FakeEndpoint\Converter'
    )
);
