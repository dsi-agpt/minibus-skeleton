<?php
namespace Jobs\Model\Browse\Formatters\Foo\Bar;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Minibus\Model\Browse\Formatters\IFormatter;
use Doctrine\ORM\AbstractQuery;
use Minibus\Util\Encoding\ArrayEncoder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr\Join;

class DataTableFormatter implements ServiceLocatorAwareInterface, IFormatter
{
    use\Minibus\Util\Traits\ServiceLocatorAwareTrait;
    use\Minibus\Util\Traits\EntityManagerTrait;

    /**
     *
     * @var QueryBuilder
     */
    protected $queryBuilder;

    /**
     * (non-PHPdoc)
     *
     * @see \Minibus\Model\Browse\Formatters\IFormatter::getData()
     */
    public function getData(array $columns, $start, $length, array $order, array $search, $anneeScolaire)
    {
        $response = array();
        $this->createQuery();
        $this->addSearchCriterium($search, $columns);
        $this->addOrder($order, $columns);
        $paginator = $this->getPaginator($start, $length);
        $records = $paginator->getQuery()->getResult(AbstractQuery::HYDRATE_ARRAY);
        $response['data'] = $this->selectColumns($columns, $records);
        $response['recordsTotal'] = count($paginator);
        $response['recordsFiltered'] = count($paginator);
        return $response;
    }

    /**
     *
     * @param array $columns            
     * @param array $records            
     * @return array
     */
    private function selectColumns(array $columns, array $records)
    {
        $selected = array();
        for ($i = 0; $i < count($records); $i ++) {
            $record = $records[$i];
            $selected[$i] = array();
            foreach ($columns as $column) {
                $name = $column['name'];
                if (array_key_exists($name, $record)) {
                    $value = $record[$name];
                    $selected[$i][$column['data']] = $value;
                }
            }
        }
        return $selected;
    }

    /**
     *
     * @return void|\Doctrine\ORM\EntityRepository
     */
    private function getRecordRepository()
    {
        return $this->getEntityManager()->getRepository('Jobs\Model\Entity\Record');
    }

    /**
     */
    protected function createQuery()
    {
        $this->queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $this->queryBuilder->select('record')->from('\Jobs\Model\Entity\Record', 'record');
    }

    /**
     *
     * @param array $search            
     * @param array $columns            
     */
    public function addSearchCriterium(array $search, array $columns)
    {
        if (is_array($search) && array_key_exists('value', $search)) {
            $searchterm = $search['value'];
            if (! empty($searchterm)) {
                $clause = "";
                foreach ($columns as $column) {
                    if (! empty($clause))
                        $clause .= ' OR ';
                    $name = $column['name'];
                    $clause .= "record.$name LIKE :searchterm";
                }
                $this->queryBuilder->andWhere($clause);
                $this->queryBuilder->setParameter('searchterm', '%' . $searchterm . '%');
            }
        }
    }

    /**
     *
     * @param array $order            
     * @param array $columns            
     */
    public function addOrder(array $orders, array $columns)
    {
        for ($i = 0; $i < count($orders); $i ++) {
            $order = $orders[$i];
            if (array_key_exists('column', $order) && array_key_exists('dir', $order)) {
                $colNum = $order['column'];
                foreach ($columns as $column) {
                    if (array_key_exists('name', $column) && array_key_exists('data', $column) && intval($colNum) == intval($column['data'])) {
                        $name = $column['name'];
                        $clause = "record.$name";
                        $this->queryBuilder->addOrderBy($clause, $order['dir'] == 'asc' ? 'ASC' : 'DESC');
                    }
                }
            }
        }
    }

    /**
     *
     * @param int $start            
     * @param int $length            
     * @return \Doctrine\ORM\Tools\Pagination\Paginator
     */
    public function getPaginator($start, $length)
    {
        $query = $this->queryBuilder->setFirstResult($start)->setMaxResults($length);
        
        $paginator = new Paginator($query, $fetchJoinCollection = true);
        
        return $paginator;
    }
}