<?php
namespace Jobs\Model\Process\DataTransfer\Acquisition\Foo\Bar\Dummy;

use Minibus\Model\Process\DataTransfer\Acquisition\AbstractDataAcquisitionAgent;
use Minibus\Model\Entity\Execution;
use Minibus\Controller\Process\Exception\ProcessException;
use Jobs\Model\Entity\Record;

class TransferAgent extends AbstractDataAcquisitionAgent
{

    private $converter;

    private function launchConversionErrors()
    {
        if (! $this->converter->hasErrors())
            return;
        foreach ($this->converter->getErrors() as $id => $errors) {
            foreach ($errors as $error) {
                $this->alertError($error, $id);
            }
        }
    }

    public function run()
    {
        switch ($this->getExecutionMode()) {
            case 'sync':
                $this->runSync();
                break;
            case 'clear':
                $this->runClear();
                break;
            default:
                $this->getLogger()->info(sprintf("Executio mode %s is not implemented", $this->getExecutionMode()));
        }
    }

    private function runClear()
    {
        $this->getLogger()->info("All registered records will be erased");
        $records = $this->getRecordRepository()->findAll();
        $nbRecords = count($records);
        if (0 === $nbRecords) {
            $message = "There is no record to delete.";
            $this->logWarn($message);
            $this->setAlive(false);
            return;
        }
        $nbRecordsDeleted = 0;
        
        foreach ($records as $record) {
            $this->getLogger()->info("Removal of record {$record->getProductId()} ");
            $this->getEntityManager()->remove($record);
            try {
                $this->getEntityManager()->flush($record);
                
            } catch (\Exception $e) {
                $message = "Failed to delete record {$record->getProductId()} :{$e->getMessage()} ";
                $this->logError($message);
                $this->alertError($message);
                $this->setAlive(false);
                return;
            }
            $nbRecordsDeleted ++;
            $this->setAlive(true);
            sleep(0.5);
        }
        
        $this->getLogger()->info("Total number of records : " . $nbRecords);
        $this->getLogger()->info("Number of deleted records : " . $nbRecordsDeleted);
        $this->getLogger()->info("Run record acquisition again.");
        $this->getLogger()->info(" Processus ended.");
        $this->setAlive(false);
    }

    public function runSync()
    {
        $this->getLogger()->info("Begin of process : look for clarinets for sale");
        
        $this->setAlive(true);
        $this->converter = $this->getConverter();
        $client = $this->getEndPointConnection();
        $this->getLogger()->info("Call ws");
        $clarinets = $client->get("");
        $response = $client->getLastResponse();
        if ($response->isSuccess()) {
            
            $products = $clarinets['Product'];
            foreach ($products as $product) {
                $domainName = array_key_exists('DomainName', $product) ? $product['DomainName'] : "no-category";
                if ($domainName != 'Music: CDs') {
                    $message = sprintf("We are not intersted in products of category %s", $domainName);
                    $this->getLogger()->warn($message);
                    $this->alertWarn($message);
                    continue;
                }
                
                $productId = null;
                if (isset($product['ProductID'])) {
                    $productIds = $product['ProductID'];
                    if (is_array($productIds)) {
                        foreach ($productIds as $entry) {
                            if (isset($entry["Type"]) && $entry["Type"] == "Reference" && isset($entry["Value"]))
                                $productId = $entry["Value"];
                        }
                    }
                }
                if (empty($productId)) {
                    $this->logWarn("This product has no productId.");
                    $this->alertWarn("Ther is a product without productId, unable to register it.");
                    continue;
                }
                $title = array_key_exists('Title', $product) ? $product['Title'] : "";
                if (empty($title)) {
                    $message = sprintf("The product with id %s has no title , unable to register it", $productId);
                    $this->logWarn($message);
                    $this->alertWarn($message);
                    continue;
                }
                $primaryArtist = "no-artist";
                $recordLabel = "no-label";
                $releaseDate = "no-date";
                if (isset($product['ItemSpecifics']['NameValueList'])) {
                    $nameValueList = $product['ItemSpecifics']['NameValueList'];
                    if (is_array($nameValueList)) {
                        foreach ($nameValueList as $entry) {
                            if (isset($entry["Name"]) && $entry["Name"] == "Primary Artist" && isset($entry["Value"]) && is_array($entry["Value"]))
                                $primaryArtist = $entry["Value"][0];
                            if (isset($entry["Name"]) && $entry["Name"] == "Record Label" && isset($entry["Value"]) && is_array($entry["Value"]))
                                $recordLabel = $entry["Value"][0];
                            if (isset($entry["Name"]) && $entry["Name"] == "Release Date" && isset($entry["Value"]) && is_array($entry["Value"]))
                                $releaseDate = $entry["Value"][0];
                        }
                    }
                }
                
                $record = $this->getEntityManager()
                    ->getRepository("Jobs\Model\Entity\Record")
                    ->findOneBy(array(
                    "product_id" => $productId
                ));
                if (! is_null($record))
                    $message = sprintf("Product with id %s yet registered in database", $productId);
                else {
                    $record = new Record();
                    $message = sprintf("Product with id %s not yet registered in database, creating new one", $productId);
                }
                $this->logInfo($message);
                $record->setTitle($title);
                $record->setRecordLabel($recordLabel);
                $record->setReleaseDate($releaseDate);
                $record->setPrimaryArtist($primaryArtist);
                $record->setProductId($productId);
                try {
                    $this->getEntityManager()->persist($record);
                    $this->getEntityManager()->flush($record);
                } catch (\Exception $e) {
                    $message = sprintf("Impossible to save one of the records with message %s", $e->getMessage());
                    $this->logError($message);
                    $this->setAlive(false);
                    return;
                }
                $this->setAlive(true);
                sleep(0.5);
            }
        } else {
            $code = $client->getLastResponse()->getStatusCode();
            
            $message = sprintf("Impossible to connect to REST json web service with HTTP code %s", $code);
            $this->getLogger()->err($message);
            $this->alertError($message);
            $this->setAlive(false);
            return;
        }
        
        $this->getLogger()->info("End of process");
        
        $this->setAlive(false);
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Minibus\Model\Process\DataTransfer\AbstractDataTransferAgent::hasConnection()
     */
    public function hasConnection()
    {
        return true;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Minibus\Model\Process\DataTransfer\AbstractDataTransferAgent::getConverter()
     */
    protected function getConverter()
    {
        return parent::getConverter();
    }

    /**
     *
     * @return void|\Doctrine\ORM\EntityRepository
     */
    public function getRecordRepository()
    {
        $elementCandidatsRepository = $this->getEntityManager()->getRepository('Jobs\Model\Entity\Record');
        
        return $elementCandidatsRepository;
    }
}