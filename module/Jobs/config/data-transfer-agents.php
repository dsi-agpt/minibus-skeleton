
<?php
return array(
    'acquisition-bar-dummy' => array(
        'class' => 'Jobs\Model\Process\DataTransfer\Acquisition\Foo\Bar\Dummy\TransferAgent',
        'converter' => 'Jobs\Model\Process\DataTransfer\Acquisition\Foo\Bar\Dummy\Converter'
    ),
    'export-bar-fake' => array(
        'class' => 'Jobs\Model\Process\DataTransfer\Export\Foo\Bar\Fake\TransferAgent',
        'converter' => 'Jobs\Model\Process\DataTransfer\Export\Foo\Bar\Fake\Converter'
    )
);
