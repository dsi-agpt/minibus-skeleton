<?php
namespace Jobs\Model\Entity;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity
 */
class Record
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=250, nullable=false)
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=250, nullable=true)
     */
    private $primary_artist;

    /**
     * @ORM\Column(type="string", length=250, nullable=true)
     */
    private $record_label;

    /**
     * @ORM\Column(type="integer", length=8, nullable=true)
     */
    private $release_date;

    /**
     * @ORM\Column(type="integer", unique=true, length=16, nullable=false)
     */
    private $product_id;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return Record
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set primaryArtist
     *
     * @param string $primaryArtist
     *
     * @return Record
     */
    public function setPrimaryArtist($primaryArtist)
    {
        $this->primary_artist = $primaryArtist;

        return $this;
    }

    /**
     * Get primaryArtist
     *
     * @return string
     */
    public function getPrimaryArtist()
    {
        return $this->primary_artist;
    }

    /**
     * Set recordLabel
     *
     * @param string $recordLabel
     *
     * @return Record
     */
    public function setRecordLabel($recordLabel)
    {
        $this->record_label = $recordLabel;

        return $this;
    }

    /**
     * Get recordLabel
     *
     * @return string
     */
    public function getRecordLabel()
    {
        return $this->record_label;
    }

    /**
     * Set releaseDate
     *
     * @param integer $releaseDate
     *
     * @return Record
     */
    public function setReleaseDate($releaseDate)
    {
        $this->release_date = $releaseDate;

        return $this;
    }

    /**
     * Get releaseDate
     *
     * @return integer
     */
    public function getReleaseDate()
    {
        return $this->release_date;
    }

    /**
     * Set productId
     *
     * @param integer $productId
     *
     * @return Record
     */
    public function setProductId($productId)
    {
        $this->product_id = $productId;

        return $this;
    }

    /**
     * Get productId
     *
     * @return integer
     */
    public function getProductId()
    {
        return $this->product_id;
    }
}
